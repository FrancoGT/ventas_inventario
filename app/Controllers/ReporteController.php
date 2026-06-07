<?php

namespace App\Controllers;

use App\Models\VentaModel;
use App\Models\EgresoModel;

class ReporteController extends BaseController
{
    // ----------------------------------------------------------------
    //  Vistas
    // ----------------------------------------------------------------

    /**
     * Página principal del reporte de flujo de caja.
     * Muestra un formulario de filtro por rango de fechas.
     */
    public function index(): string
    {
        $data = [
            'titulo'   => 'Flujo de Caja',
            'userData' => $this->userData,
        ];

        return view('reportes/flujo_caja', $data);
    }

    // ----------------------------------------------------------------
    //  Endpoints JSON  (llamados vía fetch / AJAX)
    // ----------------------------------------------------------------

    /**
     * Devuelve el flujo de caja de HOY.
     *
     * GET /reporte/hoy
     */
    public function hoy(): \CodeIgniter\HTTP\ResponseInterface
    {
        $fecha = VentaModel::fechaActual();

        return $this->response->setJSON(
            $this->buildFlujoPorFecha($fecha)
        );
    }

    /**
     * Devuelve el flujo de caja de una FECHA específica.
     *
     * GET /reporte/porFecha?fecha=YYYY-MM-DD
     */
    public function porFecha(): \CodeIgniter\HTTP\ResponseInterface
    {
        $fecha = $this->request->getGet('fecha');

        if (!$this->esFechaValida($fecha)) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['error' => 'Fecha inválida. Use el formato YYYY-MM-DD.']);
        }

        return $this->response->setJSON(
            $this->buildFlujoPorFecha($fecha)
        );
    }

    /**
     * Devuelve el flujo de caja entre dos fechas (rango).
     *
     * GET /reporte/porRango?fecha_inicio=YYYY-MM-DD&fecha_fin=YYYY-MM-DD
     */
    public function porRango(): \CodeIgniter\HTTP\ResponseInterface
    {
        $fechaInicio = $this->request->getGet('fecha_inicio');
        $fechaFin    = $this->request->getGet('fecha_fin');

        if (!$this->esFechaValida($fechaInicio) || !$this->esFechaValida($fechaFin)) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['error' => 'Fechas inválidas. Use el formato YYYY-MM-DD.']);
        }

        if ($fechaInicio > $fechaFin) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['error' => 'La fecha de inicio no puede ser mayor a la fecha de fin.']);
        }

        return $this->response->setJSON(
            $this->buildFlujoEntreFechas($fechaInicio, $fechaFin)
        );
    }

    /**
     * Devuelve el flujo de caja de la SEMANA ACTUAL
     * (lunes → domingo de la semana en curso).
     *
     * GET /reporte/semanaActual
     */
    public function semanaActual(): \CodeIgniter\HTTP\ResponseInterface
    {
        $hoy         = VentaModel::fechaActual();
        $fechaInicio = VentaModel::getLunes($hoy);
        $fechaFin    = VentaModel::finDeSemana($fechaInicio);

        return $this->response->setJSON(
            $this->buildFlujoEntreFechas($fechaInicio, $fechaFin)
        );
    }

    /**
     * Devuelve el flujo de caja del MES ACTUAL.
     *
     * GET /reporte/mesActual
     */
    public function mesActual(): \CodeIgniter\HTTP\ResponseInterface
    {
        $hoy         = VentaModel::fechaActual();
        $fechaInicio = substr($hoy, 0, 7) . '-01';   // primer día del mes
        $fechaFin    = VentaModel::finDeMes($hoy);

        return $this->response->setJSON(
            $this->buildFlujoEntreFechas($fechaInicio, $fechaFin)
        );
    }

    // ----------------------------------------------------------------
    //  Helpers privados
    // ----------------------------------------------------------------

    /**
     * Construye el array de flujo de caja para UNA fecha.
     */
    private function buildFlujoPorFecha(string $fecha): array
    {
        $ventaModel  = new VentaModel();
        $egresoModel = new EgresoModel();

        $totalVentas  = $ventaModel->totalVentasPorFecha($fecha);
        $totalPrendas = $ventaModel->totalPrendasPorFecha($fecha);
        $totalEgresos = $egresoModel->totalEgresosPorFecha($fecha);
        $saldo        = $totalVentas - $totalEgresos;

        $detalleVentas  = $ventaModel->reportePorFecha($fecha);
        $detalleEgresos = $egresoModel->reportePorFecha($fecha);

        return [
            'periodo'        => $fecha,
            'total_ingresos' => $totalVentas,
            'total_prendas'  => $totalPrendas,
            'total_egresos'  => $totalEgresos,
            'saldo'          => $saldo,
            'detalle_ventas' => $detalleVentas,
            'detalle_egresos'=> $detalleEgresos,
        ];
    }

    /**
     * Construye el array de flujo de caja para un RANGO de fechas.
     * Incluye también el desglose diario para poder graficar.
     */
    private function buildFlujoEntreFechas(string $fechaInicio, string $fechaFin): array
    {
        $ventaModel  = new VentaModel();
        $egresoModel = new EgresoModel();

        $totalVentas  = $ventaModel->totalVentasEntreFechas($fechaInicio, $fechaFin);
        $totalPrendas = $ventaModel->totalPrendasEntreFechas($fechaInicio, $fechaFin);
        $totalEgresos = $egresoModel->totalEgresosEntreFechas($fechaInicio, $fechaFin);
        $saldo        = $totalVentas - $totalEgresos;

        $detalleVentas  = $ventaModel->reporteEntreFechas($fechaInicio, $fechaFin);
        $detalleEgresos = $egresoModel->reporteEntreFechas($fechaInicio, $fechaFin);

        // Desglose día a día (útil para gráficas)
        $desgloseDiario = $this->buildDesgloseDiario(
            $fechaInicio,
            $fechaFin,
            $detalleVentas,
            $detalleEgresos
        );

        return [
            'periodo'         => ['inicio' => $fechaInicio, 'fin' => $fechaFin],
            'total_ingresos'  => $totalVentas,
            'total_prendas'   => $totalPrendas,
            'total_egresos'   => $totalEgresos,
            'saldo'           => $saldo,
            'detalle_ventas'  => $detalleVentas,
            'detalle_egresos' => $detalleEgresos,
            'desglose_diario' => $desgloseDiario,
        ];
    }

    /**
     * Agrupa ventas y egresos por día dentro del rango,
     * generando una fila por cada fecha con sus totales.
     *
     * @param  array $ventas   Resultado de VentaModel::reporteEntreFechas()
     * @param  array $egresos  Resultado de EgresoModel::reporteEntreFechas()
     */
    private function buildDesgloseDiario(
        string $fechaInicio,
        string $fechaFin,
        array  $ventas,
        array  $egresos
    ): array {
        // Indexar ventas por fecha
        $ventasPorFecha = [];
        foreach ($ventas as $v) {
            $f = $v->fecha;
            if (!isset($ventasPorFecha[$f])) {
                $ventasPorFecha[$f] = ['ingresos' => 0.0, 'prendas' => 0];
            }
            $subtotal = VentaModel::subtotal(
                (float) $v->costo_venta,
                (float) $v->costo_delivery,
                (int)   $v->cantidad
            );
            $ventasPorFecha[$f]['ingresos'] += $subtotal;
            $ventasPorFecha[$f]['prendas']  += (int) $v->cantidad;
        }

        // Indexar egresos por fecha
        $egresosPorFecha = [];
        foreach ($egresos as $e) {
            $f = $e->fecha;
            if (!isset($egresosPorFecha[$f])) {
                $egresosPorFecha[$f] = 0.0;
            }
            $egresosPorFecha[$f] += (float) $e->compra_mercaderia;
        }

        // Iterar cada día del rango
        $desglose = [];
        $cursor   = new \DateTime($fechaInicio);
        $fin      = new \DateTime($fechaFin);
        $interval = new \DateInterval('P1D');

        while ($cursor <= $fin) {
            $fecha     = $cursor->format('Y-m-d');
            $ingresos  = $ventasPorFecha[$fecha]['ingresos'] ?? 0.0;
            $prendas   = $ventasPorFecha[$fecha]['prendas']  ?? 0;
            $egresos2  = $egresosPorFecha[$fecha]            ?? 0.0;

            $desglose[] = [
                'fecha'     => $fecha,
                'dia'       => VentaModel::getNombreDia($fecha),
                'ingresos'  => $ingresos,
                'prendas'   => $prendas,
                'egresos'   => $egresos2,
                'saldo_dia' => $ingresos - $egresos2,
            ];

            $cursor->add($interval);
        }

        return $desglose;
    }

    /**
     * Valida que el string sea una fecha real en formato YYYY-MM-DD.
     */
    private function esFechaValida(?string $fecha): bool
    {
        if (empty($fecha)) {
            return false;
        }

        $d = \DateTime::createFromFormat('Y-m-d', $fecha);
        return $d && $d->format('Y-m-d') === $fecha;
    }
}