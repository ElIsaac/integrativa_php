<?php
require_once __DIR__ . '/../utils/BDHandler.php';

$db = new TiendaDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $id_producto = $data['id_producto'] ?? null;
    $cantidad = $data['cantidad'] ?? 0;
    $precio_unitario = $data['precio_unitario'] ?? 0;

    $db->insertVentas($id_producto, $cantidad, $precio_unitario);
    echo json_encode(["success" => true, "message" => "Venta registrada"]);
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $ventas = $db->selectVentas();
    echo json_encode($ventas);
} else {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
}
