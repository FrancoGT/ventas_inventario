<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo para tbl_venta_historial (backup.sql).
 *
 * Columnas reales: id_historial, id_venta, id_user,
 *   accion ENUM('CREACION','EDICION','ANULACION'), detalle_cambio, fecha_accion.
 *
 * Traza de auditoría de cada venta: quién la creó, quién la editó y
 * quién la anuló, con fecha y detalle del cambio.
 */
class VentaHistorialModel extends Model
{
    protected $table         = 'tbl_venta_historial';
    protected $primaryKey    = 'id_historial';

    protected $allowedFields = [
        'id_venta',
        'id_user',
        'accion',
        'detalle_cambio',
        'fecha_accion',
    ];

    protected $returnType    = 'object';
    protected $useTimestamps = false;

    const ACCION_CREACION  = 'CREACION';
    const ACCION_EDICION   = 'EDICION';
    const ACCION_ANULACION = 'ANULACION';

    /**
     * Registra una entrada de historial para una venta.
     */
    public function registrar(int $idVenta, int $idUser, string $accion, ?string $detalleCambio = null): int
    {
        $this->insert([
            'id_venta'       => $idVenta,
            'id_user'        => $idUser,
            'accion'         => $accion,
            'detalle_cambio' => $detalleCambio,
            'fecha_accion'   => date('Y-m-d H:i:s'),
        ]);

        return (int) $this->getInsertID();
    }

    /**
     * Historial completo de una venta, más reciente primero.
     */
    public function getPorVenta(int $idVenta): array
    {
        return $this->select('tbl_venta_historial.*, u.nombres_apellidos AS usuario_nombre')
                    ->join('tbl_users AS u', 'u.id_user = tbl_venta_historial.id_user')
                    ->where('tbl_venta_historial.id_venta', $idVenta)
                    ->orderBy('tbl_venta_historial.id_historial', 'DESC')
                    ->findAll();
    }
}
