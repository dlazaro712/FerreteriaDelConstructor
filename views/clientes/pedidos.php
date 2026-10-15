<?php
// views/clientes/pedidos.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclusión de configuraciones, base de datos y controladores requeridos
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../controllers/PedidosController.php';
require_once __DIR__ . '/../../controllers/EstadoDetallePedidoController.php';

// =========================================================================
// PROCESAMIENTO DEL CHECKOUT (PETICIÓN POST DESDE EL CARRITO)
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'registrar_pedido') {
    
    // Validación de seguridad para asegurar que el usuario haya iniciado sesión
    if (!isset($_SESSION['id_usuario'])) {
        header("Location: " . BASE_URL . "views/usuarios.php?action=login");
        exit();
    }

    $id_usuario = $_SESSION['id_usuario'];
    $carritoJson = $_POST['carritoJson'] ?? '[]';
    $itemsCarrito = json_decode($carritoJson, true);

    if (empty($itemsCarrito)) {
        header("Location: " . BASE_URL . "views/clientes/carrito.php");
        exit();
    }

    // Calcular el total general del pedido
    $totalGeneral = 0;
    foreach ($itemsCarrito as $item) {
        $totalGeneral += floatval($item['precio']) * intval($item['cantidad']);
    }

    // Iniciar conexión y transacción segura en la base de datos
    $database = new Database();
    $db = $database->getConnection();

    try {
        $db->beginTransaction();

        // 1. Insertar la cabecera del pedido
        $queryPedido = "INSERT INTO pedidos (id_usuario, fecha, total, estado) VALUES (:id_usuario, NOW(), :total, 'Pendiente')";
        $stmtPedido = $db->prepare($queryPedido);
        $stmtPedido->execute([
            ':id_usuario' => $id_usuario,
            ':total' => $totalGeneral
        ]);
        $id_pedido = $db->lastInsertId();

        // 2. Insertar cada producto en la tabla de detalle
        $queryDetalle = "INSERT INTO estadodetalle_pedido (id_pedido, id_producto, cantidad, precio) VALUES (:id_pedido, :id_producto, :cantidad, :precio)";
        $stmtDetalle = $db->prepare($queryDetalle);

        foreach ($itemsCarrito as $item) {
            $stmtDetalle->execute([
                ':id_pedido' => $id_pedido,
                ':id_producto' => $item['id_producto'],
                ':cantidad' => $item['cantidad'],
                ':precio' => $item['precio']
            ]);
        }

        // Confirmar transacción
        $db->commit();

        // Redirigir al detalle del pedido recién creado
        header("Location: " . BASE_URL . "views/clientes/pedidos.php?id=" . $id_pedido);
        exit();

    } catch (Exception $e) {
        $db->rollBack();
        error_log("Error al registrar el pedido: " . $e->getMessage());
        echo "<div style='padding: 2rem; color: red; text-align: center; font-family: sans-serif;'>Hubo un error al procesar tu compra. Por favor, inténtalo de nuevo.</div>";
        exit();
    }
}

// =========================================================================
// VISTA NORMAL (ACCESO POR GET: DETALLE O HISTORIAL DE PEDIDOS)
// =========================================================================

// Validación de seguridad para clientes autenticados
if (!isset($_SESSION['tipo_usuario']) && !isset($_SESSION['id_usuario'])) {
    header("Location: " . BASE_URL . "views/usuarios.php?action=login");
    exit();
}

// Inicialización de controladores
$pedidosController = new PedidosController();
$detallePedidoController = new EstadoDetallePedidoController();

// Identificar si se solicita el detalle de un pedido específico
$id_pedido = isset($_GET['id']) ? intval($_GET['id']) : (isset($_GET['id_pedido']) ? intval($_GET['id_pedido']) : null);

// ID del usuario cliente autenticado
$id_usuario_actual = intval($_SESSION['id_usuario'] ?? $_SESSION['user_id'] ?? 0);

// Incluir encabezado visual
include_once __DIR__ . '/../layouts/header.php';
?>

