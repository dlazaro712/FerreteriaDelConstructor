<?php
require_once __DIR__ . '/../config/Database.php';

class Usuario {
    private $db;
    private $table_name = "usuarios";

    // Propiedades del Modelo según la estructura de la base de datos
    public $id_usuario;
    public $nombre;
    public $correo;
    public $password;
    public $tipo_usuario;
    public $fecha_registro;

    public function __construct() {
        $database = new Database();
        $this->db = $database->conectar();
    }

    /**
     * CREATE: Registrar un nuevo usuario en la base de datos
     */
    public function crear() {
        try {
            $query = "INSERT INTO " . $this->table_name . " 
                      (nombre, correo, password, tipo_usuario, fecha_registro) 
                      VALUES (:nombre, :correo, :password, :tipo_usuario, NOW())";
            
            $stmt = $this->db->prepare($query);

            // Sanitizar datos
            $this->nombre       = htmlspecialchars(strip_tags($this->nombre));
            $this->correo       = filter_var($this->correo, FILTER_SANITIZE_EMAIL);
            $passwordHash       = password_hash($this->password, PASSWORD_BCRYPT);
            $this->tipo_usuario = !empty($this->tipo_usuario) ? htmlspecialchars(strip_tags($this->tipo_usuario)) : 'cliente';

            // Vincular parámetros
            $stmt->bindParam(':nombre', $this->nombre);
            $stmt->bindParam(':correo', $this->correo);
            $stmt->bindParam(':password', $passwordHash);
            $stmt->bindParam(':tipo_usuario', $this->tipo_usuario);

            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * READ: Obtener todos los usuarios registrados
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
     * READ: Obtener un solo usuario por su ID
     */
    public function obtenerPorId() {
        try {
            $query = "SELECT id_usuario, nombre, correo, tipo_usuario, fecha_registro 
                      FROM " . $this->table_name . " 
                      WHERE id_usuario = ? LIMIT 0,1";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([$this->id_usuario]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $this->nombre       = $row['nombre'];
                $this->correo       = $row['correo'];
                $this->tipo_usuario = $row['tipo_usuario'];
                $this->fecha_registro = $row['fecha_registro'];
                return $row;
            }

            return null;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * UPDATE: Actualizar la información de un usuario
     */
    public function actualizar() {
        try {
            if (!empty($this->password)) {
                $query = "UPDATE " . $this->table_name . " 
                          SET nombre = :nombre, correo = :correo, password = :password, tipo_usuario = :tipo_usuario 
                          WHERE id_usuario = :id_usuario";
                $passwordHash = password_hash($this->password, PASSWORD_BCRYPT);
            } else {
                $query = "UPDATE " . $this->table_name . " 
                          SET nombre = :nombre, correo = :correo, tipo_usuario = :tipo_usuario 
                          WHERE id_usuario = :id_usuario";
            }

            $stmt = $this->db->prepare($query);

            $this->nombre       = htmlspecialchars(strip_tags($this->nombre));
            $this->correo       = filter_var($this->correo, FILTER_SANITIZE_EMAIL);
            $this->tipo_usuario = htmlspecialchars(strip_tags($this->tipo_usuario));
            $this->id_usuario   = intval($this->id_usuario);

            $stmt->bindParam(':nombre', $this->nombre);
            $stmt->bindParam(':correo', $this->correo);
            $stmt->bindParam(':tipo_usuario', $this->tipo_usuario);
            $stmt->bindParam(':id_usuario', $this->id_usuario, PDO::PARAM_INT);

            if (!empty($this->password)) {
                $stmt->bindParam(':password', $passwordHash);
            }

            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * DELETE: Eliminar un usuario permanentemente de la base de datos
     */
    public function eliminar() {
        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE id_usuario = ?";
            $stmt = $this->db->prepare($query);
            $this->id_usuario = intval($this->id_usuario);
            $stmt->bindParam(1, $this->id_usuario, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>