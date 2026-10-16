<?php
require_once __DIR__ . '/helpers.php';

$id = $_GET['id'] ?? '';
if (!ctype_digit($id) || (int)$id < 1) {
    flash('error', 'Id de producto inválido.');
    redirigir('index.php');
}

$titulo = 'Editar producto #' . (int)$id;
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $producto = [
        'nombre'            => trim($_POST['nombre'] ?? ''),
        'proveedor'         => trim($_POST['proveedor'] ?? ''),
        'fecha_vencimiento' => $_POST['fecha_vencimiento'] ?? '',
        'precio'            => is_numeric($_POST['precio'] ?? '') ? (float)$_POST['precio'] : ($_POST['precio'] ?? ''),
        'stock'             => ctype_digit($_POST['stock'] ?? '') ? (int)$_POST['stock'] : ($_POST['stock'] ?? ''),
    ];

    $resp = api('PUT', '/productos/' . (int)$id, $producto);

    if ($resp['status'] === 200) {
        flash('ok', 'Producto actualizado correctamente.');
        redirigir('index.php');
    }
    $errores = errores_api($resp);
} else {
    $resp = api('GET', '/productos/' . (int)$id);
    if ($resp['status'] !== 200) {
        flash('error', implode(' ', errores_api($resp)));
        redirigir('index.php');
    }
    $producto = $resp['data'];
}

require __DIR__ . '/_form.php';