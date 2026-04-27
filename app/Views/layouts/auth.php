<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="<?= csrf_token() ?>" content="<?= csrf_hash() ?>">
    <title><?= esc($titulo ?? 'Sistema de Ventas') ?></title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --chardonay-bg-primary: #F7F5F2;
            --chardonay-bg-secondary: #FFFFFF;
            --chardonay-border: #E6E1D8;
            --chardonay-text-primary: #2E2E2E;
            --chardonay-text-secondary: #6B6B6B;
            --chardonay-primary: #6B8E6E;
            --chardonay-primary-hover: #5F7F63;
            --chardonay-success: #7FAF8A;
            --chardonay-error: #B85C5C;
            --chardonay-table-hover: #F0ECE6;
            --chardonay-input-bg: #FAFAF8;
            --chardonay-input-focus: #C9D8C5;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: var(--chardonay-bg-primary);
            color: var(--chardonay-text-primary);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            min-height: 100vh;
        }
    </style>

    <?= $this->renderSection('css') ?>
</head>
<body>

    <?= $this->renderSection('content') ?>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    // Token CSRF global
    const CSRF_TOKEN = '<?= csrf_hash() ?>';
    </script>

    <?= $this->renderSection('js') ?>
</body>
</html>