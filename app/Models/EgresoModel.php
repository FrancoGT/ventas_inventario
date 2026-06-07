<?php

namespace App\Models;

use CodeIgniter\Model;

class EgresoModel extends Model
{
    protected $table            = 'tbl_egreso';
    protected $primaryKey       = 'id_egreso';

    protected $allowedFields    = [
        'fecha',
        'compra_mercaderia',
        'flete',
        'descripcion',
        'status',
    ];

    protected $returnType       = 'object';
    protected $useTimestamps    = false;

    public function getParaDatatables(): array
    {
        return $this->where('status', 1)
                    ->orderBy('id_egreso', 'DESC')
                    ->findAll();
    }

    public function getActivos(): array
    {
        return $this->where('status', 1)
                    ->orderBy('id_egreso', 'DESC')
                    ->findAll();
    }

    public function guardar(array $postData): int
    {
        $this->insert([
            'fecha'             => $this->fechaActual(),
            'compra_mercaderia' => $postData['compra_mercaderia'],
            'flete'             => $postData['flete'],
            'descripcion'       => $postData['descripcion'] ?? '',
            'status'            => 1,
        ]);

        return (int) $this->getInsertID();
    }

    public function actualizar(int $idEgreso, array $data): bool
    {
        return $this->update($idEgreso, [
            'compra_mercaderia' => $data['compra_mercaderia'],
            'flete'             => $data['flete'],
            'descripcion'       => $data['descripcion'] ?? '',
        ]);
    }

    public function eliminar(int $idEgreso): bool
    {
        return $this->update($idEgreso, [
            'status' => 0,
        ]);
    }

    public function reportePorFecha(string $fecha): array
    {
        return $this->where('fecha', $fecha)
                    ->where('status', 1)
                    ->findAll();
    }

    public function totalEgresosPorFecha(string $fecha): float
    {
        $result = $this->selectSum('compra_mercaderia', 'total')
                       ->where('fecha', $fecha)
                       ->where('status', 1)
                       ->first();

        return (float) ($result->total ?? 0);
    }

    public function reporteEntreFechas(string $fechaInicio, string $fechaFin): array
    {
        return $this->where('fecha >=', $fechaInicio)
                    ->where('fecha <=', $fechaFin)
                    ->where('status', 1)
                    ->findAll();
    }

    public function totalEgresosEntreFechas(string $fechaInicio, string $fechaFin): float
    {
        $result = $this->selectSum('compra_mercaderia', 'total')
                       ->where('fecha >=', $fechaInicio)
                       ->where('fecha <=', $fechaFin)
                       ->where('status', 1)
                       ->first();

        return (float) ($result->total ?? 0);
    }

    public function fechaActual(): string
    {
        $date = new \DateTime('now', new \DateTimeZone('America/Lima'));
        return $date->format('Y-m-d');
    }
}