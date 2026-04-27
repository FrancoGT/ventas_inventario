<?php

namespace App\Controllers;

use App\Models\ProductoModel;
use App\Models\VentaModel;
use App\Models\EgresoModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $productoModel = new ProductoModel();
        $ventaModel    = new VentaModel();
        $egresoModel   = new EgresoModel();

        $fechaHoy = VentaModel::fechaActual();

        $data = [
            'titulo'          => 'Dashboard',
            'userData'        => $this->userData,
            'totalProductos'  => $productoModel->countActivos(),
            'ventasHoy'       => $ventaModel->totalVentasPorFecha($fechaHoy),
            'prendasHoy'      => $ventaModel->totalPrendasPorFecha($fechaHoy),
            'egresosHoy'      => $egresoModel->totalEgresosPorFecha($fechaHoy),
            'fechaHoy'        => $fechaHoy,
        ];

        return view('dashboard/index', $data);
    }
}