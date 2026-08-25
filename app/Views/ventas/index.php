<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h3 class="mb-0"><i class="fas fa-cash-register"></i> Ventas</h3>
            <small class="text-muted">Ventas con cliente, pago, delivery, comprobante y control de stock</small>
        </div>
        <div class="col-auto">
            <button class="btn btn-primary" id="btnNuevaVenta">
                <i class="fas fa-plus"></i> Nueva venta
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list"></i> Listado de ventas</h5>
        </div>
        <div class="card-body p-0 p-md-3">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle table-ventas" id="tablaVentas" style="width:100%">
                    <thead>
                        <tr>
                            <th>Comprobante</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Prendas</th>
                            <th>Total</th>
                            <th>Pago</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalNuevaVenta" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-cart-plus"></i> Nueva venta</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formVenta">
                <div class="modal-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label">Fecha</label>
                            <input type="text" class="form-control" id="fechaVenta" readonly>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Cliente <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <select class="form-select" id="id_cliente" name="id_cliente" required>
                                    <?php foreach ($clientes as $cliente): ?>
                                        <option value="<?= (int) $cliente->id_cliente ?>" <?= (int) $cliente->id_cliente === \App\Models\ClienteModel::CLIENTE_GENERICO ? 'selected' : '' ?>>
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
                            <label class="form-label">Método de pago <span class="text-danger">*</span></label>
                            <select class="form-select" id="id_metodo_pago" name="id_metodo_pago" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($metodosPago as $metodo): ?>
                                    <option value="<?= (int) $metodo->id_metodo_pago ?>"><?= esc($metodo->nombre) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0"><i class="fas fa-shirt"></i> Productos</h6>
                        <button type="button" class="btn btn-success btn-sm" id="btnAgregarLinea">
                            <i class="fas fa-plus-circle"></i> Agregar producto
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle" id="tablaDetalle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:42px">#</th>
                                    <th>Producto</th>
                                    <th style="width:105px">Stock</th>
                                    <th style="width:110px">Cantidad</th>
                                    <th style="width:145px">Precio (S/)</th>
                                    <th style="width:145px">Subtotal</th>
                                    <th style="width:55px"></th>
                                </tr>
                            </thead>
                            <tbody id="tbodyDetalle"></tbody>
                            <tfoot>
                                <tr class="table-light fw-bold">
                                    <td colspan="3" class="text-end">Productos:</td>
                                    <td class="text-center" id="totalCantidad">0</td>
                                    <td></td>
                                    <td class="text-end">S/ <span id="subtotalVenta">0.00</span></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="row g-3 mt-2">
                        <div class="col-md-7">
                            <label class="form-label">Dirección de entrega</label>
                            <input type="text" class="form-control" name="direccion_entrega" id="direccion_entrega" maxlength="255" placeholder="Opcional. Úsela cuando la venta tenga delivery">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Delivery (S/)</label>
                            <input type="number" class="form-control text-end" name="costo_delivery" id="costo_delivery" min="0" step="0.01" value="0.00">
                            <div class="form-text">Cargo único por venta.</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Total de venta</label>
                            <div class="total-box">S/ <span id="totalGeneral">0.00</span></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarVenta">
                        <i class="fas fa-save"></i> Registrar venta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetalleVenta" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title text-white"><i class="fas fa-eye"></i> Detalle <span id="detalleNumero"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-4"><strong>Fecha:</strong> <span id="detFecha"></span></div>
                    <div class="col-md-4"><strong>Estado:</strong> <span id="detEstado"></span></div>
                    <div class="col-md-4"><strong>Pago:</strong> <span id="detPago"></span></div>
                    <div class="col-md-12"><strong>Cliente:</strong> <span id="detCliente"></span></div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th><th>Producto</th><th class="text-center">Cantidad</th><th class="text-end">Precio</th><th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyDetalleVista"></tbody>
                        <tfoot>
                            <tr><td colspan="4" class="text-end">Subtotal:</td><td class="text-end">S/ <span id="detSubtotal">0.00</span></td></tr>
                            <tr><td colspan="4" class="text-end">Delivery:</td><td class="text-end">S/ <span id="detDelivery">0.00</span></td></tr>
                            <tr class="table-success fw-bold"><td colspan="4" class="text-end">TOTAL:</td><td class="text-end fs-5">S/ <span id="detTotal">0.00</span></td></tr>
                        </tfoot>
                    </table>
                </div>
                <div class="alert alert-danger d-none" id="bloqueAnulacion"></div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('css') ?>
