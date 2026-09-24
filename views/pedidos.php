<?php
// Iniciar sesión para control de acceso
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir el controlador de pedidos y configuración de rutas
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/PedidosController.php';

$pedidosController = new PedidosController();

// Identificar si se está viendo el detalle de un pedido específico
$id_pedido = isset($_GET['id']) ? intval($_GET['id']) : null;
$modo_admin = isset($_GET['admin']) && $_GET['admin'] === 'true';

// Manejo de peticiones POST (Actualizar estado o eliminar pedido)
$mensaje_alerta = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'admin') {
    $accion = $_POST['accion'] ?? '';
    
    if ($accion === 'actualizar_estado') {
        $id_act = intval($_POST['id_pedido']);
        $nuevo_estado = $_POST['estado'] ?? 'Pendiente';
        
        if ($pedidosController->actualizarEstadoPedido($id_act, $nuevo_estado)) {
            $mensaje_alerta = "<div class='alert success' style='background: #d1fae5; color: #065f46; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem;'>Estado del pedido #{$id_act} actualizado a '{$nuevo_estado}'.</div>";
        } else {
            $mensaje_alerta = "<div class='alert error' style='background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem;'>Error al actualizar el estado del pedido.</div>";
        }
    } elseif ($accion === 'eliminar') {
        $id_del = intval($_POST['id_pedido']);
        if ($pedidosController->eliminarPedido($id_del)) {
            $mensaje_alerta = "<div class='alert success' style='background: #d1fae5; color: #065f46; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem;'>Pedido eliminado correctamente del sistema.</div>";
        } else {
            $mensaje_alerta = "<div class='alert error' style='background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem;'>Error al eliminar el pedido.</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ferretería del Constructor - Gestión de Pedidos</title>
    <link rel="stylesheet" href="<?= BASE_URL; ?>assets/css/styles.css">
</head>
<body>

    <!-- Navegación Constante -->
    <header>
        <div class="nav-container container">
            <a href="<?= BASE_URL; ?>views/index.php" class="logo">Ferretería del Constructor</a>
            <nav style="display: flex; gap: 1rem; align-items: center;">
                <a href="<?= BASE_URL; ?>views/index.php" style="color: white; text-decoration: none;">Catálogo</a>
                <a href="<?= BASE_URL; ?>views/productos.php" style="color: white; text-decoration: none;">Productos</a>
                <a href="<?= BASE_URL; ?>views/categorias.php" style="color: white; text-decoration: none;">Categorías</a>
                <a href="<?= BASE_URL; ?>views/carrito.php" style="color: white; text-decoration: none;">Carrito</a>
                <?php if (isset($_SESSION['usuario'])): ?>
                    <a href="<?= BASE_URL; ?>views/pedidos.php?admin=true" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.85rem;">Admin Pedidos</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="container" style="padding: 2rem 1rem;">
        <?= $mensaje_alerta; ?>

        <?php if ($id_pedido): ?>
            <!-- ========================================== -->
            <!-- VISTA DETALLE DE UN PEDIDO ESPECÍFICO      -->
            <!-- ========================================== -->
            <?php 
            $pedidoDetalle = $pedidosController->obtenerPedidoPorId($id_pedido);
            if ($pedidoDetalle): 
            ?>
                <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); max-width: 800px; margin: 0 auto;">
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e5e7eb; padding-bottom: 1rem; margin-bottom: 1.5rem;">
                        <div>
                            <span style="background: #e0f2fe; color: #0369a1; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.8rem; font-weight: 600;">Pedido #<?= $pedidoDetalle['id_pedido']; ?></span>
                            <h1 style="margin: 0.5rem 0 0 0; font-size: 1.75rem; color: #111827;">Detalles de la Compra</h1>
                        </div>
                        <div style="text-align: right;">
                            <p style="margin: 0; font-size: 0.85rem; color: #6b7280;">Fecha: <?= $pedidoDetalle['fecha_creacion'] ?? 'N/D'; ?></p>
                            <span style="display: inline-block; margin-top: 0.25rem; background: #fef3c7; color: #92400e; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.8rem; font-weight: bold;">Estado: <?= $pedidoDetalle['estado']; ?></span>
                        </div>
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <h3 style="font-size: 1.1rem; color: #374151; margin-bottom: 0.5rem;">Información del Cliente</h3>
                        <p style="margin: 0; color: #4b5563;"><strong>Cliente:</strong> <?= htmlspecialchars($pedidoDetalle['cliente'] ?? 'Usuario general'); ?></p>
                        <p style="margin: 0; color: #4b5563;"><strong>Correo:</strong> <?= htmlspecialchars($pedidoDetalle['correo'] ?? 'No registrado'); ?></p>
                    </div>

                    <h3 style="font-size: 1.1rem; color: #374151; margin-bottom: 0.75rem;">Productos del Pedido</h3>
                    <div style="border: 1px solid #e5e7eb; border-radius: 6px; overflow: hidden; margin-bottom: 1.5rem;">
                        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                            <thead style="background: #f9fafb; border-bottom: 1px solid #e5e7eb;">
                                <tr>
                                    <th style="padding: 0.75rem;">Producto</th>
                                    <th style="padding: 0.75rem;">Precio Unitario</th>
                                    <th style="padding: 0.75rem;">Cantidad</th>
                                    <th style="padding: 0.75rem;">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($pedidoDetalle['detalles'])): ?>
                                    <?php foreach ($pedidoDetalle['detalles'] as $item): ?>
                                    <tr style="border-bottom: 1px solid #f3f4f6;">
                                        <td style="padding: 0.75rem; display: flex; align-items: center; gap: 0.75rem;">
                                            <span><?= htmlspecialchars($item['producto'] ?? 'Producto #' . $item['id_producto']); ?></span>
                                        </td>
                                        <td style="padding: 0.75rem;">Q.<?= number_format($item['precio_unitario'], 2); ?></td>
                                        <td style="padding: 0.75rem;"><?= $item['cantidad']; ?></td>
                                        <td style="padding: 0.75rem; font-weight: bold;">Q.<?= number_format($item['precio_unitario'] * $item['cantidad'], 2); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" style="padding: 1rem; text-align: center; color: #6b7280;">No hay detalles registrados para este pedido.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                        <span style="font-size: 1.25rem; font-weight: bold; color: #111827;">Total General:</span>
                        <span style="font-size: 1.5rem; font-weight: bold; color: #0284c7;">Q.<?= number_format($pedidoDetalle['total'], 2); ?></span>
                    </div>

                    <div style="display: flex; gap: 1rem;">
                        <a href="<?= BASE_URL; ?>views/pedidos.php?admin=true" class="btn btn-outline">Volver al Panel de Pedidos</a>
                    </div>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 3rem;">
                    <h2>Pedido no encontrado</h2>
                    <p>El identificador del pedido no existe o fue eliminado.</p>
                    <a href="<?= BASE_URL; ?>views/pedidos.php" class="btn" style="margin-top: 1rem;">Regresar</a>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- ========================================== -->
            <!-- LISTADO Y PANEL DE PEDIDOS EN TARJETAS     -->
            <!-- ========================================== -->
            <h1 class="section-title">Gestión de Pedidos del Sistema</h1>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; margin-top: 1.5rem;">
                <?php 
                // Listar pedidos (si el usuario es admin ve todos, de lo contrario se podría filtrar por usuario)
                $listaPedidos = $pedidosController->listarPedidos();
                
                if (!empty($listaPedidos)):
                    foreach($listaPedidos as $ped): 
                ?>
                <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; display: flex; flex-direction: column; box-shadow: 0 1px 3px rgba(0,0,0,0.05); padding: 1.25rem;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                        <div>
                            <span style="background: #e0f2fe; color: #0369a1; padding: 0.15rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600;">Pedido #<?= $ped['id_pedido']; ?></span>
                            <h3 style="font-size: 1.1rem; color: #111827; margin: 0.35rem 0 0 0;"><?= htmlspecialchars($ped['cliente'] ?? 'Cliente General'); ?></h3>
                        </div>
                        <span style="font-weight: bold; color: #111827; font-size: 1.1rem;">Q.<?= number_format($ped['total'], 2); ?></span>
                    </div>

                    <p style="font-size: 0.85rem; color: #6b7280; margin-bottom: 0.5rem;">
                        <strong>Fecha:</strong> <?= $ped['fecha_creacion'] ?? 'N/D'; ?>
                    </p>

                    <!-- Formulario para actualizar estado rápidamente desde el panel de admin -->
                    <?php if (isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'admin'): ?>
                    <form action="<?= BASE_URL; ?>views/pedidos.php?admin=true" method="POST" style="margin-bottom: 1rem; display: flex; gap: 0.5rem; align-items: center;">
                        <input type="hidden" name="accion" value="actualizar_estado">
                        <input type="hidden" name="id_pedido" value="<?= $ped['id_pedido']; ?>">
                        <select name="estado" style="padding: 0.3rem; border: 1px solid #ccc; border-radius: 4px; font-size: 0.8rem; flex: 1;" onchange="this.form.submit()">
                            <option value="Pendiente" <?= $ped['estado'] === 'Pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                            <option value="Completado" <?= $ped['estado'] === 'Completado' ? 'selected' : ''; ?>>Completado</option>
                            <option value="Cancelado" <?= $ped['estado'] === 'Cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                        </select>
                    </form>
                    <?php else: ?>
                        <p style="font-size: 0.85rem; margin-bottom: 1rem;"><strong>Estado:</strong> <span style="color: #0369a1;"><?= $ped['estado']; ?></span></p>
                    <?php endif; ?>

                    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f3f4f6; padding-top: 0.75rem; margin-top: auto;">
                        <a href="<?= BASE_URL; ?>views/pedidos.php?id=<?= $ped['id_pedido']; ?>" style="font-size: 0.85rem; color: var(--primary, #0284c7); text-decoration: none; font-weight: 500;">Ver Detalles</a>
                        
                        <?php if (isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'admin'): ?>
                        <!-- Botón Eliminar Pedido -->
                        <form action="<?= BASE_URL; ?>views/pedidos.php?admin=true" method="POST" onsubmit="return confirm('¿Está seguro de eliminar este pedido permanentemente?');" style="margin: 0;">
                            <input type="hidden" name="accion" value="eliminar">
                            <input type="hidden" name="id_pedido" value="<?= $ped['id_pedido']; ?>">
                            <button type="submit" style="background-color: #ef4444; color: white; padding: 0.3rem 0.6rem; font-size: 0.8rem; border: none; border-radius: 4px; cursor: pointer;">Eliminar</button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
                <?php 
                    endforeach; 
                else:
                ?>
                    <p style="color: #6b7280; grid-column: 1 / -1;">No hay pedidos registrados en el sistema actualmente.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>

    <script src="<?= BASE_URL; ?>assets/js/app.js"></script>
</body>
</html>