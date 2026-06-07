<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row justify-content-center align-items-center" style="min-height: 70vh;">
        <div class="col-md-8 col-lg-6">

            <div class="card text-center">
                <div class="card-header">
                    <h5>
                        <i class="fas fa-exclamation-triangle"></i>
                        Página no encontrada
                    </h5>
                </div>

                <div class="card-body py-5">
                    <div class="mb-4">
                        <i class="fas fa-search fa-4x text-muted"></i>
                    </div>

                    <h3 class="mb-3">Error 404</h3>

                    <p class="text-muted mb-4">
                        Lo sentimos, la página que estás buscando no existe,
                        fue movida o no está disponible en esta versión demo.
                    </p>

                    <a href="<?= base_url('dashboard') ?>" class="btn btn-primary">
                        <i class="fas fa-home"></i>
                        Volver al inicio
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

<?= $this->endSection() ?>