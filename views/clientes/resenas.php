<?php
// views/clientes/resenas.php

// Iniciar sesión para control de acceso
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validación de seguridad: solo clientes autenticados
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'cliente') {
    header("Location: " . BASE_URL . "views/usuarios.php?action=login");
    exit();
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../controllers/ResenasController.php';

$resenaController = new ResenasController();
$resenas = $resenaController->index() ?? [];

// Identificar si se pasó un id_pedido por GET para preseleccionar contexto si aplica
$id_pedido_param = isset($_GET['id_pedido']) ? intval($_GET['id_pedido']) : null;

// Obtener listados para los selectores (usuario actual logueado y productos disponibles)
$database = new Database();
$db = $database->getConnection();

$id_usuario_actual = $_SESSION['id_usuario'] ?? $_SESSION['user_id'] ?? null;
$nombre_usuario_actual = $_SESSION['nombre'] ?? 'Cliente';

$productos = [];
if ($db) {
    // Consulta para obtener productos
    $stmtP = $db->query("SELECT id_producto, nombre FROM productos ORDER BY nombre ASC");
    if ($stmtP) {
        $productos = $stmtP->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Incluir el encabezado centralizado
include_once __DIR__ . '/../layouts/header.php';
?>

<main class="container" style="padding: 2.5rem 1.5rem; max-width: 1000px; margin: 0 auto;">
    <div style="margin-bottom: 2rem;">
        <h2 style="color: #111827; margin-bottom: 0.5rem;">Calificaciones y Opiniones</h2>
        <p style="color: #4b5563; margin: 0;">Comparte tu experiencia o revisa los comentarios sobre las herramientas y materiales adquiridos.</p>
        <?php if ($id_pedido_param): ?>
            <div style="margin-top: 0.75rem; background: #e0f2fe; color: #0369a1; padding: 0.5rem 1rem; border-radius: 6px; font-size: 0.9rem;">
                Estás redactando una reseña correspondiente al <strong>Pedido #<?= $id_pedido_param; ?></strong>.
            </div>
        <?php endif; ?>
    </div>

    <!-- Formulario para registrar nueva reseña -->
    <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2.5rem;">
        <h3 style="margin-top: 0; color: #1f2937; margin-bottom: 1.5rem;">Escribe una Reseña</h3>
        <form action="<?= BASE_URL; ?>views/clientes/guardarResena.php" method="POST" id="form-resena">
            <!-- Campo oculto para registrar el usuario de la sesión actual de forma segura -->
            <input type="hidden" name="id_usuario" value="<?= $id_usuario_actual; ?>">
            <?php if ($id_pedido_param): ?>
                <input type="hidden" name="id_pedido" value="<?= $id_pedido_param; ?>">
            <?php endif; ?>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1rem;">
                <div>
                    <label style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem;">Cliente:</label>
                    <input type="text" value="<?= htmlspecialchars($nombre_usuario_actual); ?>" disabled style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 4px; background: #f9fafb; color: #374151;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem;">Nombre de Producto:</label>
                    <select name="id_producto" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 4px; background: white;">
                        <option value="">Seleccione un producto...</option>
                        <?php foreach ($productos as $p): ?>
                            <option value="<?= $p['id_producto']; ?>"><?= htmlspecialchars($p['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem;">Calificación (1 al 5 estrellas):</label>
                <select name="calificacion" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 4px; background: white;">
                    <option value="5">★★★★★ (5 - Excelente)</option>
                    <option value="4">★★★★☆ (4 - Muy bueno)</option>
                    <option value="3">★★★☆☆ (3 - Bueno)</option>
                    <option value="2">★★☆☆☆ (2 - Regular)</option>
                    <option value="1">★☆☆☆☆ (1 - Malo)</option>
                </select>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem;">Comentario:</label>
                <textarea name="comentario" rows="4" required placeholder="¿Qué te pareció el producto?" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 4px;"></textarea>
            </div>

            <button type="submit" style="background: #2563eb; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 4px; font-weight: bold; cursor: pointer;">Publicar Reseña</button>
        </form>
    </div>

    <!-- Listado de Reseñas Registradas -->
    <h3 style="color: #1f2937; margin-bottom: 1rem;">Opiniones Recientes</h3>
    <div id="contenedor-resenas" style="display: flex; flex-direction: column; gap: 1rem;">
        <?php if (empty($resenas)): ?>
            <div style="background: white; padding: 2rem; text-align: center; border-radius: 8px; color: #6b7280;">
                Aún no hay reseñas registradas en el sistema. ¡Sé el primero en opinar!
            </div>
        <?php else: ?>
            <?php foreach ($resenas as $r): ?>
                <div style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <span style="font-weight: bold; color: #111827;"><?= htmlspecialchars($r['usuario_nombre'] ?? 'Usuario #' . $r['id_usuario']); ?></span>
                        <span style="color: #f59e0b; font-size: 1.1rem;"><?= str_repeat('★', intval($r['calificacion'])) . str_repeat('☆', 5 - intval($r['calificacion'])); ?></span>
                    </div>
                    <p style="color: #4b5563; font-size: 0.9rem; margin: 0 0 0.75rem 0;">Producto: <strong><?= htmlspecialchars($r['producto_nombre'] ?? 'Producto #' . $r['id_producto']); ?></strong></p>
                    <p style="color: #374151; margin: 0 0 1rem 0;"><?= htmlspecialchars($r['comentario']); ?></p>
                    <small style="color: #9ca3af;"><?= htmlspecialchars($r['fecha'] ?? ''); ?></small>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div style="margin-top: 2rem;">
        <a href="<?= BASE_URL; ?>views/clientes/index.php" style="text-decoration: none; padding: 0.5rem 1rem; border: 1px solid #d1d5db; border-radius: 4px; color: #374151; background: white;">&larr; Volver al Catálogo</a>
    </div>
</main>

<script src="<?= BASE_URL; ?>assets/js/app.js"></script>

<?php 
// Incluir el pie de página centralizado
include_once __DIR__ . '/../layouts/footer.php'; 
?>