<?php
// controllers/PedidosController.php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Pedido.php';

class PedidosController {
    private $modeloPedido;

    public function __construct() {
        $this->modeloPedido = new Pedido();
    }

    /**
     * CREATE: Crear un nuevo pedido utilizando el modelo.
     */
    public function crearPedido($datosPedido, $itemsCarrito = []) {
        $resultado = $this->modeloPedido->crear($datosPedido, $itemsCarrito);
        
        if (isset($resultado['status']) && $resultado['status'] === 'success') {
            return [
                "status" => "success", 
                "mensaje" => "Pedido creado exitosamente.", 
                "id_pedido" => $resultado['id_pedido']
            ];
        } else {
            return [
                "status" => "error", 
                "mensaje" => "Error al procesar el pedido: " . ($resultado['message'] ?? 'Error desconocido')
            ];
        }
    }

    /**
     * READ: Listar todos los pedidos con información del usuario (Ideal para paneles administrativos).
     */
    public function listarPedidos() {
        return $this->modeloPedido->obtenerTodos();
    }

    /**
     * READ: Obtener un pedido específico junto con la información del usuario por ID.
     */
    public function obtenerPedidoPorId($id_pedido) {
        return $this->modeloPedido->obtenerPorId($id_pedido);
    }

    /**
     * READ: Listar pedidos filtrados por usuario (Para historial de compras del cliente).
     */
    public function listarPedidosPorUsuario($id_usuario) {
        return $this->modeloPedido->obtenerPorUsuario($id_usuario);
    }

    /**
     * UPDATE: Actualizar el estado de un pedido (Ej: 'Pendiente', 'Completado', 'Cancelado').
     */
    public function actualizarEstadoPedido($id_pedido, $nuevoEstado) {
        return $this->modeloPedido->actualizarEstado($id_pedido, $nuevoEstado);
    }

    /**
     * DELETE: Eliminar un pedido del sistema.
     */
    public function eliminarPedido($id_pedido) {
        return $this->modeloPedido->eliminar($id_pedido);
    }
}
?>