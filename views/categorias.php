<?php
// Iniciar sesión para control de acceso
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir el controlador de categorías necesario
require_once __DIR__ . '/../controllers/CategoriaController.php';

$categoriaController = new CategoriaController();

// Identificar si estamos viendo una categoría específica o el panel de administración general
$id_categoria = isset($_GET['id']) ? intval($_GET['id']) : null;
$modo_admin = isset($_GET['admin']) && $_GET['admin'] === 'true';

// Manejo de peticiones POST desde formularios (Crear / Actualizar / Eliminar)
$mensaje_alerta = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'admin') {
    $accion = $_POST['accion'] ?? '';
    
    if ($accion === 'crear') {
        if ($categoriaController->crear($_POST)) {
            $mensaje_alerta = "<div class='alert success' style='background: #d1fae5; color: #065f46; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem;'>Categoría creada exitosamente en la base de datos.</div>";
        } else {
            $mensaje_alerta = "<div class='alert error' style='background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem;'>Error al crear la categoría. Verifique los campos.</div>";
        }
    } elseif ($accion === 'actualizar') {
        $id_act = intval($_POST['id_categoria']);
        if ($categoriaController->actualizar($id_act, $_POST)) {
            $mensaje_alerta = "<div class='alert success' style='background: #d1fae5; color: #065f46; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem;'>Categoría actualizada exitosamente.</div>";
        } else {
            $mensaje_alerta = "<div class='alert error' style='background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem;'>Error al actualizar la categoría.</div>";
        }
    } elseif ($accion === 'eliminar') {
        $id_del = intval($_POST['id_categoria']);
        if ($categoriaController->eliminar($id_del)) {
            $mensaje_alerta = "<div class='alert success' style='background: #d1fae5; color: #065f46; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem;'>Categoría eliminada correctamente.</div>";
        } else {
            $mensaje_alerta = "<div class='alert error' style='background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem;'>Error al eliminar la categoría. Es posible que tenga productos asociados.</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ferretería del Constructor - Gestión de Categorías</title>
    <link rel="stylesheet" href="<?= BASE_URL; ?>assets/css/styles.css">
</head>
<body>

    <!-- Navegación Constante -->
    <header>
        <div class="nav-container container">
            <a href="../views/index.php" class="logo">Ferretería del Constructor</a>
            <nav style="display: flex; gap: 1rem; align-items: center;">
                <a href="index.php" style="color: white; text-decoration: none;">Catálogo</a>
                <a href="productos.php" style="color: white; text-decoration: none;">Productos</a>
                <a href="carrito.php" style="color: white; text-decoration: none;">Carrito (<span id="contador-carrito">0</span>)</a>
                <?php if (isset($_SESSION['usuario'])): ?>
                    <a href="categorias.php?admin=true" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.85rem;">Admin Categorías</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="container" style="padding: 2rem 1rem;">
        <?= $mensaje_alerta; ?>

        <?php if ($modo_admin && isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'admin'): ?>
            <!-- ========================================== -->
            <!-- PANEL CRUD ADMINISTRATIVO DE CATEGORÍAS    -->
            <!-- ========================================== -->
            <section>
                <h1 class="section-title">Administración de Categorías</h1>
                
                <!-- Formulario Create -->
                <div class="admin-card-form" style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 2rem;">
                    <h3>Registrar Nueva Categoría (CREATE)</h3>
                    <form action="categorias.php?admin=true" method="POST" style="display: grid; gap: 1rem; margin-top: 1rem;">
                        <input type="hidden" name="accion" value="crear">
                        <div>
                            <label style="font-size: 0.85rem; font-weight: bold; display: block; margin-bottom: 0.25rem;">Nombre de la Categoría:</label>
                            <input type="text" name="nombre" placeholder="Ej. Herramientas de Medición" required style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                        </div>
                        <div>
                            <label style="font-size: 0.85rem; font-weight: bold; display: block; margin-bottom: 0.25rem;">Descripción:</label>
                            <textarea name="descripcion" placeholder="Descripción breve de la categoría..." rows="3" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;"></textarea>
                        </div>
                        <button type="submit" class="btn" style="width: fit-content; margin-top: 0.5rem;">Guardar Categoría</button>
                    </form>
                </div>

                <!-- Contenedor de Tarjetas (Cards) para Administrar Categorías -->
                <h3 style="margin-bottom: 1rem;">Categorías Registradas (Vista en Tarjetas)</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem;">
                    <?php 
                    $todasCategorias = $categoriaController->listar();
                    if (!empty($todasCategorias)):
                        foreach($todasCategorias as $cat): 
                    ?>
                    <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; display: flex; flex-direction: column; box-shadow: 0 1px 3px rgba(0,0,0,0.05); padding: 1.25rem;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                            <h3 style="font-size: 1.1rem; color: #111827; margin: 0;"><?= htmlspecialchars($cat['nombre']); ?></h3>
                            <span style="background: #e0f2fe; color: #0369a1; padding: 0.15rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600;">ID: <?= $cat['id_categoria']; ?></span>
                        </div>
                        <p style="font-size: 0.9rem; color: #4b5563; margin-bottom: 1.25rem; line-height: 1.4; flex: 1;">
                            <?= !empty($cat['descripcion']) ? htmlspecialchars($cat['descripcion']) : '<em>Sin descripción registrada.</em>'; ?>
                        </p>
                        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f3f4f6; padding-top: 0.75rem; margin-top: auto;">
                            <a href="categorias.php?id=<?= $cat['id_categoria']; ?>" style="font-size: 0.85rem; color: var(--primary, #0284c7); text-decoration: none; font-weight: 500;">Ver detalles</a>
                            
                            <!-- Botón Eliminar -->
                            <form action="categorias.php?admin=true" method="POST" onsubmit="return confirm('¿Está seguro de eliminar esta categoría?');" style="margin: 0;">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="id_categoria" value="<?= $cat['id_categoria']; ?>">
                                <button type="submit" style="background-color: #ef4444; color: white; padding: 0.3rem 0.6rem; font-size: 0.8rem; border: none; border-radius: 4px; cursor: pointer;">Eliminar</button>
                            </form>
                        </div>
                    </div>
                    <?php 
                        endforeach; 
                    else:
                    ?>
                        <p style="color: #6b7280; grid-column: 1 / -1;">No hay categorías registradas en este momento.</p>
                    <?php endif; ?>
                </div>
            </section>

        <?php elseif ($id_categoria): ?>
            <!-- ========================================== -->
            <!-- VISTA DETALLE DE UNA CATEGORÍA ESPECÍFICA  -->
            <!-- ========================================== -->
            <?php 
            $categoria = $categoriaController->obtenerPorId($id_categoria);
            if ($categoria): 
            ?>
                <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); max-width: 600px; margin: 0 auto;">
                    <span style="background: #e0f2fe; color: #0369a1; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.8rem; font-weight: 600;">Categoría ID: <?= $categoria['id_categoria']; ?></span>
                    <h1 style="margin: 0.5rem 0 1rem 0; font-size: 2rem; color: #111827;"><?= htmlspecialchars($categoria['nombre']); ?></h1>
                    <p style="color: #4b5563; line-height: 1.6; margin-bottom: 2rem; font-size: 1.05rem;">
                        <strong>Descripción:</strong><br>
                        <?= !empty($categoria['descripcion']) ? nl2br(htmlspecialchars($categoria['descripcion'])) : 'Sin descripción disponible.'; ?>
                    </p>
                    
                    <div style="display: flex; gap: 1rem;">
                        <a href="categorias.php" class="btn btn-outline">Volver a Categorías</a>
                        <a href="index.php" class="btn">Ir al Catálogo General</a>
                    </div>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 3rem;">
                    <h2>Categoría no encontrada</h2>
                    <p>El registro solicitado no existe.</p>
                    <a href="categorias.php" class="btn" style="margin-top: 1rem;">Regresar</a>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- ========================================== -->
            <!-- VISTA GENERAL DE CATEGORÍAS (CATÁLOGO)     -->
            <!-- ========================================== -->
            <h1 class="section-title">Nuestras Categorías</h1>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem;">
                <?php 
                $listaCategorias = $categoriaController->listar();
                foreach($listaCategorias as $cat): 
                ?>
                <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 1.5rem; display: flex; flex-direction: column; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <h3 style="font-size: 1.25rem; color: #111827; margin-bottom: 0.5rem;"><?= htmlspecialchars($cat['nombre']); ?></h3>
                    <p style="font-size: 0.9rem; color: #4b5563; margin-bottom: 1.5rem; line-height: 1.4; flex: 1;">
                        <?= !empty($cat['descripcion']) ? htmlspecialchars($cat['descripcion']) : 'Explora productos especializados en esta categoría.'; ?>
                    </p>
                    <a href="categorias.php?id=<?= $cat['id_categoria']; ?>" class="btn btn-outline" style="text-align: center; text-decoration: none;">Ver Detalles</a>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <script src="../assets/js/app.js"></script>
</body>
</html>