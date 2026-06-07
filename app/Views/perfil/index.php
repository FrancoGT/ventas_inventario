<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">

            <div class="card">
                <div class="card-header">
                    <h5>
                        <i class="fas fa-user-circle"></i>
                        Perfil de Usuario
                    </h5>
                </div>

                <div class="card-body text-center py-5">

                    <div class="mb-4">
                        <i class="fas fa-user-lock fa-4x text-muted"></i>
                    </div>

                    <h4 class="mb-3">
                        Funcionalidad no disponible
                    </h4>

                    <p class="text-muted mb-4">
                        Gracias por probar nuestra aplicación.
                        Esta es una versión demostrativa y algunas funcionalidades,
                        como la administración del perfil de usuario, no se encuentran
                        disponibles actualmente.
                    </p>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Seguimos trabajando para incorporar nuevas características
                        en futuras versiones.
                    </div>

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