<?php
// views/clientes/carrito.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/ProductosController.php';

include_once __DIR__ . '/../layouts/header.php';
?>

<!-- Estilos CSS3 Modernos embebidos para la vista del carrito (sin estilos de imagen) -->
<style>
    .cart-wrapper {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 1rem;
        font-family: inherit;
    }

    .cart-header-title {
        font-size: 1.85rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 1.5rem;
    }

    .cart-layout-grid {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 2rem;
        align-items: flex-start;
    }

    .cart-table-card {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        border: 1px solid #e2e8f0;
    }

    .modern-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .modern-table th {
        background-color: #f8fafc;
        color: #475569;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 1rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .modern-table td {
        padding: 1rem;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        vertical-align: middle;
        font-size: 0.95rem;
    }

    .qty-input-group {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .qty-control-btn {
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        color: #334155;
        width: 28px;
        height: 28px;
        border-radius: 4px;
        cursor: pointer;
        font-weight: bold;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;
    }

    .qty-control-btn:hover {
        background: #e2e8f0;
    }

    .qty-display {
        font-weight: 600;
        min-width: 20px;
        text-align: center;
    }

    .cart-actions-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.25rem;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
    }

    .summary-card {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
        padding: 1.5rem;
        border: 1px solid #e2e8f0;
    }

    .summary-title {
        font-size: 1.2rem;
        font-weight: 700;
        color: #0f172a;
        margin-top: 0;
        margin-bottom: 1.25rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.85rem;
        color: #64748b;
        font-size: 0.95rem;
    }

    .summary-row.total-row {
        font-size: 1.25rem;
        font-weight: 700;
        color: #0f172a;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px dashed #cbd5e1;
    }

    .btn-modern {
        display: block;
        width: 100%;
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
        font-weight: 600;
        text-align: center;
        border-radius: 8px;
        cursor: pointer;
        border: none;
        transition: background 0.2s, transform 0.1s;
        text-decoration: none;
        box-sizing: border-box;
    }

    .btn-modern-primary {
        background-color: #2563eb;
        color: #ffffff;
    }

    .btn-modern-primary:hover {
        background-color: #1d4ed8;
    }

    .btn-modern-dark {
        background-color: #0f172a;
        color: #ffffff;
    }

    .btn-modern-dark:hover {
        background-color: #1e293b;
    }

    .btn-modern-outline {
        background-color: transparent;
        color: #475569;
        border: 1px solid #cbd5e1;
    }

    .btn-modern-outline:hover {
        background-color: #f1f5f9;
        color: #0f172a;
    }

    .btn-modern-danger-outline {
        background-color: transparent;
        color: #dc2626;
        border: 1px solid #fecaca;
        font-size: 0.85rem;
        padding: 0.5rem 1rem;
        width: auto;
    }

    .btn-modern-danger-outline:hover {
        background-color: #fef2f2;
    }

    .btn-delete-item {
        color: #dc2626;
        background: none;
        border: none;
        cursor: pointer;
        font-size: 1rem;
        transition: transform 0.2s;
    }

    .btn-delete-item:hover {
        transform: scale(1.15);
    }

    @media (max-width: 900px) {
        .cart-layout-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

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
                            <!-- Renderizado dinámico desde el script general -->
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

        <!-- Sección de Resumen y Pago -->
        <div class="cart-summary-container">
            <div class="summary-card">
                <h3 class="summary-title">Resumen del Pedido</h3>
                
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

<!-- Script de soporte para renderizar los elementos del carrito en la tabla sin imágenes -->
<script>
    document.addEventListener("DOMContentLoaded", () => {
        renderizarTablaCarrito();
    });

    function renderizarTablaCarrito() {
        const tbody = document.getElementById("tabla-carrito-body");
        const spanTotal = document.getElementById("total-general");
        const spanTotalFinal = document.getElementById("total-general-final");
        
        let carrito = JSON.parse(localStorage.getItem("carrito_ferreteria")) || [];

        if (!tbody) return;

        if (carrito.length === 0) {
            tbody.innerHTML = `<tr><td colspan="5" style="text-align: center; color: #94a3b8; padding: 2.5rem;">Tu carrito está vacío.</td></tr>`;
            if (spanTotal) spanTotal.textContent = "Q.0.00";
            if (spanTotalFinal) spanTotalFinal.textContent = "Q.0.00";
            return;
        }

        let html = "";
        let acumuladoTotal = 0;

        carrito.forEach((item, index) => {
            let subtotal = parseFloat(item.precio) * parseInt(item.cantidad);
            acumuladoTotal += subtotal;

            html += `
                <tr>
                    <td>
                        <span style="font-weight: 600; color: #1e293b;">${item.nombre}</span>
                    </td>
                    <td>Q.${parseFloat(item.precio).toFixed(2)}</td>
                    <td>
                        <div class="qty-input-group">
                            <button type="button" class="qty-control-btn" onclick="cambiarCantidadCarrito(${index}, -1)">-</button>
                            <span class="qty-display">${item.cantidad}</span>
                            <button type="button" class="qty-control-btn" onclick="cambiarCantidadCarrito(${index}, 1)">+</button>
                        </div>
                    </td>
                    <td style="font-weight: 600; color: #0f172a;">Q.${subtotal.toFixed(2)}</td>
                    <td style="text-align: right;">
                        <button type="button" class="btn-delete-item" onclick="eliminarItemCarrito(${index})" title="Eliminar producto">🗑️</button>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
        if (spanTotal) spanTotal.textContent = `Q.${acumuladoTotal.toFixed(2)}`;
        if (spanTotalFinal) spanTotalFinal.textContent = `Q.${acumuladoTotal.toFixed(2)}`;
    }

    function cambiarCantidadCarrito(index, delta) {
        let carrito = JSON.parse(localStorage.getItem("carrito_ferreteria")) || [];
        if (carrito[index]) {
            let nuevaCantidad = carrito[index].cantidad + delta;
            let stockMax = carrito[index].stockMax || 999;

            if (nuevaCantidad > 0 && nuevaCantidad <= stockMax) {
                carrito[index].cantidad = nuevaCantidad;
                localStorage.setItem("carrito_ferreteria", JSON.stringify(carrito));
                renderizarTablaCarrito();
                if (typeof actualizarContadorCarrito === 'function') actualizarContadorCarrito();
            } else if (nuevaCantidad > stockMax) {
                alert("Has alcanzado el límite máximo de stock disponible.");
            }
        }
    }

    function eliminarItemCarrito(index) {
        let carrito = JSON.parse(localStorage.getItem("carrito_ferreteria")) || [];
        carrito.splice(index, 1);
        localStorage.setItem("carrito_ferreteria", JSON.stringify(carrito));
        renderizarTablaCarrito();
        if (typeof actualizarContadorCarrito === 'function') actualizarContadorCarrito();
    }

    function vaciarCarrito() {
        if (confirm("¿Estás seguro de que deseas vaciar todo el carrito?")) {
            localStorage.removeItem("carrito_ferreteria");
            renderizarTablaCarrito();
            if (typeof actualizarContadorCarrito === 'function') actualizarContadorCarrito();
        }
    }

    function procesarCheckoutPedido() {
        let carrito = JSON.parse(localStorage.getItem("carrito_ferreteria")) || [];
        if (carrito.length === 0) {
            alert("El carrito está vacío.");
            return;
        }
        alert("¡Pedido preparado para procesar!");
    }
</script>

<?php 
include_once __DIR__ . '/../layouts/footer.php'; 
?>