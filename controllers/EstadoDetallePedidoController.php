<?php
// controllers/DetallePedidoController.php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/EstadoDetallePedido.php';

/**
 * Controlador para la gestión de EstadoDetallePedido.
 * Sincronizado con la arquitectura híbrida e inserción masiva por pedido.
 */
class DetallePedidoController {
    private $db;
    private $detallePedidoModel;

    /**
     * Constructor que inicializa la conexión PDO y el modelo EstadoDetallePedido
     * @param PDO|null $dbConexion
     */
    public function __construct($dbConexion = null) {
        if ($dbConexion instanceof PDO) {
            $this->db = $dbConexion;
        } else {
            $database = new Database();
            $this->db = $database->getConnection();
        }
        $this->detallePedidoModel = new EstadoDetallePedido($this->db);
    }

    /**
     * READ (Todos): Obtiene la lista completa de detalles de pedidos (con nombre de producto)
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
        return $this->detallePedidoModel->obtenerPorId(intval($id_detalle));
    }

    /**
     * READ (Por Pedido): Obtiene todos los detalles asociados a un ID de pedido en particular
     * @param int $id_pedido
     * @return array
     */
    public function listarPorPedido($id_pedido) {
        return $this->detallePedidoModel->obtenerPorPedido(intval($id_pedido));
    }

    /**
     * CREATE (Individual): Procesa e inserta un registro individual de detalle de pedido
     * @param int $id_pedido
     * @param int $id_producto
     * @param int $cantidad
     * @param float $precio
     * @return bool
     */
    public function crear($id_pedido, $id_producto, $cantidad, $precio) {
        $this->detallePedidoModel->id_pedido = intval($id_pedido);
        $this->detallePedidoModel->id_producto = intval($id_producto);
        $this->detallePedidoModel->cantidad = intval($cantidad);
        $this->detallePedidoModel->precio = floatval($precio);

        return $this->detallePedidoModel->insertar();
    }

    /**
     * CREATE (Masivo): Inserta todos los productos del payload del carrito para un pedido
     * @param int $id_pedido
     * @param array $items
     * @return bool
     */
    public function crearMasivo($id_pedido, array $items) {
        if (empty($id_pedido) || empty($items)) {
            return false;
        }
        return $this->detallePedidoModel->insertarDetallesMasivos(intval($id_pedido), $items);
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
        $this->detallePedidoModel->id_detalle = intval($id_detalle);
        $this->detallePedidoModel->id_pedido = intval($id_pedido);
        $this->detallePedidoModel->id_producto = intval($id_producto);
        $this->detallePedidoModel->cantidad = intval($cantidad);
        $this->detallePedidoModel->precio = floatval($precio);

        return $this->detallePedidoModel->actualizar();
    }

    /**
     * DELETE: Elimina un detalle de pedido mediante su ID
     * @param int $id_detalle
     * @return bool
     */
    public function eliminar($id_detalle) {
        return $this->detallePedidoModel->eliminar(intval($id_detalle));
    }
}

// Alias de retrocompatibilidad por si se mantiene la llamada bajo el nombre de clase antiguo
if (!class_exists('EstadoDetallePedidoController')) {
    class EstadoDetallePedidoController extends DetallePedidoController {}
}
?>