<main class="container" style="max-width: 1200px; margin: 2rem auto; padding: 0 1rem;">
    <?php if ($id_pedido): ?>
        <!-- ========================================== -->
        <!-- VISTA DETALLE DE UN PEDIDO ESPECÍFICO      -->
        <!-- ========================================== -->
        <?php 
        $pedidoDetalle = method_exists($pedidosController, 'obtenerPedidoPorId') 
            ? $pedidosController->obtenerPedidoPorId($id_pedido) 
            : $pedidosController->ver($id_pedido);
        
        $idPropietario = intval($pedidoDetalle['id_usuario'] ?? $pedidoDetalle['id_cliente'] ?? 0);
        $esPropietario = ($pedidoDetalle && $idPropietario === $id_usuario_actual);

        if ($pedidoDetalle && $esPropietario): 
            $itemsDetalle = [];
            if ($detallePedidoController && method_exists($detallePedidoController, 'listarPorPedido')) {
                $itemsDetalle = $detallePedidoController->listarPorPedido($id_pedido);
            } elseif (method_exists($pedidosController, 'obtenerDetallePedido')) {
                $itemsDetalle = $pedidosController->obtenerDetallePedido($id_pedido);
            }
        ?>
            <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); max-width: 850px; margin: 0 auto; border: 1px solid #e5e7eb;">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e5e7eb; padding-bottom: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 0.5rem;">
                    <div>
                        <span style="background: #e0f2fe; color: #0369a1; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.8rem; font-weight: 600;">Pedido #<?= htmlspecialchars($pedidoDetalle['id_pedido'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <h1 style="margin: 0.5rem 0 0 0; font-size: 1.75rem; color: #111827;">Detalle de mi Compra</h1>
                    </div>
                    <div style="text-align: right;">
                        <p style="margin: 0; font-size: 0.85rem; color: #6b7280;">Fecha: <?= htmlspecialchars($pedidoDetalle['fecha_creacion'] ?? $pedidoDetalle['fecha_pedido'] ?? $pedidoDetalle['fecha'] ?? 'N/D', ENT_QUOTES, 'UTF-8'); ?></p>
                        <span style="display: inline-block; margin-top: 0.25rem; background: #fef3c7; color: #92400e; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.8rem; font-weight: bold;">
                            Estado: <?= htmlspecialchars(ucfirst($pedidoDetalle['estado'] ?? 'Pendiente'), ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    </div>
                </div>

                <!-- Datos del Comprador -->
                <div style="margin-bottom: 1.5rem; background: #f8fafc; padding: 1rem; border-radius: 6px; border: 1px solid #f1f5f9;">
                    <h3 style="font-size: 1rem; color: #374151; margin: 0 0 0.5rem 0; font-weight: 600;">Datos del Cliente</h3>
                    <p style="margin: 0 0 0.25rem 0; color: #4b5563; font-size: 0.9rem;"><strong>Cliente:</strong> <?= htmlspecialchars($pedidoDetalle['cliente'] ?? $_SESSION['usuario'] ?? 'Cliente', ENT_QUOTES, 'UTF-8'); ?></p>
                    <p style="margin: 0; color: #4b5563; font-size: 0.9rem;"><strong>Correo:</strong> <?= htmlspecialchars($pedidoDetalle['correo'] ?? $_SESSION['email'] ?? $_SESSION['correo'] ?? 'No registrado', ENT_QUOTES, 'UTF-8'); ?></p>
                </div>

                <!-- Tabla de Productos del Pedido -->
                <div style="margin-bottom: 1.5rem; overflow-x: auto;">
                    <h3 style="font-size: 1rem; color: #374151; margin-bottom: 0.75rem; font-weight: 600;">Productos Comprados</h3>
                    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                        <thead>
                            <tr style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0; color: #475569;">
                                <th style="padding: 0.75rem; text-transform: uppercase; font-size: 0.75rem;">Producto</th>
                                <th style="padding: 0.75rem; text-align: center; text-transform: uppercase; font-size: 0.75rem;">Precio Unit.</th>
                                <th style="padding: 0.75rem; text-align: center; text-transform: uppercase; font-size: 0.75rem;">Cant.</th>
                                <th style="padding: 0.75rem; text-align: right; text-transform: uppercase; font-size: 0.75rem;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalSuma = 0;
                            if (!empty($itemsDetalle)):
                                foreach ($itemsDetalle as $item):
                                    $pUnit = floatval($item['precio_unitario'] ?? $item['precio'] ?? 0);
                                    $cant = intval($item['cantidad'] ?? 1);
                                    $sub = $pUnit * $cant;
                                    $totalSuma += $sub;
                            ?>
                                <tr style="border-bottom: 1px solid #f1f5f9; color: #334155;">
                                    <td style="padding: 0.75rem; font-weight: 500; color: #0f172a;">
                                        <?= htmlspecialchars($item['nombre_producto'] ?? $item['nombre'] ?? 'Producto', ENT_QUOTES, 'UTF-8'); ?>
                                    </td>
                                    <td style="padding: 0.75rem; text-align: center; color: #2563eb; font-weight: 500;">Q.<?= number_format($pUnit, 2); ?></td>
                                    <td style="padding: 0.75rem; text-align: center; font-weight: 600;"><?= $cant; ?></td>
                                    <td style="padding: 0.75rem; text-align: right; font-weight: 700; color: #0f172a;">Q.<?= number_format($sub, 2); ?></td>
                                </tr>
                            <?php 
                                endforeach;
                            else: 
                            ?>
                                <tr>
                                    <td colspan="4" style="padding: 1.5rem; text-align: center; color: #94a3b8;">No se encontraron líneas de detalle para este pedido.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Total -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; background: #f8fafc; padding: 1.25rem; border-radius: 6px; border: 1px solid #e2e8f0;">
                    <span style="font-size: 1.15rem; font-weight: bold; color: #111827;">Total General:</span>
                    <span style="font-size: 1.4rem; font-weight: bold; color: #0284c7;">Q.<?= number_format($pedidoDetalle['total'] ?? $totalSuma, 2); ?></span>
                </div>

                <!-- Botones de Acción -->
                <div style="display: flex; gap: 1rem; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                    <a href="<?= BASE_URL; ?>views/clientes/pedidos.php" class="btn btn-outline" style="padding: 0.5rem 1rem; border: 1px solid #cbd5e1; border-radius: 6px; text-decoration: none; color: #475569; font-weight: 500;">← Volver a mis pedidos</a>
                    
                    <?php if (strtolower($pedidoDetalle['estado'] ?? '') === 'completado'): ?>
                        <a href="<?= BASE_URL; ?>views/clientes/resenas.php?id_pedido=<?= htmlspecialchars($pedidoDetalle['id_pedido'], ENT_QUOTES, 'UTF-8'); ?>" class="btn" style="padding: 0.5rem 1rem; background: #10b981; color: white; border-radius: 6px; text-decoration: none; font-weight: 500;">⭐ Calificar y dejar reseña</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 3rem; background: white; border-radius: 8px; border: 1px solid #e5e7eb; max-width: 600px; margin: 0 auto;">
                <h2 style="color: #0f172a; margin-bottom: 0.5rem;">Pedido no encontrado</h2>
                <p style="color: #64748b; margin-bottom: 1.5rem;">El pedido solicitado no existe o no tienes permiso para consultarlo.</p>
                <a href="<?= BASE_URL; ?>views/clientes/pedidos.php" class="btn" style="padding: 0.6rem 1.2rem; background: #2563eb; color: white; text-decoration: none; border-radius: 6px; font-weight: 500;">Regresar a mis pedidos</a>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <!-- ========================================== -->
        <!-- LISTADO GENERAL DE PEDIDOS DEL CLIENTE     -->
        <!-- ========================================== -->
        <h1 class="section-title" style="font-size: 1.75rem; color: #0f172a; margin-bottom: 0.5rem;">Mis Pedidos</h1>
        <p style="color: #6b7280; margin-bottom: 1.5rem;">Consulta el historial de tus compras realizadas, fechas, montos y estado de entrega.</p>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; margin-top: 1.5rem;">
            <?php 
            $listaPedidos = method_exists($pedidosController, 'listarPedidosPorUsuario') 
                ? $pedidosController->listarPedidosPorUsuario($id_usuario_actual) 
                : (method_exists($pedidosController, 'listarPorUsuario') ? $pedidosController->listarPorUsuario($id_usuario_actual) : []);
            
            if (!empty($listaPedidos)):
                foreach($listaPedidos as $ped): 
                    $idPed = $ped['id_pedido'];
                    $montoTotal = floatval($ped['total'] ?? 0);
                    $estadoPed = $ped['estado'] ?? 'Pendiente';
                    $fechaPed = $ped['fecha_creacion'] ?? $ped['fecha_pedido'] ?? $ped['fecha'] ?? 'N/D';
            ?>
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; display: flex; flex-direction: column; box-shadow: 0 1px 3px rgba(0,0,0,0.05); padding: 1.25rem;">
                
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                    <div>
                        <span style="background: #e0f2fe; color: #0369a1; padding: 0.15rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600;">
                            Pedido #<?= htmlspecialchars($idPed, ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                        <h3 style="font-size: 1.05rem; color: #111827; margin: 0.35rem 0 0 0;">
                            <?= htmlspecialchars($ped['cliente'] ?? $_SESSION['usuario'] ?? 'Mi Cuenta', ENT_QUOTES, 'UTF-8'); ?>
                        </h3>
                    </div>
                    <span style="font-weight: bold; color: #0284c7; font-size: 1.15rem;">
                        Q.<?= number_format($montoTotal, 2); ?>
                    </span>
                </div>

                <div style="font-size: 0.85rem; color: #4b5563; margin-bottom: 0.4rem;">
                    <strong>Fecha:</strong> <?= htmlspecialchars($fechaPed, ENT_QUOTES, 'UTF-8'); ?>
                </div>

                <div style="font-size: 0.85rem; margin-bottom: 1.2rem;">
                    <strong>Estado:</strong> 
                    <span style="display: inline-block; padding: 0.1rem 0.4rem; background: #fef3c7; color: #92400e; border-radius: 4px; font-weight: bold; font-size: 0.8rem;">
                        <?= htmlspecialchars(ucfirst($estadoPed), ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </div>

                <div style="display: flex; flex-direction: column; gap: 0.5rem; border-top: 1px solid #f3f4f6; padding-top: 0.75rem; margin-top: auto;">
                    <a href="<?= BASE_URL; ?>views/clientes/pedidos.php?id=<?= htmlspecialchars($idPed, ENT_QUOTES, 'UTF-8'); ?>" style="font-size: 0.85rem; color: #0284c7; text-decoration: none; font-weight: 600;">
                        Ver Detalles &rarr;
                    </a>

                    <?php if (strtolower($estadoPed) === 'completado'): ?>
                        <a href="<?= BASE_URL; ?>views/clientes/resenas.php?id_pedido=<?= htmlspecialchars($idPed, ENT_QUOTES, 'UTF-8'); ?>" style="text-align: center; background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 0.4rem; font-size: 0.85rem; border-radius: 4px; text-decoration: none; font-weight: 500; display: block;">
                            ⭐ Agregar Reseña
                        </a>
                    <?php endif; ?>
                </div>

            </div>
            <?php 
                endforeach; 
            else:
            ?>
                <div style="grid-column: 1 / -1; background: #f8fafc; padding: 2.5rem; text-align: center; border-radius: 8px; border: 1px dashed #cbd5e1;">
                    <p style="color: #6b7280; margin-bottom: 1rem;">Aún no has realizado ningún pedido.</p>
                    <a href="<?= BASE_URL; ?>views/clientes/index.php" class="btn" style="background: #2563eb; color: white; padding: 0.6rem 1.2rem; border-radius: 6px; text-decoration: none; font-size: 0.9rem; font-weight: 500;">
                        Explorar productos
                    </a>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</main>

<script src="<?= BASE_URL; ?>assets/js/app.js"></script>

<?php 
include_once __DIR__ . '/../layouts/footer.php'; 
?>