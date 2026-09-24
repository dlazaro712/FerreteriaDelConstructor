<?php
require_once __DIR__ . '/../config/Database.php';

class UsuariosController {
    private $db;
    private $table_name = "usuarios";

    public function __construct() {
        $database = new Database();
        $this->db = $database->conectar();
    }

    /**
     * CREATE: Registrar un nuevo usuario en la base de datos
     * $datos: [ 'nombre' => '...', 'correo' => '...', 'password' => '...', 'tipo_usuario' => 'cliente'/'admin' ]
     */
    public function crear($datos) {
        try {
            $query = "INSERT INTO " . $this->table_name . " 
                      (nombre, correo, password, tipo_usuario, fecha_registro) 
                      VALUES (:nombre, :correo, :password, :tipo_usuario, NOW())";
            
            $stmt = $this->db->prepare($query);

            // Sanitizar y proteger datos
            $nombre = htmlspecialchars(strip_tags($datos['nombre']));
            $correo = filter_var($datos['correo'], FILTER_SANITIZE_EMAIL);
            
            // Cifrar la contraseña de forma segura
            $passwordHash = password_hash($datos['password'], PASSWORD_BCRYPT);
            
            // Definir tipo de usuario por defecto si no viene especificado
            $tipo_usuario = isset($datos['tipo_usuario']) ? htmlspecialchars(strip_tags($datos['tipo_usuario'])) : 'cliente';

            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':correo', $correo);
            $stmt->bindParam(':password', $passwordHash);
            $stmt->bindParam(':tipo_usuario', $tipo_usuario);

            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * READ: Listar todos los usuarios registrados en el sistema
     */
    public function listar() {
        try {
            $query = "SELECT id_usuario, nombre, correo, tipo_usuario, fecha_registro 
                      FROM " . $this->table_name . " 
                      ORDER BY id_usuario DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * READ: Obtener un usuario específico por su ID
     */
    public function obtenerPorId($id_usuario) {
        try {
            $query = "SELECT id_usuario, nombre, correo, tipo_usuario, fecha_registro 
                      FROM " . $this->table_name . " 
                      WHERE id_usuario = ?";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([$id_usuario]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * UPDATE: Actualizar los datos de un usuario existente
     */
    public function actualizar($id_usuario, $datos) {
        try {
            // Validar si se incluye una nueva contraseña o se mantienen los datos básicos
            if (!empty($datos['password'])) {
                $query = "UPDATE " . $this->table_name . " 
                          SET nombre = :nombre, correo = :correo, password = :password, tipo_usuario = :tipo_usuario 
                          WHERE id_usuario = :id_usuario";
                $passwordHash = password_hash($datos['password'], PASSWORD_BCRYPT);
            } else {
                $query = "UPDATE " . $this->table_name . " 
                          SET nombre = :nombre, correo = :correo, tipo_usuario = :tipo_usuario 
                          WHERE id_usuario = :id_usuario";
            }

            $stmt = $this->db->prepare($query);

            $nombre = htmlspecialchars(strip_tags($datos['nombre']));
            $correo = filter_var($datos['correo'], FILTER_SANITIZE_EMAIL);
            $tipo_usuario = htmlspecialchars(strip_tags($datos['tipo_usuario']));

            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':correo', $correo);
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

    /**
     * DELETE: Eliminar un usuario de la base de datos
     */
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