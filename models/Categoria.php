<?php
require_once __DIR__ . '/../config/Database.php';

class Categoria {
    private $db;
    private $table = 'categorias';

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    /**
     * Obtener todas las categorías
     * @return array
     */
    public function listar() {
        try {
            $query = "SELECT * FROM " . $this->table . " ORDER BY nombre ASC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Categoria::listar -> " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener una categoría por su ID
     * @param int $id
     * @return array|false
     */
    public function obtenerPorId($id) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE id_categoria = :id LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Categoria::obtenerPorId -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crear una nueva categoría
     * @param array $data [ 'nombre' => '...', 'descripcion' => '...' ]
     * @return bool|int ID de la categoría creada o false en caso de fallo
     */
    public function crear($data) {
        try {
            $query = "INSERT INTO " . $this->table . " (nombre, descripcion) VALUES (:nombre, :descripcion)";
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(':nombre', $data['nombre'], PDO::PARAM_STR);
            $stmt->bindParam(':descripcion', $data['descripcion'], PDO::PARAM_STR);

            if ($stmt->execute()) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error en Categoria::crear -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar una categoría existente
     * @param int $id
     * @param array $data [ 'nombre' => '...', 'descripcion' => '...' ]
     * @return bool
     */
    public function actualizar($id, $data) {
        try {
            $query = "UPDATE " . $this->table . " SET nombre = :nombre, descripcion = :descripcion WHERE id_categoria = :id";
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':nombre', $data['nombre'], PDO::PARAM_STR);
            $stmt->bindParam(':descripcion', $data['descripcion'], PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Categoria::actualizar -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar una categoría por su ID
     * @param int $id
     * @return bool
     */
    public function eliminar($id) {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE id_categoria = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Categoria::eliminar -> " . $e->getMessage());
            return false;
        }
    }
}