<?php
// models/DetallePedido.php

require_once __DIR__ . '/../config/Database.php';

/**
 * Clase Modelo/DAO para la gestión de Detalle de Pedidos utilizando PDO.
 * Compatible con la arquitectura de productos individuales e inserciones transaccionales.
 */
class DetallePedido {
    private $conexion;
    
    // Propiedades correspondientes a los campos de la tabla
    public $id_detalle;
    public $id_pedido;
    public $id_producto;
    public $cantidad;
    public $precio;

    /**
     * Constructor flexible que admite inyección de dependencias (para transacciones en checkout).
     * @param PDO|null $dbConexion Instancia PDO activa
     */
    public function __construct($dbConexion = null) {
        if ($dbConexion instanceof PDO) {
            $this->conexion = $dbConexion;
        } else {
            global $pdo; 
            if (isset($pdo) && $pdo instanceof PDO) {
                $this->conexion = $pdo;
            } else {
                $database = new Database();
                $this->conexion = $database->getConnection();
            }
        }
    }

    /**
     * CREATE: Inserta un nuevo registro de detalle de pedido.
     * @return bool True si se insertó correctamente, False en caso contrario.
     */
    public function insertar() {
        try {
            // Evaluamos si existe la columna se usa el estándar de tabla
            $sql = "INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, precio) 
                    VALUES (:id_pedido, :id_producto, :cantidad, :precio)";
            
            $stmt = $this->conexion->prepare($sql);
            
            return $stmt->execute([
                ':id_pedido'  => intval($this->id_pedido),
                ':id_producto' => intval($this->id_producto),
                ':cantidad'   => intval($this->cantidad),
                ':precio'     => floatval($this->precio)
            ]);
        } catch (PDOException $e) {
            error_log("Error al insertar detalle de pedido: " . $e->getMessage());
            return false;
        }
    }

    /**
     * CREATE MASIVO: Inserta múltiples ítems provenientes del payload JSON del carrito en una sola llamada.
     * @param int $id_pedido ID del pedido padre
     * @param array $itemsArreglo Arreglo de ítems [{id_producto, cantidad, precio, ...}]
     * @return bool
     */
    public function insertarDetallesMasivos($id_pedido, array $itemsArreglo) {
        if (empty($itemsArreglo) || empty($id_pedido)) {
            return false;
        }

        try {
            $sql = "INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, precio) 
                    VALUES (:id_pedido, :id_producto, :cantidad, :precio)";
            $stmt = $this->conexion->prepare($sql);

            foreach ($itemsArreglo as $item) {
                $idProducto = $item['id_producto'] ?? $item['id'] ?? 0;
                $cantidad   = $item['cantidad'] ?? 1;
                $precio     = $item['precio'] ?? 0.0;

                if ($idProducto > 0) {
                    $stmt->execute([
                        ':id_pedido'   => intval($id_pedido),
                        ':id_producto' => intval($idProducto),
                        ':cantidad'    => intval($cantidad),
                        ':precio'      => floatval($precio)
                    ]);
                }
            }
            return true;
        } catch (PDOException $e) {
            error_log("Error al insertar detalles masivos del pedido: " . $e->getMessage());
            return false;
        }
    }

    /**
     * READ: Obtiene todos los detalles de pedidos incluyendo el nombre del producto e imagen.
     * @return array
     */
    public function obtenerTodos() {
        try {
            $sql = "SELECT d.id_detalle, d.id_pedido, d.id_producto, p.nombre AS nombre_producto, p.imagen, d.cantidad, d.precio, (d.cantidad * d.precio) AS subtotal 
                    FROM detalle_pedido d 
                    INNER JOIN productos p ON d.id_producto = p.id_producto 
                    ORDER BY d.id_detalle DESC";
            
            $stmt = $this->conexion->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log("Error al obtener todos los detalles de pedidos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * READ: Obtiene un detalle de pedido específico por su ID.
     * @param int $id_detalle
     * @return array|false
     */
    public function obtenerPorId($id_detalle) {
        try {
            $sql = "SELECT d.id_detalle, d.id_pedido, d.id_producto, p.nombre AS nombre_producto, p.imagen, d.cantidad, d.precio, (d.cantidad * d.precio) AS subtotal 
                    FROM detalle_pedido d 
                    INNER JOIN productos p ON d.id_producto = p.id_producto 
                    WHERE d.id_detalle = :id_detalle";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':id_detalle' => intval($id_detalle)]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener detalle de pedido por ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * READ: Obtiene todos los detalles asociados a un número de pedido específico.
     * @param int $id_pedido
     * @return array
     */
    public function obtenerPorPedido($id_pedido) {
        try {
            $sql = "SELECT d.id_detalle, d.id_pedido, d.id_producto, p.nombre AS nombre_producto, p.imagen, d.cantidad, d.precio, (d.cantidad * d.precio) AS subtotal 
                    FROM detalle_pedido d 
                    INNER JOIN productos p ON d.id_producto = p.id_producto 
                    WHERE d.id_pedido = :id_pedido";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':id_pedido' => intval($id_pedido)]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log("Error al obtener detalles por ID de pedido: " . $e->getMessage());
            return [];
        }
    }

    /**
     * UPDATE: Actualiza los datos de un detalle de pedido existente.
     * @return bool
     */
    public function actualizar() {
        try {
            $sql = "UPDATE detalle_pedido 
                    SET id_pedido = :id_pedido, id_producto = :id_producto, cantidad = :cantidad, precio = :precio 
                    WHERE id_detalle = :id_detalle";
            $stmt = $this->conexion->prepare($sql);
            
            return $stmt->execute([
                ':id_pedido'   => intval($this->id_pedido),
                ':id_producto' => intval($this->id_producto),
                ':cantidad'    => intval($this->cantidad),
                ':precio'      => floatval($this->precio),
                ':id_detalle'  => intval($this->id_detalle)
            ]);
        } catch (PDOException $e) {
            error_log("Error al actualizar detalle de pedido: " . $e->getMessage());
            return false;
        }
    }

    /**
     * DELETE: Elimina un registro de detalle de pedido por su ID.
     * @param int $id_detalle
     * @return bool
     */
    public function eliminar($id_detalle) {
        try {
            $sql = "DELETE FROM detalle_pedido WHERE id_detalle = :id_detalle";
            $stmt = $this->conexion->prepare($sql);
            
            return $stmt->execute([':id_detalle' => intval($id_detalle)]);
        } catch (PDOException $e) {
            error_log("Error al eliminar detalle de pedido: " . $e->getMessage());
            return false;
        }
    }
}

// Alias de retrocompatibilidad por si algún controlador antiguo utiliza el nombre de clase previo
if (!class_exists('EstadoDetallePedido')) {
    class EstadoDetallePedido extends DetallePedido {}
}
?>