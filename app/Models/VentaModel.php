<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo para tbl_venta (backup.sql) — cabecera de venta.
 *
 * Columnas reales: id_venta, fecha, status.
 *
 * El resto de la información de negocio (cliente, delivery, método de
 * pago, comprobante/número comercial, stock, anulación, historial)
 * vive en tablas relacionadas y se maneja desde sus modelos dedicados:
 *   - VentaClienteModel     (tbl_venta_cliente)
 *   - VentaDeliveryModel    (tbl_venta_delivery)
 *   - VentaPagoModel        (tbl_venta_pago)
 *   - ComprobanteModel      (tbl_comprobante) + CorrelativoModel (tbl_correlativo)
 *   - VentaAnulacionModel   (tbl_venta_anulacion)
 *   - VentaHistorialModel   (tbl_venta_historial)
 *   - StockModel / StockMovimientoModel (tbl_stock, tbl_stock_movimiento)
 *
 * IMPORTANTE: el delivery ya NO se maneja por línea de producto.
 * costo_delivery en tbl_detalle_venta es una columna heredada que
 * siempre debe permanecer en 0.00; el único delivery válido de una
 * venta es el de tbl_venta_delivery (cargo único por venta completa).
 */
class VentaModel extends Model
{
    protected $table            = 'tbl_venta';
    protected $primaryKey       = 'id_venta';

    protected $allowedFields    = [
        'fecha',
        'status',
    ];

    protected $returnType       = 'object';
    protected $useTimestamps    = false;

    // Estados de tbl_venta.status (los únicos que contempla backup.sql)
    const ACTIVO   = 1;
    const INACTIVO = 0; // Venta ANULADA

    // ----------------------------------------------------------------
    //  LISTADOS
    // ----------------------------------------------------------------

    /**
     * Listado para DataTables. Incluye tanto activas como anuladas,
     * para que el panel de ventas pueda mostrar el estado real y
     * diferenciar las acciones disponibles.
     */
    public function getParaDatatables(): array
    {
        return $this->select('id_venta, fecha, status')
                    ->orderBy('id_venta', 'DESC')
                    ->findAll();
    }

    /**
     * Fechas únicas de venta (solo activas) — usado en reportes.
     */
    public function listaFechasVenta(): array
    {
        return $this->select('fecha')
                    ->where('status', self::ACTIVO)
                    ->distinct()
                    ->orderBy('fecha', 'DESC')
                    ->findAll();
    }

    // ----------------------------------------------------------------
    //  CRUD
    // ----------------------------------------------------------------

    /**
     * Guardar cabecera de venta y devolver ID.
     */
    public function guardar(array $data): int
    {
        if (!isset($data['status'])) {
            $data['status'] = self::ACTIVO;
        }

        $this->insert($data);
        return (int) $this->getInsertID();
    }

    /**
     * Anular venta (cambia status a INACTIVO). El Controller es
     * responsable de registrar el motivo (tbl_venta_anulacion), el
     * historial (tbl_venta_historial) y de reponer el stock.
     */
    public function anular(int $idVenta): bool
    {
        return $this->update($idVenta, ['status' => self::INACTIVO]);
    }

    /**
     * Indica si una venta está activa (no anulada).
     */
    public function estaActiva(int $idVenta): bool
    {
        $venta = $this->find($idVenta);
        return $venta && (int) $venta->status === self::ACTIVO;
    }

    // ----------------------------------------------------------------
    //  REPORTES  (sin concatenar strings crudos)
    // ----------------------------------------------------------------

    /**
     * Construye la condición WHERE adicional de forma segura.
     *
     * @param array $filtros  Ej: ['d.id_producto' => 5, 'v.status' => 1]
     * @return array  [string $sqlExtra, array $binds]
     */
    private function buildFiltros(array $filtros = []): array
    {
        $sqlExtra = '';
        $binds    = [];

        foreach ($filtros as $campo => $valor) {
            $sqlExtra .= " AND {$campo} = ?";
            $binds[]   = $valor;
        }

        return [$sqlExtra, $binds];
    }

