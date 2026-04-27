<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">

    <!-- HEADER -->
    <div class="row mb-3">
        <div class="col">
            <h3><i class="fas fa-cash-register"></i> Ventas</h3>
            <small class="text-muted">Registro y consulta de ventas</small>
        </div>
        <div class="col-auto">
            <button class="btn btn-primary" id="btnNuevaVenta">
                <i class="fas fa-plus"></i> Nueva Venta
            </button>
        </div>
    </div>

    <!-- TABLA DE VENTAS -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list"></i> Listado de Ventas</h5>
        </div>
        <div class="card-body p-0 p-md-3">
            <div class="table-responsive">
                <table class="table table-hover table-striped table-ventas" id="tablaVentas" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Prendas</th>
                            <th>Total (S/)</th>
                            <th width="100">Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: NUEVA VENTA -->
<div class="modal fade" id="modalNuevaVenta" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="color: var(--chardonay-bg-primary);">
                    <i class="fas fa-cart-plus"></i> Nueva Venta
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: brightness(0) invert(1);"></button>
            </div>
            <form id="formVenta">
                <div class="modal-body">

                    <!-- Fecha -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">
                                <i class="fas fa-calendar-day"></i> Fecha
                            </label>
                            <input type="text" class="form-control" id="fechaVenta" readonly>
                        </div>
                        <div class="col-md-8 d-flex align-items-end">
                            <button type="button" class="btn btn-success" id="btnAgregarLinea">
                                <i class="fas fa-plus-circle"></i> Agregar Producto
                            </button>
                        </div>
                    </div>

                    <!-- Tabla de líneas de detalle -->
                    <div class="table-responsive">
                        <table class="table table-bordered" id="tablaDetalle">
                            <thead class="table-light">
                                <tr>
                                    <th width="30">#</th>
                                    <th>Producto <span class="text-danger">*</span></th>
                                    <th width="100">Cant. <span class="text-danger">*</span></th>
                                    <th width="130">Precio Vta. (S/) <span class="text-danger">*</span></th>
                                    <th width="130">Delivery (S/)</th>
                                    <th width="130">Subtotal (S/)</th>
                                    <th width="50"></th>
                                </tr>
                            </thead>
                            <tbody id="tbodyDetalle"></tbody>
                            <tfoot>
                                <tr class="table-warning fw-bold">
                                    <td colspan="2" class="text-end">TOTALES:</td>
                                    <td id="totalPrendas" class="text-center">0</td>
                                    <td id="totalCostoVenta" class="text-end">0.00</td>
                                    <td id="totalDelivery" class="text-end">0.00</td>
                                    <td id="totalGeneral" class="text-end fs-5 text-success">S/ 0.00</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="alert alert-info text-center" id="alertSinProductos">
                        <i class="fas fa-info-circle"></i>
                        Haga clic en <strong>"Agregar Producto"</strong> para añadir líneas a la venta.
                    </div>

                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle"></i>
                        <small>Todos los campos marcados con (<span class="text-danger">*</span>) son obligatorios.
                        Puede buscar por <strong>nombre</strong> o <strong>código de barras</strong>. 
                        El precio se carga automáticamente al seleccionar un producto.</small>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarVenta" disabled>
                        <i class="fas fa-save"></i> Registrar Venta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: DETALLE DE VENTA -->
<div class="modal fade" id="modalDetalleVenta" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title text-white">
                    <i class="fas fa-eye"></i> Detalle de Venta #<span id="detalleIdVenta"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong><i class="fas fa-hashtag"></i> Venta:</strong>
                        <span id="detInfoId"></span>
                    </div>
                    <div class="col-md-6">
                        <strong><i class="fas fa-calendar"></i> Fecha:</strong>
                        <span id="detInfoFecha"></span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Producto</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-end">Precio Vta.</th>
                                <th class="text-end">Delivery</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyDetalleVista"></tbody>
                        <tfoot>
                            <tr class="table-success fw-bold">
                                <td colspan="5" class="text-end">TOTAL:</td>
                                <td class="text-end fs-5" id="detTotalGeneral">S/ 0.00</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>


