<?php
require_once __DIR__ . '/../config/Database.php';

class PedidosController {
    private $db;
    private $table_pedidos = "pedidos";
    private $table_detalle = "detalle_pedido"; // Ajusta el nombre si difiere en tu BD

    public function __construct() {
        $database = new Database();
        $this->db = $database->conectar();
    }

    /**
     * CREATE: Crear un nuevo pedido con su respectivo detalle (Transacción segura)
     * $datosPedido: [ 'id_usuario' => 1, 'total' => 450.00, 'direccion_envio' => '...', etc. ]
     * $itemsCarrito: [ [ 'id_producto' => 5, 'cantidad' => 2, 'precio_unitario' => 125.00 ], ... ]
     */
    public function crearPedido($datosPedido, $itemsCarrito) {
        try {
            // Iniciar transacción para garantizar la integridad de los datos (Pedido + Detalle + Stock)
            $this->db->beginTransaction();

            // 1. Insertar la cabecera del pedido
            $queryPedido = "INSERT INTO " . $this->table_pedidos . " 
                            (id_usuario, total, estado, fecha_creacion) 
                            VALUES (:id_usuario, :total, 'Pendiente', NOW())";
            
            $stmtPedido = $this->db->prepare($queryPedido);
            $stmtPedido->bindParam(':id_usuario', $datosPedido['id_usuario'], PDO::PARAM_INT);
            $stmtPedido->bindParam(':total', $datosPedido['total']);
            $stmtPedido->execute();

            // Obtener el ID del último pedido insertado
            $idPedidoGenerado = $this->db->lastInsertId();

            // 2. Insertar cada ítem en el detalle del pedido y descontar el stock del producto
            foreach ($itemsCarrito as $item) {
                $queryDetalle = "INSERT INTO " . $this->table_detalle . " 
                                 (id_pedido, id_producto, cantidad, precio_unitario) 
                                 VALUES (:id_pedido, :id_producto, :cantidad, :precio_unitario)";
                
                $stmtDetalle = $this->db->prepare($queryDetalle);
                $stmtDetalle->bindParam(':id_pedido', $idPedidoGenerado, PDO::PARAM_INT);
                $stmtDetalle->bindParam(':id_producto', $item['id_producto'], PDO::PARAM_INT);
                $stmtDetalle->bindParam(':cantidad', $item['cantidad'], PDO::PARAM_INT);
                $stmtDetalle->bindParam(':precio_unitario', $item['precio_unitario']);
                $stmtDetalle->execute();

                // Actualizar (descontar) el stock en la tabla productos
                $queryStock = "UPDATE productos SET cantidad = cantidad - :cantidad WHERE id_producto = :id_producto";
                $stmtStock = $this->db->prepare($queryStock);
                $stmtStock->bindParam(':cantidad', $item['cantidad'], PDO::PARAM_INT);
                $stmtStock->bindParam(':id_producto', $item['id_producto'], PDO::PARAM_INT);
                $stmtStock->execute();
            }

            // Confirmar transacción si todo sale bien
            $this->db->commit();
            return [
                "status" => "success", 
                "mensaje" => "Pedido creado exitosamente.", 
                "id_pedido" => $idPedidoGenerado
            ];

        } catch (Exception $e) {
            // Revertir cambios en caso de error
            $this->db->rollBack();
            return [
                "status" => "error", 
                "mensaje" => "Error al procesar el pedido: " . $e->getMessage()
            ];
        }
    }

    /**
     * READ: Listar todos los pedidos (Ideal para paneles administrativos)
     */
    public function listarPedidos() {
        try {
            $query = "SELECT p.*, u.nombre as cliente 
                      FROM " . $this->table_pedidos . " p 
                      LEFT JOIN usuarios u ON p.id_usuario = u.id_usuario 
                      ORDER BY p.id_pedido DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * READ: Obtener un pedido específico junto con sus detalles y productos asociados por ID
     */
    public function obtenerPedidoPorId($id_pedido) {
        try {
            // Obtener cabecera
            $queryPedido = "SELECT p.*, u.nombre as cliente, u.correo 
                            FROM " . $this->table_pedidos . " p 
                            LEFT JOIN usuarios u ON p.id_usuario = u.id_usuario 
                            WHERE p.id_pedido = ?";
            
            $stmt = $this->db->prepare($queryPedido);
            $stmt->execute([$id_pedido]);
            $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$pedido) {
                return null;
            }

            // Obtener detalle de productos del pedido
            $queryDetalle = "SELECT d.*, pr.nombre as producto, pr.imagen 
                             FROM " . $this->table_detalle . " d 
                             LEFT JOIN productos pr ON d.id_producto = pr.id_producto 
                             WHERE d.id_pedido = ?";
            
            $stmtDetalle = $this->db->prepare($queryDetalle);
            $stmtDetalle->execute([$id_pedido]);
            $pedido['detalles'] = $stmtDetalle->fetchAll(PDO::FETCH_ASSOC);

            return $pedido;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * READ: Listar pedidos filtrados por usuario (Para historial de compras del cliente)
     */
    public function listarPedidosPorUsuario($id_usuario) {
        try {
            $query = "SELECT * FROM " . $this->table_pedidos . " WHERE id_usuario = ? ORDER BY id_pedido DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$id_usuario]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * UPDATE: Actualizar el estado de un pedido (Ej: 'Pendiente', 'Completado', 'Cancelado')
     */
    public function actualizarEstadoPedido($id_pedido, $nuevoEstado) {
        try {
            $query = "UPDATE " . $this->table_pedidos . " SET estado = :estado WHERE id_pedido = :id_pedido";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':estado', $nuevoEstado);
            $stmt->bindParam(':id_pedido', $id_pedido, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * DELETE: Eliminar (o cancelar definitivamente) un pedido
     */
    public function eliminarPedido($id_pedido) {
        try {
            // Nota: En entornos de producción reales suele preferirse una baja lógica o cancelación en lugar de un DELETE físico para no romper contabilidad.
            $query = "DELETE FROM " . $this->table_pedidos . " WHERE id_pedido = ?";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([$id_pedido]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>