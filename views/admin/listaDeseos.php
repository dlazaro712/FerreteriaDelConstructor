<?php
// views/admin/listaDeseos.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validación de seguridad: solo administradores
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'admin') {
    header("Location: " . BASE_URL . "views/usuarios.php?action=login");
    exit();
}

// Inclusión de archivos de configuración y base de datos
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/Database.php';

$mensaje = '';
$error = '';

try {
    $database = new Database();
    $db = $database->getConnection();
} catch (Exception $e) {
    $db = null;
    $error = "Error de conexión a la base de datos: " . $e->getMessage();
}

// Procesar eliminación de un elemento de la lista de deseos si se solicita por GET
if (isset($_GET['accion']) && $_GET['accion'] === 'eliminar' && isset($_GET['id']) && $db) {
    $id_wishlist = intval($_GET['id']);
    try {
        $stmtDel = $db->prepare("DELETE FROM wishlist WHERE id_wishlist = ?");
        if ($stmtDel->execute([$id_wishlist])) {
            $mensaje = "El registro de la lista de deseos fue eliminado correctamente.";
        } else {
            $error = "No se pudo eliminar el elemento de la lista de deseos.";
        }
    } catch (PDOException $e) {
        $error = "Error en la base de datos al eliminar: " . $e->getMessage();
    }
}

// Obtener la lista completa de deseos uniendo las tablas según la estructura real de la base de datos:
// wishlist (id_wishlist, id_usuario, id_producto) + usuarios (nombre, correo) + productos (nombre)
$listaDeseos = [];
if ($db) {
    try {
        $query = "SELECT w.id_wishlist, p.id_producto, p.nombre AS producto_nombre, u.nombre AS usuario_nombre, u.correo AS usuario_correo 
                  FROM wishlist w
                  INNER JOIN productos p ON w.id_producto = p.id_producto
                  INNER JOIN usuarios u ON w.id_usuario = u.id_usuario
                  ORDER BY w.id_wishlist DESC";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        $listaDeseos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = "Error al consultar la lista de deseos: " . $e->getMessage() . 
                 " (Verifica que las tablas 'wishlist', 'productos' y 'usuarios' tengan campos coincidentes como id_producto e id_usuario).";
    }
}

// Incluir el encabezado centralizado del panel
include_once __DIR__ . '/../layouts/header.php';
?>

<div class="admin-dashboard" style="max-width: 1200px; margin: 0 auto; padding: 1rem;">
    
    <!-- Encabezado de la Sección -->
    <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="color: #0f172a; margin: 0 0 0.5rem 0; font-size: 1.75rem;">Panel de Administración: Lista de Deseos</h1>
            <p style="color: #64748b; margin: 0;">Monitorea los productos que los usuarios han guardado en sus listas de deseos.</p>
        </div>
    </div>

    <!-- Alertas de éxito o error -->
    <?php if (!empty($mensaje)): ?>
        <div style="background: #ecfdf5; border: 1px solid #10b981; color: #065f46; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; font-weight: 500;">
            <?= htmlspecialchars($mensaje); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div style="background: #fef2f2; border: 1px solid #ef4444; color: #991b1b; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; font-weight: 500;">
            <?= htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <!-- Tabla de Listado de Deseos -->
    <div style="background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
        <div style="padding: 1.5rem; border-bottom: 1px solid #e2e8f0;">
            <h2 style="margin: 0; font-size: 1.25rem; color: #0f172a;">Productos Guardados por Usuarios</h2>
        </div>

        <?php if (!empty($listaDeseos)): ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                    <thead>
                        <tr style="background: #f8fafc; color: #475569; border-bottom: 1px solid #e2e8f0;">
                            <th style="padding: 1rem; width: 90px;">ID Deseo</th>
                            <th style="padding: 1rem; width: 100px;">ID Producto</th>
                            <th style="padding: 1rem;">Nombre de Usuario</th>
                            <th style="padding: 1rem;">Nombre del Producto</th>
                            <th style="padding: 1rem; text-align: center; width: 150px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($listaDeseos as $item): ?>
                            <tr style="border-bottom: 1px solid #e2e8f0;">
                                <td style="padding: 1rem; color: #64748b; font-weight: 600;">#<?= htmlspecialchars($item['id_wishlist']); ?></td>
                                <td style="padding: 1rem; color: #2563eb; font-weight: 600;">#<?= htmlspecialchars($item['id_producto']); ?></td>
                                <td style="padding: 1rem;">
                                    <div style="font-weight: 600; color: #0f172a;"><?= htmlspecialchars($item['usuario_nombre']); ?></div>
                                    <div style="font-size: 0.8rem; color: #64748b;"><?= htmlspecialchars($item['usuario_correo'] ?? ''); ?></div>
                                </td>
                                <td style="padding: 1rem; color: #0f172a; font-weight: 600;">
                                    <?= htmlspecialchars($item['producto_nombre']); ?>
                                </td>
                                <td style="padding: 1rem; text-align: center;">
                                    <!-- Botón Eliminar -->
                                    <a href="<?= BASE_URL; ?>views/admin/listaDeseos.php?accion=eliminar&id=<?= $item['id_wishlist']; ?>" 
                                       onclick="return confirm('¿Estás seguro de quitar este producto de la lista de deseos del usuario?');" 
                                       style="padding: 0.4rem 0.8rem; background: #dc2626; color: white; border-radius: 4px; text-decoration: none; font-size: 0.85rem; display: inline-block;" title="Eliminar de lista de deseos">
                                        🗑️ Eliminar
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="padding: 3rem; text-align: center; color: #64748b;">
                No hay registros en las listas de deseos de los usuarios actualmente.
            </div>
        <?php endif; ?>
    </div>

</div>

<?php 
// Incluir el pie de página centralizado
include_once __DIR__ . '/../layouts/footer.php'; 
?>