    /**
     * Reporte por una fecha con detalle. El delivery ya NO se suma por
     * línea: se obtiene aparte, una vez por venta, desde
     * tbl_venta_delivery (ver ReporteController / VentaController).
     */
    public function reportePorFecha(string $fecha, array $filtros = []): array
    {
        [$sqlExtra, $extraBinds] = $this->buildFiltros($filtros);

        $db    = \Config\Database::connect();
        $query = $db->query(
            "SELECT v.id_venta, v.fecha, v.status AS venta_status,
                    d.id_detalle_venta, d.id_producto, d.cantidad,
                    d.costo_venta, d.status AS detalle_status
             FROM tbl_venta AS v
             INNER JOIN tbl_detalle_venta AS d ON v.id_venta = d.id_venta
             WHERE v.fecha = ?
               AND v.status = ?
               AND d.status = ?" . $sqlExtra,
            array_merge([$fecha, self::ACTIVO, self::ACTIVO], $extraBinds)
        );

        return $query->getResult();
    }

    /**
     * Total de prendas vendidas en una fecha.
     */
    public function totalPrendasPorFecha(string $fecha, array $filtros = []): int
    {
        [$sqlExtra, $extraBinds] = $this->buildFiltros($filtros);

        $db    = \Config\Database::connect();
        $query = $db->query(
            "SELECT COALESCE(SUM(d.cantidad), 0) AS total_prendas
             FROM tbl_venta AS v
             INNER JOIN tbl_detalle_venta AS d ON v.id_venta = d.id_venta
             WHERE v.fecha = ?
               AND v.status = ?
               AND d.status = ?" . $sqlExtra,
            array_merge([$fecha, self::ACTIVO, self::ACTIVO], $extraBinds)
        );

        return (int) $query->getRow()->total_prendas;
    }

    /**
     * Total de ventas (monto) en una fecha: subtotal de productos +
     * delivery único por venta (tbl_venta_delivery), sin duplicar el
     * delivery por cada línea de producto.
     */
    public function totalVentasPorFecha(string $fecha, array $filtros = []): float
    {
        [$sqlExtra, $extraBinds] = $this->buildFiltros($filtros);

        $db    = \Config\Database::connect();
        $query = $db->query(
            "SELECT
                COALESCE((
                    SELECT SUM(d.costo_venta * d.cantidad)
                    FROM tbl_detalle_venta d
                    INNER JOIN tbl_venta v2 ON v2.id_venta = d.id_venta
                    WHERE v2.fecha = ? AND v2.status = ? AND d.status = ?" . $sqlExtra . "
                ), 0)
                +
                COALESCE((
                    SELECT SUM(vd.costo_delivery)
                    FROM tbl_venta_delivery vd
                    INNER JOIN tbl_venta v3 ON v3.id_venta = vd.id_venta
                    WHERE v3.fecha = ? AND v3.status = ?
                ), 0) AS total_ventas",
            array_merge([$fecha, self::ACTIVO, self::ACTIVO], $extraBinds, [$fecha, self::ACTIVO])
        );

        return (float) $query->getRow()->total_ventas;
    }

    /**
     * Reporte entre dos fechas.
     */
    public function reporteEntreFechas(string $fechaInicio, string $fechaFin, array $filtros = []): array
    {
        [$sqlExtra, $extraBinds] = $this->buildFiltros($filtros);

        $db    = \Config\Database::connect();
        $query = $db->query(
            "SELECT v.id_venta, v.fecha, v.status AS venta_status,
                    d.id_detalle_venta, d.id_producto, d.cantidad,
                    d.costo_venta, d.status AS detalle_status
             FROM tbl_venta AS v
             INNER JOIN tbl_detalle_venta AS d ON v.id_venta = d.id_venta
             WHERE v.fecha BETWEEN ? AND ?
               AND v.status = ?
               AND d.status = ?" . $sqlExtra,
            array_merge([$fechaInicio, $fechaFin, self::ACTIVO, self::ACTIVO], $extraBinds)
        );

        return $query->getResult();
    }

    /**
     * Total prendas entre dos fechas.
     */
    public function totalPrendasEntreFechas(string $fechaInicio, string $fechaFin, array $filtros = []): int
    {
        [$sqlExtra, $extraBinds] = $this->buildFiltros($filtros);

        $db    = \Config\Database::connect();
        $query = $db->query(
            "SELECT COALESCE(SUM(d.cantidad), 0) AS total_prendas
             FROM tbl_venta AS v
             INNER JOIN tbl_detalle_venta AS d ON v.id_venta = d.id_venta
             WHERE v.fecha BETWEEN ? AND ?
               AND v.status = ?
               AND d.status = ?" . $sqlExtra,
            array_merge([$fechaInicio, $fechaFin, self::ACTIVO, self::ACTIVO], $extraBinds)
        );

        return (int) $query->getRow()->total_prendas;
    }

