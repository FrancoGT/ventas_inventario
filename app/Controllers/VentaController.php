<?php

namespace App\Controllers;

use App\Models\VentaModel;
use App\Models\DetalleVentaModel;
use App\Models\ProductoModel;

class VentaController extends BaseController
{
    protected VentaModel        $ventaModel;
    protected DetalleVentaModel $detalleModel;

    public function __construct()
    {
        $this->ventaModel   = new VentaModel();
        $this->detalleModel = new DetalleVentaModel();
    }

    // ----------------------------------------------------------------
    //  VISTAS
    // ----------------------------------------------------------------

    /**
     * Vista principal de ventas.
     */
    public function index()
    {
        $productoModel = new ProductoModel();

        return view('ventas/index', [
            'titulo'    => 'Ventas',
            'userData'  => $this->userData,
            'productos' => $productoModel->getActivos(),
        ]);
    }

    // ----------------------------------------------------------------
    //  AJAX: LISTAR
    // ----------------------------------------------------------------

    /**
     * AJAX: Listar ventas para DataTables.
     */
    public function listar()
    {
        if (!$this->request->isAJAX()) {
            return $this->jsonError('Acceso no permitido', 403);
        }

        $ventas = $this->ventaModel->getParaDatatables();
        $data   = [];

        foreach ($ventas as $venta) {
            $detalles = $this->detalleModel->getPorVenta($venta->id_venta);
            $total    = 0;
            $prendas  = 0;

            foreach ($detalles as $d) {
                $total   += VentaModel::subtotal(
                    (float) $d->costo_venta,
                    (float) $d->costo_delivery,
                    (int)   $d->cantidad
                );
                $prendas += (int) $d->cantidad;
            }

            $data[] = [
                'id_venta' => $venta->id_venta,
                'fecha'    => $venta->fecha,
                'prendas'  => $prendas,
                'total'    => VentaModel::formatoNumerico($total),
                'acciones' => '
                    <button class="btn btn-sm btn-info btn-detalle" 
                            data-id="' . $venta->id_venta . '">
                        <i class="fas fa-eye"></i> Ver
                    </button>',
            ];
        }

        // ✅ Total real de registros activos
        $totalRegistros = $this->ventaModel
                               ->where('status', VentaModel::ACTIVO)
                               ->countAllResults();

        return $this->response->setJSON([
            'data'            => $data,
            'recordsTotal'    => $totalRegistros,
            'recordsFiltered' => count($data),
        ]);
    }

    // ----------------------------------------------------------------
    //  AJAX: GUARDAR
    // ----------------------------------------------------------------

    /**
     * AJAX: Guardar venta con su detalle.
     */
    public function guardar()
    {
        if (!$this->request->isAJAX()) {
            return $this->jsonError('Acceso no permitido', 403);
        }

        // ── Recibir datos ──
        $productos      = $this->request->getPost('productos');
        $cantidades     = $this->request->getPost('cantidades');
        $costosVenta    = $this->request->getPost('costos_venta');
        $costosDelivery = $this->request->getPost('costos_delivery');

        // ── Validar que hay al menos un producto ──
        if (empty($productos) || !is_array($productos)) {
            return $this->jsonError('Debe agregar al menos un producto a la venta.');
        }

        // ── Validar arrays del mismo tamaño ──
        $count = count($productos);
        if (
            count($cantidades)     !== $count ||
            count($costosVenta)    !== $count ||
            count($costosDelivery) !== $count
        ) {
            return $this->jsonError('Datos de productos incompletos.');
        }

        // ── Validar cada línea de detalle ──
        for ($i = 0; $i < $count; $i++) {
            $cant = (int) $cantidades[$i];
            $cv   = (float) $costosVenta[$i];
            $cd   = (float) $costosDelivery[$i];

            if ($cant <= 0) {
                return $this->jsonError("La cantidad del producto #" . ($i + 1) . " debe ser mayor a 0.");
            }
            if ($cv < 0) {
                return $this->jsonError("El costo de venta del producto #" . ($i + 1) . " no puede ser negativo.");
            }
            if ($cd < 0) {
                return $this->jsonError("El costo de delivery del producto #" . ($i + 1) . " no puede ser negativo.");
            }
        }

        // ── Transacción ──
        $db = \Config\Database::connect();
        $db->transBegin();  // ✅ Usar transBegin (manual) para control con try/catch

        try {
            // 1. Cabecera — SIN id_user (no existe en la tabla)
            $idVenta = $this->ventaModel->guardar([
                'fecha'  => VentaModel::fechaActual(),
                'status' => VentaModel::ACTIVO,
            ]);

            if (!$idVenta) {
                throw new \RuntimeException('No se pudo crear la cabecera de venta.');
            }

            // 2. Detalle — línea por línea
            for ($i = 0; $i < $count; $i++) {
                $this->detalleModel->guardar([
                    'id_venta'       => $idVenta,
                    'id_producto'    => (int)   $productos[$i],
                    'cantidad'       => (int)   $cantidades[$i],
                    'costo_venta'    => (float) $costosVenta[$i],
                    'costo_delivery' => (float) $costosDelivery[$i],
                    'status'         => DetalleVentaModel::ACTIVO,
                ]);
            }

            // 3. Verificar estado de la transacción
            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->jsonError('Error al guardar la venta.');
            }

            $db->transCommit();

            return $this->jsonSuccess('Venta registrada correctamente.', [
                'id_venta' => $idVenta,
            ]);

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error guardando venta: ' . $e->getMessage());
            return $this->jsonError('Error interno al guardar la venta.');
        }
    }

    // ----------------------------------------------------------------
    //  AJAX: DETALLE
    // ----------------------------------------------------------------

    /**
     * AJAX: Detalle de una venta.
     */
    public function detalle(int $idVenta)
    {
        if (!$this->request->isAJAX()) {
            return $this->jsonError('Acceso no permitido', 403);
        }

        // ✅ Verificar que exista Y esté activa (CI4: where+find no aplica el where; usar where+first)
        $venta = $this->ventaModel
                      ->where('id_venta', $idVenta)
                      ->where('status', VentaModel::ACTIVO)
                      ->first();

        if (!$venta) {
            return $this->jsonError('Venta no encontrada.', 404);
        }

        // JOIN directo con nombres reales del esquema (tbl_producto.nombre)
        $db       = \Config\Database::connect();
        $detalles = $db->query(
            "SELECT d.cantidad, d.costo_venta, d.costo_delivery,
                    p.nombre AS nombre_producto
             FROM tbl_detalle_venta AS d
             INNER JOIN tbl_producto AS p ON d.id_producto = p.id_producto
             WHERE d.id_venta = ?
               AND d.status = ?",
            [$idVenta, 1]
        )->getResult();

        $total = 0;
        $items = [];

        foreach ($detalles as $d) {
            $subtotal = VentaModel::subtotal(
                (float) $d->costo_venta,
                (float) $d->costo_delivery,
                (int)   $d->cantidad
            );
            $total += $subtotal;

            $items[] = [
                'nombre'         => $d->nombre_producto,
                'cantidad'       => (int) $d->cantidad,
                'costo_venta'    => VentaModel::formatoNumerico($d->costo_venta),
                'costo_delivery' => VentaModel::formatoNumerico($d->costo_delivery),
                'subtotal'       => VentaModel::formatoNumerico($subtotal),
            ];
        }

        return $this->jsonSuccess('OK', [
            'venta' => [
                'id_venta' => $venta->id_venta,
                'fecha'    => $venta->fecha,
            ],
            'detalle' => $items,
            'total'   => VentaModel::formatoNumerico($total),
        ]);
    }
}