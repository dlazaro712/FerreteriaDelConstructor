<?php
// views/clientes/estadoDetalle.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../controllers/PedidosController.php';
require_once __DIR__ . '/../../controllers/UsuariosController.php';

// 1. Control de seguridad: solo acceso para usuarios con sesión activa
if (!isset($_SESSION['tipo_usuario']) || !isset($_SESSION['id_usuario'])) {
    header("Location: " . BASE_URL . "views/usuarios.php?action=login");
    exit();
}

$idUsuarioSession = intval($_SESSION['id_usuario']);
$idPedido = isset($_GET['id_pedido']) ? intval($_GET['id_pedido']) : 0;

if ($idPedido <= 0) {
    header("Location: " . BASE_URL . "views/clientes/pedidos.php");
    exit();
}

// 2. Inicialización de Controladores
$pedidosController = new PedidosController();
$usuariosController = new UsuariosController();

// 3. Obtención de datos del pedido, cliente y detalle
$pedido = $pedidosController->ver($idPedido);
$detallesPedido = $pedidosController->obtenerDetallePedido($idPedido);
$clienteInfo = $usuariosController->obtenerPorId($idUsuarioSession);

// Validar pertenencia del pedido al usuario logueado (Seguridad)
if (!$pedido || intval($pedido['id_usuario'] ?? $pedido['id_cliente'] ?? 0) !== $idUsuarioSession) {
    echo "<div style='max-width:800px; margin:3rem auto; padding:2rem; background:#fee2e2; color:#991b1b; border-radius:8px; font-family:sans-serif;'>
            <h2>Acceso No Autorizado</h2>
            <p>El pedido solicitado no existe o no corresponde a tu cuenta de usuario.</p>
            <a href='" . BASE_URL . "views/clientes/pedidos.php' style='color:#b91c1c; font-weight:bold;'>← Volver a mis pedidos</a>
          </div>";
    exit();
}

include_once __DIR__ . '/../layouts/header.php';
?>

