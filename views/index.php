<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/ProductosController.php';
require_once __DIR__ . '/../controllers/CategoriasController.php';

$productosController = new ProductosController();
$categoriasController = new CategoriasController();

// Obtener categoría seleccionada para filtrar (si existe)
$categoria_id = isset($_GET['categoria']) ? intval($_GET['categoria']) : null;
$busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';

// Listar productos según filtros aplicados
if ($busqueda !== '') {
    $productos = $productosController.buscar($busqueda); // Asumiendo método de búsqueda o filtrado general
} elseif ($categoria_id) {
    $productos = $productosController->listarPorCategoria($categoria_id);
} else {
    $productos = $productosController->listar();
}

$categorias = $categoriasController->listar();

include_once __DIR__ . '/layouts/header.php';
?>

<!-- Seccción de Bienvenida / Hero -->
<div class="hero-section">
    <h1 class="hero-title">Herramientas y Materiales para el Constructor</h1>
    <p class="hero-subtitle">Encuentra todo lo que necesitas para tus proyectos de construcción y remodelación con la mejor calidad y precio.</p>
    
    <!-- Barra de búsqueda rápida -->
    <form action="<?= BASE_URL; ?>views/productos.php" method="GET" class="search-bar-form">
        <input type="text" name="q" placeholder="¿Qué herramienta o material buscas?" class="form-control search-input" value="<?= htmlspecialchars($busqueda); ?>">
        <button type="submit" class="btn btn-primary">Buscar</button>
    </form>
</div>

<!-- Contenedor Principal del Catálogo y Filtros -->
<div class="catalog-layout">
    
    <!-- Sidebar de Categorías -->
    <aside class="catalog-sidebar">
        <h3 class="sidebar-title">Categorías</h3>
        <ul class="category-list">
            <li>
                <a href="<?= BASE_URL; ?>views/index.php" class="category-link <?= !$categoria_id ? 'active' : ''; ?>">Todas las categorías</a>
            </li>
            <?php if (!empty($categorias)): ?>
                <?php foreach ($categorias as $cat): ?>
                    <li>
                        <a href="<?= BASE_URL; ?>views/index.php?categoria=<?= $cat['id_categoria']; ?>" class="category-link <?= $categoria_id == $cat['id_categoria'] ? 'active' : ''; ?>">
                            <?= htmlspecialchars($cat['nombre']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </aside>

    <!-- Grilla de Productos -->
    <section class="catalog-content">
        <div class="catalog-header-info">
            <h2 class="section-title">Catálogo de Productos</h2>
            <span class="product-count"><?= count($productos); ?> productos disponibles</span>
        </div>

        <?php if (!empty($productos)): ?>
            <div class="product-grid">
                <?php foreach ($productos as $prod): ?>
                    <div class="product-card">
                        <div class="product-image-container">
                            <?php if (!empty($prod['imagen'])): ?>
                                <img src="<?= BASE_URL; ?>assets/images/<?= htmlspecialchars($prod['imagen']); ?>" alt="<?= htmlspecialchars($prod['nombre']); ?>" class="product-img">
                            <?php else: ?>
                                <div class="product-img-placeholder">Sin Imagen</div>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h4 class="product-name"><?= htmlspecialchars($prod['nombre']); ?></h4>
                            <p class="product-description"><?= htmlspecialchars(substr($prod['descripcion'] ?? '', 0, 80)); ?>...</p>
                            <div class="product-meta">
                                <span class="product-price">Q.<?= number_format($prod['precio'], 2); ?></span>
                                <span class="product-stock <?= $prod['stock'] > 0 ? 'in-stock' : 'out-stock'; ?>">
                                    <?= $prod['stock'] > 0 ? 'Stock: ' . $prod['stock'] : 'Agotado'; ?>
                                </span>
                            </div>
                            
                            <!-- Botón dinámico para agregar al carrito mediante carrito.js -->
                            <?php if ($prod['stock'] > 0): ?>
                                <button type="button" 
                                        class="btn btn-dark btn-block mt-2" 
                                        onclick="agregarAlCarrito({
                                            id: <?= $prod['id_producto']; ?>,
                                            nombre: '<?= htmlspecialchars($prod['nombre'], ENT_QUOTES); ?>',
                                            precio: <?= $prod['precio']; ?>,
                                            imagen: '<?= htmlspecialchars($prod['imagen'] ?? '', ENT_QUOTES); ?>'
                                        })">
                                    Añadir al Carrito
                                </button>
                            <?php else: ?>
                                <button type="button" class="btn btn-disabled btn-block mt-2" disabled>No disponible</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <p>No se encontraron productos disponibles en esta categoría.</p>
                <a href="<?= BASE_URL; ?>views/index.php" class="btn btn-primary mt-3">Ver todos los productos</a>
            </div>
        <?php endif; ?>
    </section>

</div>

<?php 
include_once __DIR__ . '/layouts/footer.php'; 
?>