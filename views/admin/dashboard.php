<?php
// views/admin/dashboard.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validación de seguridad: solo administradores
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'admin') {
    header("Location: ../usuarios.php?action=login");
    exit();
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/Database.php';

// Conexión y consulta de métricas para las tarjetas del dashboard
try {
    $database = new Database();
    $db = $database->getConnection();

    // 1. Cantidad total de productos
    $stmtProd = $db->query("SELECT COUNT(*) AS total FROM productos");
    $totalProductos = $stmtProd->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // 2. Cantidad de usuarios tipo cliente y admin
    $stmtUsers = $db->query("
        SELECT 
            SUM(CASE WHEN tipo_usuario = 'cliente' THEN 1 ELSE 0 END) AS total_clientes,
            SUM(CASE WHEN tipo_usuario = 'admin' THEN 1 ELSE 0 END) AS total_admins,
            COUNT(*) AS total_usuarios 
        FROM usuarios
    ");
    $dataUsers = $stmtUsers->fetch(PDO::FETCH_ASSOC);
    $totalClientes = $dataUsers['total_clientes'] ?? 0;
    $totalAdmins = $dataUsers['total_admins'] ?? 0;

    // 3. Cantidad total de pedidos
    // Nota: Ajusta el nombre de la tabla de pedidos si es diferente en tu base de datos
    $stmtPedidos = $db->query("SELECT COUNT(*) AS total FROM pedidos");
    $totalPedidos = $stmtPedidos->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

} catch (Exception $e) {
    $totalProductos = 0;
    $totalClientes = 0;
    $totalAdmins = 0;
    $totalPedidos = 0;
}

// Incluir el encabezado centralizado
include_once __DIR__ . '/../layouts/header.php';
?>

<div class="dashboard-container" style="max-width: 1200px; margin: 0 auto;">
    
    <!-- Sección de Bienvenida -->
    <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="color: #0f172a; margin: 0 0 0.5rem 0; font-size: 1.75rem;">Panel de Control Administrativo</h1>
            <p style="color: #64748b; margin: 0;">Bienvenido de nuevo, <strong><?= htmlspecialchars($_SESSION['usuario'] ?? 'Administrador'); ?></strong>. Aquí tienes un resumen general del sistema de la ferretería.</p>
        </div>
        <div>
            <span style="background: #fee2e2; color: #dc2626; padding: 0.5rem 1rem; border-radius: 6px; font-weight: bold; font-size: 0.85rem; border: 1px solid #fca5a5;">Modo Administrador Activo</span>
        </div>
    </div>

    <!-- Contenedor de Tarjetas (Grid de Métricas) -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem;">
        
        <!-- Tarjeta: Total de Productos -->
        <div style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-left: 4px solid #2563eb;">
            <div style="color: #64748b; font-size: 0.85rem; font-weight: bold; text-transform: uppercase; margin-bottom: 0.5rem;">Productos Registrados</div>
            <div style="font-size: 2rem; font-weight: bold; color: #0f172a; margin-bottom: 0.5rem;"><?= $totalProductos; ?></div>
            <a href="<?= BASE_URL; ?>views/admin/productos.php" style="color: #2563eb; text-decoration: none; font-size: 0.85rem; font-weight: 600;">Gestionar productos &rarr;</a>
        </div>

        <!-- Tarjeta: Clientes -->
        <div style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-left: 4px solid #059669;">
            <div style="color: #64748b; font-size: 0.85rem; font-weight: bold; text-transform: uppercase; margin-bottom: 0.5rem;">Clientes Activos</div>
            <div style="font-size: 2rem; font-weight: bold; color: #0f172a; margin-bottom: 0.5rem;"><?= $totalClientes; ?></div>
            <a href="<?= BASE_URL; ?>views/admin/usuarios.php" style="color: #059669; text-decoration: none; font-size: 0.85rem; font-weight: 600;">Ver usuarios &rarr;</a>
        </div>

        <!-- Tarjeta: Administradores -->
        <div style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-left: 4px solid #7c3aed;">
            <div style="color: #64748b; font-size: 0.85rem; font-weight: bold; text-transform: uppercase; margin-bottom: 0.5rem;">Administradores</div>
            <div style="font-size: 2rem; font-weight: bold; color: #0f172a; margin-bottom: 0.5rem;"><?= $totalAdmins; ?></div>
            <a href="<?= BASE_URL; ?>views/admin/usuarios.php" style="color: #7c3aed; text-decoration: none; font-size: 0.85rem; font-weight: 600;">Ver roles &rarr;</a>
        </div>

        <!-- Tarjeta: Pedidos -->
        <div style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-left: 4px solid #d97706;">
            <div style="color: #64748b; font-size: 0.85rem; font-weight: bold; text-transform: uppercase; margin-bottom: 0.5rem;">Pedidos Totales</div>
            <div style="font-size: 2rem; font-weight: bold; color: #0f172a; margin-bottom: 0.5rem;"><?= $totalPedidos; ?></div>
            <a href="<?= BASE_URL; ?>views/admin/pedidos.php?admin=true" style="color: #d97706; text-decoration: none; font-size: 0.85rem; font-weight: 600;">Revisar pedidos &rarr;</a>
        </div>

    </div>

    <!-- Sección de Accesos Rápidos del Admin -->
    <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <h3 style="color: #0f172a; margin-top: 0; margin-bottom: 1rem; font-size: 1.25rem;">Accesos Rápidos de Gestión</h3>
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <a href="<?= BASE_URL; ?>views/admin/productos.php" class="btn" style="background: #2563eb; color: white; padding: 0.75rem 1.25rem; border-radius: 6px; text-decoration: none; font-weight: 500;">Administrar Productos</a>
            <a href="<?= BASE_URL; ?>views/admin/categorias.php" class="btn" style="background: #475569; color: white; padding: 0.75rem 1.25rem; border-radius: 6px; text-decoration: none; font-weight: 500;">Administrar Categorías</a>
            <a href="<?= BASE_URL; ?>views/admin/usuarios.php" class="btn" style="background: #059669; color: white; padding: 0.75rem 1.25rem; border-radius: 6px; text-decoration: none; font-weight: 500;">Control de Usuarios</a>
            <a href="<?= BASE_URL; ?>views/admin/pedidos.php?admin=true" class="btn" style="background: #d97706; color: white; padding: 0.75rem 1.25rem; border-radius: 6px; text-decoration: none; font-weight: 500;">Ver Pedidos Globales</a>
        </div>
    </div>

</div>

<?php 
// Incluir el pie de página centralizado
include_once __DIR__ . '/../layouts/footer.php'; 
?>