<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/PedidosController.php';

// Configurar cabeceras para respuesta JSON
header('Content-Type: application/json; charset=UTF-8');

$pedidosController = new PedidosController();
$method = $_SERVER['REQUEST_METHOD'];

// Identificar la acción solicitada por parámetro GET o POST
$action = $_GET['action'] ?? '';

switch ($method) {
    case 'GET':
        // Consultar historial o detalle de pedidos
        if ($action === 'historial') {
            // Verificar si el usuario está autenticado
            if (!isset($_SESSION['id_usuario'])) {
                http_response_code(401);
                echo json_encode([
                    'status' => 'error',
                    'mensaje' => 'No autorizado. Debe iniciar sesión para ver el historial de pedidos.'
                ]);
                exit();
            }

            $id_usuario = $_SESSION['tipo_usuario'] === 'admin' && isset($_GET['usuario_id']) 
                          ? intval($_GET['usuario_id']) 
                          : intval($_SESSION['id_usuario']);

            $pedidos = $pedidosController->listarPedidosPorUsuario($id_usuario);
            
            echo json_encode([
                'status' => 'success',
                'data' => $pedidos
            ]);
            exit();

        } elseif ($action === 'detalle') {
            $id_pedido = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if ($id_pedido <= 0) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'mensaje' => 'ID de pedido inválido.']);
                exit();
            }

            $detalle = $pedidosController->obtenerDetalle($id_pedido);
            if ($detalle) {
                echo json_encode(['status' => 'success', 'data' => $detalle]);
            } else {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'mensaje' => 'Pedido no encontrado.']);
            }
            exit();

        } else {
            // Listado general (solo para administradores)
            if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'admin') {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'mensaje' => 'Acceso denegado. Se requieren privilegios de administrador.']);
                exit();
            }

            $pedidos = $pedidosController->listar();
            echo json_encode(['status' => 'success', 'data' => $pedidos]);
            exit();
        }
        break;

    case 'POST':
        // Registrar un nuevo pedido
        // Espera un JSON en el body o datos vía POST estándar
        $inputJSON = file_get_contents('php://input');
        $data = json_decode($inputJSON, true);

        if (!$data) {
            // Intentar leer desde $_POST tradicional si no viene como JSON crudo
            $data = $_POST;
        }

        // Validar autenticación
        if (!isset($_SESSION['id_usuario'])) {
            http_response_code(401);
            echo json_encode([
                'status' => 'error',
                'mensaje' => 'Debe iniciar sesión para registrar un pedido.'
            ]);
            exit();
        }

        $id_usuario = $_SESSION['id_usuario'];
        $items = $data['items'] ?? []; // Array de productos [ {id_producto, cantidad, precio}, ... ]
        $total = $data['total'] ?? 0;
        $direccion_envio = $data['direccion_envio'] ?? 'Dirección predeterminada del cliente';

        if (empty($items)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'mensaje' => 'El carrito está vacío. No se puede registrar un pedido sin productos.'
            ]);
            exit();
        }

        $datosPedido = [
            'id_usuario' => $id_usuario,
            'total' => $total,
            'direccion_envio' => $direccion_envio,
            'items' => $items
        ];

        $resultado = $pedidosController->crearPedido($datosPedido);

        if ($resultado) {
            http_response_code(201);
            echo json_encode([
                'status' => 'success',
                'mensaje' => '¡Pedido registrado exitosamente!',
                'id_pedido' => is_numeric($resultado) ? $resultado : null
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'mensaje' => 'Hubo un error al registrar el pedido en la base de datos.'
            ]);
        }
        exit();
        break;

    default:
        http_response_code(405);
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'Método HTTP no soportado.'
        ]);
        break;
}