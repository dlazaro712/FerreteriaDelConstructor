<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Manejo de peticiones de pre-verificación CORS (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/CategoriasController.php';

$controller = new CategoriasController();
$method = $_SERVER['REQUEST_METHOD'];

// Obtener datos JSON enviados en peticiones PUT/POST si aplican
$inputData = json_decode(file_get_contents("php://input"), true);
if (!$inputData) {
    $inputData = $_POST; // Compatibilidad con formularios tradicionales
}

$id = isset($_GET['id']) ? intval($_GET['id']) : null;

switch ($method) {
    case 'GET':
        if ($id) {
            $categoria = $controller->obtenerPorId($id);
            if ($categoria) {
                http_response_code(200);
                echo json_encode(["status" => "success", "data" => $categoria]);
            } else {
                http_response_code(404);
                echo json_encode(["status" => "error", "message" => "Categoría no encontrada."]);
            }
        } else {
            $categorias = $controller->listar();
            http_response_code(200);
            echo json_encode(["status" => "success", "data" => $categorias]);
        }
        break;

    case 'POST':
        // Validación básica de campos obligatorios
        if (!empty($inputData['nombre'])) {
            if ($controller->crear($inputData)) {
                http_response_code(201);
                echo json_encode(["status" => "success", "message" => "Categoría creada exitosamente."]);
            } else {
                http_response_code(500);
                echo json_encode(["status" => "error", "message" => "No se pudo crear la categoría en la base de datos."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "El campo 'nombre' es obligatorio."]);
        }
        break;

    case 'PUT':
        if ($id && !empty($inputData['nombre'])) {
            if ($controller->actualizar($id, $inputData)) {
                http_response_code(200);
                echo json_encode(["status" => "success", "message" => "Categoría actualizada exitosamente."]);
            } else {
                http_response_code(500);
                echo json_encode(["status" => "error", "message" => "No se pudo actualizar la categoría."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "ID y nombre son obligatorios para la actualización."]);
        }
        break;

    case 'DELETE':
        if ($id) {
            if ($controller->eliminar($id)) {
                http_response_code(200);
                echo json_encode(["status" => "success", "message" => "Categoría eliminada exitosamente."]);
            } else {
                http_response_code(500);
                echo json_encode(["status" => "error", "message" => "No se pudo eliminar la categoría (puede tener productos asociados)."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Se requiere un ID válido para eliminar."]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["status" => "error", "message" => "Método HTTP no permitido."]);
        break;
}
?>