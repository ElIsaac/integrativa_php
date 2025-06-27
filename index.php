<?php

require_once __DIR__ . '/utils/http_client.php';

use HttpClient\HttpClient;


ini_set('display_errors', 0);
error_reporting(0);

// ========== MANEJO DE ERRORES ==========
function jsonErrorResponse($code, $message, $details = null)
{
    header('Content-Type: application/json', true, $code);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => $code,
            'message' => $message,
            'details' => $details,
        ]
    ]);
    exit;
}

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno))
        return;
    jsonErrorResponse(500, 'Error de ejecución', [
        'tipo' => $errno,
        'mensaje' => $errstr,
        'archivo' => $errfile,
        'línea' => $errline
    ]);
});

set_exception_handler(function ($exception) {
    jsonErrorResponse(500, 'Excepción no controlada', [
        'tipo' => get_class($exception),
        'mensaje' => $exception->getMessage(),
        'archivo' => $exception->getFile(),
        'línea' => $exception->getLine(),
        'traza' => $exception->getTrace()
    ]);
});

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        jsonErrorResponse(500, 'Error fatal', [
            'mensaje' => $error['message'],
            'archivo' => $error['file'],
            'línea' => $error['line']
        ]);
    }
});

// ========== CONEXIÓN ==========
require_once __DIR__ . '/utils/BDHandler.php';
require_once __DIR__ . '/utils/http_client.php';


$db = new TiendaDB();

// ========== RUTEO ==========
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$route = rtrim($uri, '/');

header('Content-Type: application/json');

$http = new HttpClient('http://localhost:3000/');


switch ($route) {

    case '/integrativa/categorias':
        if ($method === 'GET') {
            $data = $db->selectCategorias();
            echo json_encode(['success' => true, 'data' => $data]);
        } elseif ($method === 'POST') {
            $input = json_decode(file_get_contents("php://input"), true);
            $nombre = $input['nombre'] ?? null;
            $activo = isset($input['activo']) ? (bool) $input['activo'] : true;

            if (!$nombre)
                jsonErrorResponse(400, 'El nombre es requerido');

            $db->insertCategorias($nombre, $activo);
            echo json_encode(['success' => true, 'message' => 'Categoría creada']);
        } else {
            jsonErrorResponse(405, 'Método no permitido');
        }
        break;

    case '/integrativa/productos':
        if ($method === 'GET') {
            $data = $db->selectProductos();
            echo json_encode(['success' => true, 'data' => $data]);
        } elseif ($method === 'POST') {
            $input = json_decode(file_get_contents("php://input"), true);
            $sku = $input['sku'] ?? null;
            $nombre = $input['nombre'] ?? null;
            $id_categoria = $input['id_categoria'] ?? null;
            $precio = $input['precio'] ?? null;
            $stock = $input['stock'] ?? 0;
            $activo = isset($input['activo']) ? (bool) $input['activo'] : true;

            if (!$sku || !$nombre || !$id_categoria || !$precio) {
                jsonErrorResponse(400, 'Faltan campos obligatorios');
            }

            $db->insertProductos($sku, $nombre, $id_categoria, $precio, $stock, $activo);
            echo json_encode(['success' => true, 'message' => 'Producto creado']);
        } else {
            jsonErrorResponse(405, 'Método no permitido');
        }
        break;

    case '/integrativa/ventas':
        if ($method === 'GET') {
            print_r($http->request('GET', '/ventas'));

            $data = $db->selectVentas();

            //echo json_encode(['success' => true, 'data' => $data]);
        } elseif ($method === 'POST') {
            $input = json_decode(file_get_contents("php://input"), true);
            $id_producto = $input['id_producto'] ?? null;
            $cantidad = $input['cantidad'] ?? null;
            $precio_unitario = $input['precio_unitario'] ?? null;

            if (!$id_producto || !$cantidad || !$precio_unitario) {
                jsonErrorResponse(400, 'Faltan campos obligatorios');
            }

            $producto = $db->getProductoPorId($id_producto);

            if (!$producto) {
                jsonErrorResponse(404, 'Producto no encontrado');
            }

            if ($producto['stock'] < $cantidad) {
                jsonErrorResponse(400, 'No hay suficiente stock disponible', [
                    'stock_actual' => $producto['stock'],
                    'requerido' => $cantidad
                ]);
            }

            // Registrar venta y actualizar stock
            $db->insertVentas($id_producto, $cantidad, $precio_unitario);
            $nuevo_stock = $producto['stock'] - $cantidad;
            $db->updateStock($id_producto, $nuevo_stock);

            echo json_encode(['success' => true, 'message' => 'Venta registrada y stock actualizado']);
        } else {
            jsonErrorResponse(405, 'Método no permitido');
        }
        break;

    default:
        jsonErrorResponse(404, 'Ruta no encontrada', ['ruta' => $route, 'método' => $method]);
        break;


}
