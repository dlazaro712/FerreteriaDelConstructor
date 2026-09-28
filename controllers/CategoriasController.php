<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Categoria.php';

class CategoriasController {
    private $db;
    private $categoriaModel;

    public function __construct() {
        // Inicializar conexión usando la configuración de database.php
        $database = new Database();
        $this->db = $database->getConnection();
        
        // Instanciar el modelo pasando la conexión PDO activa
        $this->categoriaModel = new Categoria($this->db);
    }

    // CREATE: Crear nueva categoría
    public function crear($data) {
        try {
            return $this->categoriaModel->crear($data);
        } catch (Exception $e) {
            error_log("Error en CategoriaController::crear -> " . $e->getMessage());
            return false;
        }
    }

    // READ: Listar todas las categorías
    public function listar() {
        try {
            return $this->categoriaModel->listar();
        } catch (Exception $e) {
            error_log("Error en CategoriaController::listar -> " . $e->getMessage());
            return [];
        }
    }

    // READ: Obtener una categoría por ID
    public function obtenerPorId($id) {
        try {
            return $this->categoriaModel->obtenerPorId($id);
        } catch (Exception $e) {
            error_log("Error en CategoriaController::obtenerPorId -> " . $e->getMessage());
            return null;
        }
    }

    // UPDATE: Actualizar categoría
    public function actualizar($id, $data) {
        try {
            return $this->categoriaModel->actualizar($id, $data);
        } catch (Exception $e) {
            error_log("Error en CategoriaController::actualizar -> " . $e->getMessage());
            return false;
        }
    }

    // DELETE: Eliminar categoría
    public function eliminar($id) {
        try {
            return $this->categoriaModel->eliminar($id);
        } catch (Exception $e) {
            error_log("Error en CategoriaController::eliminar -> " . $e->getMessage());
            return false;
        }
    }
}
?>