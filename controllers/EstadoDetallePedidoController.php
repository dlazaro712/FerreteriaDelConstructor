<?php
// Requerir la conexión a la base de datos y el modelo correspondiente
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/EstadoDetallePedido.php';

/**
 * Controlador para la gestión de EstadoDetallePedido
 */
class EstadoDetallePedidoController {
    private $db;
    private $detallePedidoModel;

    /**
     * Constructor que inicializa la conexión y el modelo
     */
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->detallePedidoModel = new EstadoDetallePedido($this->db);
    }

    /**
     * READ (Todos): Obtiene la lista completa de detalles de pedidos (con nombre de producto incluido)
     * @return array
     */
    public function index() {
        return $this->detallePedidoModel->obtenerTodos();
    }

    /**
     * READ (Uno): Obtiene un detalle de pedido específico por su ID
     * @param int $id_detalle
     * @return array|false
     */
    public function ver($id_detalle) {
        return $this->detallePedidoModel->obtenerPorId($id_detalle);
    }

    /**
     * READ (Por Pedido): Obtiene todos los detalles asociados a un ID de pedido en particular
     * @param int $id_pedido
     * @return array
     */
    public function listarPorPedido($id_pedido) {
        return $this->detallePedidoModel->obtenerPorPedido($id_pedido);
    }

    /**
     * CREATE: Procesa e inserta un nuevo registro de detalle de pedido
     * @param int $id_pedido
     * @param int $id_producto
     * @param int $cantidad
     * @param float $precio
     * @return bool
     */
    public function crear($id_pedido, $id_producto, $cantidad, $precio) {
        $this->detallePedidoModel->id_pedido = $id_pedido;
        $this->detallePedidoModel->id_producto = $id_producto;
        $this->detallePedidoModel->cantidad = $cantidad;
        $this->detallePedidoModel->precio = $precio;

        return $this->detallePedidoModel->insertar();
    }

    /**
     * UPDATE: Actualiza los datos de un detalle de pedido existente
     * @param int $id_detalle
     * @param int $id_pedido
     * @param int $id_producto
     * @param int $cantidad
     * @param float $precio
     * @return bool
     */
    public function actualizar($id_detalle, $id_pedido, $id_producto, $cantidad, $precio) {
        $this->detallePedidoModel->id_detalle = $id_detalle;
        $this->detallePedidoModel->id_pedido = $id_pedido;
        $this->detallePedidoModel->id_producto = $id_producto;
        $this->detallePedidoModel->cantidad = $cantidad;
        $this->detallePedidoModel->precio = $precio;

        return $this->detallePedidoModel->actualizar();
    }

    /**
     * DELETE: Elimina un detalle de pedido mediante su ID
     * @param int $id_detalle
     * @return bool
     */
    public function eliminar($id_detalle) {
        return $this->detallePedidoModel->eliminar($id_detalle);
    }
}
?>