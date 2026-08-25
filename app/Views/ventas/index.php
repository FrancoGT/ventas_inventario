<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid px-0 px-md-2">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
        <div>
            <h3 class="mb-1"><i class="fas fa-cash-register me-2"></i>Ventas</h3>
            <div class="text-muted">Registro, consulta, comprobantes y anulación de ventas.</div>
        </div>
        <button type="button" class="btn btn-primary" id="btnNuevaVenta">
            <i class="fas fa-plus me-1"></i> Nueva venta
        </button>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tablaVentas" class="table table-striped table-hover align-middle w-100">
                    <thead>
                        <tr>
                            <th>Comprobante</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th class="text-center">Prendas</th>
                            <th class="text-end">Total</th>
                            <th>Pago</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: nueva venta -->
<div class="modal fade" id="modalNuevaVenta" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-cart-plus me-2"></i>Nueva venta</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form id="formVenta" autocomplete="off">
                <div class="modal-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label">Fecha</label>
                            <input type="text" id="fechaVenta" class="form-control" readonly>
                        </div>

                        <div class="col-md-5">
                            <label for="id_cliente" class="form-label">Cliente <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <select class="form-select" id="id_cliente" name="id_cliente" required>
                                    <?php foreach ($clientes as $cliente): ?>
                                        <option
                                            value="<?= (int) $cliente->id_cliente ?>"
                                            <?= (int) $cliente->id_cliente === \App\Models\ClienteModel::CLIENTE_GENERICO ? 'selected' : '' ?>
                                        >
                                            <?= esc($cliente->nombres_apellidos) ?><?= !empty($cliente->numero_documento) ? ' - ' . esc($cliente->numero_documento) : '' ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <a href="<?= base_url('clientes') ?>" class="btn btn-outline-secondary" title="Administrar clientes">
                                    <i class="fas fa-users"></i>
                                </a>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="id_metodo_pago" class="form-label">Método de pago <span class="text-danger">*</span></label>
                            <select class="form-select" id="id_metodo_pago" name="id_metodo_pago" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($metodosPago as $metodo): ?>
                                    <option value="<?= (int) $metodo->id_metodo_pago ?>"><?= esc($metodo->nombre) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <h6 class="mb-0"><i class="fas fa-box-open me-1"></i>Productos</h6>
                            <small class="text-muted">El stock se valida antes de registrar la venta.</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-success" id="btnAgregarLinea">
                            <i class="fas fa-plus me-1"></i>Agregar producto
                        </button>
                    </div>

                    <div class="table-responsive border rounded">
                        <table class="table table-sm table-bordered align-middle mb-0" id="tablaDetalleVenta">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:45px" class="text-center">#</th>
                                    <th style="min-width:260px">Producto</th>
                                    <th style="width:95px" class="text-center">Stock</th>
                                    <th style="width:115px" class="text-center">Cantidad</th>
                                    <th style="width:140px" class="text-end">Precio</th>
                                    <th style="width:140px" class="text-end">Subtotal</th>
                                    <th style="width:55px"></th>
                                </tr>
                            </thead>
                            <tbody id="tbodyDetalle"></tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="3" class="text-end">Total prendas</th>
                                    <th class="text-center" id="totalCantidad">0</th>
                                    <th class="text-end">Subtotal</th>
                                    <th class="text-end">S/ <span id="subtotalVenta">0.00</span></th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="row g-3 mt-3">
                        <div class="col-lg-7">
                            <label for="direccion_entrega" class="form-label">Dirección de entrega</label>
                            <input type="text" class="form-control" id="direccion_entrega" name="direccion_entrega" maxlength="255" placeholder="Opcional">
                        </div>
                        <div class="col-md-5 col-lg-2">
                            <label for="costo_delivery" class="form-label">Delivery (S/)</label>
                            <input type="number" class="form-control text-end" id="costo_delivery" name="costo_delivery" min="0" step="0.01" value="0.00">
                        </div>
                        <div class="col-md-7 col-lg-3">
                            <label class="form-label">Total</label>
                            <div class="venta-total">S/ <span id="totalGeneral">0.00</span></div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarVenta">
                        <i class="fas fa-save me-1"></i>Registrar venta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: detalle -->
