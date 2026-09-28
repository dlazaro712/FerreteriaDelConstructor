<?php
// views/clientes/index.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validación de seguridad: solo clientes o usuarios autenticados
if (!isset($_SESSION['tipo_usuario'])) {
    header("Location: " . BASE_URL . "views/usuarios.php?action=login");
    exit();
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../controllers/ProductosController.php';

// Incluir el controlador de la Lista de Deseos para procesar el guardado
require_once __DIR__ . '/../../controllers/ListaDeseosController.php';

$productosController = new ProductosController();
$listaDeseosController = new ListaDeseosController();

// Obtener el ID del usuario actual desde la sesión
$id_usuario_actual = $_SESSION['id_usuario'] ?? $_SESSION['user_id'] ?? null;

// Procesar la acción de guardar en deseos si se recibe por URL (método POST o GET de soporte)
if (isset($_GET['action']) && $_GET['action'] === 'agregar_deseo' && isset($_GET['id_producto'])) {
    $id_producto_deseo = intval($_GET['id_producto']);
    if ($id_usuario_actual && method_exists($listaDeseosController, 'agregar')) {
        try {
            $listaDeseosController->agregarProducto($id_usuario_actual,$id_producto_deseo);
        } catch (Exception $e) {
            // Manejo silencioso o registro de excepción si falla
        }
    }
    header("Location: " . BASE_URL . "views/clientes/ListaDeseos.php");
    exit();
}

// Obtener categorías y productos usando el controlador
try {
    $database = new Database();
    $db =$database->getConnection();
    
    // Obtener categorías ordenadas alfabéticamente
    $stmtCat =$db->query("SELECT id_categoria, descripcion FROM categorias ORDER BY descripcion ASC");
    $categorias =$stmtCat->fetchAll(PDO::FETCH_ASSOC);

    // Obtener la lista completa de productos mediante el controlador
    $productos =$productosController->listarProductos() ?? [];
} catch (Exception $e) {
    $categorias = [];$productos = [];
}

// Incluir el encabezado centralizado
include_once __DIR__ . '/../layouts/header.php';
?>

<div class="cliente-dashboard" style="max-width: 1200px; margin: 0 auto; padding: 1rem;">
    
    <!-- Sección de Bienvenida -->
    <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem;">
        <h1 style="color: #0f172a; margin: 0 0 0.5rem 0; font-size: 1.75rem;">¡Bienvenido, <?= htmlspecialchars($_SESSION['usuario'] ?? 'Cliente'); ?>!</h1>
        <p style="color: #64748b; margin: 0;">Explora nuestro catálogo de herramientas y materiales, busca lo que necesitas, agrégalo al carrito o guárdalo en tu lista de deseos.</p>
    </div>

    <!-- Barra de Búsqueda y Filtros -->
    <div style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem; display: flex; flex-wrap: wrap; gap: 1rem; align-items: center; justify-content: space-between;">
        
        <!-- Input de Búsqueda -->
        <div style="flex: 1; min-width: 250px;">
            <label for="busqueda" style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.3rem;">Buscar producto:</label>
            <input type="text" id="busqueda" placeholder="Ej. Taladro, martillo, cemento..." style="width: 100%; padding: 0.65rem; border: 1px solid #d1d5db; border-radius: 6px;">
        </div>

        <!-- Filtro por Categoría -->
        <div style="min-width: 200px;">
            <label for="filtro-categoria" style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.3rem;">Categoría:</label>
            <select id="filtro-categoria" style="width: 100%; padding: 0.65rem; border: 1px solid #d1d5db; border-radius: 6px; background: white;">
                <option value="">Todas las categorías</option>
                <?php foreach ($categorias as$cat): ?>
                    <option value="<?= $cat['id_categoria']; ?>"><?= htmlspecialchars($cat['descripcion']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Filtro por Precio Máximo -->
        <div style="min-width: 220px;">
            <label for="filtro-precio" style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.3rem;">Precio Máximo: <span id="precio-max-val" style="color: #2563eb; font-weight: bold;">Q.2200</span></label>
            <input type="range" id="filtro-precio" min="0" max="2200" step="10" value="2200" style="width: 100%; cursor: pointer;">
        </div>

    </div>

    <!-- Grilla de Productos -->
    <section class="catalog-content">
        <div class="catalog-header-info" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 class="section-title" style="margin: 0; font-size: 1.25rem; color: #0f172a;">Catálogo de Productos</h2>
            <span class="product-count" id="product-count" style="color: #64748b; font-size: 0.9rem;"><?= count($productos); ?> productos disponibles</span>
        </div>

        <?php if (!empty($productos)): ?>
            <div class="product-grid" id="product-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
                <?php foreach ($productos as$prod): ?>
                    <div class="product-card" 
                         data-nombre="<?= htmlspecialchars(strtolower($prod['nombre'] ?? '')); ?>"
                         data-categoria="<?= htmlspecialchars($prod['id_categoria'] ?? ''); ?>"
                         data-precio="<?= floatval($prod['precio'] ?? 0); ?>"
                         style="background: white; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; display: flex; flex-direction: column; justify-content: space-between; padding: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        
                        <!-- Contenedor de Imagen -->
                        <div class="product-image-container" style="width: 100%; height: 180px; background: #f8fafc; display: flex; align-items: center; justify-content: center; border-radius: 6px; overflow: hidden; margin-bottom: 1rem;">
                            <?php if (!empty($prod['imagen'])): ?>
                                <img src="<?= BASE_URL; ?>assets/imgs/<?= htmlspecialchars($prod['imagen']); ?>" alt="<?= htmlspecialchars($prod['nombre']); ?>" class="product-img" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                            <?php else: ?>
                                <div class="product-img-placeholder" style="color: #94a3b8; font-size: 0.85rem;">Sin Imagen</div>
                            <?php endif; ?>
                        </div>

                        <!-- Información del Producto -->
                        <div class="product-info" style="display: flex; flex-direction: column; flex-grow: 1; justify-content: space-between;">
                            <div>
                                <h4 class="product-name" style="font-size: 1rem; font-weight: 600; color: #0f172a; margin: 0 0 0.5rem 0;"><?= htmlspecialchars($prod['nombre']); ?></h4>
                                <p class="product-description" style="font-size: 0.85rem; color: #64748b; margin: 0 0 1rem 0;"><?= htmlspecialchars(substr($prod['descripcion'] ?? '', 0, 80)); ?>...</p>
                                <div class="product-meta" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                    <span class="product-price" style="font-size: 1.1rem; font-weight: bold; color: #2563eb;">Q.<?= number_format($prod['precio'], 2); ?></span>
                                    <span class="product-stock <?= $prod['cantidad'] > 0 ? 'in-stock' : 'out-stock'; ?>" style="font-size: 0.75rem; font-weight: 500; color: <?= $prod['cantidad'] > 0 ? '#059669' : '#dc2626'; ?>;">
                                        <?= $prod['cantidad'] > 0 ? 'Stock: ' .$prod['cantidad'] : 'Agotado'; ?>
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Botones de Acción (Carrito y Wishlist) -->
                            <div style="display: flex; flex-direction: column; gap: 0.5rem; margin-top: auto;">
                                <?php if ($prod['cantidad'] > 0): ?>
                                    <button type="button" 
                                            class="btn btn-dark" 
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
                                    <button type="button" class="btn btn-disabled" style="width: 100%; padding: 0.6rem; background: #e2e8f0; color: #94a3b8; border: none; border-radius: 6px; cursor: not-allowed; font-size: 0.9rem;" disabled>No disponible</button>
                                <?php endif; ?>

                                <!-- Botón de Guardar en Deseos conectado con el controlador -->
                                <button type="button" 
                                        class="btn btn-outline" 
                                        style="display: block; width: 100%; text-align: center; padding: 0.5rem; background: white; color: #475569; border: 1px solid #cbd5e1; border-radius: 6px; cursor: pointer; font-weight: 500; font-size: 0.85rem; transition: background 0.2s; box-sizing: border-box;"
                                        onclick="guardarEnWishlist({
                                            id: <?= $prod['id_producto']; ?>,
                                            nombre: '<?= htmlspecialchars($prod['nombre'], ENT_QUOTES); ?>',
                                            precio: <?= $prod['precio']; ?>,
                                            imagen: '<?= htmlspecialchars($prod['imagen'] ?? '', ENT_QUOTES); ?>'
                                        }); window.location.href='<?= BASE_URL; ?>views/clientes/index.php?action=agregar_deseo&id_producto=<?= $prod['id_producto']; ?>';"
                                        onmouseover="this.style.background='#f1f5f9'" 
                                        onmouseout="this.style.background='white'">
                                    ❤️ Guardar en Deseos
                                </button>
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Estado vacío dinámico por si ningún producto coincide -->
            <div id="empty-filter-state" style="display: none; text-align: center; padding: 3rem; background: white; border-radius: 8px; margin-bottom: 3rem;">
                <p style="color: #64748b; margin-bottom: 1rem;">No se encontraron productos que coincidan con los filtros seleccionados.</p>
                <button type="button" id="reset-filters" class="btn btn-primary" style="padding: 0.5rem 1rem; background: #2563eb; color: white; border: none; border-radius: 6px; cursor: pointer;">Restablecer filtros</button>
            </div>

        <?php else: ?>
            <div class="empty-state" style="text-align: center; padding: 3rem; background: white; border-radius: 8px;">
                <p style="color: #64748b; margin-bottom: 1rem;">No hay productos disponibles en el sistema actualmente.</p>
            </div>
        <?php endif; ?>
    </section>

