<?php
require_once __DIR__ . '/../config/Database.php';

class ListaDeseo {
    private $conn;
    private $table_name = "wishlist";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * CREATE: Agregar un producto a la lista de deseos de un usuario
     */
    function agregar($id_usuario, $id_producto) {
        // Evitar duplicados para el mismo usuario y producto
        if ($this->existeEnWishlist($id_usuario, $id_producto)) {
            return false;
        }

        $query = "INSERT INTO " . $this->table_name . " (id_usuario, id_producto) VALUES (:id_usuario, :id_producto)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * READ: Listar todos los registros de la wishlist con nombres de usuario y producto
     */
    function listarTodos() {
        $query = "SELECT w.id_wishlist, w.id_usuario, w.id_producto, 
                         u.nombre as nombre_usuario, 
                         p.nombre as nombre_producto 
                  FROM " . $this->table_name . " w
                  INNER JOIN usuarios u ON w.id_usuario = u.id_usuario
                  INNER JOIN productos p ON w.id_producto = p.id_producto
                  ORDER BY w.id_wishlist DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * READ: Obtener los elementos de la lista de deseos de un usuario específico
     */
    function listarPorUsuario($id_usuario) {
        $query = "SELECT w.id_wishlist, w.id_usuario, w.id_producto, 
                         u.nombre as nombre_usuario, 
                         p.nombre as nombre_producto,
                         p.precio, p.imagen, p.descripcion
                  FROM " . $this->table_name . " w
                  INNER JOIN usuarios u ON w.id_usuario = u.id_usuario
                  INNER JOIN productos p ON w.id_producto = p.id_producto
                  WHERE w.id_usuario = ?
                  ORDER BY w.id_wishlist DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id_usuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * UPDATE: Actualizar un registro de la lista de deseos (por si se requiere cambiar el producto)
     */
    function actualizar($id_wishlist, $id_usuario, $id_producto) {
        $query = "UPDATE " . $this->table_name . " 
                  SET id_usuario = :id_usuario, id_producto = :id_producto 
                  WHERE id_wishlist = :id_wishlist";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
        $stmt->bindParam(':id_wishlist', $id_wishlist, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * DELETE: Eliminar un elemento de la lista de deseos por su ID
     */
    function eliminar($id_wishlist) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_wishlist = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id_wishlist]);
    }

    /**
     * MÉTODO AUXILIAR: Verificar si ya existe el producto en la wishlist del usuario
     */
    private function existeEnWishlist($id_usuario, $id_producto) {
        $query = "SELECT id_wishlist FROM " . $this->table_name . " WHERE id_usuario = ? AND id_producto = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id_usuario, $id_producto]);
        return $stmt->rowCount() > 0;
    }
}
?>