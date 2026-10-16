<?php
require_once __DIR__ . '/helpers.php';

// Eliminación lógica (el formulario envía POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_id'])) {
    $resp = api('DELETE', '/productos/' . (int)$_POST['eliminar_id']);
    if ($resp['status'] === 200) {
        flash('ok', 'Producto eliminado correctamente.');
    } else {
        flash('error', implode(' ', errores_api($resp)));
    }
    redirigir('index.php');
}

$resp = api('GET', '/productos');
$productos = $resp['status'] === 200 ? $resp['data'] : [];
$errorApi = $resp['status'] !== 200 ? implode(' ', errores_api($resp)) : null;
$flash = leer_flash();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Minimarket - Productos</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f4f6f8; color: #222; }
        .contenedor { max-width: 1000px; margin: 30px auto; padding: 0 16px; }
        h1 { margin-bottom: 4px; }
        .barra { display: flex; justify-content: space-between; align-items: center; margin: 16px 0; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.1); }
        th, td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        th { background: #1f6feb; color: #fff; }
        tr:hover td { background: #f0f6ff; }
        .btn { display: inline-block; padding: 7px 14px; border: none; border-radius: 6px; cursor: pointer; text-decoration: none; font-size: 14px; color: #fff; }
        .btn-nuevo { background: #16a34a; }
        .btn-editar { background: #f59e0b; }
        .btn-eliminar { background: #dc2626; }
        .acciones { display: flex; gap: 6px; }
        .acciones form { margin: 0; }
        .msg { padding: 10px 14px; border-radius: 6px; margin-bottom: 12px; }
        .msg.ok { background: #dcfce7; color: #166534; }
        .msg.error { background: #fee2e2; color: #991b1b; }
        .vacio { text-align: center; color: #666; padding: 24px; }
    </style>
</head>
<body>
<div class="contenedor">
    <h1>🛒 Minimarket</h1>
    <p>Gestión de productos</p>

    <?php if ($flash): ?>
        <div class="msg <?= h($flash['tipo']) ?>"><?= h($flash['mensaje']) ?></div>
    <?php endif; ?>
    <?php if ($errorApi): ?>
        <div class="msg error"><?= h($errorApi) ?></div>
    <?php endif; ?>

    <div class="barra">
        <strong>Productos activos: <?= count($productos) ?></strong>
        <a class="btn btn-nuevo" href="crear.php">+ Nuevo producto</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th><th>Nombre</th><th>Proveedor</th>
                <th>Vencimiento</th><th>Precio</th><th>Stock</th><th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php if (!$productos): ?>
            <tr><td colspan="7" class="vacio">No hay productos activos.</td></tr>
        <?php endif; ?>
        <?php foreach ($productos as $p): ?>
            <tr>
                <td><?= h($p['id']) ?></td>
                <td><?= h($p['nombre']) ?></td>
                <td><?= h($p['proveedor']) ?></td>
                <td><?= h($p['fecha_vencimiento']) ?></td>
                <td><?= number_format((float)$p['precio'], 2) ?></td>
                <td><?= h($p['stock']) ?></td>
                <td>
                    <div class="acciones">
                        <a class="btn btn-editar" href="editar.php?id=<?= (int)$p['id'] ?>">Editar</a>
                        <form method="post" onsubmit="return confirm('¿Eliminar este producto?');">
                            <input type="hidden" name="eliminar_id" value="<?= (int)$p['id'] ?>">
                            <button class="btn btn-eliminar" type="submit">Eliminar</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>