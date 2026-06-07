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

    // ----------------------------------------------------------------
    //  LISTADOS
    // ----------------------------------------------------------------

    /**
     * Listado para DataTables.
     */
    public function getParaDatatables(): array
    {
        return $this->select('id_egreso, fecha, compra_mercaderia, flete, descripcion')
                    ->orderBy('id_egreso', 'DESC')
                    ->findAll();
    }

    // ----------------------------------------------------------------
    //  GUARDAR
    // ----------------------------------------------------------------

    /**
     * Registra un nuevo egreso con la fecha actual.
     */
    public function guardar(array $postData): int
    {
        $this->insert([
            'fecha'             => $this->fechaActual(),
            'compra_mercaderia' => $postData['compra_mercaderia'],
            'flete'             => $postData['flete'],
            'descripcion'       => $postData['descripcion'],
            'status'            => 1,
        ]);

        return (int) $this->getInsertID();
    }

    // ----------------------------------------------------------------
    //  REPORTES
    // ----------------------------------------------------------------

    /**
     * Egresos de una fecha específica.
     */
    public function reportePorFecha(string $fecha): array
    {
        return $this->where('fecha', $fecha)->findAll();
    }

    /**
     * Total de compra_mercaderia en una fecha (usando SQL).
     */
    public function totalEgresosPorFecha(string $fecha): float
    {
        $result = $this->selectSum('compra_mercaderia', 'total')
                       ->where('fecha', $fecha)
                       ->first();

        return (float) ($result->total ?? 0);
    }

    /**
     * Egresos entre dos fechas.
     */
    public function reporteEntreFechas(string $fechaInicio, string $fechaFin): array
    {
        return $this->where('fecha >=', $fechaInicio)
                    ->where('fecha <=', $fechaFin)
                    ->findAll();
    }

    /**
     * Total de compra_mercaderia entre dos fechas (usando SQL).
     */
    public function totalEgresosEntreFechas(string $fechaInicio, string $fechaFin): float
    {
        $result = $this->selectSum('compra_mercaderia', 'total')
                       ->where('fecha >=', $fechaInicio)
                       ->where('fecha <=', $fechaFin)
                       ->first();

        return (float) ($result->total ?? 0);
    }

    // ----------------------------------------------------------------
    //  UTILIDADES
    // ----------------------------------------------------------------

    /**
     * Fecha actual en zona horaria de Lima.
     */
    public function fechaActual(): string
    {
        $date = new \DateTime('now', new \DateTimeZone('America/Lima'));
        return $date->format('Y-m-d');
    }
}