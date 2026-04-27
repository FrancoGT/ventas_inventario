<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Header -->
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

    <!-- Tabla -->
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
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
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

    // ── DataTable ──────────────────────────────────────────────────────────────
    tabla = $('#tablaEgresos').DataTable({
        processing: true,
        ajax: {
            url: '<?= base_url('egresos/listar') ?>',
            type: 'POST',
            dataSrc: 'data'
        },
        columns: [
            { data: 'id_egreso',          width: '60px',  className: 'text-center' },
            { data: 'fecha',              width: '120px', className: 'text-center' },
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
        ],
        order: [[0, 'desc']],
        autoWidth: false,
        scrollX: false,
        language: {
            processing:   "Procesando...",
            search:       "Buscar:",
            lengthMenu:   "Mostrar _MENU_ registros",
            info:         "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty:    "Mostrando 0 a 0 de 0 registros",
            infoFiltered: "(filtrado de _MAX_ registros totales)",
            loadingRecords: "Cargando...",
            zeroRecords:  "No se encontraron registros",
            emptyTable:   "No hay egresos registrados",
            paginate: {
                first:    "Primero",
                previous: "Anterior",
                next:     "Siguiente",
                last:     "Último"
            }
        },
        pageLength: 10
    });

    // ── Abrir modal nuevo ──────────────────────────────────────────────────────
    $('#btnNuevo').on('click', function () {
        limpiarFormulario();
        $('#modalEgresoTitulo').html('<i class="fas fa-plus"></i> Nuevo Egreso');
        modalEgreso.show();
    });

    // ── Submit formulario ──────────────────────────────────────────────────────
    $('#formEgreso').on('submit', function (e) {
        e.preventDefault();

        const btnGuardar = $('#btnGuardar');
        btnGuardar.prop('disabled', true)
                  .html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: '<?= base_url('egresos/guardar') ?>',
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
                    : (esExitoso ? 'Egreso registrado correctamente' : 'No se pudo guardar el egreso');

                if (esExitoso) {
                    modalEgreso.hide();
                    tabla.ajax.reload(null, false);

                    Swal.fire({
                        icon: 'success',
                        title: 'Registro guardado',
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
                          .html('<i class="fas fa-save"></i> Guardar');
            }
        });
    });

    // ── Limpiar al cerrar modal ────────────────────────────────────────────────
    $('#modalEgreso').on('hidden.bs.modal', function () {
        limpiarFormulario();
    });
});

function limpiarFormulario() {
    $('#formEgreso')[0].reset();
}
</script>
<?= $this->endSection() ?>