<style>
.table-ventas { min-width: 1050px; }
.table-ventas th, .table-ventas td { white-space: nowrap; }
.autocomplete-wrapper { position: relative; }
.autocomplete-results { position:absolute; top:100%; left:0; right:0; z-index:1060; max-height:240px; overflow:auto; background:#fff; border:1px solid #ced4da; border-radius:0 0 .375rem .375rem; box-shadow:0 5px 15px rgba(0,0,0,.15); display:none; }
.autocomplete-results .ac-item { padding:.55rem .75rem; cursor:pointer; border-bottom:1px solid #eee; display:flex; justify-content:space-between; gap:.75rem; }
.autocomplete-results .ac-item:hover { background:#f1f5f9; }
.autocomplete-results .ac-name { font-weight:600; }
.autocomplete-results .ac-meta { font-size:.78rem; color:#6c757d; }
.input-producto.is-selected { border-color:#198754; background:#f3fff8; }
.input-producto.is-invalid-custom { border-color:#dc3545; }
.stock-badge { display:inline-block; min-width:58px; padding:.25rem .45rem; border-radius:.35rem; text-align:center; font-weight:600; background:#eef2f7; }
.stock-badge.stock-zero { background:#f8d7da; color:#842029; }
.stock-badge.stock-low { background:#fff3cd; color:#664d03; }
.total-box { min-height:38px; display:flex; align-items:center; justify-content:flex-end; padding:.375rem .75rem; border:1px solid #badbcc; background:#d1e7dd; color:#0f5132; border-radius:.375rem; font-size:1.35rem; font-weight:700; }
@media (max-width: 767.98px) { .dataTables_wrapper .dataTables_filter input { width:100%!important; margin-left:0!important; } }
</style>
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
let tablaVentas, modalNuevaVenta, modalDetalleVenta, contadorLineas = 0;
const productosDisponibles = <?= json_encode(array_map(static function ($p) {
    return [
        'id' => (int) $p->id_producto,
        'nombre' => $p->nombre,
        'precio' => (float) $p->precio,
        'codigo_barras' => $p->codigo_barras ?? '',
        'stock' => (int) ($p->stock_actual ?? 0),
    ];
}, $productos), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

$(function () {
    modalNuevaVenta = new bootstrap.Modal(document.getElementById('modalNuevaVenta'));
    modalDetalleVenta = new bootstrap.Modal(document.getElementById('modalDetalleVenta'));

    tablaVentas = $('#tablaVentas').DataTable({
        processing: true,
        ajax: { url: '<?= base_url('ventas/listar') ?>', type: 'POST', dataSrc: 'data' },
        columns: [
            { data: 'numero_comercial' },
            { data: 'fecha' },
            { data: 'cliente' },
            { data: 'prendas', className: 'text-center' },
            { data: 'total', className: 'text-end', render: d => 'S/ ' + d },
            { data: 'metodo_pago' },
            { data: 'estado', className: 'text-center' },
            { data: 'acciones', orderable:false, searchable:false, className:'text-center' }
        ],
        order: [[1, 'desc']],
        pageLength: 10,
        scrollX: true,
        language: {
            processing:'Procesando...', search:'Buscar:', lengthMenu:'Mostrar _MENU_ registros',
            info:'Mostrando _START_ a _END_ de _TOTAL_ registros', infoEmpty:'Mostrando 0 a 0 de 0 registros',
            infoFiltered:'(filtrado de _MAX_ registros totales)', loadingRecords:'Cargando...',
            zeroRecords:'No se encontraron registros', emptyTable:'No hay ventas registradas',
            paginate:{ first:'Primero', previous:'Anterior', next:'Siguiente', last:'Último' }
        }
    });

    $('#btnNuevaVenta').on('click', function () {
        limpiarFormularioVenta();
        $('#fechaVenta').val(new Intl.DateTimeFormat('sv-SE', { timeZone: 'America/Lima' }).format(new Date()));
        agregarLinea();
        modalNuevaVenta.show();
    });

    $('#btnAgregarLinea').on('click', agregarLinea);
    $('#costo_delivery').on('input', recalcularTotales);

    $(document).on('click', '.btn-quitar-linea', function () {
        $(this).closest('tr').remove();
        actualizarNumerosFila();
        recalcularTotales();
    });

    $(document).on('input', '.input-cantidad, .input-costo-venta', function () {
        recalcularFila($(this).closest('tr'));
    });

    $(document).on('input', '.input-producto', function () {
        const input = $(this), fila = input.closest('tr'), lista = fila.find('.autocomplete-results');
        const q = input.val().trim().toLowerCase();
        input.removeClass('is-selected is-invalid-custom');
        fila.find('.hidden-producto-id').val('');
        fila.find('.stock-badge').text('—').removeClass('stock-zero stock-low');
        if (!q) return lista.hide();

        const resultados = productosDisponibles.filter(p => p.nombre.toLowerCase().includes(q) || (p.codigo_barras && p.codigo_barras.toLowerCase().includes(q))).slice(0, 20);
        if (!resultados.length) {
            lista.html('<div class="p-2 text-muted text-center">Sin resultados</div>').show();
            return;
        }
        lista.html(resultados.map(p => `
            <div class="ac-item" data-id="${p.id}">
                <div><div class="ac-name">${escapeHtml(p.nombre)}</div><div class="ac-meta">${p.codigo_barras ? 'Código: ' + escapeHtml(p.codigo_barras) + ' · ' : ''}Stock: ${p.stock}</div></div>
                <div class="text-end"><strong>S/ ${Number(p.precio).toFixed(2)}</strong></div>
            </div>`).join('')).show();
    });

    $(document).on('click', '.ac-item', function () {
        const p = productosDisponibles.find(x => x.id === Number($(this).data('id')));
        if (p) seleccionarProducto($(this).closest('tr'), p);
    });

    $(document).on('click', function (e) {
        if (!$(e.target).closest('.autocomplete-wrapper').length) $('.autocomplete-results').hide();
    });

    $('#formVenta').on('submit', function (e) {
        e.preventDefault();
        if (!validarVenta()) return;
        Swal.fire({
            title:'¿Registrar venta?',
            html:`Total: <strong>S/ ${$('#totalGeneral').text()}</strong>`,
            icon:'question', showCancelButton:true, confirmButtonText:'Sí, registrar', cancelButtonText:'Cancelar'
        }).then(r => { if (r.isConfirmed) enviarVenta(); });
    });

    $(document).on('click', '.btn-detalle', function () { cargarDetalle($(this).data('id')); });
    $(document).on('click', '.btn-anular', function () { anularVenta($(this).data('id')); });
});

function agregarLinea() {
    contadorLineas++;
    $('#tbodyDetalle').append(`
        <tr data-linea="${contadorLineas}">
            <td class="text-center numero-fila">${contadorLineas}</td>
            <td><div class="autocomplete-wrapper">
                <input type="text" class="form-control form-control-sm input-producto" autocomplete="off" placeholder="Nombre o código de barras">
                <input type="hidden" class="hidden-producto-id" name="productos[]">
                <div class="autocomplete-results"></div>
            </div></td>
            <td class="text-center"><span class="stock-badge">—</span></td>
            <td><input type="number" class="form-control form-control-sm text-center input-cantidad" name="cantidades[]" min="1" value="1" required></td>
            <td><input type="number" class="form-control form-control-sm text-end input-costo-venta" name="costos_venta[]" min="0" step="0.01" required></td>
            <td class="text-end fw-bold">S/ <span class="subtotal-linea">0.00</span></td>
            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-quitar-linea"><i class="fas fa-trash"></i></button></td>
        </tr>`);
    $('#tbodyDetalle tr:last .input-producto').focus();
    recalcularTotales();
}

function seleccionarProducto(fila, producto) {
    const repetido = $('#tbodyDetalle .hidden-producto-id').toArray().some(el => el !== fila.find('.hidden-producto-id')[0] && Number($(el).val()) === producto.id);
    if (repetido) {
        Swal.fire({ icon:'warning', title:'Producto repetido', text:'Ese producto ya fue agregado. Cambia la cantidad en la fila existente.' });
        return;
    }
    fila.find('.input-producto').val(producto.nombre).addClass('is-selected').removeClass('is-invalid-custom');
    fila.find('.hidden-producto-id').val(producto.id);
    fila.find('.input-costo-venta').val(Number(producto.precio).toFixed(2));
    const badge = fila.find('.stock-badge').text(producto.stock);
    badge.toggleClass('stock-zero', producto.stock <= 0).toggleClass('stock-low', producto.stock > 0 && producto.stock <= 3);
    fila.find('.autocomplete-results').hide();
    recalcularFila(fila);
    fila.find('.input-cantidad').focus().select();
}

function recalcularFila(fila) {
    const id = Number(fila.find('.hidden-producto-id').val());
    const p = productosDisponibles.find(x => x.id === id);
    const cantidad = Math.max(0, Number(fila.find('.input-cantidad').val()) || 0);
    const precio = Math.max(0, Number(fila.find('.input-costo-venta').val()) || 0);
    fila.find('.subtotal-linea').text((cantidad * precio).toFixed(2));
    fila.find('.input-cantidad').toggleClass('is-invalid', !!p && cantidad > p.stock);
    recalcularTotales();
}

function recalcularTotales() {
    let cantidad = 0, subtotal = 0;
    $('#tbodyDetalle tr').each(function () {
        cantidad += Number($(this).find('.input-cantidad').val()) || 0;
        subtotal += Number($(this).find('.subtotal-linea').text()) || 0;
    });
    const delivery = Math.max(0, Number($('#costo_delivery').val()) || 0);
    $('#totalCantidad').text(cantidad);
    $('#subtotalVenta').text(subtotal.toFixed(2));
    $('#totalGeneral').text((subtotal + delivery).toFixed(2));
}

function validarVenta() {
    if (!$('#id_metodo_pago').val()) {
        Swal.fire({ icon:'warning', title:'Método de pago', text:'Seleccione un método de pago.' }); return false;
    }
    const filas = $('#tbodyDetalle tr');
    if (!filas.length) { Swal.fire({ icon:'warning', title:'Sin productos', text:'Agregue al menos un producto.' }); return false; }
    let error = '';
    filas.each(function (i) {
        const id = Number($(this).find('.hidden-producto-id').val());
        const p = productosDisponibles.find(x => x.id === id);
        const cant = Number($(this).find('.input-cantidad').val());
        const precio = Number($(this).find('.input-costo-venta').val());
        if (!p) error = `Seleccione un producto válido en la fila ${i + 1}.`;
        else if (!Number.isInteger(cant) || cant <= 0) error = `La cantidad de la fila ${i + 1} debe ser mayor a 0.`;
        else if (cant > p.stock) error = `Stock insuficiente para ${p.nombre}. Disponible: ${p.stock}.`;
        else if (!Number.isFinite(precio) || precio < 0) error = `El precio de la fila ${i + 1} no es válido.`;
        if (error) return false;
    });
    if (error) { Swal.fire({ icon:'warning', title:'Revise la venta', text:error }); return false; }
    return true;
}

function enviarVenta() {
    const btn = $('#btnGuardarVenta');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Registrando...');
    $.ajax({
        url:'<?= base_url('ventas/guardar') ?>', type:'POST', data:$('#formVenta').serialize(), dataType:'json',
        success:r => {
            if (r.status) {
                modalNuevaVenta.hide();
                tablaVentas.ajax.reload(null, false);
                Swal.fire({ icon:'success', title:'Venta registrada', html:`Comprobante <strong>${escapeHtml(r.data.numero_comercial || ('#' + r.data.id_venta))}</strong>`, showCancelButton:true, confirmButtonText:'Imprimir', cancelButtonText:'Cerrar' })
                    .then(x => { if (x.isConfirmed) window.open('<?= base_url('ventas/comprobante') ?>/' + r.data.id_venta, '_blank'); });
            } else Swal.fire({ icon:'error', title:'Error', text:r.message || 'No se pudo registrar la venta.' });
        },
        error:x => Swal.fire({ icon:'error', title:'Error', text:(x.responseJSON && x.responseJSON.message) || 'No se pudo registrar la venta.' }),
        complete:() => btn.prop('disabled', false).html('<i class="fas fa-save"></i> Registrar venta')
    });
}

function cargarDetalle(id) {
    $('#tbodyDetalleVista').html('<tr><td colspan="5" class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>');
    modalDetalleVenta.show();
    $.ajax({ url:'<?= base_url('ventas/detalle') ?>/' + id, type:'POST', dataType:'json' })
        .done(r => {
            if (!r.status) return Swal.fire({ icon:'error', title:'Error', text:r.message });
            const d=r.data, c=d.venta.cliente;
            $('#detalleNumero').text(d.venta.numero_comercial);
            $('#detFecha').text(d.venta.fecha);
            $('#detEstado').html(d.venta.estado === 'ACTIVA' ? '<span class="badge bg-success">ACTIVA</span>' : '<span class="badge bg-danger">ANULADA</span>');
            $('#detPago').text(d.venta.metodo_pago || '—');
            $('#detCliente').text(c ? `${c.nombres_apellidos}${c.numero_documento ? ' - ' + c.tipo_documento + ' ' + c.numero_documento : ''}` : 'Público General');
            $('#tbodyDetalleVista').html(d.detalle.map((x,i)=>`<tr><td class="text-center">${i+1}</td><td>${escapeHtml(x.nombre)}</td><td class="text-center">${x.cantidad}</td><td class="text-end">S/ ${x.costo_venta}</td><td class="text-end">S/ ${x.subtotal}</td></tr>`).join(''));
            $('#detSubtotal').text(d.subtotal); $('#detDelivery').text(d.delivery); $('#detTotal').text(d.total);
            if (d.anulacion) $('#bloqueAnulacion').removeClass('d-none').html(`<strong>Venta anulada:</strong> ${escapeHtml(d.anulacion.motivo)}<br><small>${escapeHtml(d.anulacion.fecha_anulacion)}${d.anulacion.usuario ? ' · ' + escapeHtml(d.anulacion.usuario) : ''}</small>`);
            else $('#bloqueAnulacion').addClass('d-none').empty();
        })
        .fail(x => Swal.fire({ icon:'error', title:'Error', text:(x.responseJSON && x.responseJSON.message) || 'No se pudo cargar el detalle.' }));
}

function anularVenta(id) {
    Swal.fire({ title:'Anular venta', input:'textarea', inputLabel:'Motivo de anulación', inputPlaceholder:'Indique el motivo...', showCancelButton:true, confirmButtonText:'Anular venta', confirmButtonColor:'#dc3545', inputValidator:v => !v || !v.trim() ? 'El motivo es obligatorio.' : undefined })
        .then(r => {
            if (!r.isConfirmed) return;
            $.ajax({ url:'<?= base_url('ventas/anular') ?>/' + id, type:'POST', data:{ motivo:r.value }, dataType:'json' })
                .done(x => { if (x.status) { Swal.fire({ icon:'success', title:'Venta anulada', text:x.message }); tablaVentas.ajax.reload(null,false); } else Swal.fire({ icon:'error', title:'Error', text:x.message }); })
                .fail(x => Swal.fire({ icon:'error', title:'Error', text:(x.responseJSON && x.responseJSON.message) || 'No se pudo anular la venta.' }));
        });
}

function actualizarNumerosFila() { $('#tbodyDetalle tr').each(function(i){ $(this).find('.numero-fila').text(i+1); }); }
function limpiarFormularioVenta() {
    $('#formVenta')[0].reset(); $('#tbodyDetalle').empty(); contadorLineas=0;
    $('#costo_delivery').val('0.00');
    $('#id_cliente').val('<?= \App\Models\ClienteModel::CLIENTE_GENERICO ?>');
    recalcularTotales();
}
function escapeHtml(v) { return String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c])); }
</script>
<?= $this->endSection() ?>