</div>

<!-- Script de filtrado en tiempo real automático -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    const inputBusqueda = document.getElementById('busqueda');
    const selectCategoria = document.getElementById('filtro-categoria');
    const rangePrecio = document.getElementById('filtro-precio');
    const spanPrecioVal = document.getElementById('precio-max-val');
    const productCards = document.querySelectorAll('.product-card');
    const productCount = document.getElementById('product-count');
    const emptyFilterState = document.getElementById('empty-filter-state');
    const btnReset = document.getElementById('reset-filters');

    function filtrarProductos() {
        const textoBusqueda = inputBusqueda.value.toLowerCase().trim();
        const categoriaSeleccionada = selectCategoria.value;
        const precioMaximo = parseFloat(rangePrecio.value) || 2200;

        spanPrecioVal.textContent = 'Q.' + precioMaximo;

        let visibles = 0;

        productCards.forEach(card => {
            const nombre = card.getAttribute('data-nombre');
            const categoria = card.getAttribute('data-categoria');
            const precio = parseFloat(card.getAttribute('data-precio'));

            const coincideTexto = nombre.includes(textoBusqueda);
            const coincideCategoria = (categoriaSeleccionada === "" || categoria === categoriaSeleccionada);
            const coincidePrecio = (precio <= precioMaximo);

            if (coincideTexto && coincideCategoria && coincidePrecio) {
                card.style.display = 'flex';
                visibles++;
            } else {
                card.style.display = 'none';
            }
        });

        if (productCount) {
            productCount.textContent = `${visibles} productos disponibles`;
        }

        if (emptyFilterState) {
            if (visibles === 0) {
                emptyFilterState.style.display = 'block';
            } else {
                emptyFilterState.style.display = 'none';
            }
        }
    }

    if (inputBusqueda) {
        inputBusqueda.addEventListener('input', filtrarProductos);
    }
    if (selectCategoria) {
        selectCategoria.addEventListener('change', filtrarProductos);
    }
    if (rangePrecio) {
        rangePrecio.addEventListener('input', filtrarProductos);
    }

    if (btnReset) {
        btnReset.addEventListener('click', function() {
            inputBusqueda.value = '';
            selectCategoria.value = '';
            rangePrecio.value = 2200;
            filtrarProductos();
        });
    }
});
</script>

<!-- Conexión con app.js general para el carrito y wishlist -->
<script src="<?= BASE_URL; ?>assets/js/app.js"></script>

<?php 
// Incluir el pie de página centralizado
include_once __DIR__ . '/../layouts/footer.php'; 
?>