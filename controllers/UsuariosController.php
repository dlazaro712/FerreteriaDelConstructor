<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Usuario.php';

class UsuariosController {
    private $db;
    private $modeloUsuario;

    public function __construct() {
        // Inicializar la conexión usando Database.php
        $database = new Database();
        $this->db = $database->getConnection();

        // Instanciar el modelo Usuario pasándole la conexión si es necesario, 
        // o dejando que el modelo gestione su propia instancia.
        $this->modeloUsuario = new Usuario();
    }

    /**
     * CREATE: Registrar un nuevo usuario
     */
    public function crear($datos) {
        return $this->modeloUsuario->crear($datos);
    }

    /**
     * READ: Listar todos los usuarios
     */
    public function listar() {
        return $this->modeloUsuario->listar();
    }

    /**
     * READ: Obtener un usuario por su ID
     */
    public function obtenerPorId($id_usuario) {
        return $this->modeloUsuario->obtenerPorId($id_usuario);
    }

    /**
     * UPDATE: Actualizar los datos de un usuario
     */
    public function actualizar($id_usuario, $datos) {
        return $this->modeloUsuario->actualizar($id_usuario, $datos);
    }

    /**
     * DELETE: Eliminar un usuario
     */
    public function eliminar($id_usuario) {
        return $this->modeloUsuario->eliminar($id_usuario);
    }
}
?>