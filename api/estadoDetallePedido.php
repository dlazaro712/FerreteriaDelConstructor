<?php
// Configurar cabeceras para permitir peticiones JSON (REST API)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Manejo de peticiones OPTIONS (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Requerir el controlador correspondiente
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/EstadoDetallePedidoController.php';

$controller = new EstadoDetallePedidoController();
$method = $_SERVER['REQUEST_METHOD'];

// Obtener datos enviados en crudo (JSON) para POST, PUT o DELETE
$inputData = json_decode(file_get_contents("php://input"), true);

switch ($method) {
    case 'GET':
        // Consultar por id_detalle específico, por id_pedido o listar todos
        if (isset($_GET['id_detalle'])) {
            $id_detalle = filter_var($_GET['id_detalle'], FILTER_SANITIZE_NUMBER_INT);
            $resultado = $controller->ver($id_detalle);
            
            if ($resultado) {
                http_response_code(200);
                echo json_encode(["status" => "success", "data" => $resultado]);
            } else {
                http_response_code(404);
                echo json_encode(["status" => "error", "message" => "Detalle de pedido no encontrado."]);
            }
        } elseif (isset($_GET['id_pedido'])) {
            $id_pedido = filter_var($_GET['id_pedido'], FILTER_SANITIZE_NUMBER_INT);
            $resultado = $controller->listarPorPedido($id_pedido);
            
            http_response_code(200);
            echo json_encode(["status" => "success", "data" => $resultado]);
        } else {
            $resultado = $controller->index();
            http_response_code(200);
            echo json_encode(["status" => "success", "data" => $resultado]);
        }
        break;

    case 'POST':
        // Crear un nuevo registro
        $id_pedido   = $inputData['id_pedido'] ?? null;
        $id_producto = $inputData['id_producto'] ?? null;
        $cantidad    = $inputData['cantidad'] ?? null;
        $precio      = $inputData['precio'] ?? null;

        if (empty($id_pedido) || empty($id_producto) || empty($cantidad) || empty($precio)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Faltan datos obligatorios (id_pedido, id_producto, cantidad, precio)."]);
            break;
        }

        $exito = $controller->crear($id_pedido, $id_producto, $cantidad, $precio);

        if ($exito) {
            http_response_code(201);
            echo json_encode(["status" => "success", "message" => "Detalle de pedido creado exitosamente."]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "No se pudo registrar el detalle del pedido."]);
        }
        break;

    case 'PUT':
        // Actualizar un registro existente
        $id_detalle  = $inputData['id_detalle'] ?? $_GET['id_detalle'] ?? null;
        $id_pedido   = $inputData['id_pedido'] ?? null;
        $id_producto = $inputData['id_producto'] ?? null;
        $cantidad    = $inputData['cantidad'] ?? null;
        $precio      = $inputData['precio'] ?? null;

        if (empty($id_detalle) || empty($id_pedido) || empty($id_producto) || empty($cantidad) || empty($precio)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Faltan datos para la actualización (id_detalle, id_pedido, id_producto, cantidad, precio)."]);
            break;
        }

        $exito = $controller->actualizar($id_detalle, $id_pedido, $id_producto, $cantidad, $precio);

        if ($exito) {
            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Detalle de pedido actualizado correctamente."]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "No se pudo actualizar el detalle del pedido."]);
        }
        break;

    case 'DELETE':
        // Eliminar un registro
        $id_detalle = $inputData['id_detalle'] ?? $_GET['id_detalle'] ?? null;

        if (empty($id_detalle)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "El parámetro 'id_detalle' es obligatorio para eliminar."]);
            break;
        }

        $exito = $controller->eliminar($id_detalle);

        if ($exito) {
            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Detalle de pedido eliminado correctamente."]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "No se pudo eliminar el detalle del pedido."]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["status" => "error", "message" => "Método no permitido."]);
        break;
}
?>