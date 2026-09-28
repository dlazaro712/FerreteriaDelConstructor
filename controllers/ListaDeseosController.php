<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/ListaDeseo.php';

class ListaDeseosController {
    private $modeloWishlist;

    public function __construct() {
        // Instanciamos el modelo de lista de deseos que ya maneja internamente la conexión a la base de datos
        $this->modeloWishlist = new ListaDeseo();
    }

    /**
     * CREATE: Agregar un producto a la lista de deseos
     */
    public function agregarProducto($id_usuario, $id_producto) {
        try {
            $resultado = $this->modeloWishlist->agregar($id_usuario, $id_producto);
            
            if ($resultado) {
                return [
                    "status" => "success", 
                    "mensaje" => "Producto agregado a la lista de deseos exitosamente."
                ];
            } else {
                return [
                    "status" => "error", 
                    "mensaje" => "El producto ya se encuentra en la lista de deseos o no se pudo agregar."
                ];
            }
        } catch (Exception $e) {
            return [
                "status" => "error", 
                "mensaje" => "Error al procesar la solicitud: " . $e->getMessage()
            ];
        }
    }

    /**
     * READ: Listar todas las listas de deseos con nombres de usuario y producto (Ideal para administradores)
     */
    public function listarTodasWishlists() {
        try {
            return $this->modeloWishlist->listarTodos();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * READ: Listar la lista de deseos de un usuario en específico
     */
    public function listarPorUsuario($id_usuario) {
        try {
            return $this->modeloWishlist->listarPorUsuario($id_usuario);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * UPDATE: Actualizar un registro de la lista de deseos
     */
    public function actualizarWishlist($id_wishlist, $id_usuario, $id_producto) {
        try {
            $resultado = $this->modeloWishlist->actualizar($id_wishlist, $id_usuario, $id_producto);
            
            if ($resultado) {
                return [
                    "status" => "success", 
                    "mensaje" => "Lista de deseos actualizada correctamente."
                ];
            } else {
                return [
                    "status" => "error", 
                    "mensaje" => "No se pudo actualizar el registro."
                ];
            }
        } catch (Exception $e) {
            return [
                "status" => "error", 
                "mensaje" => "Error al actualizar: " . $e->getMessage()
            ];
        }
    }

    /**
     * DELETE: Eliminar un producto de la lista de deseos por su ID
     */
    public function eliminarWishlist($id_wishlist) {
        try {
            $resultado = $this->modeloWishlist->eliminar($id_wishlist);
            
            if ($resultado) {
                return [
                    "status" => "success", 
                    "mensaje" => "Producto eliminado de la lista de deseos."
                ];
            } else {
                return [
                    "status" => "error", 
                    "mensaje" => "No se pudo eliminar el registro."
                ];
            }
        } catch (Exception $e) {
            return [
                "status" => "error", 
                "mensaje" => "Error al eliminar: " . $e->getMessage()
            ];
        }
    }
}
?>