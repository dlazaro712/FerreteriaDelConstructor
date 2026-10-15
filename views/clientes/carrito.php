<?php
// views/clientes/carrito.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclusión de configuraciones y controladores permitidos (Sin modelos directos)
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../controllers/ProductosController.php';
require_once __DIR__ . '/../../controllers/PedidosController.php';
require_once __DIR__ . '/../../controllers/EstadoDetallePedidoController.php';

include_once __DIR__ . '/../layouts/header.php';
?>

<!-- Enlace al archivo CSS externo -->
<link rel="stylesheet" href="<?= BASE_URL; ?>assets/css/styles.css">

<div class="cart-wrapper">
    <h1 class="cart-header-title">🛒 Carrito de Compras</h1>
    
    <div class="cart-layout-grid">
        
        <!-- Sección de Listado de Productos -->
        <div class="cart-items-container">
            <div class="cart-table-card">
                <div style="overflow-x: auto;">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Precio Unitario</th>
                                <th>Cantidad</th>
                                <th>Subtotal</th>
                                <th style="text-align: right;">Acción</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-carrito-body">
                            <!-- Renderizado dinámico mediante app.js -->
                            <tr>
                                <td colspan="5" style="text-align: center; color: #94a3b8; padding: 2rem;">Cargando tu carrito...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="cart-actions-bar">
                    <a href="<?= BASE_URL; ?>views/clientes/index.php" class="btn-modern btn-modern-outline" style="width: auto; padding: 0.5rem 1rem; font-size: 0.85rem;">← Seguir Comprando</a>
                    <button type="button" class="btn-modern btn-modern-danger-outline" onclick="vaciarCarrito()">🗑️ Vaciar Carrito</button>
                </div>
            </div>
        </div>

        <!-- Sección de Resumen del Pedido -->
        <div class="cart-summary-container">
            <div class="summary-card">
                <h3 class="summary-title">Resumen del Pedido</h3>
                
                <div class="summary-row">
                    <span>Nombre de Usuario:</span>
                    <span style="font-weight: 600; color: #334155;"><?= htmlspecialchars($_SESSION['usuario'] ?? 'Invitado'); ?></span>
                </div>

                <div class="summary-row">
                    <span>Fecha:</span>
                    <span style="font-weight: 600; color: #334155;"><?= date('d/m/Y'); ?></span>
                </div>

                <div class="summary-row">
                    <span>Estado del Pedido:</span>
                    <span style="font-weight: 600; color: #059669;">Pendiente</span>
                </div>

                <div class="summary-row">
                    <span>Subtotal</span>
                    <span id="total-general" style="font-weight: 600; color: #334155;">Q.0.00</span>
                </div>

                <div class="summary-row total-row">
                    <span>Total a Pagar</span>
                    <span id="total-general-final" style="color: #2563eb;">Q.0.00</span>
                </div>

                <div style="margin-top: 1.5rem;">
                    <?php if (isset($_SESSION['id_usuario'])): ?>
                        <button type="button" class="btn-modern btn-modern-primary" onclick="procesarCheckoutPedido()">Continuar con la Compra</button>
                    <?php else: ?>
                        <div style="background-color: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; padding: 0.85rem; border-radius: 8px; font-size: 0.85rem; text-align: center; margin-bottom: 1rem;">
                            Debes <a href="<?= BASE_URL; ?>views/usuarios.php?action=login" style="color: #2563eb; font-weight: 600; text-decoration: underline;">iniciar sesión</a> para completar tu pedido de forma segura.
                        </div>
                        <a href="<?= BASE_URL; ?>views/usuarios.php?action=login" class="btn-modern btn-modern-dark">Iniciar Sesión para Comprar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Inclusión del script centralizado app.js -->
<script src="<?= BASE_URL; ?>assets/js/app.js"></script>

<?php 
include_once __DIR__ . '/../layouts/footer.php'; 
?>