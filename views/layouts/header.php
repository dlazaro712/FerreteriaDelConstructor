<?php
// Iniciar sesión si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/config.php';

$es_admin = isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'admin';
$usuario_autenticado = isset($_SESSION['usuario']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ferretería del Constructor</title>
    <link rel="stylesheet" href="<?= BASE_URL; ?>assets/css/styles.css">
</head>
<body>

    <!-- Navegación Principal -->
    <header style="background-color: #0f172a; color: white; border-bottom: 1px solid #334155;">
        <div class="nav-container container" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem;">
            
            <!-- Logo y Enlace Principal -->
            <a href="<?= BASE_URL; ?>views/index.php" class="logo" style="color: white; text-decoration: none; font-weight: bold; font-size: 1.25rem;">
                Ferretería del Constructor
            </a>

            <!-- Navegación condicional según Rol -->
            <nav style="display: flex; gap: 1.25rem; align-items: center; flex-wrap: wrap;">
                
                <?php if ($es_admin): ?>
                    <!-- ========================================== -->
                    <!-- MENÚ CENTRALIZADO PARA ADMINISTRADOR       -->
                    <!-- ========================================== -->
                    <span style="background: #dc2626; color: white; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: bold;">MODO ADMIN</span>
                    <a href="<?= BASE_URL; ?>views/admin/dashboard.php" style="color: #f8fafc; text-decoration: none; font-size: 0.9rem;">Dashboard</a>
                    <a href="<?= BASE_URL; ?>views/admin/productos.php" style="color: #f8fafc; text-decoration: none; font-size: 0.9rem;">Productos</a>
                    <a href="<?= BASE_URL; ?>views/admin/categorias.php" style="color: #f8fafc; text-decoration: none; font-size: 0.9rem;">Categorías</a>
                    <a href="<?= BASE_URL; ?>views/admin/usuarios.php" style="color: #f8fafc; text-decoration: none; font-size: 0.9rem;">Usuarios</a>
                    <a href="<?= BASE_URL; ?>views/admin/pedidos.php" style="color: #f8fafc; text-decoration: none; font-size: 0.9rem;">Pedidos</a>
                    <a href="<?= BASE_URL; ?>views/admin/listaDeseos.php" style="color: #f8fafc; text-decoration: none; font-size: 0.9rem;">Lista de deseos</a>

                <?php else: ?>
                    <!-- ========================================== -->
                    <!-- MENÚ PARA CLIENTES / PÚBLICO GENERAL       -->
                    <!-- ========================================== -->
                    <a href="<?= BASE_URL; ?>index.php" style="color: #f8fafc; text-decoration: none; font-size: 0.9rem;">Inicio</a>
                    <a href="<?= BASE_URL; ?>views/clientes/carrito.php" style="color: #f8fafc; text-decoration: none; font-size: 0.9rem;">Carrito (<span id="contador-carrito">0</span>)</a>
                    <?php if ($usuario_autenticado): ?>
                        <a href="<?= BASE_URL; ?>views/clientes/index.php" style="color: #f8fafc; text-decoration: none; font-size: 0.9rem;">Catálogo</a>
                        <a href="<?= BASE_URL; ?>views/clientes/pedidos.php" style="color: #f8fafc; text-decoration: none; font-size: 0.9rem;">Mis Pedidos</a>
                        <a href="<?= BASE_URL; ?>views/clientes/ListaDeseos.php" style="color: #f8fafc; text-decoration: none; font-size: 0.9rem;">Lista de Deseos</a>
                        <a href="<?= BASE_URL; ?>views/clientes/resenas.php" style="color: #f8fafc; text-decoration: none; font-size: 0.9rem;">Reseñas</a>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Acciones de Sesión Generales -->
                <div style="border-left: 1px solid #334155; padding-left: 1rem; display: flex; gap: 0.75rem; align-items: center;">
                    <?php if ($usuario_autenticado): ?>
                        <span style="color: #94a3b8; font-size: 0.85rem;">Hola, <strong><?= htmlspecialchars($_SESSION['usuario']); ?></strong></span>
                        <a href="<?= BASE_URL; ?>views/usuarios.php?action=logout" style="color: #fca5a5; text-decoration: none; font-size: 0.85rem;">Salir</a>
                    <?php else: ?>
                        <a href="<?= BASE_URL; ?>views/usuarios.php?action=login" style="color: #f8fafc; text-decoration: none; font-size: 0.85rem;">Iniciar Sesión</a>
                        <a href="<?= BASE_URL; ?>views/usuarios.php?action=register" class="btn" style="padding: 0.3rem 0.7rem; font-size: 0.85rem; background: #0284c7; color: white; border-radius: 4px; text-decoration: none;">Registrarse</a>
                    <?php endif; ?>
                </div>

            </nav>
        </div>
    </header>

    <main class="container" style="padding: 2rem 1rem; min-height: 75vh;">