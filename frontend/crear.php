<?php
require_once __DIR__ . '/helpers.php';

$titulo = 'Nuevo producto';
$producto = [];
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $producto = [
        'nombre'            => trim($_POST['nombre'] ?? ''),
        'proveedor'         => trim($_POST['proveedor'] ?? ''),
        'fecha_vencimiento' => $_POST['fecha_vencimiento'] ?? '',
        'precio'            => is_numeric($_POST['precio'] ?? '') ? (float)$_POST['precio'] : ($_POST['precio'] ?? ''),
        'stock'             => ctype_digit($_POST['stock'] ?? '') ? (int)$_POST['stock'] : ($_POST['stock'] ?? ''),
    ];

    $resp = api('POST', '/productos', $producto);

    if ($resp['status'] === 201) {
        flash('ok', 'Producto creado correctamente.');
        redirigir('index.php');
    }
    $errores = errores_api($resp);
}

require __DIR__ . '/_form.php';