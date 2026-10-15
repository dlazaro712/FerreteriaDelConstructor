<?php
// views/clientes/ListaDeseos.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Inclusión de configuraciones y controladores necesarios
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../controllers/ListaDeseosController.php';

// 2. Validación de seguridad: solo clientes autenticados
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'cliente') {
    header("Location: " . BASE_URL . "views/usuarios.php?action=login");
    exit();
}

$id_usuario_actual = intval($_SESSION['id_usuario'] ?? $_SESSION['user_id'] ?? 0);
$listaDeseosController = new ListaDeseosController();

// 3. Procesar eliminación si se envía por GET (?action=eliminar&id=...)
if (isset($_GET['action']) && $_GET['action'] === 'eliminar' && isset($_GET['id'])) {
    $id_wishlist = intval($_GET['id']);
    if ($id_wishlist > 0) {
        $listaDeseosController->eliminarWishlist($id_wishlist);
    }
    header("Location: " . BASE_URL . "views/clientes/ListaDeseos.php");
    exit();
}

// 4. Obtención de la lista de deseos del usuario
$productosDeseados = [];
try {
    if ($id_usuario_actual > 0) {
        $productosDeseados = $listaDeseosController->listarPorUsuario($id_usuario_actual) ?: [];
    }
} catch (Exception $e) {
    error_log("Error al cargar lista de deseos en vista: " . $e->getMessage());
    $productosDeseados = [];
}

// 5. Incluir el encabezado centralizado de la aplicación
include_once __DIR__ . '/../layouts/header.php';
?>

