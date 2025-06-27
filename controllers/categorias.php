<?php
require_once __DIR__ . '/../utils/BDHandler.php';

$db = new TiendaDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $nombre = $data['nombre'] ?? '';
    $activo = isset($data['activo']) ? (bool)$data['activo'] : true;
    
    $db->insertCategorias($nombre, $activo);
    echo json_encode(["success" => true, "message" => "Categoría insertada"]);
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $categorias = $db->selectCategorias();
    echo json_encode($categorias);
} else {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
}
