<?php

namespace App\Controllers;

use App\Models\VentaModel;
use App\Models\DetalleVentaModel;
use App\Models\ProductoModel;
use App\Models\ClienteModel;
use App\Models\MetodoPagoModel;
use App\Models\StockModel;
use App\Models\StockMovimientoModel;
use App\Models\CorrelativoModel;
use App\Models\ComprobanteModel;
use App\Models\VentaClienteModel;
use App\Models\VentaDeliveryModel;
use App\Models\VentaPagoModel;
use App\Models\VentaAnulacionModel;
use App\Models\VentaHistorialModel;

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

    public function index()
    {
        $productoModel  = new ProductoModel();
        $clienteModel   = new ClienteModel();
        $metodoPagoModel = new MetodoPagoModel();
        $stockModel     = new StockModel();

        $mapaStock = $stockModel->getMapaStock();
        $productos = $productoModel->getActivos();

        // Adjuntar stock_actual a cada producto para la UI
        foreach ($productos as $p) {
            $p->stock_actual = isset($mapaStock[$p->id_producto])
                ? (int) $mapaStock[$p->id_producto]->stock_actual
                : 0;
        }

        return view('ventas/index', [
            'titulo'          => 'Ventas',
            'userData'        => $this->userData,
            'productos'       => $productos,
            'clientes'        => $clienteModel->getActivos(),
            'metodosPago'     => $metodoPagoModel->getActivos(),
        ]);
    }

    // ----------------------------------------------------------------
    //  AJAX: LISTAR
    // ----------------------------------------------------------------

    public function listar()
    {
        if (!$this->request->isAJAX()) {
            return $this->jsonError('Acceso no permitido', 403);
        }

        $ventas = $this->ventaModel->getParaDatatables();

        $idsVenta = array_map(fn($v) => (int) $v->id_venta, $ventas);

        $comprobanteModel   = new ComprobanteModel();
        $ventaClienteModel  = new VentaClienteModel();
        $ventaDeliveryModel = new VentaDeliveryModel();
        $ventaPagoModel     = new VentaPagoModel();

        $numerosComerciales = $comprobanteModel->getNumerosComercialesPorVentas($idsVenta);
        $clientesPorVenta   = $ventaClienteModel->getClientesPorVentas($idsVenta);
        $deliveryPorVenta   = $ventaDeliveryModel->getCostosPorVentas($idsVenta);
        $metodosPorVenta    = $ventaPagoModel->getMetodosPorVentas($idsVenta);

        $data = [];

        foreach ($ventas as $venta) {
            $idVenta  = (int) $venta->id_venta;
            $detalles = $this->detalleModel->getPorVenta($idVenta);

            $subtotal = 0.0;
            $prendas  = 0;
            foreach ($detalles as $d) {
                $subtotal += VentaModel::subtotalLinea((float) $d->costo_venta, (int) $d->cantidad);
                $prendas  += (int) $d->cantidad;
            }

            $delivery = $deliveryPorVenta[$idVenta] ?? 0.0;
            $total    = $subtotal + $delivery;

            $esActiva = (int) $venta->status === VentaModel::ACTIVO;

            $cliente = $clientesPorVenta[$idVenta] ?? null;
            $nombreCliente = $cliente ? $cliente->nombres_apellidos : 'Publico General';

            $numeroComercial = $numerosComerciales[$idVenta] ?? ('#' . $idVenta);
            $metodoPago      = $metodosPorVenta[$idVenta] ?? '—';

            $acciones = '<button class="btn btn-sm btn-info text-white btn-detalle" data-id="' . $idVenta . '">
                            <i class="fas fa-eye"></i> Ver
                         </button> ';

            $acciones .= '<a class="btn btn-sm btn-secondary" target="_blank" href="'
                . base_url('ventas/comprobante/' . $idVenta) . '">
                            <i class="fas fa-print"></i> Imprimir
                         </a> ';

            if ($esActiva) {
                $acciones .= '<button class="btn btn-sm btn-danger btn-anular" data-id="' . $idVenta . '">
                                <i class="fas fa-ban"></i> Anular
                             </button>';
            }

            $data[] = [
                'id_venta'         => $idVenta,
                'numero_comercial' => $numeroComercial,
                'fecha'            => $venta->fecha,
                'cliente'          => esc($nombreCliente),
                'prendas'          => $prendas,
                'total'            => VentaModel::formatoNumerico($total),
                'metodo_pago'      => esc($metodoPago),
                'estado'           => $esActiva
                    ? '<span class="badge bg-success">ACTIVA</span>'
                    : '<span class="badge bg-danger">ANULADA</span>',
                'acciones'         => $acciones,
            ];
        }

        $totalRegistros = $this->ventaModel->countAllResults();

        return $this->response->setJSON([
            'data'            => $data,
            'recordsTotal'    => $totalRegistros,
            'recordsFiltered' => count($data),
        ]);
    }

    // ----------------------------------------------------------------
    //  AJAX: GUARDAR
    // ----------------------------------------------------------------

    public function guardar()
    {
        if (!$this->request->isAJAX()) {
            return $this->jsonError('Acceso no permitido', 403);
        }

        $idCliente       = (int) ($this->request->getPost('id_cliente') ?: \App\Models\ClienteModel::CLIENTE_GENERICO);
        $idMetodoPago    = (int) $this->request->getPost('id_metodo_pago');
        $costoDelivery   = (float) ($this->request->getPost('costo_delivery') ?: 0);
        $direccionEntrega = $this->request->getPost('direccion_entrega');

        $productos   = $this->request->getPost('productos');
        $cantidades  = $this->request->getPost('cantidades');
        $costosVenta = $this->request->getPost('costos_venta');

        if (empty($productos) || !is_array($productos)) {
            return $this->jsonError('Debe agregar al menos un producto a la venta.');
        }

        $count = count($productos);
        if (count($cantidades) !== $count || count($costosVenta) !== $count) {
            return $this->jsonError('Datos de productos incompletos.');
        }

        $metodoPagoModel = new MetodoPagoModel();
        if (!$idMetodoPago || !$metodoPagoModel->esValido($idMetodoPago)) {
            return $this->jsonError('Seleccione un método de pago válido.');
        }

        $stockModel = new StockModel();
        $lineas = [];

        for ($i = 0; $i < $count; $i++) {
            $idProducto = (int) $productos[$i];
            $cant       = (int) $cantidades[$i];
            $cv         = (float) $costosVenta[$i];

            if ($cant <= 0) {
                return $this->jsonError('La cantidad del producto #' . ($i + 1) . ' debe ser mayor a 0.');
            }
            if ($cv < 0) {
                return $this->jsonError('El precio del producto #' . ($i + 1) . ' no puede ser negativo.');
            }

            // Validación de stock en backend (no solo en frontend)
            $stockActual = $stockModel->getStockActual($idProducto);
            if ($stockActual < $cant) {
                return $this->jsonError(
                    'Stock insuficiente para el producto #' . ($i + 1) .
                    ". Disponible: {$stockActual}, solicitado: {$cant}."
                );
            }

            $lineas[] = ['id_producto' => $idProducto, 'cantidad' => $cant, 'costo_venta' => $cv];
        }

        if ($costoDelivery < 0) {
            return $this->jsonError('El costo de delivery no puede ser negativo.');
        }

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            $idUser = (int) ($this->userData['user_id'] ?? 0);

            // 1. Cabecera de venta
            $idVenta = $this->ventaModel->guardar([
                'fecha'  => VentaModel::fechaActual(),
                'status' => VentaModel::ACTIVO,
            ]);

            if (!$idVenta) {
                throw new \RuntimeException('No se pudo crear la cabecera de venta.');
            }

            // 2. Detalle de productos + descuento de stock
            $stockMovModel = new StockMovimientoModel();

            foreach ($lineas as $linea) {
                $this->detalleModel->guardar([
                    'id_venta'    => $idVenta,
                    'id_producto' => $linea['id_producto'],
                    'cantidad'    => $linea['cantidad'],
                    'costo_venta' => $linea['costo_venta'],
                    'status'      => DetalleVentaModel::ACTIVO,
                ]);

                // Re-verificar stock dentro de la transacción para evitar carreras
                if (!$stockModel->haySuficiente($linea['id_producto'], $linea['cantidad'])) {
                    throw new \RuntimeException('Stock insuficiente para el producto ID ' . $linea['id_producto'] . '.');
                }

                $stockModel->descontar($linea['id_producto'], $linea['cantidad']);
                $stockMovModel->registrarSalidaPorVenta($linea['id_producto'], $linea['cantidad'], $idVenta);
            }

            // 3. Cliente de la venta (por defecto: Publico General)
            (new VentaClienteModel())->asociar($idVenta, $idCliente);

            // 4. Delivery de la venta completa (cargo único, no por producto)
            (new VentaDeliveryModel())->guardar($idVenta, $costoDelivery, $direccionEntrega ?: null);

            // 5. Método de pago
            $subtotal = 0.0;
            foreach ($lineas as $linea) {
                $subtotal += VentaModel::subtotalLinea($linea['costo_venta'], $linea['cantidad']);
            }
            $totalVenta = $subtotal + $costoDelivery;
            (new VentaPagoModel())->guardar($idVenta, $idMetodoPago, $totalVenta);

            // 6. Número comercial (correlativo + comprobante)
            $correlativoModel = new CorrelativoModel();
            $numero = $correlativoModel->siguienteNumero(CorrelativoModel::SERIE_DEFAULT);
            (new ComprobanteModel())->emitir($idVenta, $numero, CorrelativoModel::SERIE_DEFAULT, ComprobanteModel::TIPO_BOLETA);

            // 7. Historial de auditoría
            if ($idUser > 0) {
                (new VentaHistorialModel())->registrar(
                    $idVenta,
                    $idUser,
                    VentaHistorialModel::ACCION_CREACION,
                    'Venta registrada con ' . $count . ' producto(s). Total: S/ ' . number_format($totalVenta, 2)
                );
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->jsonError('Error al guardar la venta.');
            }

            $db->transCommit();

            return $this->jsonSuccess('Venta registrada correctamente.', [
                'id_venta'         => $idVenta,
                'numero_comercial' => CorrelativoModel::formatear(CorrelativoModel::SERIE_DEFAULT, $numero),
            ]);

        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Error guardando venta: ' . $e->getMessage());
            return $this->jsonError($e->getMessage() ?: 'Error interno al guardar la venta.');
        }
    }

    // ----------------------------------------------------------------
    //  AJAX: DETALLE
    // ----------------------------------------------------------------

    public function detalle(int $idVenta)
    {
        if (!$this->request->isAJAX()) {
            return $this->jsonError('Acceso no permitido', 403);
        }

        $venta = $this->ventaModel->find($idVenta);

        if (!$venta) {
            return $this->jsonError('Venta no encontrada.', 404);
        }

        $detalles = (new DetalleVentaModel())->getDetallConProducto($idVenta);

        $subtotal = 0.0;
        $items    = [];

        foreach ($detalles as $d) {
            $sub = VentaModel::subtotalLinea((float) $d->costo_venta, (int) $d->cantidad);
            $subtotal += $sub;

            $items[] = [
                'nombre'      => $d->nombre_producto,
                'cantidad'    => (int) $d->cantidad,
                'costo_venta' => VentaModel::formatoNumerico($d->costo_venta),
                'subtotal'    => VentaModel::formatoNumerico($sub),
            ];
        }

        $delivery = (new VentaDeliveryModel())->getCostoDelivery($idVenta);
        $cliente  = (new VentaClienteModel())->getClienteDeVenta($idVenta);
        $pago     = (new VentaPagoModel())->getMetodoPrincipal($idVenta);
        $numeroComercial = (new ComprobanteModel())->getNumeroComercial($idVenta);

        $total = $subtotal + $delivery;

        $anulacion = null;
        if ((int) $venta->status === VentaModel::INACTIVO) {
            $reg = (new VentaAnulacionModel())->getPorVenta($idVenta);
            if ($reg) {
                $anulacion = [
                    'motivo'          => $reg->motivo,
                    'fecha_anulacion' => $reg->fecha_anulacion,
                    'usuario'         => $reg->usuario_nombre,
                ];
            }
        }

        return $this->jsonSuccess('OK', [
            'venta' => [
                'id_venta'         => $venta->id_venta,
                'numero_comercial' => $numeroComercial ?? ('#' . $venta->id_venta),
                'fecha'            => $venta->fecha,
                'estado'           => (int) $venta->status === VentaModel::ACTIVO ? 'ACTIVA' : 'ANULADA',
                'cliente'          => $cliente ? [
                    'nombres_apellidos' => $cliente->nombres_apellidos,
                    'tipo_documento'    => $cliente->tipo_documento,
                    'numero_documento'  => $cliente->numero_documento,
                    'telefono'          => $cliente->telefono,
                    'direccion'         => $cliente->direccion,
                ] : null,
                'metodo_pago' => $pago ? $pago->metodo_pago_nombre : null,
            ],
            'detalle'   => $items,
            'subtotal'  => VentaModel::formatoNumerico($subtotal),
            'delivery'  => VentaModel::formatoNumerico($delivery),
            'total'     => VentaModel::formatoNumerico($total),
            'anulacion' => $anulacion,
        ]);
    }

    // ----------------------------------------------------------------
    //  AJAX: ANULAR
    // ----------------------------------------------------------------

    public function anular(int $idVenta)
    {
        if (!$this->request->isAJAX()) {
            return $this->jsonError('Acceso no permitido', 403);
        }

        $motivo = trim((string) $this->request->getPost('motivo'));
        if ($motivo === '') {
            return $this->jsonError('Debe indicar el motivo de anulación.');
        }

        $venta = $this->ventaModel->find($idVenta);
        if (!$venta) {
            return $this->jsonError('Venta no encontrada.', 404);
        }
        if ((int) $venta->status !== VentaModel::ACTIVO) {
            return $this->jsonError('La venta ya se encuentra anulada.');
        }

        $idUser = (int) ($this->userData['user_id'] ?? 0);
        if ($idUser <= 0) {
            return $this->jsonError('Sesión inválida. Vuelva a iniciar sesión.', 401);
        }

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // 1. Cambiar estado de la venta
            $this->ventaModel->anular($idVenta);

            // 2. Anular el detalle (líneas de producto)
            $detalles = $this->detalleModel->getPorVenta($idVenta);
            $this->detalleModel->anularPorVenta($idVenta);

            // 3. Reponer stock de cada línea anulada
            $stockModel     = new StockModel();
            $stockMovModel  = new StockMovimientoModel();
            foreach ($detalles as $d) {
                $stockModel->reponer((int) $d->id_producto, (int) $d->cantidad);
                $stockMovModel->registrarIngresoPorAnulacion((int) $d->id_producto, (int) $d->cantidad, $idVenta);
            }

            // 4. Registrar motivo de anulación
            (new VentaAnulacionModel())->registrar($idVenta, $idUser, $motivo);

            // 5. Historial de auditoría
            (new VentaHistorialModel())->registrar(
                $idVenta,
                $idUser,
                VentaHistorialModel::ACCION_ANULACION,
                'Venta anulada. Motivo: ' . $motivo
            );

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->jsonError('Error al anular la venta.');
            }

            $db->transCommit();

            return $this->jsonSuccess('Venta anulada correctamente.');

        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Error anulando venta: ' . $e->getMessage());
            return $this->jsonError('Error interno al anular la venta.');
        }
    }

    // ----------------------------------------------------------------
    //  COMPROBANTE (vista imprimible)
    // ----------------------------------------------------------------

    public function comprobante(int $idVenta)
    {
        $venta = $this->ventaModel->find($idVenta);
        if (!$venta) {
            return redirect()->to('/ventas')->with('error', 'Venta no encontrada.');
        }

        $detalles = (new DetalleVentaModel())->getDetallConProducto($idVenta);
        $subtotal = 0.0;
        foreach ($detalles as $d) {
            $subtotal += VentaModel::subtotalLinea((float) $d->costo_venta, (int) $d->cantidad);
        }

        $delivery = (new VentaDeliveryModel())->getCostoDelivery($idVenta);
        $cliente  = (new VentaClienteModel())->getClienteDeVenta($idVenta);
        $pago     = (new VentaPagoModel())->getMetodoPrincipal($idVenta);
        $numeroComercial = (new ComprobanteModel())->getNumeroComercial($idVenta);
        $comprobante = (new ComprobanteModel())->getPorVenta($idVenta);

        return view('ventas/comprobante', [
            'venta'            => $venta,
            'detalles'         => $detalles,
            'subtotal'         => $subtotal,
            'delivery'         => $delivery,
            'total'            => $subtotal + $delivery,
            'cliente'          => $cliente,
            'metodoPago'       => $pago ? $pago->metodo_pago_nombre : '—',
            'numeroComercial'  => $numeroComercial ?? ('#' . $idVenta),
            'tipoComprobante'  => $comprobante ? $comprobante->tipo_comprobante : 'BOLETA',
            'esActiva'         => (int) $venta->status === VentaModel::ACTIVO,
        ]);
    }
}
