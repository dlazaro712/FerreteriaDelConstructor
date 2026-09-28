<?php
require_once __DIR__ . '/../config/Database.php';

class Usuario {
    private $db;
    private $table_name = "usuarios";

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function crear($datos) {
        try {
            $query = "INSERT INTO " . $this->table_name . " 
                      (nombre, apellido, correo, password, telefono, direccion, tipo_usuario) 
                      VALUES (:nombre, :apellido, :correo, :password, :telefono, :direccion, :tipo_usuario)";
            
            $stmt = $this->db->prepare($query);

            $nombre = htmlspecialchars(strip_tags($datos['nombre'] ?? ''));
            $apellido = htmlspecialchars(strip_tags($datos['apellido'] ?? ''));
            $correo = filter_var($datos['correo'] ?? '', FILTER_SANITIZE_EMAIL);
            $telefono = htmlspecialchars(strip_tags($datos['telefono'] ?? ''));
            $direccion = htmlspecialchars(strip_tags($datos['direccion'] ?? ''));
            $passwordHash = password_hash($datos['password'], PASSWORD_BCRYPT);
            $tipo_usuario = isset($datos['tipo_usuario']) ? htmlspecialchars(strip_tags($datos['tipo_usuario'])) : 'cliente';

            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':apellido', $apellido);
            $stmt->bindParam(':correo', $correo);
            $stmt->bindParam(':password', $passwordHash);
            $stmt->bindParam(':telefono', $telefono);
            $stmt->bindParam(':direccion', $direccion);
            $stmt->bindParam(':tipo_usuario', $tipo_usuario);

            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function listar() {
        try {
            $query = "SELECT id_usuario, nombre, apellido, correo, password, telefono, direccion, tipo_usuario 
                      FROM " . $this->table_name . " 
                      ORDER BY id_usuario DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function obtenerPorId($id_usuario) {
        try {
            $query = "SELECT id_usuario, nombre, apellido, correo, telefono, direccion, tipo_usuario 
                      FROM " . $this->table_name . " 
                      WHERE id_usuario = ?";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([$id_usuario]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    public function actualizar($id_usuario, $datos) {
        try {
            if (!empty($datos['password'])) {
                $query = "UPDATE " . $this->table_name . " 
                          SET nombre = :nombre, apellido = :apellido, correo = :correo, password = :password, telefono = :telefono, direccion = :direccion, tipo_usuario = :tipo_usuario 
                          WHERE id_usuario = :id_usuario";
                $passwordHash = password_hash($datos['password'], PASSWORD_BCRYPT);
            } else {
                $query = "UPDATE " . $this->table_name . " 
                          SET nombre = :nombre, apellido = :apellido, correo = :correo, telefono = :telefono, direccion = :direccion, tipo_usuario = :tipo_usuario 
                          WHERE id_usuario = :id_usuario";
            }

            $stmt = $this->db->prepare($query);

            $nombre = htmlspecialchars(strip_tags($datos['nombre']));
            $apellido = htmlspecialchars(strip_tags($datos['apellido']));
            $correo = filter_var($datos['correo'], FILTER_SANITIZE_EMAIL);
            $telefono = htmlspecialchars(strip_tags($datos['telefono']));
            $direccion = htmlspecialchars(strip_tags($datos['direccion']));
            $tipo_usuario = htmlspecialchars(strip_tags($datos['tipo_usuario']));

            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':apellido', $apellido);
            $stmt->bindParam(':correo', $correo);
            $stmt->bindParam(':telefono', $telefono);
            $stmt->bindParam(':direccion', $direccion);
            $stmt->bindParam(':tipo_usuario', $tipo_usuario);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);

            if (!empty($datos['password'])) {
                $stmt->bindParam(':password', $passwordHash);
            }

            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function eliminar($id_usuario) {
        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE id_usuario = ?";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([$id_usuario]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>