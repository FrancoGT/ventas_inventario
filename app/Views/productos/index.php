<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h3><i class="fas fa-box"></i> Productos</h3>
            <small class="text-muted">Administración de productos del sistema</small>
        </div>
        <div class="col-auto">
            <button class="btn btn-primary" id="btnNuevo">
                <i class="fas fa-plus"></i> Nuevo Producto
            </button>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-list"></i> Listado de Productos</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped" id="tablaProductos" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Código de Barras</th>
                            <th>Nombre</th>
                            <th>Precio (S/)</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalProducto" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalProductoTitulo">
                    <i class="fas fa-box"></i> Nuevo Producto
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formProducto">
                <div class="modal-body">
                    <input type="hidden" name="id_producto" id="id_producto">

                    <div class="mb-3">
                        <label for="codigo_barras" class="form-label">
                            <i class="fas fa-barcode"></i> Código de Barras <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="codigo_barras" name="codigo_barras"
                            placeholder="Ej: 7501234567890" required>
                    </div>

                    <div class="mb-3">
                        <label for="nombre" class="form-label">
                            <i class="fas fa-tag"></i> Nombre del Producto <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="nombre" name="nombre"
                            placeholder="Ej: Camisa Polo Blanca" required>
                    </div>

                    <div class="mb-3">
                        <label for="precio" class="form-label">
                            <i class="fas fa-dollar-sign"></i> Precio (S/) <span class="text-danger">*</span>
                        </label>
                        <input type="number" class="form-control" id="precio" name="precio"
                            placeholder="Ej: 25.50" step="0.01" min="0.01" required>
                    </div>

                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-info-circle"></i>
                        <small>Todos los campos marcados con (*) son obligatorios</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnGuardar">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
let tabla;
let modalProducto;

$(document).ready(function() {
    modalProducto = new bootstrap.Modal(document.getElementById('modalProducto'));

    tabla = $('#tablaProductos').DataTable({
        processing: true,
        ajax: {
            url: '<?= base_url('productos/listar') ?>',
            type: 'POST',
            dataSrc: 'data'
        },
        columns: [
            { data: 'id_producto', width: '60px', className: 'text-center' },
            { data: 'codigo_barras', width: '20%' },
            { data: 'nombre' },
            { 
                data: 'precio',
                width: '120px',
                className: 'text-end',
                render: function(data) {
                    return 'S/ ' + parseFloat(data).toFixed(2);
                }
            },
            { 
                data: 'acciones',
                width: '120px',
                orderable: false,
                searchable: false,
                className: 'text-center'
            }
        ],
        order: [[0, 'desc']],
        autoWidth: false,
        scrollX: false,
        language: {
            processing: "Procesando...",
            search: "Buscar:",
            lengthMenu: "Mostrar _MENU_ registros",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "Mostrando 0 a 0 de 0 registros",
            infoFiltered: "(filtrado de _MAX_ registros totales)",
            loadingRecords: "Cargando...",
            zeroRecords: "No se encontraron registros",
            emptyTable: "No hay productos registrados",
            paginate: {
                first: "Primero",
                previous: "Anterior",
                next: "Siguiente",
                last: "Último"
            }
        },
        pageLength: 10
    });

    $('#btnNuevo').on('click', function() {
        limpiarFormulario();
        $('#modalProductoTitulo').html('<i class="fas fa-plus"></i> Nuevo Producto');
        modalProducto.show();
    });

    $('#formProducto').on('submit', function(e) {
        e.preventDefault();
        const idProducto = $('#id_producto').val();
        const url = idProducto 
            ? '<?= base_url('productos/actualizar') ?>' 
            : '<?= base_url('productos/guardar') ?>';
        const btnGuardar = $('#btnGuardar');
        
        btnGuardar.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: url,
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    modalProducto.hide();
                    tabla.ajax.reload(null, false);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al guardar el producto'
                });
            },
            complete: function() {
                btnGuardar.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');
            }
        });
    });

    $(document).on('click', '.btn-editar', function(e) {
        e.preventDefault();
        const btn = $(this).hasClass('btn-editar') ? $(this) : $(this).closest('.btn-editar');
        const id = btn.attr('data-id');

        $.ajax({
            url: '<?= base_url('productos/editar') ?>/' + id,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success' && response.data) {
                    $('#id_producto').val(response.data.id_producto);
                    $('#codigo_barras').val(response.data.codigo_barras);
                    $('#nombre').val(response.data.nombre);
                    $('#precio').val(response.data.precio);
                    $('#modalProductoTitulo').html('<i class="fas fa-edit"></i> Editar Producto');
                    modalProducto.show();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'No se pudo cargar el producto'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al cargar el producto'
                });
            }
        });
    });

    $(document).on('click', '.btn-eliminar', function(e) {
        e.preventDefault();
        const btn = $(this).hasClass('btn-eliminar') ? $(this) : $(this).closest('.btn-eliminar');
        const id = btn.attr('data-id');
        
        Swal.fire({
            title: '¿Está seguro?',
            text: 'Esta acción eliminará el producto',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#B85C5C',
            cancelButtonColor: '#6B6B6B',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= base_url('productos/eliminar') ?>',
                    type: 'POST',
                    data: { id_producto: id },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Eliminado',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            tabla.ajax.reload(null, false);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error al eliminar el producto'
                        });
                    }
                });
            }
        });
    });

    $('#modalProducto').on('hidden.bs.modal', function() {
        limpiarFormulario();
    });
});

function limpiarFormulario() {
    $('#formProducto')[0].reset();
    $('#id_producto').val('');
}
</script>
<?= $this->endSection() ?>