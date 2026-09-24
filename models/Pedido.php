<?php
require_once __DIR__ . '/../config/Database.php';

class Pedido {
    private $db;
    private $table = "pedidos";
    private $table_detalle = "detalle_pedido";

    // Propiedades del Modelo
    public $id_pedido;
    public $id_usuario;
    public $total;
    public $estado;
    public $fecha_creacion;

    public function __construct() {
        $database = new Database();
        $this->db = $database->conectar();
    }

    /**
     * Obtener todos los pedidos registrados en el sistema con información del usuario.
     */
    public function obtenerTodos() {
        try {
            $query = "SELECT p.*, u.nombre as cliente, u.correo 
                      FROM " . $this->table . " p 
                      LEFT JOIN usuarios u ON p.id_usuario = u.id_usuario 
                      ORDER BY p.fecha_creacion DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Obtener un pedido específico por su ID junto con sus ítems (detalles).
     */
    public function obtenerPorId($id) {
        try {
            $query = "SELECT p.*, u.nombre as cliente, u.correo 
                      FROM " . $this->table . " p 
                      LEFT JOIN usuarios u ON p.id_usuario = u.id_usuario 
                      WHERE p.id_pedido = ?";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([$id]);
            $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($pedido) {
                // Consultar los detalles de los productos asociados a este pedido
                $queryDetalle = "SELECT d.*, pr.nombre as producto, pr.imagen 
                                 FROM " . $this->table_detalle . " d 
                                 LEFT JOIN productos pr ON d.id_producto = pr.id_producto 
                                 WHERE d.id_pedido = ?";
                $stmtDetalle = $this->db->prepare($queryDetalle);
                $stmtDetalle->execute([$id]);
                $pedido['detalles'] = $stmtDetalle->fetchAll(PDO::FETCH_ASSOC);
            }

            return $pedido;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Obtener todos los pedidos realizados por un usuario en específico.
     */
    public function obtenerPorUsuario($id_usuario) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE id_usuario = ? ORDER BY fecha_creacion DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$id_usuario]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Crear un nuevo pedido en la base de datos (con soporte para transacción y detalle).
     */
    public function crear($datosPedido, $itemsCarrito) {
        try {
            $this->db->beginTransaction();

            // 1. Insertar la cabecera del pedido
            $query = "INSERT INTO " . $this->table . " (id_usuario, total, estado, fecha_creacion) 
                      VALUES (:id_usuario, :total, :estado, NOW())";
            
            $stmt = $this->db->prepare($query);
            $estadoInicial = 'Pendiente';
            
            $stmt->bindParam(':id_usuario', $datosPedido['id_usuario'], PDO::PARAM_INT);
            $stmt->bindParam(':total', $datosPedido['total']);
            $stmt->bindParam(':estado', $estadoInicial);
            $stmt->execute();

            $id_pedido_generado = $this->db->lastInsertId();

            // 2. Insertar los detalles y descontar stock de los productos
            foreach ($itemsCarrito as $item) {
                $queryDetalle = "INSERT INTO " . $this->table_detalle . " (id_pedido, id_producto, cantidad, precio_unitario) 
                                 VALUES (:id_pedido, :id_producto, :cantidad, :precio_unitario)";
                
                $stmtDetalle = $this->db->prepare($queryDetalle);
                $stmtDetalle->bindParam(':id_pedido', $id_pedido_generado, PDO::PARAM_INT);
                $stmtDetalle->bindParam(':id_producto', $item['id_producto'], PDO::PARAM_INT);
                $stmtDetalle->bindParam(':cantidad', $item['cantidad'], PDO::PARAM_INT);
                $stmtDetalle->bindParam(':precio_unitario', $item['precio_unitario']);
                $stmtDetalle->execute();

                // Descontar inventario
                $queryStock = "UPDATE productos SET cantidad = cantidad - :cantidad WHERE id_producto = :id_producto";
                $stmtStock = $this->db->prepare($queryStock);
                $stmtStock->bindParam(':cantidad', $item['cantidad'], PDO::PARAM_INT);
                $stmtStock->bindParam(':id_producto', $item['id_producto'], PDO::PARAM_INT);
                $stmtStock->execute();
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
     * Eliminar (o cancelar) un pedido del sistema.
     */
    public function eliminar($id) {
        try {
            // Nota: Se pueden eliminar también los detalles asociados si no hay restricciones de FK en cascada
            $queryDetalle = "DELETE FROM " . $this->table_detalle . " WHERE id_pedido = ?";
            $stmtDetalle = $this->db->prepare($queryDetalle);
            $stmtDetalle->execute([$id]);

            $query = "DELETE FROM " . $this->table . " WHERE id_pedido = ?";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>