<div class="modal fade" id="modalDetalleVenta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-receipt me-2"></i>Detalle <span id="detalleNumero"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2 mb-3">
                    <div class="col-md-4"><strong>Fecha:</strong> <span id="detFecha">—</span></div>
                    <div class="col-md-4"><strong>Estado:</strong> <span id="detEstado">—</span></div>
                    <div class="col-md-4"><strong>Pago:</strong> <span id="detPago">—</span></div>
                    <div class="col-12"><strong>Cliente:</strong> <span id="detCliente">—</span></div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:45px" class="text-center">#</th>
                                <th>Producto</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-end">Precio</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyDetalleVista"></tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4" class="text-end">Subtotal</th>
                                <th class="text-end">S/ <span id="detSubtotal">0.00</span></th>
                            </tr>
                            <tr>
                                <th colspan="4" class="text-end">Delivery</th>
                                <th class="text-end">S/ <span id="detDelivery">0.00</span></th>
                            </tr>
                            <tr class="table-success">
                                <th colspan="4" class="text-end">TOTAL</th>
                                <th class="text-end">S/ <span id="detTotal">0.00</span></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div id="bloqueAnulacion" class="alert alert-danger d-none mb-0"></div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('css') ?>
<style>
#tablaVentas th,
#tablaVentas td { vertical-align: middle; }

#tablaVentas .btn { margin: 2px; }

.venta-total {
    min-height: 38px;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding: .375rem .75rem;
    border: 1px solid #badbcc;
    border-radius: .375rem;
    background: #d1e7dd;
    color: #0f5132;
    font-size: 1.35rem;
    font-weight: 700;
}

