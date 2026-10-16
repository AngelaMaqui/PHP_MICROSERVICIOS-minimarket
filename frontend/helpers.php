<?php

session_start();

function cargar_env(string $ruta): array
{
    $vars = [];
    if (!file_exists($ruta)) {
        return $vars;
    }
    foreach (file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linea) {
        $linea = trim($linea);
        if ($linea === '' || $linea[0] === '#' || strpos($linea, '=') === false) {
            continue;
        }
        [$k, $v] = explode('=', $linea, 2);
        $vars[trim($k)] = trim($v);
    }
    return $vars;
}

/**
 * Llama a la API REST del backend.
 * Devuelve ['status' => int, 'data' => array|null]
 * status 0 = no se pudo conectar con la API.
 */
function api(string $metodo, string $ruta, ?array $datos = null): array
{
    $env = cargar_env(__DIR__ . '/.env');
    $base = rtrim($env['API_URL'] ?? 'http://localhost:8000', '/');

    $opciones = [
        'method'        => $metodo,
        'header'        => "Content-Type: application/json\r\nAccept: application/json\r\n",
        'ignore_errors' => true,
        'timeout'       => 10,
    ];
    if ($datos !== null) {
        $opciones['content'] = json_encode($datos, JSON_UNESCAPED_UNICODE);
    }

    $respuesta = @file_get_contents($base . $ruta, false, stream_context_create(['http' => $opciones]));

    if ($respuesta === false || empty($http_response_header)) {
        return ['status' => 0, 'data' => null];
    }

    preg_match('{HTTP/\S+\s(\d{3})}', $http_response_header[0], $m);

    return [
        'status' => (int)($m[1] ?? 0),
        'data'   => json_decode($respuesta, true),
    ];
}

function h($texto): string
{
    return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
}

function flash(string $tipo, string $mensaje): void
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

function leer_flash(): ?array
{
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function redirigir(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/** Convierte la respuesta de error de la API en una lista de textos. */
function errores_api(array $resp): array
{
    if ($resp['status'] === 0) {
        return ['No se pudo conectar con la API. ¿Está encendido el backend?'];
    }
    if (isset($resp['data']['errores'])) {
        return $resp['data']['errores'];
    }
    return [$resp['data']['error'] ?? 'Ocurrió un error inesperado.'];
}