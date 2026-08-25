<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo para tbl_stock_movimiento (backup.sql).
 *
 * Columnas reales:
 *   id_movimiento, id_producto, tipo_movimiento ENUM('INGRESO','SALIDA','AJUSTE'),
 *   cantidad, id_venta (nullable), motivo, fecha.
 *
 * Registra la trazabilidad de cada cambio de stock. Toda salida generada
 * por una venta debe ir vinculada a id_venta; toda reversión por
 * anulación se registra como un nuevo movimiento de tipo INGRESO (no se
 * borra el movimiento original, para mantener el historial).
 */
class StockMovimientoModel extends Model
{
    protected $table         = 'tbl_stock_movimiento';
    protected $primaryKey    = 'id_movimiento';

    protected $allowedFields = [
        'id_producto',
        'tipo_movimiento',
        'cantidad',
        'id_venta',
        'motivo',
        'fecha',
    ];

    protected $returnType    = 'object';
    protected $useTimestamps = false;

    const INGRESO = 'INGRESO';
    const SALIDA  = 'SALIDA';
    const AJUSTE  = 'AJUSTE';

    /**
     * Registra una SALIDA de stock asociada a una venta.
     */
    public function registrarSalidaPorVenta(int $idProducto, int $cantidad, int $idVenta, string $motivo = 'Venta'): int
    {
        $this->insert([
            'id_producto'     => $idProducto,
            'tipo_movimiento' => self::SALIDA,
            'cantidad'        => $cantidad,
            'id_venta'        => $idVenta,
            'motivo'          => $motivo,
            'fecha'           => date('Y-m-d H:i:s'),
        ]);

        return (int) $this->getInsertID();
    }

    /**
     * Registra un INGRESO de stock por reversión de una venta anulada.
     */
    public function registrarIngresoPorAnulacion(int $idProducto, int $cantidad, int $idVenta, string $motivo = 'Reversión por anulación de venta'): int
    {
        $this->insert([
            'id_producto'     => $idProducto,
            'tipo_movimiento' => self::INGRESO,
            'cantidad'        => $cantidad,
            'id_venta'        => $idVenta,
            'motivo'          => $motivo,
            'fecha'           => date('Y-m-d H:i:s'),
        ]);

        return (int) $this->getInsertID();
    }

    /**
     * Registra un movimiento manual de ajuste/ingreso (sin venta asociada).
     */
    public function registrarAjuste(int $idProducto, string $tipo, int $cantidad, ?string $motivo = null): int
    {
        $this->insert([
            'id_producto'     => $idProducto,
            'tipo_movimiento' => $tipo,
            'cantidad'        => $cantidad,
            'id_venta'        => null,
            'motivo'          => $motivo,
            'fecha'           => date('Y-m-d H:i:s'),
        ]);

        return (int) $this->getInsertID();
    }

    /**
     * Movimientos asociados a una venta (para auditoría/detalle).
     */
    public function getPorVenta(int $idVenta): array
    {
        return $this->where('id_venta', $idVenta)
                    ->orderBy('id_movimiento', 'ASC')
                    ->findAll();
    }

    /**
     * Historial de movimientos de un producto.
     */
    public function getPorProducto(int $idProducto): array
    {
        return $this->where('id_producto', $idProducto)
                    ->orderBy('fecha', 'DESC')
                    ->findAll();
    }
}
