<?php

namespace App\Controllers;

use App\Models\VentaModel;
use App\Models\EgresoModel;

class ReporteController extends BaseController
{
    protected VentaModel  $ventaModel;
    protected EgresoModel $egresoModel;

    public function __construct()
    {
        $this->ventaModel  = new VentaModel();
        $this->egresoModel = new EgresoModel();
    }

    /**
     * Vista principal de reportes.
     */
    public function index()
    {
        $data = [
            'titulo'        => 'Reportes',
            'userData'       => $this->userData,
            'fechasVenta'    => $this->ventaModel->listaFechasVenta(),
            'iniciosSemana'  => $this->ventaModel->listaInicioSemana(),
        ];

        return view('reportes/index', $data);
    }

    /**
     * AJAX: Reporte por una fecha.
     */
    public function porFecha()
    {
        if (!$this->request->isAJAX()) {
            return $this->jsonError('Acceso no permitido', 403);
        }

        $fecha = $this->request->getPost('fecha');

        if (empty($fecha)) {
            return $this->jsonError('Debe seleccionar una fecha.');
        }

        $data = [
            'fecha'         => $fecha,
            'dia'           => VentaModel::getNombreDia($fecha),
            'total_ventas'  => VentaModel::formatoNumerico(
                $this->ventaModel->totalVentasPorFecha($fecha)
            ),
            'total_prendas' => $this->ventaModel->totalPrendasPorFecha($fecha),
            'total_egresos' => VentaModel::formatoNumerico(
                $this->egresoModel->totalEgresosPorFecha($fecha)
            ),
            'detalle_ventas'  => $this->ventaModel->reportePorFecha($fecha),
            'detalle_egresos' => $this->egresoModel->reportePorFecha($fecha),
        ];

        return $this->jsonSuccess('OK', $data);
    }

    /**
     * AJAX: Reporte entre dos fechas.
     */
    public function entreFechas()
    {
        if (!$this->request->isAJAX()) {
            return $this->jsonError('Acceso no permitido', 403);
        }

        $fechaInicio = $this->request->getPost('fecha_inicio');
        $fechaFin    = $this->request->getPost('fecha_fin');

        if (empty($fechaInicio) || empty($fechaFin)) {
            return $this->jsonError('Debe seleccionar ambas fechas.');
        }

        if ($fechaInicio > $fechaFin) {
            return $this->jsonError('La fecha de inicio no puede ser mayor a la fecha fin.');
        }

        $data = [
            'fecha_inicio'  => $fechaInicio,
            'fecha_fin'     => $fechaFin,
            'total_ventas'  => VentaModel::formatoNumerico(
                $this->ventaModel->totalVentasEntreFechas($fechaInicio, $fechaFin)
            ),
            'total_prendas' => $this->ventaModel->totalPrendasEntreFechas($fechaInicio, $fechaFin),
            'total_egresos' => VentaModel::formatoNumerico(
                $this->egresoModel->totalEgresosEntreFechas($fechaInicio, $fechaFin)
            ),
        ];

        return $this->jsonSuccess('OK', $data);
    }
}