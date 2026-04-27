<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row mb-3">
    <div class="col">
        <h3><i class="fas fa-tachometer-alt"></i> Dashboard</h3>
        <small class="text-muted">Resumen del día: <?= esc($fechaHoy) ?></small>
    </div>
</div>

<div class="row">
    <!-- Productos -->
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-start border-primary border-4 h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small text-uppercase">Productos</div>
                        <div class="h3 mb-0"><?= esc($totalProductos) ?></div>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-box fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent">
                <a href="/productos" class="small text-decoration-none">Ver todos <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>

    <!-- Ventas Hoy -->
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-start border-success border-4 h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small text-uppercase">Ventas Hoy</div>
                        <div class="h3 mb-0">S/ <?= esc($ventasHoy) ?></div>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-shopping-cart fa-2x text-success"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent">
                <a href="/ventas" class="small text-decoration-none">Ver ventas <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>

    <!-- Prendas Hoy -->
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-start border-info border-4 h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small text-uppercase">Productos Vendidos</div>
                        <div class="h3 mb-0"><?= esc($prendasHoy) ?></div>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-tshirt fa-2x text-info"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent">
                <a href="/reportes" class="small text-decoration-none">Ver reportes <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>

    <!-- Egresos Hoy -->
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-start border-danger border-4 h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small text-uppercase">Egresos Hoy</div>
                        <div class="h3 mb-0">S/ <?= esc($egresosHoy) ?></div>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-money-bill-wave fa-2x text-danger"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent">
                <a href="/egresos" class="small text-decoration-none">Ver egresos <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>