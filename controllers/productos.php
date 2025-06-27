<?php
require_once __DIR__ . '/../utils/BDHandler.php';

$db = new TiendaDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $sku = $data['sku'] ?? '';
    $nombre = $data['nombre'] ?? '';
    $id_categoria = $data['id_categoria'] ?? null;
    $precio = $data['precio'] ?? 0;
    $stock = $data['stock'] ?? 0;
    $activo = isset($data['activo']) ? (bool)$data['activo'] : true;

    $db->insertProductos($sku, $nombre, $id_categoria, $precio, $stock, $activo);
    echo json_encode(["success" => true, "message" => "Producto insertado"]);
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $productos = $db->selectProductos();
    echo json_encode($productos);
} else {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
}
