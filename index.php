<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/controllers/ProductosController.php';
require_once __DIR__ . '/controllers/CategoriasController.php';

$productosController = new ProductosController();
$categoriasController = new CategoriasController();

// Obtener categoría seleccionada y término de búsqueda (si existen)
$categoria_id = isset($_GET['categoria']) ? intval($_GET['categoria']) : null;
$busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';

// Obtener la lista completa de productos de forma segura usando el controlador
$todosLosProductos = $productosController->listarProductos();
$todosLosProductos = is_array($todosLosProductos) ? $todosLosProductos : [];

$productos = [];

// Aplicar lógica de filtrado según búsqueda o categoría
if ($busqueda !== '') {
    // Filtrar por texto de búsqueda (coincidencia en nombre o descripción)
    $productos = array_filter($todosLosProductos, function($p) use ($busqueda) {
        $nombre = $p['nombre'] ?? '';
        $descripcion = $p['descripcion'] ?? '';
        return (stripos($nombre, $busqueda) !== false || stripos($descripcion, $busqueda) !== false);
    });
    $productos = array_values($productos); // Reindexar array
} elseif ($categoria_id) {
    // Filtrar por categoría seleccionada
    $productos = array_filter($todosLosProductos, function($p) use ($categoria_id) {
        return isset($p['id_categoria']) && intval($p['id_categoria']) === intval($categoria_id);
    });
    $productos = array_values($productos); // Reindexar array
} else {
    // Mostrar todos si no hay filtros
    $productos = $todosLosProductos;
}

// Obtener categorías de forma segura para el menú lateral
$listaCategorias = $categoriasController->listar();
$categorias = is_array($listaCategorias) ? $listaCategorias : [];

include_once __DIR__ . '/views/layouts/header.php';
?>

<!-- Sección de Bienvenida / Hero -->
<div class="hero-section" style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem;">
    <h1 class="hero-title" style="color: #0f172a; margin: 0 0 0.5rem 0; font-size: 1.75rem;">Herramientas y Materiales para el Constructor</h1>
    <p class="hero-subtitle" style="color: #64748b; margin: 0 0 1.5rem 0;">Encuentra todo lo que necesitas para tus proyectos de construcción y remodelación con la mejor calidad y precio. <a href="<?= BASE_URL; ?>views/usuarios.php?action=login" style="color: #2563eb; text-decoration: none; font-weight: 500;">Inicia sesión</a> si deseas guardar artículos en tu lista de deseos.</p>
    
    <!-- Barra de búsqueda rápida -->
    <form action="<?= BASE_URL; ?>index.php" method="GET" class="search-bar-form" style="display: flex; gap: 0.5rem;">
        <input type="text" name="q" placeholder="¿Qué herramienta o material buscas?" class="form-control search-input" value="<?= htmlspecialchars($busqueda); ?>" style="flex: 1; padding: 0.65rem; border: 1px solid #d1d5db; border-radius: 6px;">
        <button type="submit" class="btn btn-primary" style="padding: 0.65rem 1.25rem; background: #2563eb; color: white; border: none; border-radius: 6px; cursor: pointer;">Buscar</button>
    </form>
</div>

