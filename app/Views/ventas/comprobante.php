<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Comprobante <?= esc($numeroComercial) ?></title>
<style>
body{font-family:Consolas,monospace;max-width:420px;margin:20px auto;color:#222;}
h2{text-align:center;margin:0 0 4px;}
.center{text-align:center;}
table{width:100%;border-collapse:collapse;margin-top:10px;}
th,td{padding:4px 2px;font-size:13px;}
th{border-bottom:1px solid #333;text-align:left;}
.text-end{text-align:right;}
.totales td{border-top:1px solid #333;font-weight:bold;}
.badge{padding:2px 8px;border-radius:4px;color:#fff;font-size:12px;}
.bg-success{background:#198754;}
.bg-danger{background:#dc3545;}
hr{border-top:1px dashed #999;}
@media print{ .no-print{display:none;} }
</style>
</head>
<body>
    <h2 class="center"><?= esc($tipoComprobante) ?></h2>
    <p class="center"><strong><?= esc($numeroComercial) ?></strong></p>
    <p class="center">
        <?= $esActiva
            ? '<span class="badge bg-success">ACTIVA</span>'
            : '<span class="badge bg-danger">ANULADA</span>' ?>
    </p>
    <hr>
    <p>
        <strong>Fecha:</strong> <?= esc($venta->fecha) ?><br>
        <strong>Cliente:</strong> <?= esc($cliente->nombres_apellidos ?? 'Publico General') ?><br>
        <?php if (!empty($cliente->numero_documento)): ?>
            <strong>Doc. (<?= esc($cliente->tipo_documento) ?>):</strong> <?= esc($cliente->numero_documento) ?><br>
        <?php endif; ?>
        <strong>Método de pago:</strong> <?= esc($metodoPago) ?>
    </p>
    <hr>
    <table>
        <thead>
            <tr><th>Producto</th><th class="text-end">Cant.</th><th class="text-end">P.Unit</th><th class="text-end">Subt.</th></tr>
        </thead>
        <tbody>
        <?php foreach ($detalles as $d): ?>
            <tr>
                <td><?= esc($d->nombre_producto) ?></td>
                <td class="text-end"><?= (int) $d->cantidad ?></td>
                <td class="text-end"><?= number_format((float) $d->costo_venta, 2) ?></td>
                <td class="text-end"><?= number_format((float) $d->costo_venta * (int) $d->cantidad, 2) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr><td colspan="3" class="text-end">Subtotal:</td><td class="text-end">S/ <?= number_format($subtotal, 2) ?></td></tr>
            <tr><td colspan="3" class="text-end">Delivery:</td><td class="text-end">S/ <?= number_format($delivery, 2) ?></td></tr>
            <tr class="totales"><td colspan="3" class="text-end">TOTAL:</td><td class="text-end">S/ <?= number_format($total, 2) ?></td></tr>
        </tfoot>
    </table>
    <hr>
    <p class="center no-print">
        <button onclick="window.print()">Imprimir</button>
    </p>
</body>
</html>
