<?php
// views/clientes/ListaDeseos.php

// Iniciar sesión para control de acceso
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validación de seguridad: solo clientes autenticados
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'cliente') {
    header("Location: " . BASE_URL . "views/usuarios.php?action=login");
    exit();
}

// Incluir configuración, base de datos y el controlador de la Lista de Deseos
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../controllers/ListaDeseosController.php';

$listaDeseosController = new ListaDeseosController();

// Obtener de forma segura el ID del usuario cliente logueado actualmente desde la sesión
$id_usuario = $_SESSION['id_usuario'] ?? $_SESSION['user_id'] ?? null;

// Obtener los productos de la lista de deseos a través del controlador usando el ID de sesión
$productosDeseados = [];
try {
    if ($id_usuario) {
        $productosDeseados = $listaDeseosController->listarPorUsuario($id_usuario) ?? [];
    }
} catch (Exception $e) {
    // Manejo básico de respaldo en caso de que el método del controlador tenga otro nombre
    if (method_exists($listaDeseosController, 'index')) {
        $productosDeseados = $listaDeseosController->index() ?? [];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Lista de Deseos - Ferretería del Constructor</title>
    <!-- Enlace a la hoja de estilos general utilizando BASE_URL -->
    <link rel="stylesheet" href="<?= BASE_URL; ?>assets/css/styles.css">
</head>
<body style="background-color: #f3f4f6; margin: 0; font-family: sans-serif;">
    <header style="background: #1f2937; color: white; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center;">
        <h1 style="font-size: 1.25rem; margin: 0;">Ferretería del Constructor</h1>
        <nav>
            <a href="<?= BASE_URL; ?>views/clientes/index.php" style="color: white; text-decoration: none; margin-right: 1.5rem;">Catálogo</a>
            <a href="<?= BASE_URL; ?>views/clientes/ListaDeseos.php" style="color: #f59e0b; text-decoration: none; font-weight: bold; margin-right: 1.5rem;">Lista de Deseos</a>
            <a href="<?= BASE_URL; ?>views/clientes/carrito.php" style="color: white; text-decoration: none; margin-right: 1.5rem;">Carrito</a>
            <a href="<?= BASE_URL; ?>views/clientes/resenas.php" style="color: white; text-decoration: none;">Reseñas</a>
        </nav>
    </header>

    <main class="container" style="padding: 2.5rem 1.5rem; max-width: 1200px; margin: 0 auto;">
        <div style="margin-bottom: 2rem;">
            <h2 style="color: #111827; margin-bottom: 0.5rem;">Mi Lista de Deseos</h2>
            <p style="color: #4b5563; margin: 0;">Guarda aquí las herramientas y materiales que deseas consultar o comprar posteriormente.</p>
        </div>

        <!-- Contenedor donde se renderizará dinámicamente la lista de deseos -->
        <div id="contenedor-wishlist-cliente" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 1.5rem;">
            <?php if (empty($productosDeseados)): ?>
                <div style="grid-column: 1 / -1; background: white; padding: 2.5rem; text-align: center; border-radius: 8px; color: #6b7280; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <p style="margin: 0 0 1rem 0; font-size: 1.1rem;">Tu lista de deseos está vacía actualmente.</p>
                    <a href="<?= BASE_URL; ?>views/clientes/index.php" style="color: #2563eb; text-decoration: none; font-weight: bold;">Explorar el catálogo de productos &rarr;</a>
                </div>
            <?php else: ?>
                <?php foreach ($productosDeseados as $item): ?>
                    <div style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <h3 style="color: #111827; margin-top: 0; font-size: 1.1rem;"><?= htmlspecialchars($item['producto_nombre'] ?? $item['nombre'] ?? 'Producto'); ?></h3>
                            <p style="color: #4b5563; font-size: 0.9rem; margin-bottom: 1rem;"><?= htmlspecialchars($item['descripcion'] ?? ''); ?></p>
                            <span style="color: #059669; font-weight: bold; font-size: 1.1rem; display: block; margin-bottom: 1rem;">Q.<?= number_format($item['precio'] ?? 0, 2); ?></span>
                        </div>
                        <div style="display: flex; gap: 0.5rem;">
                            <a href="<?= BASE_URL; ?>views/clientes/carrito.php?agregar=<?= $item['id_producto'] ?? 0; ?>" style="flex: 1; background: #2563eb; color: white; text-align: center; padding: 0.6rem; border-radius: 4px; text-decoration: none; font-size: 0.9rem; font-weight: bold;">Comprar</a>
                            <a href="<?= BASE_URL; ?>controllers/ListaDeseosController.php?accion=eliminar&id=<?= $item['id_deseo'] ?? 0; ?>" style="background: #ef4444; color: white; padding: 0.6rem 0.9rem; border-radius: 4px; text-decoration: none; font-size: 0.9rem;" title="Eliminar de la lista">✕</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div style="margin-top: 3rem;">
            <a href="<?= BASE_URL; ?>views/clientes/index.php" class="btn btn-outline" style="text-decoration: none; padding: 0.6rem 1.2rem; border: 1px solid #d1d5db; border-radius: 4px; color: #374151; background: white;">&larr; Volver al catálogo principal</a>
        </div>
    </main>

    <!-- Conexión centralizada con app.js -->
    <script src="<?= BASE_URL; ?>assets/js/app.js"></script>
</body>
</html>