<!-- Estilos CSS3 Modernos para la Vista de Estado y Detalle -->
<style>
    .detail-wrapper {
        max-width: 1100px;
        margin: 2rem auto;
        padding: 0 1rem;
        font-family: inherit;
    }

    .detail-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .detail-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
    }

    .status-badge {
        display: inline-block;
        padding: 0.35rem 0.85rem;
        border-radius: 9999px;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: capitalize;
    }

    .status-pendiente { background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
    .status-procesando { background-color: #e0f2fe; color: #075985; border: 1px solid #bae6fd; }
    .status-completado { background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    .status-cancelado { background-color: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

    .detail-grid {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 1.5rem;
        align-items: flex-start;
    }

    .card-panel {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
        border: 1px solid #e2e8f0;
        overflow: hidden;
        margin-bottom: 1.5rem;
    }

    .card-panel-header {
        background-color: #f8fafc;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #e2e8f0;
        font-weight: 700;
        color: #1e293b;
        font-size: 1rem;
    }

    .card-panel-body {
        padding: 1.25rem;
    }

    .detail-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .detail-table th {
        background-color: #f8fafc;
        color: #475569;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        padding: 0.85rem 1rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .detail-table td {
        padding: 0.85rem 1rem;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        font-size: 0.9rem;
        vertical-align: middle;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.75rem;
        font-size: 0.9rem;
        color: #475569;
    }

    .info-row span:first-child {
        color: #64748b;
    }

    .info-row.total-highlight {
        font-size: 1.15rem;
        font-weight: 700;
        color: #0f172a;
        padding-top: 0.75rem;
        border-top: 1px dashed #cbd5e1;
        margin-top: 0.75rem;
    }

    .btn-back {
        display: inline-block;
        padding: 0.6rem 1.2rem;
        background-color: #ffffff;
        color: #475569;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 500;
        font-size: 0.875rem;
        transition: background 0.2s;
    }

    .btn-back:hover {
        background-color: #f8fafc;
        color: #0f172a;
    }

    @media (max-width: 850px) {
        .detail-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="detail-wrapper">
    
    <!-- Encabezado de la página -->
    <div class="detail-header">
        <div>
            <h1 class="detail-title">📄 Pedido #<?= str_pad($pedido['id_pedido'], 6, '0', STR_PAD_LEFT); ?></h1>
            <p style="color: #64748b; margin: 0.25rem 0 0 0; font-size: 0.9rem;">
                Realizado el <?= date('d/m/Y h:i A', strtotime($pedido['fecha_pedido'] ?? $pedido['fecha'] ?? 'now')); ?>
            </p>
        </div>
        <div>
            <a href="<?= BASE_URL; ?>views/clientes/pedidos.php" class="btn-back">← Volver a Mis Pedidos</a>
        </div>
    </div>

    <div class="detail-grid">
        
        <!-- Columna Izquierda: Tabla de Productos Comprados -->
        <div>
            <div class="card-panel">
                <div class="card-panel-header">
                    🛠️ Productos en este Pedido
                </div>
                <div style="overflow-x: auto;">
                    <table class="detail-table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th style="text-align: center;">Precio Unitario</th>
                                <th style="text-align: center;">Cantidad</th>
                                <th style="text-align: right;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalCalculado = 0;
                            if (!empty($detallesPedido)): 
                                foreach ($detallesPedido as $det):
                                    $precioUnit = floatval($det['precio'] ?? 0);
                                    $cant = intval($det['cantidad'] ?? 1);
                                    $subtotal = $precioUnit * $cant;
                                    $totalCalculado += $subtotal;
                            ?>
                                <tr>
                                    <td>
                                        <span style="font-weight: 600; color: #0f172a; display: block;">
                                            <?= htmlspecialchars($det['nombre_producto'] ?? $det['nombre'] ?? 'Producto'); ?>
                                        </span>
                                    </td>
                                    <td style="text-align: center; color: #2563eb; font-weight: 500;">
                                        Q.<?= number_format($precioUnit, 2); ?>
                                    </td>
                                    <td style="text-align: center; font-weight: 600;">
                                        <?= $cant; ?>
                                    </td>
                                    <td style="text-align: right; font-weight: 700; color: #0f172a;">
                                        Q.<?= number_format($subtotal, 2); ?>
                                    </td>
                                </tr>
                            <?php 
                                endforeach; 
                            else: 
                            ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; color: #94a3b8; padding: 2rem;">
                                        No se encontraron ítems asociados a este pedido.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Estado, Datos del Cliente y Resumen -->
        <div>
            <!-- Estado del Pedido -->
            <div class="card-panel">
                <div class="card-panel-header">
                    📌 Estado del Pedido
                </div>
                <div class="card-panel-body" style="text-align: center;">
                    <?php 
                        $estadoRaw = strtolower($pedido['estado'] ?? 'pendiente');
                        $badgeClass = 'status-pendiente';
                        if (strpos($estadoRaw, 'completad') !== false || strpos($estadoRaw, 'entregad') !== false) {
                            $badgeClass = 'status-completado';
                        } elseif (strpos($estadoRaw, 'proces') !== false || strpos($estadoRaw, 'enviad') !== false) {
                            $badgeClass = 'status-procesando';
                        } elseif (strpos($estadoRaw, 'cancel') !== false) {
                            $badgeClass = 'status-cancelado';
                        }
                    ?>
                    <span class="status-badge <?= $badgeClass; ?>" style="font-size: 1rem; padding: 0.5rem 1.25rem;">
                        <?= ucfirst($estadoRaw); ?>
                    </span>
                </div>
            </div>

            <!-- Resumen de Costos -->
            <div class="card-panel">
                <div class="card-panel-header">
                    💳 Resumen Financiero
                </div>
                <div class="card-panel-body">
                    <div class="info-row">
                        <span>Subtotal</span>
                        <span>Q.<?= number_format($totalCalculado, 2); ?></span>
                    </div>
                    <div class="info-row">
                        <span>Envío / Entrega</span>
                        <span style="color: #059669; font-weight: 500;">Gratis</span>
                    </div>
                    <div class="info-row total-highlight">
                        <span>Total Pagado</span>
                        <span style="color: #2563eb;">Q.<?= number_format($pedido['total'] ?? $totalCalculado, 2); ?></span>
                    </div>
                </div>
            </div>

            <!-- Datos del Cliente -->
            <div class="card-panel">
                <div class="card-panel-header">
                    👤 Datos del Cliente
                </div>
                <div class="card-panel-body">
                    <div class="info-row">
                        <span>Nombre:</span>
                        <strong style="color: #0f172a;"><?= htmlspecialchars($clienteInfo['nombre'] ?? $_SESSION['usuario'] ?? 'Cliente'); ?></strong>
                    </div>
                    <div class="info-row">
                        <span>Email:</span>
                        <span><?= htmlspecialchars($clienteInfo['email'] ?? $clienteInfo['correo'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="info-row">
                        <span>Teléfono:</span>
                        <span><?= htmlspecialchars($clienteInfo['telefono'] ?? 'N/A'); ?></span>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>

<script src="<?= BASE_URL; ?>assets/js/app.js"></script>

<!-- Limpieza automática de LocalStorage tras confirmación de compra redirigida -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Si la redirección incluye confirmación de compra, limpiamos el carrito local
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get("checkout") === "success") {
        localStorage.removeItem("carrito_ferreteria");
        if (typeof window.actualizarContadorCarrito === 'function') {
            window.actualizarContadorCarrito();
        }
    }
});
</script>

<?php 
include_once __DIR__ . '/../layouts/footer.php'; 
?>