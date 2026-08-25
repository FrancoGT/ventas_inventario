<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo para tbl_venta_anulacion (backup.sql).
 *
 * Columnas reales: id_anulacion, id_venta, id_user, motivo, fecha_anulacion.
 *
 * Registra el motivo, usuario y fecha de anulación de una venta.
 * La venta en sí NUNCA se elimina físicamente: solo cambia su
 * tbl_venta.status a INACTIVO (ver VentaModel::anular()).
 */
class VentaAnulacionModel extends Model
{
    protected $table         = 'tbl_venta_anulacion';
    protected $primaryKey    = 'id_anulacion';

    protected $allowedFields = [
        'id_venta',
        'id_user',
        'motivo',
        'fecha_anulacion',
    ];

    protected $returnType    = 'object';
    protected $useTimestamps = false;

    /**
     * Registra la anulación de una venta.
     */
    public function registrar(int $idVenta, int $idUser, string $motivo): int
    {
        $this->insert([
            'id_venta'        => $idVenta,
            'id_user'         => $idUser,
            'motivo'          => $motivo,
            'fecha_anulacion' => date('Y-m-d H:i:s'),
        ]);

        return (int) $this->getInsertID();
    }

    /**
     * Obtiene el registro de anulación de una venta (si existe).
     */
    public function getPorVenta(int $idVenta): ?object
    {
        return $this->select('tbl_venta_anulacion.*, u.nombres_apellidos AS usuario_nombre')
                    ->join('tbl_users AS u', 'u.id_user = tbl_venta_anulacion.id_user')
                    ->where('tbl_venta_anulacion.id_venta', $idVenta)
                    ->orderBy('tbl_venta_anulacion.id_anulacion', 'DESC')
                    ->first();
    }
}
