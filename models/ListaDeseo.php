<?php
// models/ListaDeseo.php

require_once __DIR__ . '/../config/Database.php';

class ListaDeseo {
    private $conn;
    private $table_name = "wishlist";

    /**
     * Constructor flexible con soporte para inyección de dependencias o instancia directa.
     * @param PDO|null $dbConexion
     */
    public function __construct($dbConexion = null) {
        if ($dbConexion instanceof PDO) {
            $this->conn = $dbConexion;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
    }

    /**
     * CREATE: Agregar un producto a la lista de deseos de un usuario
     */
    public function agregar($id_usuario, $id_producto) {
        try {
            // Evitar duplicados para el mismo usuario y producto
            if ($this->existeEnWishlist($id_usuario, $id_producto)) {
                return true; // Consideramos éxito si ya está registrado
            }

            $query = "INSERT INTO " . $this->table_name . " (id_usuario, id_producto) VALUES (:id_usuario, :id_producto)";
            $stmt = $this->conn->prepare($query);

            return $stmt->execute([
                ':id_usuario'  => intval($id_usuario),
                ':id_producto' => intval($id_producto)
            ]);
        } catch (PDOException $e) {
            error_log("Error en ListaDeseo::agregar: " . $e->getMessage());
            return false;
        }
    }

    /**
     * READ: Listar todos los registros de la wishlist con nombres de usuario y producto
     */
    public function listarTodos() {
        try {
            $query = "SELECT w.id_wishlist, w.id_usuario, w.id_producto,
                             u.nombre as nombre_usuario, 
                             p.nombre as nombre_producto,
                             p.precio, p.imagen, p.descripcion, p.cantidad as stock
                      FROM " . $this->table_name . " w
                      INNER JOIN usuarios u ON w.id_usuario = u.id_usuario
                      INNER JOIN productos p ON w.id_producto = p.id_producto
                      ORDER BY w.id_wishlist DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log("Error en ListaDeseo::listarTodos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * READ: Obtener los elementos de la lista de deseos de un usuario específico
     */
    public function listarPorUsuario($id_usuario) {
        try {
            $query = "SELECT w.id_wishlist, w.id_usuario, w.id_producto,
                             u.nombre as nombre_usuario, 
                             p.nombre as nombre_producto,
                             p.precio, p.imagen, p.descripcion, p.cantidad as stock
                      FROM " . $this->table_name . " w
                      INNER JOIN usuarios u ON w.id_usuario = u.id_usuario
                      INNER JOIN productos p ON w.id_producto = p.id_producto
                      WHERE w.id_usuario = :id_usuario
                      ORDER BY w.id_wishlist DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id_usuario' => intval($id_usuario)]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log("Error en ListaDeseo::listarPorUsuario: " . $e->getMessage());
            return [];
        }
    }

    /**
     * UPDATE: Actualizar un registro de la lista de deseos
     */
    public function actualizar($id_wishlist, $id_usuario, $id_producto) {
        try {
            $query = "UPDATE " . $this->table_name . " 
                      SET id_usuario = :id_usuario, id_producto = :id_producto 
                      WHERE id_wishlist = :id_wishlist";

            $stmt = $this->conn->prepare($query);

            return $stmt->execute([
                ':id_usuario'  => intval($id_usuario),
                ':id_producto' => intval($id_producto),
                ':id_wishlist' => intval($id_wishlist)
            ]);
        } catch (PDOException $e) {
            error_log("Error en ListaDeseo::actualizar: " . $e->getMessage());
            return false;
        }
    }

    /**
     * DELETE: Eliminar un elemento de la lista de deseos por su ID
     */
    public function eliminar($id_wishlist) {
        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE id_wishlist = ?";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([intval($id_wishlist)]);
        } catch (PDOException $e) {
            error_log("Error en ListaDeseo::eliminar: " . $e->getMessage());
            return false;
        }
    }

    /**
     * MÉTODO AUXILIAR: Verificar si ya existe el producto en la wishlist del usuario
     */
    private function existeEnWishlist($id_usuario, $id_producto) {
        try {
            $query = "SELECT id_wishlist FROM " . $this->table_name . " WHERE id_usuario = ? AND id_producto = ? LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([intval($id_usuario), intval($id_producto)]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error en ListaDeseo::existeEnWishlist: " . $e->getMessage());
            return false;
        }
    }
}
?>