<div style="max-width: 1100px; margin: 2rem auto; padding: 0 1rem; font-family: inherit;">
    <!-- Encabezado de la Sección -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-size: 1.85rem; font-weight: 700; color: #0f172a; margin: 0;">❤️ Mi Lista de Deseos</h1>
            <p style="color: #64748b; margin: 0.25rem 0 0 0; font-size: 0.95rem;">Guarda aquí las herramientas y materiales que deseas consultar o comprar posteriormente.</p>
        </div>
        <div>
            <a href="<?= BASE_URL; ?>views/clientes/index.php" style="padding: 0.6rem 1.2rem; background: #ffffff; color: #475569; border: 1px solid #cbd5e1; border-radius: 6px; text-decoration: none; font-weight: 500; font-size: 0.9rem; transition: background 0.2s;">← Seguir Comprando</a>
        </div>
    </div>

    <!-- Contenedor Principal de la Tabla / Lista -->
    <div style="background: #ffffff; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0; overflow: hidden;">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background-color: #f8fafc; color: #475569; font-size: 0.85rem; font-weight: 600; text-transform: uppercase;">
                        <th style="padding: 1rem;">Producto</th>
                        <th style="padding: 1rem;">Descripción</th>
                        <th style="padding: 1rem; text-align: center;">Precio</th>
                        <th style="padding: 1rem; text-align: center;">Disponibilidad</th>
                        <th style="padding: 1rem; text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($productosDeseados)): ?>
                        <?php foreach ($productosDeseados as $item): 
                            $idWish = $item['id_wishlist'] ?? 0;
                            $idProd = $item['id_producto'] ?? 0;
                            $nombreProd = $item['nombre_producto'] ?? $item['nombre'] ?? 'Producto';
                            $precioProd = floatval($item['precio'] ?? 0);
                            $stockProd = intval($item['stock'] ?? $item['cantidad'] ?? 0);
                            $imagenProd = $item['imagen'] ?? '';
                            $descripcionProd = $item['descripcion'] ?? 'Sin descripción detallada.';
                        ?>
                            <tr style="border-bottom: 1px solid #f1f5f9; color: #334155; font-size: 0.95rem;">
                                <!-- Columna Producto e Imagen -->
                                <td style="padding: 1rem; display: flex; align-items: center; gap: 1rem;">
                                    <?php if (!empty($imagenProd)): ?>
                                        <img src="<?= BASE_URL; ?>assets/imgs/<?= htmlspecialchars($imagenProd, ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($nombreProd, ENT_QUOTES, 'UTF-8'); ?>" style="width: 50px; height: 50px; object-fit: contain; border-radius: 6px; background: #f8fafc; border: 1px solid #e2e8f0;" onerror="this.src='<?= BASE_URL; ?>assets/imgs/Taladro-Atornillador-Inalambrico.png'">
                                    <?php else: ?>
                                        <div style="width: 50px; height: 50px; background: #f1f5f9; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; color: #94a3b8;">Sin img</div>
                                    <?php endif; ?>
                                    <span style="font-weight: 600; color: #0f172a;"><?= htmlspecialchars($nombreProd, ENT_QUOTES, 'UTF-8'); ?></span>
                                </td>

                                <!-- Columna Descripción -->
                                <td style="padding: 1rem; color: #64748b; font-size: 0.85rem; max-width: 250px;">
                                    <?= htmlspecialchars(substr($descripcionProd, 0, 65), ENT_QUOTES, 'UTF-8'); ?>...
                                </td>

                                <!-- Columna Precio -->
                                <td style="padding: 1rem; text-align: center; color: #2563eb; font-weight: 600;">
                                    Q.<?= number_format($precioProd, 2); ?>
                                </td>

                                <!-- Columna Stock -->
                                <td style="padding: 1rem; text-align: center;">
                                    <span style="font-size: 0.8rem; font-weight: 500; color: <?= $stockProd > 0 ? '#059669' : '#dc2626'; ?>;">
                                        <?= $stockProd > 0 ? 'En Stock (' . $stockProd . ')' : 'Agotado'; ?>
                                    </span>
                                </td>

                                <!-- Columna Acciones -->
                                <td style="padding: 1rem; text-align: right;">
                                    <div style="display: flex; gap: 0.5rem; justify-content: flex-end; align-items: center;">
                                        <?php if ($stockProd > 0): ?>
                                            <button type="button" 
                                                    onclick="agregarAlCarrito({
                                                        id_producto: <?= $idProd; ?>,
                                                        nombre: '<?= htmlspecialchars($nombreProd, ENT_QUOTES, 'UTF-8'); ?>',
                                                        precio: <?= $precioProd; ?>,
                                                        imagen: '<?= htmlspecialchars($imagenProd, ENT_QUOTES, 'UTF-8'); ?>',
                                                        stockMax: <?= $stockProd; ?>
                                                    }); window.location.href='<?= BASE_URL; ?>views/clientes/ListaDeseos.php?action=eliminar&id=<?= $idWish; ?>';"
                                                    style="padding: 0.5rem 0.85rem; background: #2563eb; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.85rem; font-weight: 500;"
                                                    title="Mueve este producto al carrito y lo retira de deseos">
                                                🛒 Enviar a Carrito
                                            </button>
                                        <?php endif; ?>

                                        <a href="<?= BASE_URL; ?>views/clientes/ListaDeseos.php?action=eliminar&id=<?= $idWish; ?>" 
                                           style="padding: 0.5rem 0.75rem; background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; border-radius: 6px; text-decoration: none; font-size: 0.85rem;" 
                                           onclick="return confirm('¿Estás seguro de eliminar este producto de tu lista de deseos?');"
                                           title="Eliminar de la lista">
                                            🗑️
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 3.5rem; color: #94a3b8;">
                                <p style="margin: 0 0 1rem 0; font-size: 1.1rem;">Tu lista de deseos está vacía actualmente.</p>
                                <a href="<?= BASE_URL; ?>views/clientes/index.php" style="color: #2563eb; text-decoration: none; font-weight: 600;">Explorar el catálogo de productos &rarr;</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Enlace al script centralizado -->
<script src="<?= BASE_URL; ?>assets/js/app.js"></script>

<?php 
// Incluir el pie de página centralizado de la aplicación
include_once __DIR__ . '/../layouts/footer.php'; 
?>