<?php
class TiendaDB {
    private $pdo;

    public function __construct() {
        $host = 'localhost';
        $db   = 'tiendamax_simple';
        $user = 'root'; // Cambia si tu usuario es distinto
        $pass = '';
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }

    // Insertar en categorias
    public function insertCategorias($nombre, $activo = true) {
        $sql = "INSERT INTO categorias (nombre, activo) VALUES (?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$nombre, $activo]);
    }

    // Consultar categorias
    public function selectCategorias() {
        $sql = "SELECT * FROM categorias";
        return $this->pdo->query($sql)->fetchAll();
    }

    // Insertar en productos
    public function insertProductos($sku, $nombre, $id_categoria, $precio, $stock = 0, $activo = true) {
        $sql = "INSERT INTO productos (sku, nombre, id_categoria, precio, stock, activo) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$sku, $nombre, $id_categoria, $precio, $stock, $activo]);
    }

    // Consultar productos
    public function selectProductos() {
        $sql = "SELECT * FROM productos";
        return $this->pdo->query($sql)->fetchAll();
    }

    // Insertar en ventas
    public function insertVentas($id_producto, $cantidad, $precio_unitario) {
        $sql = "INSERT INTO ventas (id_producto, cantidad, precio_unitario) VALUES (?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_producto, $cantidad, $precio_unitario]);
    }

    public function updateStock($id_producto, $new_stock){
        // sin imp;ementar
        return 0;
    }

    // Consultar ventas
    public function selectVentas() {
        $sql = "SELECT * FROM ventas";
        return $this->pdo->query($sql)->fetchAll();
    }
}
