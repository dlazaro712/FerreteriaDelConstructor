<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/Database.php';
include_once '../models/Producto.php';

$database = new Database();
$db = $database->getConnection();
$producto = new Producto($db);

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            $data = $producto->obtenerPorId($_GET['id']);
            if($data) {
                echo json_encode($data);
            } else {
                http_response_code(404);
                echo json_encode(array("mensaje" => "Producto no encontrado."));
            }
        } else {
            $stmt = $producto->leerTodos();
            $productos = $stmt->fetchAll();
            echo json_encode($productos);
        }
        break;

    case 'POST':
        // Crear un nuevo producto (CREATE)
        $data = json_decode(file_get_contents("php://input"), true);
        if(!empty($data['nombre']) && !empty($data['precio']) && !empty($data['id_categoria'])) {
            if($producto->crear($data)) {
                http_response_code(201);
                echo json_encode(array("mensaje" => "Producto creado exitosamente."));
            } else {
                http_response_code(503);
                echo json_encode(array("mensaje" => "No se pudo crear el producto."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("mensaje" => "Datos incompletos para la creación."));
        }
        break;

    case 'PUT':
        // Actualizar un producto existente (UPDATE)
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Se puede recibir el ID por parámetro en la URL (?id=X) o dentro del cuerpo JSON
        $id = isset($_GET['id']) ? $_GET['id'] : (isset($data['id_producto']) ? $data['id_producto'] : null);

        if($id && !empty($data['nombre']) && !empty($data['precio'])) {
            if($producto->actualizar($id, $data)) {
                http_response_code(200);
                echo json_encode(array("mensaje" => "Producto actualizado exitosamente."));
            } else {
                http_response_code(503);
                echo json_encode(array("mensaje" => "No se pudo actualizar el producto."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("mensaje" => "ID o datos esenciales incompletos para la actualización."));
        }
        break;

    case 'DELETE':
        // Eliminar un producto (DELETE - Eliminación lógica o física)
        $id = isset($_GET['id']) ? $_GET['id'] : null;

        if($id) {
            // Usamos eliminación lógica para no romper registros históricos de pedidos
            if($producto->eliminarLogico($id)) {
                http_response_code(200);
                echo json_encode(array("mensaje" => "Producto eliminado lógicamente de forma exitosa."));
            } else {
                http_response_code(503);
                echo json_encode(array("mensaje" => "No se pudo eliminar el producto."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("mensaje" => "ID del producto no especificado."));
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(array("mensaje" => "Método no permitido."));
        break;
}
?>