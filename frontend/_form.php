<?php
// Variables esperadas: $titulo, $producto (array), $errores (array)
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Minimarket - <?= h($titulo) ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f4f6f8; color: #222; }
        .contenedor { max-width: 520px; margin: 30px auto; padding: 0 16px; }
        form { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,.1); }
        label { display: block; margin-top: 12px; font-weight: bold; }
        input { width: 100%; padding: 8px; margin-top: 4px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 6px; }
        .botones { margin-top: 18px; display: flex; gap: 8px; }
        .btn { padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; text-decoration: none; font-size: 14px; color: #fff; }
        .btn-guardar { background: #1f6feb; }
        .btn-cancelar { background: #6b7280; }
        .msg.error { background: #fee2e2; color: #991b1b; padding: 10px 14px; border-radius: 6px; margin-bottom: 12px; }
        .msg.error ul { margin: 0; padding-left: 18px; }
    </style>
</head>
<body>
<div class="contenedor">
    <h1><?= h($titulo) ?></h1>

    <?php if ($errores): ?>
        <div class="msg error">
            <ul><?php foreach ($errores as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <form method="post">
        <label>Nombre
            <input type="text" name="nombre" maxlength="100" required value="<?= h($producto['nombre'] ?? '') ?>">
        </label>
        <label>Proveedor
            <input type="text" name="proveedor" maxlength="100" required value="<?= h($producto['proveedor'] ?? '') ?>">
        </label>
        <label>Fecha de vencimiento
            <input type="date" name="fecha_vencimiento" required value="<?= h($producto['fecha_vencimiento'] ?? '') ?>">
        </label>
        <label>Precio
            <input type="number" name="precio" step="0.01" min="0" required value="<?= h($producto['precio'] ?? '') ?>">
        </label>
        <label>Stock
            <input type="number" name="stock" step="1" min="0" required value="<?= h($producto['stock'] ?? '') ?>">
        </label>
        <div class="botones">
            <button class="btn btn-guardar" type="submit">Guardar</button>
            <a class="btn btn-cancelar" href="index.php">Cancelar</a>
        </div>
    </form>
</div>
</body>
</html>