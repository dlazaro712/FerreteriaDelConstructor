<?php
// models/Pedido.php
require_once __DIR__ . '/../config/Database.php';

class Pedido {
    private $db;
    private $table = "pedidos";

    // Propiedades del Modelo
    public $id_pedido;
    public $id_usuario;
    public $total;
    public $estado;
    public $fecha;

    public function __construct() {
        $database = new Database();
        // Compatibilidad con ambos nombres comunes de métodos de conexión (conectar / getConnection)
        $this->db = method_exists($database, 'conectar') ? $database->conectar() : $database->getConnection();
    }

    /**
     * Obtener todos los pedidos registrados en el sistema con información del usuario.
     */
    public function obtenerTodos() {
        try {
            $query = "SELECT p.*, u.nombre as cliente, u.correo 
                      FROM " . $this->table . " p 
                      LEFT JOIN usuarios u ON p.id_usuario = u.id_usuario 
                      ORDER BY p.fecha DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Obtener un pedido específico por su ID junto con la información del usuario.
     */
    public function obtenerPorId($id) {
        try {
            $query = "SELECT p.*, u.nombre as cliente, u.correo 
                      FROM " . $this->table . " p 
                      LEFT JOIN usuarios u ON p.id_usuario = u.id_usuario 
                      WHERE p.id_pedido = ?";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Obtener todos los pedidos realizados por un usuario en específico.
     */
    public function obtenerPorUsuario($id_usuario) {
        try {
            $query = "SELECT p.*, u.nombre as cliente, u.correo 
                      FROM " . $this->table . " p 
                      LEFT JOIN usuarios u ON p.id_usuario = u.id_usuario 
                      WHERE p.id_usuario = ? 
                      ORDER BY p.fecha DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([$id_usuario]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Crear un nuevo pedido en la base de datos (sin tabla detalle_pedido).
     */
    public function crear($datosPedido, $itemsCarrito = []) {
        try {
            $this->db->beginTransaction();

            // 1. Insertar la cabecera del pedido en la tabla pedidos
            $query = "INSERT INTO " . $this->table . " (id_usuario, total, estado, fecha) 
                      VALUES (:id_usuario, :total, :estado, NOW())";
            
            $stmt = $this->db->prepare($query);
            $estadoInicial = 'Pendiente';
            
            $stmt->bindParam(':id_usuario', $datosPedido['id_usuario'], PDO::PARAM_INT);
            $stmt->bindParam(':total', $datosPedido['total']);
            $stmt->bindParam(':estado', $estadoInicial);
            $stmt->execute();

            $id_pedido_generado = $this->db->lastInsertId();

            // 2. Descontar stock de los productos si se proporcionan los ítems
            if (!empty($itemsCarrito)) {
                foreach ($itemsCarrito as $item) {
                    $queryStock = "UPDATE productos SET cantidad = cantidad - :cantidad WHERE id_producto = :id_producto";
                    $stmtStock = $this->db->prepare($queryStock);
                    $stmtStock->bindParam(':cantidad', $item['cantidad'], PDO::PARAM_INT);
                    $stmtStock->bindParam(':id_producto', $item['id_producto'], PDO::PARAM_INT);
                    $stmtStock->execute();
                }
            }

            $this->db->commit();
            return ["status" => "success", "id_pedido" => $id_pedido_generado];

        } catch (Exception $e) {
            $this->db->rollBack();
            return ["status" => "error", "message" => $e->getMessage()];
        }
    }

    /**
     * Actualizar el estado de un pedido existente.
     */
    public function actualizarEstado($id, $nuevoEstado) {
        try {
            $query = "UPDATE " . $this->table . " SET estado = :estado WHERE id_pedido = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':estado', $nuevoEstado);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Eliminar un pedido del sistema.
     */
    public function eliminar($id) {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE id_pedido = ?";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>