<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h3><i class="fas fa-money-bill-wave"></i> Egresos</h3>
            <small class="text-muted">Administración de egresos del sistema</small>
        </div>
        <div class="col-auto">
            <button class="btn btn-primary" id="btnNuevo">
                <i class="fas fa-plus"></i> Nuevo Egreso
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-list"></i> Listado de Egresos</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped" id="tablaEgresos" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Compra Mercadería (S/)</th>
                            <th>Flete (S/)</th>
                            <th>Descripción</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEgreso" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEgresoTitulo">
                    <i class="fas fa-money-bill-wave"></i> Nuevo Egreso
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="formEgreso">
                <input type="hidden" id="id_egreso" name="id_egreso">

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="compra_mercaderia" class="form-label">
                            <i class="fas fa-shopping-cart"></i> Compra Mercadería (S/) <span class="text-danger">*</span>
                        </label>
                        <input type="number"
                               class="form-control"
                               id="compra_mercaderia"
                               name="compra_mercaderia"
                               placeholder="Ej: 500.00"
                               step="0.01"
                               min="0"
                               required>
                    </div>

                    <div class="mb-3">
                        <label for="flete" class="form-label">
                            <i class="fas fa-truck"></i> Flete (S/) <span class="text-danger">*</span>
                        </label>
                        <input type="number"
                               class="form-control"
                               id="flete"
                               name="flete"
                               placeholder="Ej: 50.00"
                               step="0.01"
                               min="0"
                               required>
                    </div>

                    <div class="mb-3">
                        <label for="descripcion" class="form-label">
                            <i class="fas fa-align-left"></i> Descripción
                        </label>
                        <textarea class="form-control"
                                  id="descripcion"
                                  name="descripcion"
                                  rows="3"
                                  maxlength="500"
                                  placeholder="Descripción opcional del egreso..."></textarea>
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
let modalEgreso;

$(document).ready(function () {
    modalEgreso = new bootstrap.Modal(document.getElementById('modalEgreso'));

    tabla = $('#tablaEgresos').DataTable({
        processing: true,
        ajax: {
            url: '<?= base_url('egresos/listar') ?>',
            type: 'POST',
            dataSrc: 'data'
        },
        columns: [
            { data: 'id_egreso', width: '60px', className: 'text-center' },
            { data: 'fecha', width: '120px', className: 'text-center' },
            {
                data: 'compra_mercaderia',
                width: '160px',
                className: 'text-end',
                render: function (data) {
                    const monto = parseFloat(data || 0);
                    return 'S/ ' + monto.toFixed(2);
                }
            },
            {
                data: 'flete',
                width: '120px',
                className: 'text-end',
                render: function (data) {
                    const monto = parseFloat(data || 0);
                    return 'S/ ' + monto.toFixed(2);
                }
            },
            { data: 'descripcion', orderable: false },
            {
                data: null,
                width: '130px',
                className: 'text-center',
                orderable: false,
                render: function (data, type, row) {
                    return `
                        <button class="btn btn-warning btn-sm btnEditar" data-id="${row.id_egreso}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-danger btn-sm btnEliminar" data-id="${row.id_egreso}">
                            <i class="fas fa-trash"></i>
                        </button>
                    `;
                }
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
            emptyTable: "No hay egresos registrados",
            paginate: {
                first: "Primero",
                previous: "Anterior",
                next: "Siguiente",
                last: "Último"
            }
        },
        pageLength: 10
    });

    $('#btnNuevo').on('click', function () {
        limpiarFormulario();
        modalEgreso.show();
    });

    $('#formEgreso').on('submit', function (e) {
        e.preventDefault();

        const id = $('#id_egreso').val();
        const btnGuardar = $('#btnGuardar');

        btnGuardar.prop('disabled', true)
                  .html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: id
                ? '<?= base_url('egresos/actualizar') ?>'
                : '<?= base_url('egresos/guardar') ?>',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (response) {
                const esExitoso = response && (
                    response.status === 'success' ||
                    response.status === true ||
                    response.success === true ||
                    response.ok === true
                );

                const mensaje = response && response.message
                    ? response.message
                    : (id ? 'Egreso actualizado correctamente' : 'Egreso registrado correctamente');

                if (esExitoso) {
                    modalEgreso.hide();
                    tabla.ajax.reload(null, false);

                    Swal.fire({
                        icon: 'success',
                        title: id ? 'Egreso actualizado' : 'Egreso registrado',
                        text: mensaje,
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'No se pudo guardar',
                        text: mensaje
                    });
                }
            },
            error: function (xhr) {
                let mensaje = 'Error al guardar el egreso';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    mensaje = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error de solicitud',
                    text: mensaje
                });
            },
            complete: function () {
                btnGuardar.prop('disabled', false)
                          .html(id ? '<i class="fas fa-save"></i> Actualizar' : '<i class="fas fa-save"></i> Guardar');
            }
        });
    });

    $(document).on('click', '.btnEditar', function () {
        const id = $(this).data('id');

        $.ajax({
            url: '<?= base_url('egresos/obtener') ?>',
            type: 'POST',
            data: { id_egreso: id },
            dataType: 'json',
            success: function (response) {
                const esExitoso = response && (
                    response.status === 'success' ||
                    response.status === true ||
                    response.success === true ||
                    response.ok === true
                );

                if (!esExitoso) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'No se pudo obtener el egreso'
                    });
                    return;
                }

                const egreso = response.data.egreso;

                $('#id_egreso').val(egreso.id_egreso);
                $('#compra_mercaderia').val(egreso.compra_mercaderia);
                $('#flete').val(egreso.flete);
                $('#descripcion').val(egreso.descripcion);

                $('#modalEgresoTitulo').html('<i class="fas fa-edit"></i> Editar Egreso');
                $('#btnGuardar').html('<i class="fas fa-save"></i> Actualizar');

                modalEgreso.show();
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo cargar el egreso'
                });
            }
        });
    });

    $(document).on('click', '.btnEliminar', function () {
        const id = $(this).data('id');

        Swal.fire({
            title: '¿Eliminar egreso?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= base_url('egresos/eliminar') ?>',
                    type: 'POST',
                    data: { id_egreso: id },
                    dataType: 'json',
                    success: function (response) {
                        const esExitoso = response && (
                            response.status === 'success' ||
                            response.status === true ||
                            response.success === true ||
                            response.ok === true
                        );

                        if (esExitoso) {
                            tabla.ajax.reload(null, false);

                            Swal.fire({
                                icon: 'success',
                                title: 'Egreso eliminado',
                                text: response.message || 'Egreso eliminado correctamente',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'No se pudo eliminar',
                                text: response.message || 'Error al eliminar el egreso'
                            });
                        }
                    },
                    error: function (xhr) {
                        let mensaje = 'Error al eliminar el egreso';

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            mensaje = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error de solicitud',
                            text: mensaje
                        });
                    }
                });
            }
        });
    });

    $('#modalEgreso').on('hidden.bs.modal', function () {
        limpiarFormulario();
    });
});

function limpiarFormulario() {
    $('#formEgreso')[0].reset();
    $('#id_egreso').val('');
    $('#modalEgresoTitulo').html('<i class="fas fa-plus"></i> Nuevo Egreso');
    $('#btnGuardar').html('<i class="fas fa-save"></i> Guardar');
}
</script>
<?= $this->endSection() ?>