<?php
// api/productos.php

// Limpiar buffers previos para evitar contaminación de caracteres antes de enviar JSON
if (ob_get_length()) ob_clean();

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

try {
    // Inclusión de dependencias con rutas absolutas
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../config/Database.php';
    require_once __DIR__ . '/../controllers/ProductosController.php';

    // Instanciar el controlador de productos
    $productosController = new ProductosController();
    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            if (isset($_GET['id']) && !empty($_GET['id'])) {
                $id = intval($_GET['id']);
                // Obtener producto individual desde el controlador
                $data = method_exists($productosController, 'ver') 
                    ? $productosController->ver($id) 
                    : (method_exists($productosController, 'obtenerPorId') ? $productosController->obtenerPorId($id) : null);

                if ($data) {
                    echo json_encode($data, JSON_UNESCAPED_UNICODE);
                } else {
                    http_response_code(404);
                    echo json_encode(["mensaje" => "Producto no encontrado."]);
                }
            } else {
                // Obtener catálogo completo desde el controlador
                $listaProductos = [];
                if (method_exists($productosController, 'index')) {
                    $listaProductos = $productosController->index();
                } elseif (method_exists($productosController, 'listar')) {
                    $listaProductos = $productosController->listar();
                }

                // Normalización de tipos de datos para JavaScript
                $productosNormalizados = array_map(function($p) {
                    return [
                        'id_producto'  => intval($p['id_producto'] ?? $p['id'] ?? 0),
                        'nombre'       => $p['nombre'] ?? $p['nombre_producto'] ?? 'Producto',
                        'descripcion'  => $p['descripcion'] ?? '',
                        'precio'       => floatval($p['precio'] ?? 0),
                        'cantidad'     => intval($p['cantidad'] ?? $p['stock'] ?? 0),
                        'stock'        => intval($p['cantidad'] ?? $p['stock'] ?? 0),
                        'imagen'       => $p['imagen'] ?? '',
                        'id_categoria' => intval($p['id_categoria'] ?? 0),
                        'categoria'    => $p['categoria'] ?? 'General'
                    ];
                }, $listaProductos ?: []);

                echo json_encode($productosNormalizados, JSON_UNESCAPED_UNICODE);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents("php://input"), true);
            if (!empty($data['nombre']) && !empty($data['precio']) && !empty($data['id_categoria'])) {
                $creado = method_exists($productosController, 'crear') 
                    ? $productosController->crear($data) 
                    : false;

                if ($creado) {
                    http_response_code(201);
                    echo json_encode(["mensaje" => "Producto creado exitosamente."]);
                } else {
                    http_response_code(503);
                    echo json_encode(["mensaje" => "No se pudo crear el producto."]);
                }
            } else {
                http_response_code(400);
                echo json_encode(["mensaje" => "Datos incompletos para la creación."]);
            }
            break;

        case 'PUT':
            $data = json_decode(file_get_contents("php://input"), true);
            $id = isset($_GET['id']) ? intval($_GET['id']) : (isset($data['id_producto']) ? intval($data['id_producto']) : null);

            if ($id && !empty($data['nombre']) && !empty($data['precio'])) {
                $actualizado = method_exists($productosController, 'actualizar') 
                    ? $productosController->actualizar($id, $data) 
                    : false;

                if ($actualizado) {
                    http_response_code(200);
                    echo json_encode(["mensaje" => "Producto actualizado exitosamente."]);
                } else {
                    http_response_code(503);
                    echo json_encode(["mensaje" => "No se pudo actualizar el producto."]);
                }
            } else {
                http_response_code(400);
                echo json_encode(["mensaje" => "ID o datos esenciales incompletos para la actualización."]);
            }
            break;

        case 'DELETE':
            $id = isset($_GET['id']) ? intval($_GET['id']) : null;
            if ($id) {
                $eliminado = method_exists($productosController, 'eliminar') 
                    ? $productosController->eliminar($id) 
                    : false;

                if ($eliminado) {
                    http_response_code(200);
                    echo json_encode(["mensaje" => "Producto eliminado exitosamente."]);
                } else {
                    http_response_code(503);
                    echo json_encode(["mensaje" => "No se pudo eliminar el producto."]);
                }
            } else {
                http_response_code(400);
                echo json_encode(["mensaje" => "ID del producto no especificado."]);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(["mensaje" => "Método no permitido."]);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => true, "mensaje" => "Error interno en la API: " . $e->getMessage()]);
}
?>