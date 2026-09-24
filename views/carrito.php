<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';

include_once __DIR__ . '/layouts/header.php';
?>

<div class="cart-page-container">
    <h1 class="section-title">Carrito de Compras</h1>
    
    <div class="cart-layout">
        
        <!-- Listado de Productos en el Carrito (Renderizado por carrito.js) -->
        <div class="cart-items-section">
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Precio Unitario</th>
                            <th>Cantidad</th>
                            <th>Subtotal</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-carrito-body">
                        <!-- El contenido se llena dinámicamente con JavaScript -->
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">Cargando carrito...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="cart-actions-row">
                <a href="<?= BASE_URL; ?>views/index.php" class="btn btn-outline">Seguir Comprando</a>
                <button type="button" class="btn btn-danger-outline" onclick="vaciarCarrito()">Vaciar Carrito</button>
            </div>
        </div>

        <!-- Resumen de Compra y Pago -->
        <div class="cart-summary-section" id="resumen-carrito-container">
            <div class="card-summary">
                <h3 class="summary-title">Resumen del Pedido</h3>
                
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span id="total-general">Q.0.00</span>
                </div>
                <div class="summary-row">
                    <span>Envío</span>
                    <span>Calculado al finalizar</span>
                </div>
                <hr class="summary-divider">
                <div class="summary-row summary-total">
                    <span>Total Estimado</span>
                    <span id="total-general-final">Q.0.00</span>
                </div>

                <?php if (isset($_SESSION['usuario'])): ?>
                    <a href="<?= BASE_URL; ?>views/checkout.php" class="btn btn-primary btn-block mt-3">Proceder al Pago</a>
                <?php else: ?>
                    <div class="alert alert-info mt-3" style="font-size: 0.85rem; text-align: center;">
                        Debes <a href="<?= BASE_URL; ?>views/usuarios.php?action=login" class="text-link">iniciar sesión</a> para completar tu pedido.
                    </div>
                    <a href="<?= BASE_URL; ?>views/usuarios.php?action=login" class="btn btn-dark btn-block mt-2">Iniciar Sesión para Comprar</a>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php 
include_once __DIR__ . '/layouts/footer.php'; 
?>