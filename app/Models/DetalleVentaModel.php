<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo para tbl_detalle_venta (backup.sql).
 *
 * Columnas reales:
 *   id_detalle_venta, id_venta, id_producto, cantidad,
 *   costo_venta, costo_delivery (heredada, SIEMPRE 0.00), status.
 *
 * IMPORTANTE: costo_delivery por línea es una columna heredada del
 * antiguo modelo (delivery por producto), ya corregido en backup.sql.
 * El delivery real de la venta se maneja como cargo único en
 * tbl_venta_delivery (ver VentaDeliveryModel). Esta clase NUNCA debe
 * escribir un valor distinto de 0.00 en costo_delivery.
 */
class DetalleVentaModel extends Model
{
    protected $table            = 'tbl_detalle_venta';
    protected $primaryKey       = 'id_detalle_venta';

    protected $allowedFields    = [
        'id_venta',
        'id_producto',
        'cantidad',
        'costo_venta',
        'costo_delivery',
        'status',
    ];

    protected $returnType       = 'object';
    protected $useTimestamps    = false;

    const ACTIVO   = 1;
    const INACTIVO = 0;

    // ----------------------------------------------------------------
    //  CRUD
    // ----------------------------------------------------------------

    /**
     * Insertar un detalle de venta. costo_delivery se fuerza a 0.00
     * porque el delivery ya no se maneja por producto.
     */
    public function guardar(array $data): int
    {
        if (!isset($data['status'])) {
            $data['status'] = self::ACTIVO;
        }

        $data['costo_delivery'] = 0.00;

        $this->insert($data);
        return (int) $this->getInsertID();
    }

    /**
     * Insertar múltiples detalles de una sola vez.
     *
     * @param  int   $idVenta
     * @param  array $productos  Cada elemento: [id_producto, cantidad, costo_venta]
     * @return int   Cantidad de filas insertadas
     */
    public function guardarLote(int $idVenta, array $productos): int
    {
        $batch = [];

        foreach ($productos as $p) {
            $batch[] = [
                'id_venta'       => $idVenta,
                'id_producto'    => $p['id_producto'],
                'cantidad'       => $p['cantidad'],
                'costo_venta'    => $p['costo_venta'],
                'costo_delivery' => 0.00, // el delivery ya no es por producto
                'status'         => self::ACTIVO,
            ];
        }

        return $this->insertBatch($batch);
    }

    /**
     * Anular un detalle.
     */
    public function anular(int $idDetalle): bool
    {
        return $this->update($idDetalle, ['status' => self::INACTIVO]);
    }

    /**
     * Anular todos los detalles de una venta.
     */
    public function anularPorVenta(int $idVenta): bool
    {
        return $this->where('id_venta', $idVenta)
                     ->set(['status' => self::INACTIVO])
                     ->update();
    }

    // ----------------------------------------------------------------
    //  CONSULTAS
    // ----------------------------------------------------------------

    /**
     * Obtener detalles activos de una venta.
     */
    public function getPorVenta(int $idVenta): array
    {
        return $this->where('id_venta', $idVenta)
                    ->where('status', self::ACTIVO)
                    ->findAll();
    }

    /**
     * Detalle con nombre de producto (requiere tbl_producto).
     */
    public function getDetallConProducto(int $idVenta): array
    {
        return $this->select('
                tbl_detalle_venta.*,
                p.nombre AS nombre_producto,
                p.codigo_barras
            ')
            ->join('tbl_producto AS p', 'p.id_producto = tbl_detalle_venta.id_producto')
            ->where('tbl_detalle_venta.id_venta', $idVenta)
            ->where('tbl_detalle_venta.status', self::ACTIVO)
            ->findAll();
    }

    /**
     * Subtotal de productos (sin delivery) de una venta activa.
     */
    public function subtotalProductos(int $idVenta): float
    {
        $result = $this->selectSum('cantidad * costo_venta', 'subtotal')
                       ->where('id_venta', $idVenta)
                       ->where('status', self::ACTIVO)
                       ->first();

        return (float) ($result->subtotal ?? 0);
    }

    /**
     * Total de prendas (unidades) de una venta activa.
     */
    public function totalCantidad(int $idVenta): int
    {
        $result = $this->selectSum('cantidad', 'total')
                       ->where('id_venta', $idVenta)
                       ->where('status', self::ACTIVO)
                       ->first();

        return (int) ($result->total ?? 0);
    }
}