<!-- Contenedor Principal del Catálogo y Filtros -->
<div class="catalog-layout" style="display: flex; gap: 2rem; align-items: flex-start;">
    
    <!-- Sidebar de Categorías -->
    <aside class="catalog-sidebar" style="width: 260px; background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); flex-shrink: 0;">
        <h3 class="sidebar-title" style="font-size: 1.1rem; color: #0f172a; margin-top: 0; margin-bottom: 1rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.5rem;">Categorías</h3>
        <ul class="category-list" style="list-style: none; padding: 0; margin: 0;">
            <li style="margin-bottom: 0.5rem;">
                <a href="<?= BASE_URL; ?>index.php" class="category-link <?= !$categoria_id && $busqueda === '' ? 'active' : ''; ?>" style="display: block; padding: 0.5rem; color: <?= !$categoria_id && $busqueda === '' ? '#2563eb' : '#475569'; ?>; text-decoration: none; font-weight: <?= !$categoria_id && $busqueda === '' ? 'bold' : 'normal'; ?>;">Todas las categorías</a>
            </li>
            <?php if (!empty($categorias)): ?>
                <?php foreach ($categorias as $cat): ?>
                    <li style="margin-bottom: 0.5rem;">
                        <a href="<?= BASE_URL; ?>index.php?categoria=<?= $cat['id_categoria']; ?>" class="category-link <?= $categoria_id == $cat['id_categoria'] ? 'active' : ''; ?>" style="display: block; padding: 0.5rem; color: <?= $categoria_id == $cat['id_categoria'] ? '#2563eb' : '#475569'; ?>; text-decoration: none; font-weight: <?= $categoria_id == $cat['id_categoria'] ? 'bold' : 'normal'; ?>;">
                            <?= htmlspecialchars($cat['descripcion']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </aside>

    <!-- Grilla de Productos -->
    <section class="catalog-content" style="flex: 1;">
        <div class="catalog-header-info" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 class="section-title" style="margin: 0; font-size: 1.25rem; color: #0f172a;">
                <?= $busqueda !== '' ? 'Resultados de búsqueda: "' . htmlspecialchars($busqueda) . '"' : 'Catálogo de Productos'; ?>
            </h2>
            <span class="product-count" style="color: #64748b; font-size: 0.9rem;"><?= count($productos); ?> productos disponibles</span>
        </div>

        <?php if (!empty($productos)): ?>
            <div class="product-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
                <?php foreach ($productos as $prod): ?>
                    <div class="product-card" style="background: white; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; display: flex; flex-direction: column; justify-content: space-between; padding: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <div class="product-image-container" style="width: 100%; height: 180px; background: #f8fafc; display: flex; align-items: center; justify-content: center; border-radius: 6px; overflow: hidden; margin-bottom: 1rem;">
                            <?php if (!empty($prod['imagen'])): ?>
                                <img src="<?= BASE_URL; ?>assets/imgs/<?= htmlspecialchars($prod['imagen']); ?>" alt="<?= htmlspecialchars($prod['nombre']); ?>" class="product-img" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                            <?php else: ?>
                                <div class="product-img-placeholder" style="color: #94a3b8; font-size: 0.85rem;">Sin Imagen</div>
                            <?php endif; ?>
                        </div>
                        <div class="product-info" style="display: flex; flex-direction: column; flex-grow: 1; justify-content: space-between;">
                            <div>
                                <h4 class="product-name" style="font-size: 1rem; font-weight: 600; color: #0f172a; margin: 0 0 0.5rem 0;"><?= htmlspecialchars($prod['nombre']); ?></h4>
                                <p class="product-description" style="font-size: 0.85rem; color: #64748b; margin: 0 0 1rem 0;"><?= htmlspecialchars(substr($prod['descripcion'] ?? '', 0, 80)); ?>...</p>
                                <div class="product-meta" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                    <span class="product-price" style="font-size: 1.1rem; font-weight: bold; color: #2563eb;">Q.<?= number_format($prod['precio'], 2); ?></span>
                                    <span class="product-stock <?= $prod['cantidad'] > 0 ? 'in-stock' : 'out-stock'; ?>" style="font-size: 0.75rem; font-weight: 500; color: <?= $prod['cantidad'] > 0 ? '#059669' : '#dc2626'; ?>;">
                                        <?= $prod['cantidad'] > 0 ? 'Stock: ' . $prod['cantidad'] : 'Agotado'; ?>
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Botón dinámico para agregar al carrito mediante carrito.js -->
                            <div style="margin-top: auto;">
                                <?php if ($prod['cantidad'] > 0): ?>
                                    <button type="button" 
                                            class="btn btn-dark btn-block mt-2" 
                                            style="width: 100%; padding: 0.6rem; background: #0f172a; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 500; font-size: 0.9rem; transition: background 0.2s;"
                                            onclick="agregarAlCarrito({
                                                id: <?= $prod['id_producto']; ?>,
                                                nombre: '<?= htmlspecialchars($prod['nombre'], ENT_QUOTES); ?>',
                                                precio: <?= $prod['precio']; ?>,
                                                imagen: '<?= htmlspecialchars($prod['imagen'] ?? '', ENT_QUOTES); ?>'
                                            })"
                                            onmouseover="this.style.background='#1e293b'" 
                                            onmouseout="this.style.background='#0f172a'">
                                        🛒 Añadir al Carrito
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-disabled btn-block mt-2" style="width: 100%; padding: 0.6rem; background: #e2e8f0; color: #94a3b8; border: none; border-radius: 6px; cursor: not-allowed; font-size: 0.9rem;" disabled>No disponible</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state" style="text-align: center; padding: 3rem; background: white; border-radius: 8px;">
                <p style="color: #64748b; margin-bottom: 1rem;">No se encontraron productos disponibles para tu búsqueda o categoría seleccionada.</p>
                <a href="<?= BASE_URL; ?>index.php" class="btn btn-primary mt-3" style="display: inline-block; padding: 0.5rem 1rem; background: #2563eb; color: white; border-radius: 6px; text-decoration: none;">Ver todos los productos</a>
            </div>
        <?php endif; ?>
    </section>

</div>

<!-- Conexión con app.js general para el carrito -->
<script src="<?= BASE_URL; ?>assets/js/app.js"></script>

<?php 
include_once __DIR__ . '/views/layouts/footer.php'; 
?>