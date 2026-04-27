<?= $this->extend('layouts/auth') ?>

<?= $this->section('css') ?>
<style>
    .login-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    
    .login-card {
        width: 100%;
        max-width: 420px;
        border: none;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        overflow: hidden;
        background-color: var(--chardonay-bg-secondary);
    }
    
    .login-header {
        background: linear-gradient(135deg, var(--chardonay-primary) 0%, var(--chardonay-primary-hover) 100%);
        color: white;
        padding: 2rem;
        text-align: center;
    }
    
    .login-header h4 {
        margin: 0 0 0.5rem 0;
        font-weight: 600;
        font-size: 1.5rem;
    }
    
    .login-header small {
        opacity: 0.9;
        font-size: 0.9rem;
    }
    
    .login-body {
        padding: 2rem;
    }
    
    .form-label {
        color: var(--chardonay-text-secondary);
        font-weight: 500;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }
    
    .form-control {
        background-color: var(--chardonay-input-bg);
        border: 1px solid var(--chardonay-border);
        color: var(--chardonay-text-primary);
        padding: 0.75rem 1rem;
        border-radius: 6px;
        transition: all 0.2s ease;
    }
    
    .form-control:focus {
        background-color: var(--chardonay-bg-secondary);
        border-color: var(--chardonay-input-focus);
        box-shadow: 0 0 0 0.2rem rgba(107, 142, 110, 0.15);
        outline: none;
    }
    
    .form-control::placeholder {
        color: #999;
    }
    
    .input-group .btn-outline-secondary {
        border: 1px solid var(--chardonay-border);
        border-left: none;
        background-color: var(--chardonay-input-bg);
        color: var(--chardonay-text-secondary);
        transition: all 0.2s ease;
    }
    
    .input-group .btn-outline-secondary:hover {
        background-color: var(--chardonay-table-hover);
        color: var(--chardonay-text-primary);
        border-color: var(--chardonay-border);
    }
    
    .input-group .form-control {
        border-right: none;
    }
    
    .input-group .form-control:focus + .btn-outline-secondary {
        border-color: var(--chardonay-input-focus);
    }
    
    .btn-login {
        background-color: var(--chardonay-primary);
        border-color: var(--chardonay-primary);
        color: white;
        padding: 0.875rem;
        font-weight: 500;
        border-radius: 6px;
        transition: all 0.2s ease;
        font-size: 1rem;
        width: 100%;
    }
    
    .btn-login:hover {
        background-color: var(--chardonay-primary-hover);
        border-color: var(--chardonay-primary-hover);
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(107, 142, 110, 0.3);
    }
    
    .btn-login:active {
        transform: translateY(0);
    }
    
    .btn-login:disabled {
        background-color: var(--chardonay-text-secondary);
        border-color: var(--chardonay-text-secondary);
        opacity: 0.7;
        transform: none;
    }
    
    .alert {
        border-radius: 6px;
        padding: 0.75rem 1rem;
        border: 1px solid transparent;
        font-size: 0.9rem;
        margin-bottom: 1.25rem;
    }
    
    .alert-danger {
        background-color: #fef2f2;
        border-color: var(--chardonay-error);
        color: var(--chardonay-error);
    }
    
    .alert-success {
        background-color: #f0f9f4;
        border-color: var(--chardonay-success);
        color: #2d5f3a;
    }
    
    .alert ul {
        padding-left: 1.25rem;
        margin-bottom: 0;
    }
    
    .alert ul li {
        margin-bottom: 0.25rem;
    }
    
    .alert ul li:last-child {
        margin-bottom: 0;
    }

    .alert i {
        margin-right: 0.5rem;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            <h4><i class="fas fa-store"></i> Sistema de Ventas</h4>
            <small>Ingrese sus credenciales</small>
        </div>
        <div class="login-body">

            <!-- Errores de validación -->
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

            <!-- Error general -->
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>

            <!-- Mensaje de éxito -->
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?= session()->getFlashdata('success') ?>
                </div>
            <?php endif; ?>

            <!-- Formulario -->
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
                            id="togglePassword">
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-login" id="btnLogin">
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
$(document).ready(function() {
    // Mostrar/ocultar contraseña
    $('#togglePassword').on('click', function() {
        const input = $('#password');
        const icon = $('#eyeIcon');

        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Deshabilitar botón al enviar
    $('#loginForm').on('submit', function() {
        const btn = $('#btnLogin');
        btn.prop('disabled', true);
        btn.html('<i class="fas fa-spinner fa-spin"></i> Verificando...');
    });
});
</script>
<?= $this->endSection() ?>