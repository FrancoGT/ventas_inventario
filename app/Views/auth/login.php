<?= $this->extend('layouts/auth') ?>

<?= $this->section('css') ?>
<style>
    .login-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
        background-color: var(--chardonay-bg-primary);
    }

    .login-card {
        width: 100%;
        max-width: 360px;
        background-color: var(--chardonay-bg-secondary);
        border: 1px solid var(--chardonay-border);
        border-radius: var(--radius-card);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }

    .login-header {
        background-color: var(--chardonay-sidebar-bg);
        padding: 0.875rem 1.125rem;
        border-bottom: 1px solid var(--chardonay-sidebar-border);
    }

    .login-header-brand {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.25rem;
    }

    .login-header-icon {
        width: 28px;
        height: 28px;
        background: rgba(90, 158, 99, 0.18);
        border-radius: 2px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .login-header-icon i {
        font-size: 0.8rem;
        color: var(--chardonay-sidebar-accent);
    }

    .login-header h4 {
        margin: 0;
        font-family: var(--font-display);
        font-size: 0.975rem;
        font-weight: 700;
        color: #FFFFFF;
        line-height: 1.2;
    }

    .login-header small {
        display: block;
        color: #7A8D98;
        font-family: var(--font-ui);
        font-size: 0.7rem;
        font-weight: 400;
        padding-left: 36px;
        letter-spacing: 0.1px;
    }

    .login-body {
        padding: 1.125rem;
        background-color: var(--chardonay-bg-secondary);
    }

    .login-body .mb-3 {
        margin-bottom: 0.8rem !important;
    }

    .login-body .mb-4 {
        margin-bottom: 0.8rem !important;
    }

    .login-body .form-label {
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.35px;
        color: var(--chardonay-text-secondary);
        margin-bottom: 0.3rem;
        display: flex;
        align-items: center;
        gap: 0.3rem;
        line-height: 1;
    }

    .login-body .form-label i {
        font-size: 0.7rem;
        color: var(--chardonay-secondary);
    }

    .login-body .form-control {
        height: var(--h-control);
        background-color: var(--chardonay-input-bg);
        border: 1px solid var(--chardonay-border);
        border-radius: var(--radius);
        color: var(--chardonay-text-primary);
        padding: 0 0.625rem;
        font-size: 0.8125rem;
        font-family: var(--font-ui);
        line-height: 1;
        transition: border-color 160ms ease, box-shadow 160ms ease;
    }

    .login-body .form-control:focus {
        background-color: var(--chardonay-bg-secondary);
        border-color: var(--chardonay-primary);
        box-shadow: 0 0 0 3px rgba(61, 107, 66, 0.12);
        outline: none;
    }

    .login-body .form-control::placeholder {
        color: var(--chardonay-text-secondary);
        font-size: 0.8125rem;
        opacity: 0.75;
    }

    .login-body .input-group .form-control {
        border-right: none;
        border-radius: var(--radius) 0 0 var(--radius);
    }

    .login-body .input-group .btn-outline-secondary {
        height: var(--h-control);
        border: 1px solid var(--chardonay-border);
        border-left: none;
        border-radius: 0 var(--radius) var(--radius) 0;
        background-color: var(--chardonay-input-bg);
        color: var(--chardonay-text-secondary);
        box-shadow: none;
        padding: 0 0.75rem;
        transition: background-color 150ms, color 150ms;
        font-size: 0.8125rem;
    }

    .login-body .input-group .btn-outline-secondary:hover {
        background-color: var(--chardonay-table-hover);
        color: var(--chardonay-text-primary);
        border-color: var(--chardonay-border);
        transform: none;
        box-shadow: none;
    }

    .login-body .input-group .form-control:focus {
        border-right: none;
    }

    .login-divider {
        border: none;
        border-top: 1px solid var(--chardonay-border);
        margin: 0.9rem 0 0.875rem;
    }

    .btn-login {
        width: 100%;
        height: var(--h-control);
        background-color: var(--chardonay-primary);
        border: 1px solid var(--chardonay-primary-hover);
        border-radius: var(--radius);
        color: #FFFFFF;
        font-family: var(--font-ui);
        font-size: 0.8125rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.375rem;
        box-shadow: var(--shadow-xs);
        transition: all 160ms ease;
        cursor: pointer;
    }

    .btn-login:hover {
        background-color: var(--chardonay-primary-hover);
        border-color: #2C4D30;
        color: #FFFFFF;
        box-shadow: var(--shadow-sm);
        transform: translateY(-1px);
    }

    .btn-login:active {
        transform: translateY(0);
        box-shadow: none;
    }

    .btn-login:disabled {
        background-color: var(--chardonay-text-secondary);
        border-color: var(--chardonay-text-secondary);
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    /* Alerts dentro del login */
    .login-body .alert {
        border-radius: var(--radius-card);
        padding: 0.6875rem 1rem;
        font-size: 0.8125rem;
        font-family: var(--font-ui);
        line-height: 1.5;
        margin-bottom: 0.875rem;
        display: block;
    }

    .login-body .alert-danger {
        background-color: rgba(168, 72, 72, 0.07);
        border: 1px solid rgba(168, 72, 72, 0.18);
        border-left: 3px solid var(--chardonay-error);
        color: var(--chardonay-text-primary);
    }

    .login-body .alert-success {
        background-color: rgba(74, 143, 90, 0.07);
        border: 1px solid rgba(74, 143, 90, 0.18);
        border-left: 3px solid var(--chardonay-success);
        color: var(--chardonay-text-primary);
    }

    .login-body .alert i {
        margin-right: 0.4rem;
    }

    .login-body .alert ul {
        padding-left: 1.25rem;
        margin: 0.375rem 0 0;
    }

    .login-body .alert ul li {
        margin-bottom: 0.2rem;
        font-family: var(--font-ui);
        font-size: 0.8rem;
        line-height: 1.4;
    }

    .login-body .alert ul li:last-child {
        margin-bottom: 0;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="login-container">
    <div class="login-card">

        <div class="login-header">
            <div class="login-header-brand">
                <div class="login-header-icon">
                    <i class="fas fa-store"></i>
                </div>
                <h4>Sistema de Ventas</h4>
            </div>
            <small>Ingrese sus credenciales para continuar</small>
        </div>

        <div class="login-body">

            <?php if (session()->getFlashdata('errors')): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Errores de validación:</strong>
                    <ul class="mt-2 mb-0">
                        <?php foreach (session()->getFlashdata('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?= session()->getFlashdata('success') ?>
                </div>
            <?php endif; ?>

            <form action="/login/authenticate" method="POST" id="loginForm">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label for="username" class="form-label">
                        <i class="fas fa-user"></i> Usuario
                    </label>
                    <input
                        type="text"
                        class="form-control"
                        id="username"
                        name="username"
                        value="<?= old('username') ?>"
                        placeholder="Ingrese su usuario"
                        autocomplete="username"
                        autofocus
                        required>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i> Contraseña
                    </label>
                    <div class="input-group">
                        <input
                            type="password"
                            class="form-control"
                            id="password"
                            name="password"
                            placeholder="Ingrese su contraseña"
                            autocomplete="current-password"
                            required>
                        <button
                            class="btn btn-outline-secondary"
                            type="button"
                            id="togglePassword"
                            title="Mostrar / ocultar contraseña">
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <hr class="login-divider">

                <div class="d-grid">
                    <button type="submit" class="btn-login" id="btnLogin">
                        <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
<?= $this->endSection() ?>


<?= $this->section('js') ?>
<script>
$(document).ready(function () {

    $('#togglePassword').on('click', function () {
        const input = $('#password');
        const icon  = $('#eyeIcon');

        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    $('#loginForm').on('submit', function () {
        const btn = $('#btnLogin');
        btn.prop('disabled', true);
        btn.html('<i class="fas fa-spinner fa-spin"></i> Verificando...');
    });

});
</script>
<?= $this->endSection() ?>