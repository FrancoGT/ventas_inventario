<?php

namespace App\Models;

use CodeIgniter\Model;

class VentaModel extends Model
{
    protected $table            = 'tbl_venta';
    protected $primaryKey       = 'id_venta';

    protected $allowedFields    = [
        'fecha',
        'status',       // ← existente en la tabla
        // 'id_user',   // ✘ NO existe en tbl_venta; agrégalo solo si añades la columna
    ];

    protected $returnType       = 'object';
    protected $useTimestamps    = false;

    // Valor por defecto para registros activos
    const ACTIVO   = 1;
    const INACTIVO = 0;

    // ----------------------------------------------------------------
    //  LISTADOS
    // ----------------------------------------------------------------

    /**
     * Listado para DataTables (solo registros activos).
     */
    public function getParaDatatables(): array
    {
        return $this->select('id_venta, fecha, status')
                    ->where('status', self::ACTIVO)
                    ->orderBy('id_venta', 'DESC')
                    ->findAll();
    }

    /**
     * Fechas únicas de venta (solo activas).
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
     * Guardar venta y devolver ID.
     */
    public function guardar(array $data): int
    {
        // Asegurar que status se setee si no viene
        if (!isset($data['status'])) {
            $data['status'] = self::ACTIVO;
        }

        $this->insert($data);
        return (int) $this->getInsertID();
    }

    /**
     * Anular venta (soft delete cambiando status).
     */
    public function anular(int $idVenta): bool
    {
        return $this->update($idVenta, ['status' => self::INACTIVO]);
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
     * Reporte por una fecha con detalle.
     */
    public function reportePorFecha(string $fecha, array $filtros = []): array
    {
        [$sqlExtra, $extraBinds] = $this->buildFiltros($filtros);

        $db    = \Config\Database::connect();
        $query = $db->query(
            "SELECT v.id_venta, v.fecha, v.status AS venta_status,
                    d.id_detalle_venta, d.id_producto, d.cantidad,
                    d.costo_venta, d.costo_delivery, d.status AS detalle_status
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
     * Total de ventas (monto) en una fecha.
     */
    public function totalVentasPorFecha(string $fecha, array $filtros = []): float
    {
        [$sqlExtra, $extraBinds] = $this->buildFiltros($filtros);

        $db    = \Config\Database::connect();
        $query = $db->query(
            "SELECT COALESCE(SUM((d.costo_venta * d.cantidad) + d.costo_delivery), 0) AS total_ventas
             FROM tbl_venta AS v
             INNER JOIN tbl_detalle_venta AS d ON v.id_venta = d.id_venta
             WHERE v.fecha = ?
               AND v.status = ?
               AND d.status = ?" . $sqlExtra,
            array_merge([$fecha, self::ACTIVO, self::ACTIVO], $extraBinds)
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
                    d.costo_venta, d.costo_delivery, d.status AS detalle_status
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
     * Total ventas (monto) entre dos fechas.
     */
    public function totalVentasEntreFechas(string $fechaInicio, string $fechaFin, array $filtros = []): float
    {
        [$sqlExtra, $extraBinds] = $this->buildFiltros($filtros);

        $db    = \Config\Database::connect();
        $query = $db->query(
            "SELECT COALESCE(SUM((d.costo_venta * d.cantidad) + d.costo_delivery), 0) AS total_ventas
             FROM tbl_venta AS v
             INNER JOIN tbl_detalle_venta AS d ON v.id_venta = d.id_venta
             WHERE v.fecha BETWEEN ? AND ?
               AND v.status = ?
               AND d.status = ?" . $sqlExtra,
            array_merge([$fechaInicio, $fechaFin, self::ACTIVO, self::ACTIVO], $extraBinds)
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

    public static function subtotal(float $costoVenta, float $costoDelivery, int $cantidad): float
    {
        return ($costoVenta * $cantidad) + $costoDelivery;
    }
}