<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo para tbl_metodo_pago (backup.sql).
 *
 * Columnas reales: id_metodo_pago, nombre, status.
 * Datos semilla: Efectivo, Yape, Plin, Tarjeta, Transferencia.
 */
class MetodoPagoModel extends Model
{
    protected $table         = 'tbl_metodo_pago';
    protected $primaryKey    = 'id_metodo_pago';

    protected $allowedFields = [
        'nombre',
        'status',
    ];

    protected $returnType    = 'object';
    protected $useTimestamps = false;

    const ACTIVO   = 1;
    const INACTIVO = 0;

    /**
     * Métodos de pago activos (para selects en el formulario de venta).
     */
    public function getActivos(): array
    {
        return $this->where('status', self::ACTIVO)
                    ->orderBy('id_metodo_pago', 'ASC')
                    ->findAll();
    }

    /**
     * Verifica que el método de pago exista y esté activo.
     */
    public function esValido(int $idMetodoPago): bool
    {
        return $this->where('id_metodo_pago', $idMetodoPago)
                    ->where('status', self::ACTIVO)
                    ->countAllResults() > 0;
    }

    public function getNombre(int $idMetodoPago): string
    {
        $mp = $this->find($idMetodoPago);
        return $mp ? $mp->nombre : '';
    }
}
