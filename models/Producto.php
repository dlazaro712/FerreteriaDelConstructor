<?php
class Producto {
    private $conn;
    private $table = "productos";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * READ: Consultar todos los productos activos
     */
    public function leerTodos() {
        $query = "SELECT p.*, c.descripcion as categoria FROM " . $this->table . " p 
                  JOIN categorias c ON p.id_categoria = c.id_categoria WHERE p.estado = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    /**
     * READ: Consultar un producto específico por su ID
     */
    public function obtenerPorId($id) {
        $query = "SELECT p.*, c.descripcion as categoria FROM " . $this->table . " p 
                  JOIN categorias c ON p.id_categoria = c.id_categoria WHERE p.id_producto = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * CREATE: Registrar un nuevo producto en la base de datos
     */
    public function crear($data) {
        $query = "INSERT INTO " . $this->table . " (id_categoria, nombre, descripcion, precio, cantidad, imagen, estado) 
        VALUES (?, ?, ?, ?, ?, ?, 1)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            $data['id_categoria'], 
            $data['nombre'], 
            $data['descripcion'], 
            $data['precio'], 
            $data['cantidad'], 
            $data['imagen']
        ]);
    }

    /**
     * UPDATE: Actualizar la información de un producto existente
     */
    public function actualizar($id, $data) {
        $query = "UPDATE " . $this->table . " 
                  SET id_categoria = ?, nombre = ?, descripcion = ?, precio = ?, cantidad = ?, imagen = ? 
                  WHERE id_producto = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            $data['id_categoria'],
            $data['nombre'],
            $data['descripcion'],
            $data['precio'],
            $data['cantidad'],
            $data['imagen'],
            $id
        ]);
    }

    /**
     * DELETE (Eliminación lógica): Cambia el estado del producto a 0 (inactivo)
     * Recomendado para conservar la integridad referencial con los pedidos e historiales.
     */
    public function eliminarLogico($id) {
        $query = "UPDATE " . $this->table . " SET estado = 0 WHERE id_producto = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    /**
     * DELETE (Eliminación física): Borra el registro de la base de datos permanentemente
     */
    public function eliminarFisico($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id_producto = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }
}
?>