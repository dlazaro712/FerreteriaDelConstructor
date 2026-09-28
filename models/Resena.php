<?php
// models/Resena.php

require_once __DIR__ . '/../config/Database.php';

class Resena {
    private $conexion;

    /**
     * Constructor de la clase Resena.
     * Permite inyectar una conexión PDO existente o inicializar una nueva automáticamente.
     * 
     * @param PDO|null $db Conexión opcional a la base de datos vía PDO.
     */
    public function __construct($db = null) {
        if ($db !== null) {
            $this->conexion = $db;
        } else {
            $database = new Database();
            $this->conexion = $database->getConnection();
        }
    }

    /**
     * CREATE: Agregar una nueva reseña
     */
    public function crear($id_usuario, $id_producto, $calificacion, $comentario) {
        $query = "INSERT INTO resenas (id_usuario, id_producto, calificacion, comentario, fecha) VALUES (:id_usuario, :id_producto, :calificacion, :comentario, NOW())";
        $stmt = $this->conexion->prepare($query);
        
        if ($stmt) {
            return $stmt->execute([
                ':id_usuario'   => $id_usuario,
                ':id_producto'  => $id_producto,
                ':calificacion' => $calificacion,
                ':comentario'   => $comentario
            ]);
        }
        return false;
    }

    /**
     * READ: Obtener todas las reseñas con información básica
     */
    public function obtenerTodas() {
        $query = "SELECT r.*, u.nombre AS usuario_nombre, p.nombre AS producto_nombre 
                  FROM resenas r 
                  LEFT JOIN usuarios u ON r.id_usuario = u.id_usuario 
                  LEFT JOIN productos p ON r.id_producto = p.id_producto 
                  ORDER BY r.fecha DESC";
        $stmt = $this->conexion->query($query);
        
        if ($stmt) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return [];
    }

    /**
     * READ: Obtener una reseña por su ID
     */
    public function obtenerPorId($id_resena) {
        $query = "SELECT * FROM resenas WHERE id_resena = :id_resena";
        $stmt = $this->conexion->prepare($query);
        
        if ($stmt) {
            $stmt->execute([':id_resena' => $id_resena]);
            $resena = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resena ? $resena : null;
        }
        return null;
    }

    /**
     * READ: Obtener reseñas asociadas a un producto específico
     */
    public function obtenerPorProducto($id_producto) {
        $query = "SELECT r.*, u.nombre AS usuario_nombre 
                  FROM resenas r 
                  LEFT JOIN usuarios u ON r.id_usuario = u.id_usuario 
                  WHERE r.id_producto = :id_producto 
                  ORDER BY r.fecha DESC";
        $stmt = $this->conexion->prepare($query);
        
        if ($stmt) {
            $stmt->execute([':id_producto' => $id_producto]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return [];
    }

    /**
     * UPDATE: Actualizar una reseña existente
     */
    public function actualizar($id_resena, $calificacion, $comentario) {
        $query = "UPDATE resenas SET calificacion = :calificacion, comentario = :comentario WHERE id_resena = :id_resena";
        $stmt = $this->conexion->prepare($query);
        
        if ($stmt) {
            return $stmt->execute([
                ':calificacion' => $calificacion,
                ':comentario'   => $comentario,
                ':id_resena'    => $id_resena
            ]);
        }
        return false;
    }

    /**
     * DELETE: Eliminar una reseña por su ID
     */
    public function eliminar($id_resena) {
        $query = "DELETE FROM resenas WHERE id_resena = :id_resena";
        $stmt = $this->conexion->prepare($query);
        
        if ($stmt) {
            return $stmt->execute([':id_resena' => $id_resena]);
        }
        return false;
    }
}
?>