    /**
     * Total ventas (monto) entre dos fechas: subtotal de productos +
     * delivery único por venta.
     */
    public function totalVentasEntreFechas(string $fechaInicio, string $fechaFin, array $filtros = []): float
    {
        [$sqlExtra, $extraBinds] = $this->buildFiltros($filtros);

        $db    = \Config\Database::connect();
        $query = $db->query(
            "SELECT
                COALESCE((
                    SELECT SUM(d.costo_venta * d.cantidad)
                    FROM tbl_detalle_venta d
                    INNER JOIN tbl_venta v2 ON v2.id_venta = d.id_venta
                    WHERE v2.fecha BETWEEN ? AND ? AND v2.status = ? AND d.status = ?" . $sqlExtra . "
                ), 0)
                +
                COALESCE((
                    SELECT SUM(vd.costo_delivery)
                    FROM tbl_venta_delivery vd
                    INNER JOIN tbl_venta v3 ON v3.id_venta = vd.id_venta
                    WHERE v3.fecha BETWEEN ? AND ? AND v3.status = ?
                ), 0) AS total_ventas",
            array_merge(
                [$fechaInicio, $fechaFin, self::ACTIVO, self::ACTIVO],
                $extraBinds,
                [$fechaInicio, $fechaFin, self::ACTIVO]
            )
        );

        return (float) $query->getRow()->total_ventas;
    }

    // ----------------------------------------------------------------
    //  UTILIDADES DE FECHA (sin cambios, están correctas)
    // ----------------------------------------------------------------

    public static function fechaActual(): string
    {
        return (new \DateTime('now', new \DateTimeZone('America/Lima')))->format('Y-m-d');
    }

    public static function isDecimal($val): bool
    {
        return is_numeric($val) && floor($val) != $val;
    }

    public static function formatoNumerico($var): string
    {
        if (self::isDecimal($var)) {
            return number_format($var, 2, '.', '');
        }
        return number_format($var, 0, '.', '');
    }

    public static function diaAnterior(string $fecha, int $dias): string
    {
        return date('Y-m-d', strtotime($fecha . "- {$dias} days"));
    }

    public static function getNombreDia(string $fecha): string
    {
        $dias = ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'];
        return $dias[(int) date('w', strtotime($fecha))];
    }

    public static function getNombreMes(string $mes): string
    {
        $meses = [
            '01' => 'Enero',    '02' => 'Febrero',   '03' => 'Marzo',
            '04' => 'Abril',    '05' => 'Mayo',       '06' => 'Junio',
            '07' => 'Julio',    '08' => 'Agosto',     '09' => 'Septiembre',
            '10' => 'Octubre',  '11' => 'Noviembre',  '12' => 'Diciembre',
        ];
        return $meses[$mes] ?? '';
    }

    public static function getLunes(string $fecha): string
    {
        $offset = [
            'Domingo' => 6, 'Lunes' => 0, 'Martes' => 1, 'Miercoles' => 2,
            'Jueves'  => 3, 'Viernes' => 4, 'Sabado' => 5,
        ];
        $dia = self::getNombreDia($fecha);
        return self::diaAnterior($fecha, $offset[$dia]);
    }

    public function listaInicioSemana(): array
    {
        $fechas = $this->listaFechasVenta();
        $lunes  = [];
        foreach ($fechas as $fila) {
            $lunes[] = self::getLunes($fila->fecha);
        }
        return array_unique($lunes);
    }

    public static function finDeSemana(string $fecha): string
    {
        return date('Y-m-d', strtotime($fecha . '+ 6 days'));
    }

    public static function finDeMes(string $fecha): string
    {
        return date('Y-m-t', strtotime($fecha));
    }

    /**
     * Subtotal de UNA línea de producto (cantidad x precio unitario).
     * Ya NO incluye delivery: el delivery es un cargo único de la venta
     * completa (ver VentaDeliveryModel), no por línea de producto.
     */
    public static function subtotalLinea(float $costoVenta, int $cantidad): float
    {
        return $costoVenta * $cantidad;
    }

    /**
     * Compatibilidad retro: firma anterior con costoDelivery ignorado
     * (siempre 0 en la nueva estructura). Se mantiene para no romper
     * llamadas existentes, pero el cálculo correcto es subtotalLinea().
     */
    public static function subtotal(float $costoVenta, float $costoDelivery, int $cantidad): float
    {
        return ($costoVenta * $cantidad) + $costoDelivery;
    }
}
