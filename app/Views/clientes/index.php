<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h3><i class="fas fa-users"></i> Clientes</h3>
            <small class="text-muted">Administración de clientes del sistema (tbl_cliente)</small>
        </div>
        <div class="col-auto">
            <button class="btn btn-primary" id="btnNuevo">
                <i class="fas fa-plus"></i> Nuevo Cliente
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-list"></i> Listado de Clientes</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped" id="tablaClientes" style="width:100%">
                    <thead>
                        <tr>
                            <th>Nombres / Razón Social</th>
                            <th>Tipo Doc.</th>
                            <th>N.º Documento</th>
                            <th>Teléfono</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCliente" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalClienteTitulo">
                    <i class="fas fa-user"></i> Nuevo Cliente
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formCliente">
                <div class="modal-body">
                    <input type="hidden" name="id_cliente" id="id_cliente">

                    <div class="mb-3">
                        <label class="form-label">Nombres / Razón Social <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nombres_apellidos" name="nombres_apellidos" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo de Documento <span class="text-danger">*</span></label>
                            <select class="form-select" id="tipo_documento" name="tipo_documento" required>
                                <option value="SIN_DOCUMENTO">Sin documento</option>
                                <option value="DNI">DNI</option>
                                <option value="RUC">RUC</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">N.º Documento</label>
                            <input type="text" class="form-control" id="numero_documento" name="numero_documento" maxlength="20">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="text" class="form-control" id="telefono" name="telefono" maxlength="20">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Dirección</label>
                        <input type="text" class="form-control" id="direccion" name="direccion" maxlength="255">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
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
let tablaClientes;
let modalCliente;

$(document).ready(function () {
    modalCliente = new bootstrap.Modal(document.getElementById('modalCliente'));

    tablaClientes = $('#tablaClientes').DataTable({
        processing: true,
        ajax: { url: '<?= base_url('clientes/listar') ?>', type: 'POST', dataSrc: 'data' },
        columns: [
            { data: 'nombres_apellidos' },
            { data: 'tipo_documento', width: '120px', className: 'text-center' },
            { data: 'numero_documento', width: '140px' },
            { data: 'telefono', width: '120px' },
            { data: 'acciones', orderable: false, searchable: false, className: 'text-center', width: '110px' }
        ],
        order: [[1, 'desc']],
        language: {
            processing: "Procesando...", search: "Buscar:", lengthMenu: "Mostrar _MENU_ registros",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros", infoEmpty: "Mostrando 0 a 0 de 0 registros",
            infoFiltered: "(filtrado de _MAX_ registros totales)", loadingRecords: "Cargando...",
            zeroRecords: "No se encontraron registros", emptyTable: "No hay clientes registrados",
            paginate: { first: "Primero", previous: "Anterior", next: "Siguiente", last: "Último" }
        },
        pageLength: 10
    });

    $('#btnNuevo').on('click', function () {
        limpiarFormulario();
        $('#modalClienteTitulo').html('<i class="fas fa-plus"></i> Nuevo Cliente');
        modalCliente.show();
    });

    $('#formCliente').on('submit', function (e) {
        e.preventDefault();
        const idCliente = $('#id_cliente').val();
        const url = idCliente ? '<?= base_url('clientes/actualizar') ?>' : '<?= base_url('clientes/guardar') ?>';

        $.ajax({
            url: url, type: 'POST', data: $(this).serialize(), dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    Swal.fire({ icon: 'success', title: 'Éxito', text: response.message, timer: 2000, showConfirmButton: false });
                    modalCliente.hide();
                    tablaClientes.ajax.reload(null, false);
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                }
            },
            error: function () {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Error al guardar el cliente' });
            }
        });
    });

    $(document).on('click', '.btn-editar', function () {
        const id = $(this).data('id');
        $.ajax({
            url: '<?= base_url('clientes/editar') ?>/' + id, type: 'POST', dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    $('#id_cliente').val(response.data.id_cliente);
                    $('#nombres_apellidos').val(response.data.nombres_apellidos);
                    $('#tipo_documento').val(response.data.tipo_documento);
                    $('#numero_documento').val(response.data.numero_documento);
                    $('#telefono').val(response.data.telefono);
                    $('#direccion').val(response.data.direccion);
                    $('#modalClienteTitulo').html('<i class="fas fa-edit"></i> Editar Cliente');
                    modalCliente.show();
                }
            }
        });
    });

    $(document).on('click', '.btn-eliminar', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: '¿Está seguro?', text: 'Esta acción eliminará el cliente', icon: 'warning',
            showCancelButton: true, confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= base_url('clientes/eliminar') ?>', type: 'POST', data: { id_cliente: id }, dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            Swal.fire({ icon: 'success', title: 'Eliminado', text: response.message, timer: 2000, showConfirmButton: false });
                            tablaClientes.ajax.reload(null, false);
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                        }
                    }
                });
            }
        });
    });

    $('#modalCliente').on('hidden.bs.modal', limpiarFormulario);
});

function limpiarFormulario() {
    $('#formCliente')[0].reset();
    $('#id_cliente').val('');
}
</script>
<?= $this->endSection() ?>