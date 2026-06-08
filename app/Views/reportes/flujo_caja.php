<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h3><i class="fas fa-chart-line"></i> Flujo de Caja</h3>
            <small class="text-muted">Reporte de ingresos, egresos, prendas y saldo</small>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h5><i class="fas fa-filter"></i> Filtros</h5>
        </div>
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Fecha inicio</label>
                    <input type="date" class="form-control" id="fecha_inicio">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Fecha fin</label>
                    <input type="date" class="form-control" id="fecha_fin">
                </div>

                <div class="col-md-6">
                    <button class="btn btn-primary" id="btnBuscarRango">
                        <i class="fas fa-search"></i> Buscar rango
                    </button>

                    <button class="btn btn-success" id="btnHoy">
                        <i class="fas fa-calendar-day"></i> Hoy
                    </button>

                    <button class="btn btn-info text-white" id="btnSemana">
                        <i class="fas fa-calendar-week"></i> Semana actual
                    </button>

                    <button class="btn btn-dark" id="btnMes">
                        <i class="fas fa-calendar-alt"></i> Mes actual
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">Ingresos</h6>
                    <h4 id="total_ingresos">S/ 0.00</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">Egresos</h6>
                    <h4 id="total_egresos">S/ 0.00</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">Saldo</h6>
                    <h4 id="saldo">S/ 0.00</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">Productos</h6>
                    <h4 id="total_prendas">0</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3" id="cardDesglose">
        <div class="card-header">
            <h5><i class="fas fa-calendar"></i> Desglose diario</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="tablaDesglose">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Día</th>
                            <th>Ingresos</th>
                            <th>Productos</th>
                            <th>Egresos</th>
                            <th>Saldo día</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="6" class="text-center text-muted">Sin datos</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">
                    <h5><i class="fas fa-shopping-cart"></i> Detalle de ventas</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover" id="tablaVentas">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Costo venta</th>
                                    <th>Delivery</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Sin datos</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">
                    <h5><i class="fas fa-money-bill-wave"></i> Detalle de egresos</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover" id="tablaEgresos">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Compra mercadería</th>
                                    <th>Flete</th>
                                    <th>Descripción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Sin datos</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
$(document).ready(function () {
    cargarHoy();

    $('#btnHoy').on('click', function () {
        cargarHoy();
    });

    $('#btnSemana').on('click', function () {
        cargarReporte('<?= base_url('reportes/semanaActual') ?>');
    });

    $('#btnMes').on('click', function () {
        cargarReporte('<?= base_url('reportes/mesActual') ?>');
    });

    $('#btnBuscarRango').on('click', function () {
        const inicio = $('#fecha_inicio').val();
        const fin = $('#fecha_fin').val();

        if (!inicio || !fin) {
            Swal.fire({
                icon: 'warning',
                title: 'Fechas requeridas',
                text: 'Seleccione fecha de inicio y fecha fin.'
            });
            return;
        }

        cargarReporte('<?= base_url('reportes/porRango') ?>?fecha_inicio=' + inicio + '&fecha_fin=' + fin);
    });
});

function cargarHoy() {
    cargarReporte('<?= base_url('reportes/hoy') ?>');
}

function cargarReporte(url) {
    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
        beforeSend: function () {
            limpiarTablas();
        },
        success: function (response) {
            if (response.error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.error
                });
                return;
            }

            pintarResumen(response);
            pintarDesglose(response.desglose_diario || []);
            pintarVentas(response.detalle_ventas || []);
            pintarEgresos(response.detalle_egresos || []);
        },
        error: function (xhr) {
            let mensaje = 'No se pudo cargar el reporte.';

            if (xhr.responseJSON && xhr.responseJSON.error) {
                mensaje = xhr.responseJSON.error;
            }

            Swal.fire({
                icon: 'error',
                title: 'Error de solicitud',
                text: mensaje
            });
        }
    });
}

function pintarResumen(data) {
    $('#total_ingresos').text(formatoSoles(data.total_ingresos || 0));
    $('#total_egresos').text(formatoSoles(data.total_egresos || 0));
    $('#saldo').text(formatoSoles(data.saldo || 0));
    $('#total_prendas').text(data.total_prendas || 0);
}

function pintarDesglose(items) {
    const tbody = $('#tablaDesglose tbody');
    tbody.empty();

    if (!items.length) {
        tbody.append('<tr><td colspan="6" class="text-center text-muted">Sin desglose diario</td></tr>');
        return;
    }

    items.forEach(function (item) {
        tbody.append(`
            <tr>
                <td>${escapar(item.fecha)}</td>
                <td>${escapar(item.dia)}</td>
                <td class="text-end">${formatoSoles(item.ingresos || 0)}</td>
                <td class="text-center">${item.prendas || 0}</td>
                <td class="text-end">${formatoSoles(item.egresos || 0)}</td>
                <td class="text-end">${formatoSoles(item.saldo_dia || 0)}</td>
            </tr>
        `);
    });
}

function pintarVentas(items) {
    const tbody = $('#tablaVentas tbody');
    tbody.empty();

    if (!items.length) {
        tbody.append('<tr><td colspan="5" class="text-center text-muted">Sin ventas</td></tr>');
        return;
    }

    items.forEach(function (item) {
        tbody.append(`
            <tr>
                <td>${escapar(item.fecha)}</td>
                <td>${escapar(item.nombre || item.producto || item.descripcion || '-')}</td>
                <td class="text-center">${item.cantidad || 0}</td>
                <td class="text-end">${formatoSoles(item.costo_venta || 0)}</td>
                <td class="text-end">${formatoSoles(item.costo_delivery || 0)}</td>
            </tr>
        `);
    });
}

function pintarEgresos(items) {
    const tbody = $('#tablaEgresos tbody');
    tbody.empty();

    if (!items.length) {
        tbody.append('<tr><td colspan="4" class="text-center text-muted">Sin egresos</td></tr>');
        return;
    }

    items.forEach(function (item) {
        tbody.append(`
            <tr>
                <td>${escapar(item.fecha)}</td>
                <td class="text-end">${formatoSoles(item.compra_mercaderia || 0)}</td>
                <td class="text-end">${formatoSoles(item.flete || 0)}</td>
                <td>${escapar(item.descripcion || '-')}</td>
            </tr>
        `);
    });
}

function limpiarTablas() {
    $('#tablaDesglose tbody').html('<tr><td colspan="6" class="text-center text-muted">Cargando...</td></tr>');
    $('#tablaVentas tbody').html('<tr><td colspan="5" class="text-center text-muted">Cargando...</td></tr>');
    $('#tablaEgresos tbody').html('<tr><td colspan="4" class="text-center text-muted">Cargando...</td></tr>');
}

function formatoSoles(valor) {
    const numero = parseFloat(valor || 0);
    return 'S/ ' + numero.toFixed(2);
}

function escapar(valor) {
    if (valor === null || valor === undefined) {
        return '';
    }

    return String(valor)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}
</script>
<?= $this->endSection() ?>