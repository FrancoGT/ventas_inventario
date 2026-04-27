<?php

namespace App\Models;

use CodeIgniter\Model;

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
     * Insertar un detalle de venta.
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
     * Insertar múltiples detalles de una sola vez.
     *
     * @param  int   $idVenta
     * @param  array $productos  Cada elemento: [id_producto, cantidad, costo_venta, costo_delivery]
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
                'costo_delivery' => $p['costo_delivery'] ?? 0.00,
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
                p.nombre AS nombre_producto
            ')
            ->join('tbl_producto AS p', 'p.id_producto = tbl_detalle_venta.id_producto')
            ->where('tbl_detalle_venta.id_venta', $idVenta)
            ->where('tbl_detalle_venta.status', self::ACTIVO)
            ->findAll();
    }
}