.stock-ok { color: #198754; font-weight: 700; }
.stock-low { color: #b58105; font-weight: 700; }
.stock-zero { color: #dc3545; font-weight: 700; }

@media (max-width: 767.98px) {
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter { text-align: left !important; }
    .dataTables_wrapper .dataTables_filter input { width: 100%; margin: .35rem 0 0 !important; }
}
</style>
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
'use strict';

let tablaVentas = null;
let modalNuevaVenta = null;
let modalDetalleVenta = null;
let lineaSecuencia = 0;

const URL_VENTAS_LISTAR = <?= json_encode(base_url('ventas/listar')) ?>;
const URL_VENTAS_GUARDAR = <?= json_encode(base_url('ventas/guardar')) ?>;
const URL_VENTAS_DETALLE = <?= json_encode(base_url('ventas/detalle')) ?>;
const URL_VENTAS_ANULAR = <?= json_encode(base_url('ventas/anular')) ?>;
const URL_VENTAS_COMPROBANTE = <?= json_encode(base_url('ventas/comprobante')) ?>;

const productosDisponibles = <?= json_encode(array_map(static function ($p) {
    return [
        'id' => (int) $p->id_producto,
        'nombre' => (string) $p->nombre,
        'codigo' => (string) ($p->codigo_barras ?? ''),
        'precio' => (float) $p->precio,
        'stock' => (int) ($p->stock_actual ?? 0),
    ];
}, $productos), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

$(document).ready(function () {
    modalNuevaVenta = new bootstrap.Modal(document.getElementById('modalNuevaVenta'));
    modalDetalleVenta = new bootstrap.Modal(document.getElementById('modalDetalleVenta'));

    inicializarTablaVentas();
    registrarEventosVentas();
});

function inicializarTablaVentas() {
    if ($.fn.DataTable.isDataTable('#tablaVentas')) {
        $('#tablaVentas').DataTable().destroy();
        $('#tablaVentas tbody').empty();
    }

    tablaVentas = $('#tablaVentas').DataTable({
        processing: true,
        serverSide: false,
        autoWidth: false,
        responsive: false,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [[1, 'desc']],
        ajax: {
            url: URL_VENTAS_LISTAR,
            type: 'POST',
            dataType: 'json',
            dataSrc: function (json) {
                if (!json || !Array.isArray(json.data)) {
                    console.error('Respuesta inválida de ventas/listar:', json);
                    return [];
                }
                return json.data;
            },
            error: function (xhr) {
                console.error('Error al cargar ventas:', xhr.status, xhr.responseText);
                const mensaje = xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : 'No se pudo cargar el listado de ventas.';
                Swal.fire({ icon: 'error', title: 'Error al cargar ventas', text: mensaje });
            }
        },
        columns: [
            { data: 'numero_comercial', defaultContent: '—' },
            { data: 'fecha', defaultContent: '—' },
            { data: 'cliente', defaultContent: 'Público General' },
            { data: 'prendas', defaultContent: 0, className: 'text-center' },
            {
                data: 'total',
                defaultContent: '0.00',
                className: 'text-end',
                render: function (data, type) {
                    const numero = Number(data);
                    if (type === 'sort' || type === 'type') return Number.isFinite(numero) ? numero : 0;
                    return 'S/ ' + (Number.isFinite(numero) ? numero.toFixed(2) : '0.00');
                }
            },
            { data: 'metodo_pago', defaultContent: '—' },
            { data: 'estado', defaultContent: '—', className: 'text-center', orderable: false },
            { data: 'acciones', defaultContent: '', className: 'text-center', orderable: false, searchable: false }
        ],
        columnDefs: [
            { targets: [0, 1, 3, 4, 5, 6, 7], className: 'text-nowrap' }
        ],
        language: {
            processing: 'Procesando...',
            search: 'Buscar:',
            lengthMenu: 'Mostrar _MENU_ registros',
            info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
            infoEmpty: 'Mostrando 0 a 0 de 0 registros',
            infoFiltered: '(filtrado de _MAX_ registros totales)',
            loadingRecords: 'Cargando...',
            zeroRecords: 'No se encontraron ventas',
            emptyTable: 'No hay ventas registradas',
            paginate: {
                first: 'Primero',
                previous: 'Anterior',
                next: 'Siguiente',
                last: 'Último'
            }
        }
    });
}

function registrarEventosVentas() {
    $('#btnNuevaVenta').on('click', function () {
        limpiarFormularioVenta();
        agregarLineaVenta();
        modalNuevaVenta.show();
    });

    $('#btnAgregarLinea').on('click', agregarLineaVenta);

    $('#costo_delivery').on('input', recalcularTotalesVenta);

    $('#tbodyDetalle').on('change', '.select-producto', function () {
        seleccionarProductoEnFila($(this).closest('tr'));
    });

    $('#tbodyDetalle').on('input', '.input-cantidad, .input-precio', function () {
        recalcularFilaVenta($(this).closest('tr'));
    });

    $('#tbodyDetalle').on('click', '.btn-quitar-linea', function () {
        $(this).closest('tr').remove();
        renumerarLineas();
        recalcularTotalesVenta();
    });

    $('#formVenta').on('submit', function (e) {
        e.preventDefault();
        if (!validarFormularioVenta()) return;

        Swal.fire({
            icon: 'question',
            title: '¿Registrar venta?',
            html: 'Total: <strong>S/ ' + $('#totalGeneral').text() + '</strong>',
            showCancelButton: true,
            confirmButtonText: 'Sí, registrar',
            cancelButtonText: 'Cancelar'
        }).then(function (result) {
            if (result.isConfirmed) guardarVenta();
        });
    });

    $('#tablaVentas tbody').on('click', '.btn-detalle', function () {
        cargarDetalleVenta(Number($(this).data('id')));
    });

    $('#tablaVentas tbody').on('click', '.btn-anular', function () {
        solicitarAnulacionVenta(Number($(this).data('id')));
    });

    $('#modalNuevaVenta').on('hidden.bs.modal', function () {
        limpiarFormularioVenta();
    });
}

function opcionesProductosHtml() {
    let html = '<option value="">Seleccione un producto...</option>';
    productosDisponibles.forEach(function (p) {
        const codigo = p.codigo ? ' [' + escapeHtml(p.codigo) + ']' : '';
        const agotado = p.stock <= 0 ? ' - SIN STOCK' : ' - Stock: ' + p.stock;
        html += '<option value="' + p.id + '"' + (p.stock <= 0 ? ' disabled' : '') + '>'
            + escapeHtml(p.nombre) + codigo + agotado + '</option>';
    });
    return html;
}

function agregarLineaVenta() {
    lineaSecuencia++;

    const fila = $(
        '<tr>' +
            '<td class="numero-fila text-center"></td>' +
            '<td><select class="form-select form-select-sm select-producto">' + opcionesProductosHtml() + '</select>' +
                '<input type="hidden" name="productos[]" class="input-producto-id"></td>' +
            '<td class="text-center"><span class="stock-valor">—</span></td>' +
            '<td><input type="number" name="cantidades[]" class="form-control form-control-sm text-center input-cantidad" min="1" step="1" value="1" required></td>' +
            '<td><input type="number" name="costos_venta[]" class="form-control form-control-sm text-end input-precio" min="0" step="0.01" value="" required></td>' +
            '<td class="text-end fw-semibold">S/ <span class="subtotal-linea">0.00</span></td>' +
            '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-quitar-linea" title="Quitar"><i class="fas fa-trash"></i></button></td>' +
        '</tr>'
    );

    $('#tbodyDetalle').append(fila);
    renumerarLineas();
    fila.find('.select-producto').focus();
    recalcularTotalesVenta();
}

function seleccionarProductoEnFila(fila) {
    const id = Number(fila.find('.select-producto').val());
    const producto = productosDisponibles.find(function (p) { return p.id === id; });

    fila.find('.input-producto-id').val('');
    fila.find('.stock-valor').text('—').removeClass('stock-ok stock-low stock-zero');
    fila.find('.input-precio').val('');

    if (!producto) {
        recalcularFilaVenta(fila);
        return;
    }

    const repetido = $('#tbodyDetalle .input-producto-id').toArray().some(function (elemento) {
        return elemento !== fila.find('.input-producto-id')[0] && Number($(elemento).val()) === producto.id;
    });

    if (repetido) {
        fila.find('.select-producto').val('');
        Swal.fire({
            icon: 'warning',
            title: 'Producto repetido',
            text: 'Ese producto ya está en la venta. Modifique la cantidad de la fila existente.'
        });
        recalcularFilaVenta(fila);
        return;
    }

    fila.find('.input-producto-id').val(producto.id);
    fila.find('.input-precio').val(Number(producto.precio).toFixed(2));

    const stock = fila.find('.stock-valor').text(producto.stock);
    stock.addClass(producto.stock <= 0 ? 'stock-zero' : (producto.stock <= 3 ? 'stock-low' : 'stock-ok'));

    fila.find('.input-cantidad').attr('max', producto.stock).val(1);
    recalcularFilaVenta(fila);
}

function recalcularFilaVenta(fila) {
    const id = Number(fila.find('.input-producto-id').val());
    const producto = productosDisponibles.find(function (p) { return p.id === id; });
    const cantidad = Number(fila.find('.input-cantidad').val()) || 0;
    const precio = Number(fila.find('.input-precio').val()) || 0;

    const cantidadInvalida = producto && (cantidad <= 0 || !Number.isInteger(cantidad) || cantidad > producto.stock);
    fila.find('.input-cantidad').toggleClass('is-invalid', Boolean(cantidadInvalida));
    fila.find('.subtotal-linea').text(Math.max(0, cantidad * precio).toFixed(2));

    recalcularTotalesVenta();
}

function recalcularTotalesVenta() {
    let prendas = 0;
    let subtotal = 0;

    $('#tbodyDetalle tr').each(function () {
        prendas += Number($(this).find('.input-cantidad').val()) || 0;
        subtotal += Number($(this).find('.subtotal-linea').text()) || 0;
    });

    const delivery = Math.max(0, Number($('#costo_delivery').val()) || 0);

    $('#totalCantidad').text(prendas);
    $('#subtotalVenta').text(subtotal.toFixed(2));
    $('#totalGeneral').text((subtotal + delivery).toFixed(2));
}

function renumerarLineas() {
    $('#tbodyDetalle tr').each(function (index) {
        $(this).find('.numero-fila').text(index + 1);
    });
}

function limpiarFormularioVenta() {
    const form = document.getElementById('formVenta');
    if (form) form.reset();

    $('#tbodyDetalle').empty();
    $('#costo_delivery').val('0.00');
    $('#direccion_entrega').val('');
    $('#fechaVenta').val(fechaActualLima());
    $('#totalCantidad').text('0');
    $('#subtotalVenta').text('0.00');
    $('#totalGeneral').text('0.00');
    lineaSecuencia = 0;
}

function validarFormularioVenta() {
    if (!$('#id_cliente').val()) {
        mostrarAdvertencia('Cliente', 'Seleccione un cliente válido.');
        return false;
    }

    if (!$('#id_metodo_pago').val()) {
        mostrarAdvertencia('Método de pago', 'Seleccione un método de pago.');
        return false;
    }

    const filas = $('#tbodyDetalle tr');
    if (!filas.length) {
        mostrarAdvertencia('Sin productos', 'Agregue al menos un producto.');
        return false;
    }

    let mensaje = '';
    const ids = [];

    filas.each(function (index) {
        if (mensaje) return false;

        const fila = $(this);
        const id = Number(fila.find('.input-producto-id').val());
        const producto = productosDisponibles.find(function (p) { return p.id === id; });
        const cantidad = Number(fila.find('.input-cantidad').val());
        const precio = Number(fila.find('.input-precio').val());

        if (!producto) {
            mensaje = 'Seleccione un producto en la fila ' + (index + 1) + '.';
        } else if (ids.includes(id)) {
            mensaje = 'El producto "' + producto.nombre + '" está repetido.';
        } else if (!Number.isInteger(cantidad) || cantidad <= 0) {
            mensaje = 'La cantidad de la fila ' + (index + 1) + ' debe ser un entero mayor a 0.';
        } else if (cantidad > producto.stock) {
            mensaje = 'Stock insuficiente para "' + producto.nombre + '". Disponible: ' + producto.stock + '.';
        } else if (!Number.isFinite(precio) || precio < 0) {
            mensaje = 'El precio de la fila ' + (index + 1) + ' no es válido.';
        }

        ids.push(id);
    });

    if (mensaje) {
        mostrarAdvertencia('Revise la venta', mensaje);
        return false;
    }

    const delivery = Number($('#costo_delivery').val()) || 0;
    if (delivery < 0) {
        mostrarAdvertencia('Delivery', 'El costo de delivery no puede ser negativo.');
        return false;
    }

    return true;
}

function guardarVenta() {
    const boton = $('#btnGuardarVenta');
    boton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Registrando...');

    $.ajax({
        url: URL_VENTAS_GUARDAR,
        type: 'POST',
        data: $('#formVenta').serialize(),
        dataType: 'json'
    }).done(function (response) {
        if (response.status !== true) {
            Swal.fire({ icon: 'error', title: 'Error', text: response.message || 'No se pudo registrar la venta.' });
            return;
        }

        modalNuevaVenta.hide();
        recargarTablaVentas();

        const idVenta = response.data && response.data.id_venta ? response.data.id_venta : null;
        const numero = response.data && response.data.numero_comercial ? response.data.numero_comercial : (idVenta ? '#' + idVenta : '');

        Swal.fire({
            icon: 'success',
            title: 'Venta registrada',
            html: numero ? 'Comprobante <strong>' + escapeHtml(numero) + '</strong>' : 'La venta fue registrada correctamente.',
            showCancelButton: Boolean(idVenta),
            confirmButtonText: idVenta ? 'Imprimir' : 'Aceptar',
            cancelButtonText: 'Cerrar'
        }).then(function (result) {
            if (result.isConfirmed && idVenta) {
                window.open(URL_VENTAS_COMPROBANTE + '/' + idVenta, '_blank');
            }
        });
    }).fail(function (xhr) {
        const mensaje = xhr.responseJSON && xhr.responseJSON.message
            ? xhr.responseJSON.message
            : 'No se pudo registrar la venta.';
        Swal.fire({ icon: 'error', title: 'Error', text: mensaje });
    }).always(function () {
        boton.prop('disabled', false).html('<i class="fas fa-save me-1"></i>Registrar venta');
    });
}

function recargarTablaVentas() {
    if (tablaVentas) tablaVentas.ajax.reload(null, false);
}

function cargarDetalleVenta(idVenta) {
    if (!idVenta) return;

    $('#detalleNumero').text('');
    $('#detFecha, #detPago, #detCliente').text('—');
    $('#detEstado').text('—');
    $('#detSubtotal, #detDelivery, #detTotal').text('0.00');
    $('#bloqueAnulacion').addClass('d-none').empty();
    $('#tbodyDetalleVista').html('<tr><td colspan="5" class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i>Cargando...</td></tr>');
    modalDetalleVenta.show();

    $.ajax({
        url: URL_VENTAS_DETALLE + '/' + idVenta,
        type: 'POST',
        dataType: 'json'
    }).done(function (response) {
        if (response.status !== true || !response.data) {
            Swal.fire({ icon: 'error', title: 'Error', text: response.message || 'No se pudo cargar el detalle.' });
            return;
        }

        const data = response.data;
        const venta = data.venta || {};
        const cliente = venta.cliente || null;
        const detalle = Array.isArray(data.detalle) ? data.detalle : [];

        $('#detalleNumero').text(venta.numero_comercial || ('#' + idVenta));
        $('#detFecha').text(venta.fecha || '—');
        $('#detPago').text(venta.metodo_pago || '—');
        $('#detEstado').html(venta.estado === 'ANULADA'
            ? '<span class="badge bg-danger">ANULADA</span>'
            : '<span class="badge bg-success">ACTIVA</span>');

        if (cliente) {
            let textoCliente = cliente.nombres_apellidos || 'Cliente';
            if (cliente.numero_documento) {
                textoCliente += ' - ' + (cliente.tipo_documento || '') + ' ' + cliente.numero_documento;
            }
            $('#detCliente').text(textoCliente.trim());
        } else {
            $('#detCliente').text('Público General');
        }

        if (!detalle.length) {
            $('#tbodyDetalleVista').html('<tr><td colspan="5" class="text-center text-muted">No hay productos en esta venta.</td></tr>');
        } else {
            let filas = '';
            detalle.forEach(function (item, index) {
                filas += '<tr>' +
                    '<td class="text-center">' + (index + 1) + '</td>' +
                    '<td>' + escapeHtml(item.nombre || '') + '</td>' +
                    '<td class="text-center">' + Number(item.cantidad || 0) + '</td>' +
                    '<td class="text-end">S/ ' + formatoDinero(item.costo_venta) + '</td>' +
                    '<td class="text-end">S/ ' + formatoDinero(item.subtotal) + '</td>' +
                '</tr>';
            });
            $('#tbodyDetalleVista').html(filas);
        }

        $('#detSubtotal').text(formatoDinero(data.subtotal));
        $('#detDelivery').text(formatoDinero(data.delivery));
        $('#detTotal').text(formatoDinero(data.total));

        if (data.anulacion) {
            const a = data.anulacion;
            let texto = '<strong>Venta anulada.</strong><br>Motivo: ' + escapeHtml(a.motivo || '—');
            if (a.fecha_anulacion) texto += '<br><small>' + escapeHtml(a.fecha_anulacion);
            if (a.usuario) texto += ' · ' + escapeHtml(a.usuario);
            if (a.fecha_anulacion) texto += '</small>';
            $('#bloqueAnulacion').removeClass('d-none').html(texto);
        }
    }).fail(function (xhr) {
        const mensaje = xhr.responseJSON && xhr.responseJSON.message
            ? xhr.responseJSON.message
            : 'No se pudo cargar el detalle de la venta.';
        $('#tbodyDetalleVista').html('<tr><td colspan="5" class="text-center text-danger">Error al cargar el detalle.</td></tr>');
        Swal.fire({ icon: 'error', title: 'Error', text: mensaje });
    });
}

function solicitarAnulacionVenta(idVenta) {
    if (!idVenta) return;

    Swal.fire({
        icon: 'warning',
        title: 'Anular venta',
        input: 'textarea',
        inputLabel: 'Motivo de anulación',
        inputPlaceholder: 'Escriba el motivo...',
        inputAttributes: { maxlength: 500 },
        showCancelButton: true,
        confirmButtonText: 'Anular venta',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
        preConfirm: function (motivo) {
            const texto = String(motivo || '').trim();
            if (!texto) {
                Swal.showValidationMessage('Debe indicar el motivo de anulación.');
                return false;
            }
            return texto;
        }
    }).then(function (result) {
        if (!result.isConfirmed) return;

        $.ajax({
            url: URL_VENTAS_ANULAR + '/' + idVenta,
            type: 'POST',
            dataType: 'json',
            data: { motivo: result.value }
        }).done(function (response) {
            if (response.status === true) {
                Swal.fire({ icon: 'success', title: 'Venta anulada', text: response.message || 'La venta fue anulada correctamente.' });
                recargarTablaVentas();
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: response.message || 'No se pudo anular la venta.' });
            }
        }).fail(function (xhr) {
            const mensaje = xhr.responseJSON && xhr.responseJSON.message
                ? xhr.responseJSON.message
                : 'No se pudo anular la venta.';
            Swal.fire({ icon: 'error', title: 'Error', text: mensaje });
        });
    });
}

function mostrarAdvertencia(titulo, mensaje) {
    Swal.fire({ icon: 'warning', title: titulo, text: mensaje });
}

function fechaActualLima() {
    try {
        return new Intl.DateTimeFormat('es-PE', {
            timeZone: 'America/Lima',
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        }).format(new Date());
    } catch (e) {
        return new Date().toLocaleDateString('es-PE');
    }
}

function formatoDinero(valor) {
    const numero = Number(valor);
    return Number.isFinite(numero) ? numero.toFixed(2) : '0.00';
}

function escapeHtml(valor) {
    return String(valor == null ? '' : valor)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
</script>
<?= $this->endSection() ?>
