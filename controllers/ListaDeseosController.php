<?php
// controllers/ListaDeseosController.php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/ListaDeseo.php';

class ListaDeseosController {
    private $db;
    private $modeloWishlist;

    /**
     * Constructor con soporte opcional para inyección de dependencias PDO.
     * @param PDO|null $dbConexion
     */
    public function __construct($dbConexion = null) {
        if ($dbConexion instanceof PDO) {
            $this->db = $dbConexion;
            $this->modeloWishlist = new ListaDeseo($this->db);
        } else {
            $database = new Database();
            $this->db = $database->getConnection();
            $this->modeloWishlist = new ListaDeseo($this->db);
        }
    }

    /**
     * CREATE: Agregar un producto a la lista de deseos
     */
    public function agregarProducto($id_usuario, $id_producto) {
        try {
            $idUsuarioInt = intval($id_usuario);
            $idProductoInt = intval($id_producto);

            if ($idUsuarioInt <= 0 || $idProductoInt <= 0) {
                return [
                    "status" => "error", 
                    "mensaje" => "Identificadores de usuario o producto no válidos."
                ];
            }

            $resultado = $this->modeloWishlist->agregar($idUsuarioInt, $idProductoInt);
            
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
            error_log("Excepción en ListaDeseosController::agregarProducto: " . $e->getMessage());
            return [
                "status" => "error", 
                "mensaje" => "Error al procesar la solicitud: " . $e->getMessage()
            ];
        }
    }

    /**
     * READ: Listar todas las listas de deseos (Ideal para administradores)
     */
    public function listarTodasWishlists() {
        try {
            return $this->modeloWishlist->listarTodos() ?: [];
        } catch (Exception $e) {
            error_log("Excepción en ListaDeseosController::listarTodasWishlists: " . $e->getMessage());
            return [];
        }
    }

    /**
     * READ: Listar la lista de deseos de un usuario en específico
     */
    public function listarPorUsuario($id_usuario) {
        try {
            $idUsuarioInt = intval($id_usuario);
            if ($idUsuarioInt <= 0) return [];

            return $this->modeloWishlist->listarPorUsuario($idUsuarioInt) ?: [];
        } catch (Exception $e) {
            error_log("Excepción en ListaDeseosController::listarPorUsuario: " . $e->getMessage());
            return [];
        }
    }

    /**
     * UPDATE: Actualizar un registro de la lista de deseos
     */
    public function actualizarWishlist($id_wishlist, $id_usuario, $id_producto) {
        try {
            $resultado = $this->modeloWishlist->actualizar(intval($id_wishlist), intval($id_usuario), intval($id_producto));
            
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
            error_log("Excepción en ListaDeseosController::actualizarWishlist: " . $e->getMessage());
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
            $resultado = $this->modeloWishlist->eliminar(intval($id_wishlist));
            
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
            error_log("Excepción en ListaDeseosController::eliminarWishlist: " . $e->getMessage());
            return [
                "status" => "error", 
                "mensaje" => "Error al eliminar: " . $e->getMessage()
            ];
        }
    }
}
?>