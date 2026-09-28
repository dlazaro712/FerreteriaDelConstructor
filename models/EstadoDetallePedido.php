<?php
require_once __DIR__ . '/../config/Database.php'; // Ajusta la ruta si tu archivo de conexión se encuentra en otra carpeta

/**
 * Clase Modelo/DAO para la gestión de Detalle de Pedidos utilizando PDO.
 */
class EstadoDetallePedido {
    private $conexion;
    
    // Propiedades correspondientes a los campos de la tabla y vistas
    public $id_detalle;
    public $id_pedido;
    public $id_producto;
    public $cantidad;
    public $precio;

    /**
     * Constructor que inicializa automáticamente la conexión PDO si no se pasa una activa,
     * o acepta una conexión existente por inyección de dependencias.
     * @param PDO|null $dbConexion
     */
    public function __construct($dbConexion = null) {
        if ($dbConexion instanceof PDO) {
            $this->conexion = $dbConexion;
        } else {
            // Se asume que database.php retorna o define una instancia de PDO llamada $pdo o similar
            global $pdo; 
            if (isset($pdo)) {
                $this->conexion = $pdo;
            } else {
                // Instancia directa en caso de que la conexión se maneje mediante una clase o función global
                $database = new Database();
                $this->conexion = $database->getConnection();
            }
        }
    }

    /**
     * CREATE: Inserta un nuevo registro de detalle de pedido
     * @return bool True si se insertó correctamente, False en caso contrario.
     */
    public function insertar() {
        try {
            $sql = "INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, precio) VALUES (:id_pedido, :id_producto, :cantidad, :precio)";
            $stmt = $this->conexion->prepare($sql);
            
            return $stmt->execute([
                ':id_pedido' => $this->id_pedido,
                ':id_producto' => $this->id_producto,
                ':cantidad' => $this->cantidad,
                ':precio' => $this->precio
            ]);
        } catch (PDOException $e) {
            error_log("Error al insertar detalle de pedido: " . $e->getMessage());
            return false;
        }
    }

    /**
     * READ: Obtiene todos los detalles de pedidos incluyendo el nombre del producto
     * @return array Arreglo asociativo con todos los registros.
     */
    public function obtenerTodos() {
        try {
            $sql = "SELECT d.id_detalle, d.id_pedido, d.id_producto, p.nombre AS nombre_producto, d.cantidad, d.precio 
                    FROM detalle_pedido d 
                    INNER JOIN productos p ON d.id_producto = p.id_producto"; // Ajusta 'id_producto' o 'id' según tu tabla de productos
            
            $stmt = $this->conexion->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener detalles de pedidos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * READ: Obtiene un detalle de pedido específico por su ID principal incluyendo el nombre del producto
     * @param int $id_detalle
     * @return array|false Arreglo asociativo con los datos del registro o false si no existe.
     */
    public function obtenerPorId($id_detalle) {
        try {
            $sql = "SELECT d.id_detalle, d.id_pedido, d.id_producto, p.nombre AS nombre_producto, d.cantidad, d.precio 
                    FROM detalle_pedido d 
                    INNER JOIN productos p ON d.id_producto = p.id_producto 
                    WHERE d.id_detalle = :id_detalle";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':id_detalle' => $id_detalle]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener detalle de pedido por ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * READ: Obtiene todos los detalles asociados a un número de pedido específico con el nombre del producto
     * @param int $id_pedido
     * @return array
     */
    public function obtenerPorPedido($id_pedido) {
        try {
            $sql = "SELECT d.id_detalle, d.id_pedido, d.id_producto, p.nombre AS nombre_producto, d.cantidad, d.precio 
                    FROM detalle_pedido d 
                    INNER JOIN productos p ON d.id_producto = p.id_producto 
                    WHERE d.id_pedido = :id_pedido";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':id_pedido' => $id_pedido]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener detalles por ID de pedido: " . $e->getMessage());
            return [];
        }
    }

    /**
     * UPDATE: Actualiza los datos de un detalle de pedido existente
     * @return bool True si se actualizó con éxito, False de lo contrario.
     */
    public function actualizar() {
        try {
            $sql = "UPDATE detalle_pedido SET id_pedido = :id_pedido, id_producto = :id_producto, cantidad = :cantidad, precio = :precio WHERE id_detalle = :id_detalle";
            $stmt = $this->conexion->prepare($sql);
            
            return $stmt->execute([
                ':id_pedido' => $this->id_pedido,
                ':id_producto' => $this->id_producto,
                ':cantidad' => $this->cantidad,
                ':precio' => $this->precio,
                ':id_detalle' => $this->id_detalle
            ]);
        } catch (PDOException $e) {
            error_log("Error al actualizar detalle de pedido: " . $e->getMessage());
            return false;
        }
    }

    /**
     * DELETE: Elimina un registro de detalle de pedido por su ID
     * @param int $id_detalle
     * @return bool True si se eliminó con éxito, False de lo contrario.
     */
    public function eliminar($id_detalle) {
        try {
            $sql = "DELETE FROM detalle_pedido WHERE id_detalle = :id_detalle";
            $stmt = $this->conexion->prepare($sql);
            
            return $stmt->execute([':id_detalle' => $id_detalle]);
        } catch (PDOException $e) {
            error_log("Error al eliminar detalle de pedido: " . $e->getMessage());
            return false;
        }
    }
}
?>