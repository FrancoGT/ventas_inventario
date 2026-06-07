<?php

namespace App\Models;

use CodeIgniter\Model;

class EgresoModel extends Model
{
    protected $table      = 'tbl_egreso';
    protected $primaryKey = 'id_egreso';

    protected $allowedFields = [
        'fecha',
        'compra_mercaderia',
        'flete',
        'descripcion',
        'status',
    ];

    protected $returnType    = 'object';
    protected $useTimestamps = false;

    public function getActivos(): array
    {
        return $this->where('status', 1)
                    ->orderBy('id_egreso', 'DESC')
                    ->findAll();
    }

    public function guardar(array $data): int
    {
        $this->insert([
            'fecha'             => $this->fechaActual(),
            'compra_mercaderia' => $data['compra_mercaderia'],
            'flete'             => $data['flete'],
            'descripcion'       => $data['descripcion'] ?? '',
            'status'            => 1,
        ]);

        return (int) $this->getInsertID();
    }

    public function actualizar(int $idEgreso, array $data): bool
    {
        return $this->update($idEgreso, $data);
    }

    public function eliminar(int $idEgreso): bool
    {
        return $this->update($idEgreso, ['status' => 0]);
    }

    public function fechaActual(): string
    {
        $date = new \DateTime('now', new \DateTimeZone('America/Lima'));
        return $date->format('Y-m-d');
    }
}