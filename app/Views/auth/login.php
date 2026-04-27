<?= $this->extend('layouts/auth') ?>

<?= $this->section('css') ?>
<style>
    /* Variables locales — fallback por si el layout auth no hereda style.css completo */
    :root {
        --chardonay-bg-primary:     #F4F2EF;
        --chardonay-bg-secondary:   #FFFFFF;
        --chardonay-border:         #DEDAD3;
        --chardonay-text-primary:   #1E1E1E;
        --chardonay-text-secondary: #636363;
        --chardonay-primary:        #3D6B42;
        --chardonay-primary-hover:  #335A38;
        --chardonay-secondary:      #B8963A;
        --chardonay-success:        #4A8F5A;
        --chardonay-error:          #A84848;
        --chardonay-table-hover:    #EDEBE6;
        --chardonay-input-bg:       #FAFAF8;
        --shadow-xs: 0 1px 2px rgba(0,0,0,.06);
        --shadow-sm: 0 2px 5px rgba(0,0,0,.10);
        --h-control: 34px;
        --radius:      3px;
        --radius-card: 3px;
        --font-display: 'Georgia', 'Times New Roman', serif;
        --font-ui: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }

    *, *::before, *::after { box-sizing: border-box; }

    body {
        background-color: var(--chardonay-bg-primary);
        font-family: var(--font-ui);
        margin: 0;
    }

    /* ── Centrado vertical y horizontal ── */
    .login-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 1.25rem;
        background-color: var(--chardonay-bg-primary);
    }

    /* ── Card principal ── */
    .login-card {
        width: 100%;
        max-width: 420px;
        background-color: var(--chardonay-bg-secondary);
        border: 1px solid var(--chardonay-border);
        border-radius: var(--radius-card);
        box-shadow: 0 4px 16px rgba(0,0,0,.10);
        overflow: hidden;
    }

    /* ── Header oscuro ── */
    .login-header {
        background-color: #1C2128;
        padding: 1rem 1.25rem 0.9rem;
        border-bottom: 1px solid #2E3841;
    }

    .login-header-brand {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        margin-bottom: 0.3rem;
    }

    .login-header-icon {
        width: 30px;
        height: 30px;
        background: rgba(90, 158, 99, 0.20);
        border-radius: 2px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .login-header-icon i {
        font-size: 0.875rem;
        color: #5A9E63;
    }

    .login-header h4 {
        margin: 0;
        font-family: var(--font-display);
        font-size: 1rem;
        font-weight: 700;
        color: #FFFFFF !important;
        line-height: 1.2;
        letter-spacing: 0.1px;
    }

    .login-header small {
        display: block;
        color: #7A8D98 !important;
        font-family: var(--font-ui);
        font-size: 0.72rem;
        font-weight: 400;
        padding-left: 38px;
        letter-spacing: 0.15px;
        text-decoration: none !important;
    }

    /* ── Cuerpo del form ── */
    .login-body {
        padding: 1.375rem 1.25rem 1.25rem;
        background-color: var(--chardonay-bg-secondary);
    }

    /* ── Labels ── */
    .login-body .form-label {
        display: flex;
        align-items: center;
        gap: 0.3rem;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--chardonay-text-secondary);
        margin-bottom: 0.35rem;
        line-height: 1;
        font-family: var(--font-ui);
    }

    .login-body .form-label i {
        font-size: 0.7rem;
        color: var(--chardonay-secondary);
        opacity: 0.85;
    }

    /* ── Inputs ── */
    .login-body .form-control {
        height: var(--h-control);
        background-color: var(--chardonay-input-bg);
        border: 1px solid var(--chardonay-border);
        border-radius: var(--radius);
        color: var(--chardonay-text-primary);
        padding: 0 0.75rem;
        font-size: 0.8125rem;
        font-family: var(--font-ui);
        line-height: 1;
        width: 100%;
        transition: border-color 160ms ease, box-shadow 160ms ease;
        outline: none;
    }

    .login-body .form-control:focus {
        background-color: var(--chardonay-bg-secondary);
        border-color: var(--chardonay-primary);
        box-shadow: 0 0 0 3px rgba(61, 107, 66, 0.13);
    }

    .login-body .form-control::placeholder {
        color: var(--chardonay-text-secondary);
        opacity: 0.65;
        font-size: 0.8125rem;
    }

    /* ── Input group (contraseña + ojo) ── */
    .login-body .input-group {
        display: flex;
    }

    .login-body .input-group .form-control {
        border-right: none;
        border-radius: var(--radius) 0 0 var(--radius);
        flex: 1;
    }

    .login-body .input-group .form-control:focus {
        border-right: none;
    }

    .login-body .input-group .btn-outline-secondary {
        height: var(--h-control);
        border: 1px solid var(--chardonay-border);
        border-left: none;
        border-radius: 0 var(--radius) var(--radius) 0;
        background-color: var(--chardonay-input-bg);
        color: var(--chardonay-text-secondary);
        padding: 0 0.8rem;
        font-size: 0.8125rem;
        cursor: pointer;
        transition: background-color 150ms, color 150ms;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: none;
        outline: none;
        flex-shrink: 0;
    }

    .login-body .input-group .btn-outline-secondary:hover {
        background-color: var(--chardonay-table-hover);
        color: var(--chardonay-text-primary);
        transform: none;
        box-shadow: none;
    }

    /* ── Espaciado entre campos ── */
    .field-group {
        margin-bottom: 0.875rem;
    }

    /* ── Divisor ── */
    .login-divider {
        border: none;
        border-top: 1px solid var(--chardonay-border);
        margin: 1.125rem 0 1rem;
    }

    /* ── Botón principal ── */
    .btn-login {
        width: 100%;
        height: 36px;
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
        gap: 0.4rem;
        box-shadow: var(--shadow-xs);
        transition: background-color 160ms ease, transform 160ms ease, box-shadow 160ms ease;
        cursor: pointer;
        letter-spacing: 0.1px;
    }

    .btn-login:hover {
        background-color: var(--chardonay-primary-hover);
        border-color: #2C4D30;
        color: #FFFFFF;
        box-shadow: var(--shadow-sm);
        transform: translateY(-1px);
    }

    .btn-login:active  { transform: translateY(0); box-shadow: none; }

    .btn-login:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    /* ── Alerts ── */
    .login-body .alert {
        border-radius: var(--radius-card);
        padding: 0.6875rem 0.875rem;
        font-size: 0.8rem;
        font-family: var(--font-ui);
        line-height: 1.5;
        margin-bottom: 1rem;
        display: block;
    }

    .login-body .alert-danger {
        background-color: rgba(168, 72, 72, 0.07);
        border: 1px solid rgba(168, 72, 72, 0.20);
        border-left: 3px solid var(--chardonay-error);
        color: var(--chardonay-text-primary);
    }

    .login-body .alert-success {
        background-color: rgba(74, 143, 90, 0.07);
        border: 1px solid rgba(74, 143, 90, 0.20);
        border-left: 3px solid var(--chardonay-success);
        color: var(--chardonay-text-primary);
    }

    .login-body .alert i { margin-right: 0.4rem; }

    .login-body .alert ul {
        padding-left: 1.25rem;
        margin: 0.375rem 0 0;
    }

    .login-body .alert ul li {
        margin-bottom: 0.2rem;
        font-size: 0.7875rem;
        line-height: 1.4;
        font-family: var(--font-ui);
    }

    .login-body .alert ul li:last-child { margin-bottom: 0; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="login-container">
    <div class="login-card">

        <!-- HEADER OSCURO -->
        <div class="login-header">
            <div class="login-header-brand">
                <div class="login-header-icon">
                    <i class="fas fa-store"></i>
                </div>
                <h4>Sistema de Ventas</h4>
            </div>
            <small>Ingrese sus credenciales para continuar</small>
        </div>

        <!-- CUERPO -->
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

                <div class="field-group">
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

                <div class="field-group">
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

        </div><!-- /login-body -->
    </div><!-- /login-card -->
</div><!-- /login-container -->
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