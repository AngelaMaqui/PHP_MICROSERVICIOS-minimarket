<?php

require_once __DIR__ . '/Database.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

function responder(int $codigo, array $datos): void
{
    http_response_code($codigo);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

function validar(array $d): array
{
    $errores = [];

    foreach (['nombre', 'proveedor'] as $campo) {
        if (!isset($d[$campo]) || trim((string)$d[$campo]) === '') {
            $errores[] = "El campo $campo es obligatorio.";
        } elseif (mb_strlen(trim((string)$d[$campo])) > 100) {
            $errores[] = "El campo $campo no puede superar 100 caracteres.";
        }
    }

    if (empty($d['fecha_vencimiento'])) {
        $errores[] = 'El campo fecha_vencimiento es obligatorio.';
    } else {
        $f = DateTime::createFromFormat('Y-m-d', (string)$d['fecha_vencimiento']);
        if (!$f || $f->format('Y-m-d') !== $d['fecha_vencimiento']) {
            $errores[] = 'fecha_vencimiento debe tener formato YYYY-MM-DD.';
        }
    }

    if (!isset($d['precio']) || !is_numeric($d['precio']) || $d['precio'] < 0) {
        $errores[] = 'El precio debe ser un número mayor o igual a cero.';
    }

    if (!isset($d['stock']) || filter_var($d['stock'], FILTER_VALIDATE_INT) === false || $d['stock'] < 0) {
        $errores[] = 'El stock debe ser un entero mayor o igual a cero.';
    }

    return $errores;
}

// ---------- Enrutamiento ----------
$metodo = $_SERVER['REQUEST_METHOD'];
$ruta   = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$partes = explode('/', $ruta);

if ($partes[0] !== 'productos') {
    responder(404, ['error' => 'Ruta no encontrada.']);
}

$id = isset($partes[1]) && $partes[1] !== '' ? $partes[1] : null;

if ($id !== null && (!ctype_digit($id) || (int)$id < 1)) {
    responder(400, ['error' => 'El id debe ser un entero positivo.']);
}
$id = $id !== null ? (int)$id : null;

try {
    $pdo = Database::connect();

    // GET /productos  -> listar activos
    if ($metodo === 'GET' && $id === null) {
        $stmt = $pdo->query(
            'SELECT id, nombre, proveedor, fecha_vencimiento, precio, stock
             FROM Producto WHERE eliminado = 0 ORDER BY id'
        );
        responder(200, $stmt->fetchAll());
    }

    // GET /productos/{id} -> obtener uno
    if ($metodo === 'GET' && $id !== null) {
        $stmt = $pdo->prepare(
            'SELECT id, nombre, proveedor, fecha_vencimiento, precio, stock
             FROM Producto WHERE id = ? AND eliminado = 0'
        );
        $stmt->execute([$id]);
        $producto = $stmt->fetch();
        if (!$producto) {
            responder(404, ['error' => 'Producto no encontrado.']);
        }
        responder(200, $producto);
    }

    // POST /productos -> crear
    if ($metodo === 'POST' && $id === null) {
        $datos = json_decode(file_get_contents('php://input'), true);
        if (!is_array($datos)) {
            responder(400, ['error' => 'El cuerpo debe ser JSON válido.']);
        }
        $errores = validar($datos);
        if ($errores) {
            responder(422, ['errores' => $errores]);
        }
        $stmt = $pdo->prepare(
            'INSERT INTO Producto (nombre, proveedor, fecha_vencimiento, precio, stock)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            trim($datos['nombre']),
            trim($datos['proveedor']),
            $datos['fecha_vencimiento'],
            $datos['precio'],
            (int)$datos['stock'],
        ]);
        responder(201, ['mensaje' => 'Producto creado.', 'id' => (int)$pdo->lastInsertId()]);
    }

    // PUT /productos/{id} -> editar
    if ($metodo === 'PUT' && $id !== null) {
        $datos = json_decode(file_get_contents('php://input'), true);
        if (!is_array($datos)) {
            responder(400, ['error' => 'El cuerpo debe ser JSON válido.']);
        }
        $errores = validar($datos);
        if ($errores) {
            responder(422, ['errores' => $errores]);
        }
        $stmt = $pdo->prepare(
            'UPDATE Producto
             SET nombre = ?, proveedor = ?, fecha_vencimiento = ?, precio = ?, stock = ?
             WHERE id = ? AND eliminado = 0'
        );
        $stmt->execute([
            trim($datos['nombre']),
            trim($datos['proveedor']),
            $datos['fecha_vencimiento'],
            $datos['precio'],
            (int)$datos['stock'],
            $id,
        ]);

        // Verificamos que el producto exista y esté activo
        $check = $pdo->prepare('SELECT COUNT(*) FROM Producto WHERE id = ? AND eliminado = 0');
        $check->execute([$id]);
        if ((int)$check->fetchColumn() === 0) {
            responder(404, ['error' => 'Producto no encontrado.']);
        }
        responder(200, ['mensaje' => 'Producto actualizado.']);
    }

    // DELETE /productos/{id} -> eliminación lógica
    if ($metodo === 'DELETE' && $id !== null) {
        $stmt = $pdo->prepare('UPDATE Producto SET eliminado = 1 WHERE id = ? AND eliminado = 0');
        $stmt->execute([$id]);
        if ($stmt->rowCount() === 0) {
            responder(404, ['error' => 'Producto no encontrado.']);
        }
        responder(200, ['mensaje' => 'Producto eliminado lógicamente.']);
    }

    responder(405, ['error' => 'Método no permitido.']);

} catch (PDOException $e) {
    error_log($e->getMessage());
    responder(500, ['error' => 'Error de base de datos.']);
}