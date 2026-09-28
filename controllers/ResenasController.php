<?php
// controllers/ResenasController.php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Resena.php';

class ResenasController {
    private $db;
    private $resenaModel;

    public function __construct() {
        // Inicializa la conexión a la base de datos (ajustar según tu clase de conexión)
        $database = new Database();
        $this->db = $database->getConnection();
        $this->resenaModel = new Resena($this->db);
    }

    /**
     * Listar todas las reseñas
     */
    public function index() {
        return $this->resenaModel->obtenerTodas();
    }

    /**
     * Obtener detalle o reseñas por producto
     */
    public function mostrarPorProducto($id_producto) {
        return $this->resenaModel->obtenerPorProducto($id_producto);
    }

    /**
     * Crear una nueva reseña
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_usuario = $_POST['id_usuario'] ?? null;
            $id_producto = $_POST['id_producto'] ?? null;
            $calificacion = $_POST['calificacion'] ?? null;
            $comentario = $_POST['comentario'] ?? '';

            // Asegurar que BASE_URL esté disponible
            if (!defined('BASE_URL')) {
                require_once __DIR__ . '/../config/config.php';
            }

            if ($id_usuario && $id_producto && $calificacion) {
                $resultado = $this->resenaModel->crear($id_usuario, $id_producto, $calificacion, $comentario);
                if ($resultado) {
                    header("Location: " . BASE_URL . "views/clientes/resenas.php?status=success");
                    exit();
                } else {
                    header("Location: " . BASE_URL . "views/clientes/resenas.php?status=error");
                    exit();
                }
            } else {
                header("Location: " . BASE_URL . "views/clientes/resenas.php?status=campos_incompletos");
                exit();
            }
        }
    }

    /**
     * Actualizar una reseña existente
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_resena = $_POST['id_resena'] ?? null;
            $calificacion = $_POST['calificacion'] ?? null;
            $comentario = $_POST['comentario'] ?? '';

            if ($id_resena && $calificacion) {
                $resultado = $this->resenaModel->actualizar($id_resena, $calificacion, $comentario);
                if ($resultado) {
                    header("Location: ../views/clientes/resenas.php?status=updated");
                    exit();
                } else {
                    header("Location: ../views/clientes/resenas.php?status=error");
                    exit();
                }
            }
        }
    }

    /**
     * Eliminar una reseña
     */
    public function delete($id_resena) {
        if ($id_resena) {
            $resultado = $this->resenaModel->eliminar($id_resena);
            if ($resultado) {
                header("Location: ../views/clientes/resenas.php?status=deleted");
                exit();
            } else {
                header("Location: ../views/clientes/resenas.php?status=error");
                exit();
            }
        }
    }
}
?>