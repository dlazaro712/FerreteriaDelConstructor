<?php
require_once __DIR__ . '/../config/Database.php';

/**
 * Clase Modelo/DAO para la gestión de Productos utilizando PDO.
 */
class Producto {
    private $conn;
    private $table = "productos";

    /**
     * Constructor que acepta una conexión PDO opcional o la inicializa automáticamente.
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
     * READ: Consultar todos los productos activos
     * @return array
     */
    public function leerTodos() {
        try {
            $query = "SELECT p.*, c.descripcion as categoria FROM " . $this->table . " p 
                      JOIN categorias c ON p.id_categoria = c.id_categoria WHERE p.estado = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al leer productos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * READ: Consultar un producto específico por su ID
     * @param int $id
     * @return array|false
     */
    public function obtenerPorId($id) {
        try {
            $query = "SELECT p.*, c.descripcion as categoria FROM " . $this->table . " p 
                      JOIN categorias c ON p.id_categoria = c.id_categoria WHERE p.id_producto = ? LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener producto por ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * CREATE: Registrar un nuevo producto en la base de datos
     * @param array $data
     * @return bool
     */
    public function crear($data) {
        try {
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
        } catch (PDOException $e) {
            error_log("Error al crear producto: " . $e->getMessage());
            return false;
        }
    }

    /**
     * UPDATE: Actualizar la información de un producto existente
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function actualizar($id, $data) {
        try {
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
        } catch (PDOException $e) {
            error_log("Error al actualizar producto: " . $e->getMessage());
            return false;
        }
    }

    /**
     * DELETE (Eliminación lógica): Cambia el estado del producto a 0 (inactivo)
     * Recomendado para conservar la integridad referencial con los pedidos e historiales.
     * @param int $id
     * @return bool
     */
    public function eliminarLogico($id) {
        try {
            $query = "UPDATE " . $this->table . " SET estado = 0 WHERE id_producto = ?";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error en eliminación lógica de producto: " . $e->getMessage());
            return false;
        }
    }

    /**
     * DELETE (Eliminación física): Borra el registro de la base de datos permanentemente
     * @param int $id
     * @return bool
     */
    public function eliminarFisico($id) {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE id_producto = ?";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error en eliminación física de producto: " . $e->getMessage());
            return false;
        }
    }
}
?>