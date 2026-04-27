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
        max-width: 400px;
        background-color: var(--chardonay-bg-secondary);
        border: 1px solid var(--chardonay-border);
        border-radius: var(--radius-card);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }

    .login-header {
        background-color: #2B3340;
        color: #E8EFF3;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--chardonay-sidebar-border);
        text-align: left;
    }

    .login-header h4 {
        margin: 0;
        font-family: var(--font-ui);
        font-size: 0.95rem;
        font-weight: 700;
        color: #FFFFFF;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        line-height: 1.2;
    }

    .login-header h4 i {
        color: var(--chardonay-sidebar-accent);
        font-size: 0.9rem;
    }

    .login-header small {
        display: block;
        margin-top: 0.35rem;
        color: #B8C4CC;
        font-family: var(--font-ui);
        font-size: 0.75rem;
        font-weight: 400;
    }

    .login-body {
        padding: 1.25rem;
        background-color: var(--chardonay-bg-secondary);
    }

    .login-body .mb-3 {
        margin-bottom: 0.875rem !important;
    }

    .login-body .mb-4 {
        margin-bottom: 1rem !important;
    }

    .form-label {
        color: var(--chardonay-text-secondary);
        font-weight: 600;
        font-size: 0.775rem;
        font-family: var(--font-ui);
        margin-bottom: 0.3125rem;
        display: flex;
        align-items: center;
        gap: 0.375rem;
        line-height: 1;
    }

    .form-label i {
        color: var(--chardonay-secondary);
        font-size: 0.775rem;
    }

    .form-control {
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

    .form-control:focus {
        background-color: var(--chardonay-bg-secondary);
        border-color: var(--chardonay-primary);
        box-shadow: 0 0 0 3px rgba(61, 107, 66, 0.12);
        outline: none;
    }

    .form-control::placeholder {
        color: var(--chardonay-text-secondary);
        font-size: 0.8125rem;
        opacity: 0.75;
    }

    .input-group .form-control {
        border-right: none;
    }

    .input-group .btn-outline-secondary {
        height: var(--h-control);
        border: 1px solid var(--chardonay-border);
        border-left: none;
        border-radius: 0 var(--radius) var(--radius) 0;
        background-color: var(--chardonay-input-bg);
        color: var(--chardonay-text-secondary);
        box-shadow: none;
        padding: 0 0.75rem;
    }

    .input-group .btn-outline-secondary:hover {
        background-color: var(--chardonay-table-hover);
        color: var(--chardonay-text-primary);
        border-color: var(--chardonay-border);
        transform: none;
        box-shadow: none;
    }

    .input-group .form-control:focus + .btn-outline-secondary {
        border-color: var(--chardonay-primary);
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
        opacity: 0.55;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .alert {
        border-radius: var(--radius-card);
        padding: 0.6875rem 1rem;
        font-size: 0.8125rem;
        font-family: var(--font-ui);
        line-height: 1.5;
        margin-bottom: 1rem;
        border: 1px solid var(--chardonay-border);
        display: block;
    }

    .alert-danger {
        background-color: rgba(168, 72, 72, 0.07);
        border-left: 3px solid var(--chardonay-error);
        border-color: rgba(168, 72, 72, 0.18);
        color: var(--chardonay-text-primary);
    }

    .alert-success {
        background-color: rgba(74, 143, 90, 0.07);
        border-left: 3px solid var(--chardonay-success);
        border-color: rgba(74, 143, 90, 0.18);
        color: var(--chardonay-text-primary);
    }

    .alert i {
        margin-right: 0.5rem;
    }

    .alert ul {
        padding-left: 1.25rem;
        margin-bottom: 0;
    }

    .alert ul li {
        margin-bottom: 0.25rem;
        font-family: var(--font-ui);
        line-height: 1.4;
    }

    .alert ul li:last-child {
        margin-bottom: 0;
    }
</style>
<?= $this->endSection() ?>