<?= $this->section('css') ?>
<style>
/* ══════════════════════════════════════════════════════════════
   TABLA DE VENTAS RESPONSIVE
   ══════════════════════════════════════════════════════════════ */

/* Ancho mínimo para forzar scroll horizontal en pantallas chicas */
.table-ventas {
    min-width: 580px;
}

/* Evitar que las cabeceras se rompan o descuadren */
.table-ventas thead th {
    white-space: nowrap;
    position: sticky;
    top: 0;
    z-index: 1;
    vertical-align: middle;
}

/* Celdas del body tampoco se rompen */
.table-ventas tbody td {
    white-space: nowrap;
    vertical-align: middle;
}

/* Indicador visual de scroll en móvil */
@media (max-width: 767.98px) {
    .table-responsive {
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
    }

    /* Sombra sutil a la derecha para indicar que hay más contenido */
    .card-body > .table-responsive {
        position: relative;
    }
    .card-body > .table-responsive::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        width: 20px;
        background: linear-gradient(to right, transparent, rgba(0,0,0,.04));
        pointer-events: none;
        border-radius: 0 0 .375rem 0;
    }

    /* Controles de DataTables apilados */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        text-align: left !important;
        margin-bottom: .5rem;
    }
    .dataTables_wrapper .dataTables_filter input {
        width: 100% !important;
        margin-left: 0 !important;
    }
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        text-align: center !important;
        margin-top: .5rem;
    }
    .dataTables_wrapper .dataTables_paginate .pagination {
        justify-content: center;
        flex-wrap: wrap;
    }
}

/* Scrollbar estilizado en navegadores webkit */
.table-responsive::-webkit-scrollbar {
    height: 6px;
}
.table-responsive::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}
.table-responsive::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}
.table-responsive::-webkit-scrollbar-thumb:hover {
    background: #a1a1a1;
}

/* ══════════════════════════════════════════════════════════════
   ESTILOS DEL AUTOCOMPLETE
   ══════════════════════════════════════════════════════════════ */
.autocomplete-wrapper {
    position: relative;
}

.autocomplete-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    z-index: 1055;
    max-height: 220px;
    overflow-y: auto;
    background: #fff;
    border: 1px solid #ced4da;
    border-top: none;
    border-radius: 0 0 .375rem .375rem;
    box-shadow: 0 4px 12px rgba(0,0,0,.15);
    display: none;
}

