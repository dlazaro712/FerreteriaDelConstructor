<?php
// views/clientes/pedidos.php

// Iniciar sesión para control de acceso
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validación de seguridad: solo clientes autenticados
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'cliente') {
    header("Location: " . BASE_URL . "views/usuarios.php?action=login");
    exit();
}

// Incluir configuración, base de datos y el controlador de pedidos
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../controllers/PedidosController.php';

$pedidosController = new PedidosController();

// Identificar si se está viendo el detalle de un pedido específico del cliente
$id_pedido = isset($_GET['id']) ? intval($_GET['id']) : null;

// Obtener el ID del usuario cliente logueado actualmente desde la sesión
$id_usuario_actual = $_SESSION['id_usuario'] ?? $_SESSION['user_id'] ?? null;

// Incluir el encabezado centralizado
include_once __DIR__ . '/../layouts/header.php';
?>

<main class="container" style="max-width: 1200px; margin: 2rem auto; padding: 0 1rem;">
    <?php if ($id_pedido): ?>
        <!-- ========================================== -->
        <!-- VISTA DETALLE DE UN PEDIDO DEL CLIENTE     -->
        <!-- ========================================== -->
        <?php 
        $pedidoDetalle = $pedidosController->obtenerPedidoPorId($id_pedido);
        
        // Validar que el pedido exista y pertenezca estrictamente al cliente actual
        $esPropietario = ($pedidoDetalle && isset($pedidoDetalle['id_usuario']) && $pedidoDetalle['id_usuario'] == $id_usuario_actual);

        if ($pedidoDetalle && $esPropietario): 
        ?>
            <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); max-width: 800px; margin: 0 auto;">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e5e7eb; padding-bottom: 1rem; margin-bottom: 1.5rem;">
                    <div>
                        <span style="background: #e0f2fe; color: #0369a1; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.8rem; font-weight: 600;">Pedido #<?= $pedidoDetalle['id_pedido']; ?></span>
                        <h1 style="margin: 0.5rem 0 0 0; font-size: 1.75rem; color: #111827;">Detalle de mi Compra</h1>
                    </div>
                    <div style="text-align: right;">
                        <p style="margin: 0; font-size: 0.85rem; color: #6b7280;">Fecha: <?= $pedidoDetalle['fecha_creacion'] ?? $pedidoDetalle['fecha'] ?? 'N/D'; ?></p>
                        <span style="display: inline-block; margin-top: 0.25rem; background: #fef3c7; color: #92400e; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.8rem; font-weight: bold;">Estado: <?= htmlspecialchars($pedidoDetalle['estado']); ?></span>
                    </div>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <h3 style="font-size: 1.1rem; color: #374151; margin-bottom: 0.5rem;">Información del Cliente</h3>
                    <p style="margin: 0; color: #4b5563;"><strong>Cliente:</strong> <?= htmlspecialchars($pedidoDetalle['cliente'] ?? $_SESSION['nombre'] ?? 'Cliente'); ?></p>
                    <p style="margin: 0; color: #4b5563;"><strong>Correo:</strong> <?= htmlspecialchars($pedidoDetalle['correo'] ?? $_SESSION['correo'] ?? 'No registrado'); ?></p>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; background: #f8fafc; padding: 1.25rem; border-radius: 6px;">
                    <span style="font-size: 1.25rem; font-weight: bold; color: #111827;">Total Pagado:</span>
                    <span style="font-size: 1.5rem; font-weight: bold; color: #0284c7;">Q.<?= number_format($pedidoDetalle['total'], 2); ?></span>
                </div>

                <div style="display: flex; gap: 1rem; justify-content: space-between; align-items: center;">
                    <a href="<?= BASE_URL; ?>views/clientes/pedidos.php" class="btn btn-outline" style="padding: 0.5rem 1rem; border: 1px solid #cbd5e1; border-radius: 6px; text-decoration: none; color: #475569;">Volver a mis pedidos</a>
                    
                    <?php if (strtolower($pedidoDetalle['estado']) === 'completado'): ?>
                        <a href="<?= BASE_URL; ?>views/clientes/resenas.php?id_pedido=<?= $pedidoDetalle['id_pedido']; ?>" class="btn" style="padding: 0.5rem 1rem; background: #10b981; color: white; border-radius: 6px; text-decoration: none; font-weight: 500;">Calificar y dejar reseña</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 3rem; background: white; border-radius: 8px;">
                <h2>Pedido no encontrado o sin acceso</h2>
                <p>El pedido solicitado no existe o no pertenece a tu cuenta.</p>
                <a href="<?= BASE_URL; ?>views/clientes/pedidos.php" class="btn" style="margin-top: 1rem; display: inline-block; padding: 0.5rem 1rem; background: #2563eb; color: white; text-decoration: none; border-radius: 6px;">Regresar a mis pedidos</a>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <!-- ========================================== -->
        <!-- LISTADO DE PEDIDOS DEL CLIENTE (TARJETAS)  -->
        <!-- ========================================== -->
        <h1 class="section-title" style="font-size: 1.75rem; color: #0f172a; margin-bottom: 0.5rem;">Mis Pedidos</h1>
        <p style="color: #6b7280; margin-bottom: 1.5rem;">Consulta el historial de tus compras realizadas, fechas, montos y estado de entrega.</p>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; margin-top: 1.5rem;">
            <?php 
            // Obtener únicamente los pedidos del cliente autenticado actual
            $listaPedidos = $pedidosController->listarPedidosPorUsuario($id_usuario_actual);
            
            if (!empty($listaPedidos)):
                foreach($listaPedidos as $ped): 
            ?>
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; display: flex; flex-direction: column; box-shadow: 0 1px 3px rgba(0,0,0,0.05); padding: 1.25rem;">
                <!-- Cabecera de la Tarjeta: ID y Total -->
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                    <div>
                        <span style="background: #e0f2fe; color: #0369a1; padding: 0.15rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600;">Pedido #<?= $ped['id_pedido']; ?></span>
                        <h3 style="font-size: 1.05rem; color: #111827; margin: 0.35rem 0 0 0;"><?= htmlspecialchars($ped['cliente'] ?? $_SESSION['nombre'] ?? 'Mi Cuenta'); ?></h3>
                    </div>
                    <span style="font-weight: bold; color: #0284c7; font-size: 1.15rem;">Q.<?= number_format($ped['total'], 2); ?></span>
                </div>

                <!-- Información de Fecha -->
                <div style="font-size: 0.85rem; color: #4b5563; margin-bottom: 0.4rem;">
                    <strong>Fecha:</strong> <?= $ped['fecha_creacion'] ?? $ped['fecha'] ?? 'N/D'; ?>
                </div>

                <!-- Información de Estado -->
                <div style="font-size: 0.85rem; margin-bottom: 1.2rem;">
                    <strong>Estado:</strong> 
                    <span style="display: inline-block; padding: 0.1rem 0.4rem; background: #fef3c7; color: #92400e; border-radius: 4px; font-weight: bold; font-size: 0.8rem;"><?= htmlspecialchars($ped['estado']); ?></span>
                </div>

                <!-- Acciones y Enlaces de la Tarjeta -->
                <div style="display: flex; flex-direction: column; gap: 0.5rem; border-top: 1px solid #f3f4f6; padding-top: 0.75rem; margin-top: auto;">
                    <a href="<?= BASE_URL; ?>views/clientes/pedidos.php?id=<?= $ped['id_pedido']; ?>" style="font-size: 0.85rem; color: #0284c7; text-decoration: none; font-weight: 500;">Ver Detalles &rarr;</a>

                    <?php if (strtolower($ped['estado']) === 'completado'): ?>
                        <a href="<?= BASE_URL; ?>views/clientes/resenas.php?id_pedido=<?= $ped['id_pedido']; ?>" style="text-align: center; background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 0.4rem; font-size: 0.85rem; border-radius: 4px; text-decoration: none; font-weight: 500; display: block;">⭐ Agregar Reseña</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php 
                endforeach; 
            else:
            ?>
                <div style="grid-column: 1 / -1; background: #f8fafc; padding: 2.5rem; text-align: center; border-radius: 8px; border: 1px dashed #cbd5e1;">
                    <p style="color: #6b7280; margin-bottom: 1rem;">Aún no has realizado ningún pedido.</p>
                    <a href="<?= BASE_URL; ?>views/clientes/index.php" class="btn" style="background: #2563eb; color: white; padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; font-size: 0.9rem;">Explorar productos</a>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</main>

<script src="<?= BASE_URL; ?>assets/js/app.js"></script>

<?php 
// Incluir el pie de página centralizado
include_once __DIR__ . '/../layouts/footer.php'; 
?>