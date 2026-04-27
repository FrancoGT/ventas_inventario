<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class TestDB extends BaseCommand
{
    protected $group       = 'Testing';
    protected $name        = 'test:db';
    protected $description = 'Prueba conexión a BD y modelos.';

    public function run(array $params)
    {
        try {
            $db = \Config\Database::connect();
            CLI::write('✅ Conexión a BD exitosa', 'green');

            $productoModel = new \App\Models\ProductoModel();
            $total = $productoModel->countActivos();
            CLI::write("✅ Productos activos: {$total}", 'green');

            $ventaModel = new \App\Models\VentaModel();
            CLI::write('✅ Fecha actual: ' . $ventaModel::fechaActual(), 'green');
            CLI::write('✅ Hoy es: ' . $ventaModel::getNombreDia($ventaModel::fechaActual()), 'green');

        } catch (\Exception $e) {
            CLI::write('❌ Error: ' . $e->getMessage(), 'red');
        }
    }
}