.autocomplete-results .ac-item {
    padding: 8px 12px;
    cursor: pointer;
    font-size: .875rem;
    border-bottom: 1px solid #f0f0f0;
    transition: background .15s;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.autocomplete-results .ac-item:last-child {
    border-bottom: none;
}

.autocomplete-results .ac-item:hover,
.autocomplete-results .ac-item.ac-active {
    background-color: #0d6efd;
    color: #fff;
}

.autocomplete-results .ac-item .ac-info {
    flex: 1;
    min-width: 0;
}

.autocomplete-results .ac-item .ac-nombre {
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.autocomplete-results .ac-item .ac-codigo {
    display: block;
    font-size: .75rem;
    color: #6c757d;
    margin-top: 1px;
}

.autocomplete-results .ac-item:hover .ac-codigo,
.autocomplete-results .ac-item.ac-active .ac-codigo {
    color: #cfe2ff;
}

.autocomplete-results .ac-item .ac-precio {
    font-weight: 600;
    color: #198754;
    white-space: nowrap;
    margin-left: 10px;
}

.autocomplete-results .ac-item:hover .ac-precio,
.autocomplete-results .ac-item.ac-active .ac-precio {
    color: #fff;
}

.autocomplete-results .ac-no-results {
    padding: 10px 12px;
    color: #999;
    font-size: .85rem;
    text-align: center;
}

/* Indicadores visuales */
.input-producto.is-selected {
    border-color: #198754;
    background-color: #f0fdf4;
}

.input-producto.is-invalid-custom {
    border-color: #dc3545;
    background-color: #fff5f5;
}

/* Badge de coincidencia por código */
.ac-match-badge {
    display: inline-block;
    font-size: .65rem;
    padding: 1px 5px;
    border-radius: 3px;
    background: #ffc107;
    color: #000;
    margin-left: 5px;
    font-weight: 600;
}

.ac-item:hover .ac-match-badge,
.ac-item.ac-active .ac-match-badge {
    background: rgba(255,255,255,.3);
    color: #fff;
}
</style>
<?= $this->endSection() ?>


<?= $this->section('js') ?>
<script>
// ══════════════════════════════════════════════════════════════
//  VARIABLES GLOBALES
// ══════════════════════════════════════════════════════════════
let tablaVentas;
let modalNuevaVenta;
let modalDetalleVenta;
let contadorLineas = 0;

// ── Productos con código de barras incluido ──
const productosDisponibles = <?= json_encode(
    array_map(function($p) {
        return [
            'id'            => $p->id_producto,
            'nombre'        => $p->nombre,
            'precio'        => $p->precio,
            'codigo_barras' => $p->codigo_barras ?? '',
        ];
    }, $productos)
) ?>;

$(document).ready(function() {

    modalNuevaVenta   = new bootstrap.Modal(document.getElementById('modalNuevaVenta'));
    modalDetalleVenta = new bootstrap.Modal(document.getElementById('modalDetalleVenta'));

    // ══════════════════════════════════════════════════════════
    //  DATATABLE
    // ══════════════════════════════════════════════════════════
    tablaVentas = $('#tablaVentas').DataTable({
        processing: true,
        ajax: {
            url: '<?= base_url('ventas/listar') ?>',
            type: 'POST',
            dataSrc: 'data'
        },
        columns: [
            { data: 'id_venta' },
            { data: 'fecha' },
            { data: 'prendas', className: 'text-center' },
            { 
                data: 'total',
                className: 'text-end',
                render: d => 'S/ ' + d
            },
            { 
                data: 'acciones',
                orderable: false,
                searchable: false,
                className: 'text-center'
            }
        ],
        order: [[0, 'desc']],
        language: {
            processing: "Procesando...",
            search: "Buscar:",
            lengthMenu: "Mostrar _MENU_ registros",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "Mostrando 0 a 0 de 0 registros",
            infoFiltered: "(filtrado de _MAX_ registros totales)",
            loadingRecords: "Cargando...",
            zeroRecords: "No se encontraron registros",
            emptyTable: "No hay ventas registradas",
            paginate: { first: "Primero", previous: "Anterior", next: "Siguiente", last: "Último" }
        },
        pageLength: 10,
        scrollX: true,
        autoWidth: false
    });

    // ══════════════════════════════════════════════════════════
    //  ABRIR MODAL NUEVA VENTA
    // ══════════════════════════════════════════════════════════
    $('#btnNuevaVenta').on('click', function() {
        limpiarFormularioVenta();
        const hoy = new Date();
        const fechaFormateada = hoy.getFullYear() + '-' +
            String(hoy.getMonth() + 1).padStart(2, '0') + '-' +
            String(hoy.getDate()).padStart(2, '0');
        $('#fechaVenta').val(fechaFormateada);
        agregarLinea();
        modalNuevaVenta.show();
    });

    // ══════════════════════════════════════════════════════════
    //  AGREGAR / QUITAR LÍNEA
    // ══════════════════════════════════════════════════════════
    $('#btnAgregarLinea').on('click', agregarLinea);

    $(document).on('click', '.btn-quitar-linea', function() {
        $(this).closest('tr').remove();
        recalcularTodo();
        actualizarNumerosFila();
        toggleEstado();
    });

    // ══════════════════════════════════════════════════════════
    //  AUTOCOMPLETE: ESCRIBIR EN EL INPUT DE PRODUCTO
    //  Busca por NOMBRE y por CÓDIGO DE BARRAS
    // ══════════════════════════════════════════════════════════
    $(document).on('input', '.input-producto', function() {
        const input   = $(this);
        const wrapper = input.closest('.autocomplete-wrapper');
        const lista   = wrapper.find('.autocomplete-results');
        const texto   = input.val().trim().toLowerCase();
        const fila    = input.closest('tr');

        // Al escribir, des-seleccionar producto anterior
        input.removeClass('is-selected is-invalid-custom');
        fila.find('.hidden-producto-id').val('');

        if (texto.length === 0) {
            lista.hide();
            return;
        }

        // ── Filtrar por nombre O código de barras ──
        const coincidencias = productosDisponibles.filter(p => {
            const coincideNombre = p.nombre.toLowerCase().includes(texto);
            const coincideCodigo = p.codigo_barras && p.codigo_barras.toLowerCase().includes(texto);
            return coincideNombre || coincideCodigo;
        });

        // ── Si hay coincidencia EXACTA de código de barras, seleccionar automáticamente ──
        const exactoCodigo = productosDisponibles.find(p => 
            p.codigo_barras && p.codigo_barras.toLowerCase() === texto
        );

        if (exactoCodigo && coincidencias.length === 1) {
            seleccionarProducto(fila, exactoCodigo);
            lista.hide();
            return;
        }

        // ── Construir lista de resultados ──
        if (coincidencias.length === 0) {
            lista.html('<div class="ac-no-results"><i class="fas fa-search"></i> Sin resultados para "<strong>' + escapeHtml(texto) + '</strong>"</div>');
        } else {
            let html = '';
            coincidencias.forEach(p => {
                const coincidePorCodigo = p.codigo_barras && p.codigo_barras.toLowerCase().includes(texto);
                const coincidePorNombre = p.nombre.toLowerCase().includes(texto);

                // Determinar qué resaltar
                let nombreHtml = coincidePorNombre 
                    ? resaltarCoincidencia(p.nombre, texto) 
                    : escapeHtml(p.nombre);

                // Badge si coincidió por código
                let badgeHtml = '';
                if (coincidePorCodigo) {
                    badgeHtml = `<span class="ac-match-badge"><i class="fas fa-barcode"></i> coincide</span>`;
                }

                // Código de barras formateado
                let codigoHtml = '';
                if (p.codigo_barras) {
                    codigoHtml = coincidePorCodigo
                        ? '<i class="fas fa-barcode"></i> ' + resaltarCoincidencia(p.codigo_barras, texto)
                        : '<i class="fas fa-barcode"></i> ' + escapeHtml(p.codigo_barras);
                }

                html += `<div class="ac-item" 
                              data-id="${p.id}" 
                              data-nombre="${escapeAttr(p.nombre)}" 
                              data-precio="${p.precio}"
                              data-codigo="${escapeAttr(p.codigo_barras || '')}">
                            <div class="ac-info">
                                <span class="ac-nombre">${nombreHtml} ${badgeHtml}</span>
                                ${codigoHtml ? `<span class="ac-codigo">${codigoHtml}</span>` : ''}
                            </div>
                            <span class="ac-precio">S/ ${parseFloat(p.precio).toFixed(2)}</span>
                         </div>`;
            });
            lista.html(html);
        }

        lista.show();
    });

    // ══════════════════════════════════════════════════════════
    //  AUTOCOMPLETE: SELECCIONAR PRODUCTO (click)
    // ══════════════════════════════════════════════════════════
    $(document).on('click', '.ac-item', function() {
        const item = $(this);
        const fila = item.closest('tr');

        const producto = {
            id:            item.data('id'),
            nombre:        item.data('nombre'),
            precio:        parseFloat(item.data('precio')),
            codigo_barras: item.data('codigo')
        };

        seleccionarProducto(fila, producto);
        item.closest('.autocomplete-results').hide();
    });

    // ══════════════════════════════════════════════════════════
    //  AUTOCOMPLETE: NAVEGACIÓN CON TECLADO
    // ══════════════════════════════════════════════════════════
    $(document).on('keydown', '.input-producto', function(e) {
        const wrapper = $(this).closest('.autocomplete-wrapper');
        const lista   = wrapper.find('.autocomplete-results');
        const items   = lista.find('.ac-item');

        if (!lista.is(':visible') || items.length === 0) {
            // Si presiona Enter sin lista visible y hay producto seleccionado,
            // saltar al campo cantidad
            if (e.key === 'Enter') {
                e.preventDefault();
                const fila = $(this).closest('tr');
                if (fila.find('.hidden-producto-id').val()) {
                    fila.find('.input-cantidad').focus().select();
                }
            }
            return;
        }

        let activo = lista.find('.ac-active');
        let index  = items.index(activo);

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            index = (index + 1) % items.length;
            items.removeClass('ac-active');
            $(items[index]).addClass('ac-active');
            items[index].scrollIntoView({ block: 'nearest' });

        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            index = index <= 0 ? items.length - 1 : index - 1;
            items.removeClass('ac-active');
            $(items[index]).addClass('ac-active');
            items[index].scrollIntoView({ block: 'nearest' });

        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (activo.length) {
                activo.trigger('click');
            } else if (items.length === 1) {
                // Si solo hay un resultado, seleccionarlo automáticamente
                $(items[0]).trigger('click');
            }

        } else if (e.key === 'Escape') {
            lista.hide();

        } else if (e.key === 'Tab') {
            lista.hide();
        }
    });

    // ══════════════════════════════════════════════════════════
    //  AUTOCOMPLETE: CERRAR AL HACER CLIC FUERA
    // ══════════════════════════════════════════════════════════
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.autocomplete-wrapper').length) {
            $('.autocomplete-results').hide();
        }
    });

    // ══════════════════════════════════════════════════════════
    //  AUTOCOMPLETE: VALIDAR AL PERDER FOCO
    // ══════════════════════════════════════════════════════════
    $(document).on('blur', '.input-producto', function() {
        const input = $(this);
        const fila  = input.closest('tr');

        setTimeout(() => {
            const idProducto = fila.find('.hidden-producto-id').val();
            if (!idProducto) {
                if (input.val().trim() !== '') {
                    input.addClass('is-invalid-custom');
                }
                fila.find('.input-costo-venta').val('');
                recalcularFila(fila);
            }
        }, 250);
    });

    // Al hacer focus, mostrar resultados si hay texto sin selección
    $(document).on('focus', '.input-producto', function() {
        const input = $(this);
        if (input.val().trim().length > 0 && !input.hasClass('is-selected')) {
            input.trigger('input');
        }
    });

    // ══════════════════════════════════════════════════════════
    //  RECALCULAR AL CAMBIAR CANTIDAD, PRECIO O DELIVERY
    // ══════════════════════════════════════════════════════════
    $(document).on('input', '.input-cantidad, .input-costo-venta, .input-costo-delivery', function() {
        recalcularFila($(this).closest('tr'));
    });

    // ══════════════════════════════════════════════════════════
    //  GUARDAR VENTA
    // ══════════════════════════════════════════════════════════
    $('#formVenta').on('submit', function(e) {
        e.preventDefault();

        const filas = $('#tbodyDetalle tr');

        if (filas.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Sin productos', text: 'Debe agregar al menos un producto.' });
            return;
        }

        let valido = true;
        filas.each(function(i) {
            const fila       = $(this);
            const productoId = fila.find('.hidden-producto-id').val();
            const cantidad   = parseInt(fila.find('.input-cantidad').val());
            const costoVenta = parseFloat(fila.find('.input-costo-venta').val());

            if (!productoId || productoId === '') {
                fila.find('.input-producto').addClass('is-invalid-custom').focus();
                Swal.fire({ icon: 'warning', title: 'Error', text: 'Seleccione un producto válido en la fila #' + (i+1) });
                valido = false;
                return false;
            }
            if (isNaN(cantidad) || cantidad <= 0) {
                Swal.fire({ icon: 'warning', title: 'Error', text: 'La cantidad en la fila #' + (i+1) + ' debe ser mayor a 0.' });
                valido = false;
                return false;
            }
            if (isNaN(costoVenta) || costoVenta < 0) {
                Swal.fire({ icon: 'warning', title: 'Error', text: 'El precio en la fila #' + (i+1) + ' no es válido.' });
                valido = false;
                return false;
            }
        });

        if (!valido) return;

        Swal.fire({
            title: '¿Registrar esta venta?',
            html: `Se registrarán <strong>${filas.length}</strong> producto(s).<br>
                   Total: <strong class="text-success">${$('#totalGeneral').text()}</strong>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-check"></i> Sí, registrar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) enviarVenta();
        });
    });

    // ══════════════════════════════════════════════════════════
    //  VER DETALLE DE VENTA
    // ══════════════════════════════════════════════════════════
    $(document).on('click', '.btn-detalle', function() {
        const idVenta = $(this).data('id');
        $.ajax({
            url: '<?= base_url('ventas/detalle') ?>/' + idVenta,
            type: 'POST',
            dataType: 'json',
            beforeSend: function() {
                $('#tbodyDetalleVista').html(
                    '<tr><td colspan="6" class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>'
                );
                modalDetalleVenta.show();
            },
            success: function(response) {
                if (response.status) {
                    const d = response.data;
                    $('#detalleIdVenta').text(d.venta.id_venta);
                    $('#detInfoId').text('#' + d.venta.id_venta);
                    $('#detInfoFecha').text(d.venta.fecha);
                    let html = '';
                    d.detalle.forEach((item, index) => {
                        html += `<tr>
                            <td class="text-center">${index + 1}</td>
                            <td>${item.nombre}</td>
                            <td class="text-center">${item.cantidad}</td>
                            <td class="text-end">S/ ${item.costo_venta}</td>
                            <td class="text-end">S/ ${item.costo_delivery}</td>
                            <td class="text-end">S/ ${item.subtotal}</td>
                        </tr>`;
                    });
                    $('#tbodyDetalleVista').html(html);
                    $('#detTotalGeneral').text('S/ ' + d.total);
                } else {
                    $('#tbodyDetalleVista').html('<tr><td colspan="6" class="text-center text-danger">'+response.message+'</td></tr>');
                }
            },
            error: function(xhr) {
                let msg = 'Error al cargar el detalle.';
                let detail = '';
                try {
                    const resp = xhr.responseJSON || JSON.parse(xhr.responseText);
                    if (resp && resp.msg) msg = resp.msg;
                    detail = `<br><small class="text-muted">HTTP ${xhr.status}: ${xhr.statusText}</small>`;
                } catch(e) {
                    detail = `<br><small class="text-muted">HTTP ${xhr.status}: ${xhr.statusText}</small>`;
                }
                $('#tbodyDetalleVista').html('<tr><td colspan="6" class="text-center text-danger">'+msg+'</td></tr>');
                Swal.fire({
                    icon: 'error',
                    title: 'Error al cargar detalle',
                    html: msg + detail,
                    footer: '<small>Revisa la consola del navegador (F12) para más detalles</small>'
                });
            }
        });
    });

    // Limpiar al cerrar modal
    $('#modalNuevaVenta').on('hidden.bs.modal', limpiarFormularioVenta);
});


// ══════════════════════════════════════════════════════════════
//  FUNCIONES AUXILIARES
// ══════════════════════════════════════════════════════════════

/**
 * Selecciona un producto en la fila (usado por click y por coincidencia exacta de código)
 */
function seleccionarProducto(fila, producto) {
    const input = fila.find('.input-producto');
    const precio = parseFloat(producto.precio);

    input.val(producto.nombre)
         .addClass('is-selected')
         .removeClass('is-invalid-custom');

    fila.find('.hidden-producto-id').val(producto.id);
    fila.find('.input-costo-venta').val(precio.toFixed(2));

    recalcularFila(fila);

    // Mover foco a cantidad
    setTimeout(() => {
        fila.find('.input-cantidad').focus().select();
    }, 100);
}

/**
 * Agrega una fila con input de búsqueda
 */
function agregarLinea() {
    contadorLineas++;

    const fila = `
        <tr data-linea="${contadorLineas}">
            <td class="text-center align-middle numero-fila">${contadorLineas}</td>
            <td>
                <div class="autocomplete-wrapper">
                    <input type="text" 
                           class="form-control form-control-sm input-producto" 
                           placeholder="🔍 Nombre o código de barras..."
                           autocomplete="off">
                    <input type="hidden" 
                           class="hidden-producto-id" 
                           name="productos[]">
                    <div class="autocomplete-results"></div>
                </div>
            </td>
            <td>
                <input type="number" 
                       class="form-control form-control-sm text-center input-cantidad" 
                       name="cantidades[]" 
                       value="1" min="1" required>
            </td>
            <td>
                <input type="number" 
                       class="form-control form-control-sm text-end input-costo-venta" 
                       name="costos_venta[]" 
                       step="0.01" min="0" 
                       placeholder="0.00" required>
            </td>
            <td>
                <input type="number" 
                       class="form-control form-control-sm text-end input-costo-delivery" 
                       name="costos_delivery[]" 
                       step="0.01" min="0" value="0.00">
            </td>
            <td class="text-end align-middle fw-bold subtotal-linea">0.00</td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-outline-danger btn-quitar-linea" title="Quitar">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </td>
        </tr>
    `;

    $('#tbodyDetalle').append(fila);
    toggleEstado();
    $('#tbodyDetalle tr:last-child .input-producto').focus();
}

/**
 * Resalta coincidencias en el texto
 */
function resaltarCoincidencia(texto, busqueda) {
    if (!busqueda) return escapeHtml(texto);
    const regex = new RegExp(`(${busqueda.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
    return escapeHtml(texto).replace(regex, '<strong>$1</strong>');
}

/**
 * Escapa HTML para prevenir XSS
 */
function escapeHtml(text) {
    if (!text) return '';
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return text.replace(/[&<>"']/g, m => map[m]);
}

/**
 * Escapa atributos HTML
 */
function escapeAttr(text) {
    if (!text) return '';
    return text.replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

function recalcularFila(fila) {
    const cantidad      = parseInt(fila.find('.input-cantidad').val()) || 0;
    const costoVenta    = parseFloat(fila.find('.input-costo-venta').val()) || 0;
    const costoDelivery = parseFloat(fila.find('.input-costo-delivery').val()) || 0;
    const subtotal      = (costoVenta * cantidad) + costoDelivery;
    fila.find('.subtotal-linea').text(subtotal.toFixed(2));
    recalcularTotales();
}

function recalcularTodo() {
    $('#tbodyDetalle tr').each(function() { recalcularFila($(this)); });
}

function recalcularTotales() {
    let totalPrendas = 0, totalCV = 0, totalDel = 0, totalGen = 0;
    $('#tbodyDetalle tr').each(function() {
        const cant = parseInt($(this).find('.input-cantidad').val()) || 0;
        const cv   = parseFloat($(this).find('.input-costo-venta').val()) || 0;
        const cd   = parseFloat($(this).find('.input-costo-delivery').val()) || 0;
        const sub  = parseFloat($(this).find('.subtotal-linea').text()) || 0;
        totalPrendas += cant;
        totalCV      += (cv * cant);
        totalDel     += cd;
        totalGen     += sub;
    });
    $('#totalPrendas').text(totalPrendas);
    $('#totalCostoVenta').text(totalCV.toFixed(2));
    $('#totalDelivery').text(totalDel.toFixed(2));
    $('#totalGeneral').text('S/ ' + totalGen.toFixed(2));
}

function actualizarNumerosFila() {
    $('#tbodyDetalle tr').each(function(i) {
        $(this).find('.numero-fila').text(i + 1);
    });
}

function toggleEstado() {
    const hay = $('#tbodyDetalle tr').length > 0;
    $('#alertSinProductos').toggle(!hay);
    $('#tablaDetalle').toggle(hay);
    $('#btnGuardarVenta').prop('disabled', !hay);
}

function enviarVenta() {
    const btn = $('#btnGuardarVenta');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Registrando...');

    $.ajax({
        url: '<?= base_url('ventas/guardar') ?>',
        type: 'POST',
        data: $('#formVenta').serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.status) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Venta registrada!',
                    html: `Venta <strong>#${response.data.id_venta}</strong> guardada correctamente.`,
                    timer: 2500,
                    showConfirmButton: false
                });
                modalNuevaVenta.hide();
                tablaVentas.ajax.reload();
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: response.message });
            }
        },
        error: function(xhr) {
            let msg = 'Error al registrar la venta.';
            if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
            Swal.fire({ icon: 'error', title: 'Error', text: msg });
        },
        complete: function() {
            btn.prop('disabled', false).html('<i class="fas fa-save"></i> Registrar Venta');
        }
    });
}

function limpiarFormularioVenta() {
    $('#formVenta')[0].reset();
    $('#tbodyDetalle').empty();
    contadorLineas = 0;
    recalcularTotales();
    toggleEstado();
}
</script>
<?= $this->endSection() ?>