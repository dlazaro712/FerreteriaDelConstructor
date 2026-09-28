<?php
include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/../models/Producto.php';

class ProductosController {
    private $db;
    private $productoModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->productoModel = new Producto($this->db);
    }

    /**
     * READ: Obtiene todos los productos activos para el catálogo
     * @return array
     */
    public function listarProductos() {
        // El modelo refactorizado ya retorna directamente el arreglo asociativo
        return $this->productoModel->leerTodos();
    }

    /**
     * READ: Obtiene un producto individual por su ID para la vista de detalles
     * @param int $id
     * @return array|false
     */
    public function obtenerProducto($id) {
        if (empty($id) || !is_numeric($id)) {
            return false;
        }
        // El modelo refactorizado ya retorna directamente el arreglo asociativo o false
        return $this->productoModel->obtenerPorId($id);
    }

    /**
     * CREATE: Procesa y valida la creación de un nuevo producto (Panel Admin)
     * @param array $data
     * @return bool
     */
    public function crearProducto($data) {
        // Reglas de negocio y validación básica
        if (empty($data['nombre']) || empty($data['precio']) || empty($data['id_categoria'])) {
            return false;
        }

        // Sanitización y normalización de datos
        $datosLimpios = [
            'id_categoria' => filter_var($data['id_categoria'], FILTER_SANITIZE_NUMBER_INT),
            'nombre'       => htmlspecialchars(strip_tags(trim($data['nombre']))),
            'descripcion'  => htmlspecialchars(strip_tags(trim($data['descripcion']))),
            'precio'       => filter_var($data['precio'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
            'cantidad'     => filter_var($data['cantidad'], FILTER_SANITIZE_NUMBER_INT),
            'imagen'       => htmlspecialchars(strip_tags(trim($data['imagen'] ?? 'default.jpg')))
        ];

        return $this->productoModel->crear($datosLimpios);
    }

    /**
     * UPDATE: Procesa la actualización de los datos de un producto existente
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function actualizarProducto($id, $data) {
        if (empty($id) || !is_numeric($id) || empty($data['nombre']) || empty($data['precio'])) {
            return false;
        }

        $datosLimpios = [
            'id_categoria' => filter_var($data['id_categoria'], FILTER_SANITIZE_NUMBER_INT),
            'nombre'       => htmlspecialchars(strip_tags(trim($data['nombre']))),
            'descripcion'  => htmlspecialchars(strip_tags(trim($data['descripcion']))),
            'precio'       => filter_var($data['precio'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
            'cantidad'     => filter_var($data['cantidad'], FILTER_SANITIZE_NUMBER_INT),
            'imagen'       => htmlspecialchars(strip_tags(trim($data['imagen'] ?? 'default.jpg')))
        ];

        return $this->productoModel->actualizar($id, $datosLimpios);
    }

    /**
     * DELETE: Realiza una baja lógica del producto para conservar integridad referencial en pedidos
     * @param int $id
     * @return bool
     */
    public function eliminarProducto($id) {
        if (empty($id) || !is_numeric($id)) {
            return false;
        }

        // Se recomienda la eliminación lógica para evitar errores con llaves foráneas en 'detalle_pedido'
        return $this->productoModel->eliminarLogico($id);
    }
}
?>