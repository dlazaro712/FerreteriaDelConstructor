<?php
// Iniciar sesión para control de acceso y carrito
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir controladores necesarios
require_once __DIR__ . '/../controllers/ProductoController.php';

$productoController = new ProductoController();

// Mapeo auxiliar de categorías para mostrar nombres legibles en caso de que la BD devuelva el ID
$categoriasNombres = [
    1 => 'Herramientas Eléctricas',
    2 => 'Accesorios de Ferretería',
    3 => 'Limpieza y Presión',
    4 => 'Construcción y Medición'
];

// Identificar si estamos viendo un producto específico o el catálogo general
$id_producto = isset($_GET['id']) ? intval($_GET['id']) : null;
$modo_admin = isset($_GET['admin']) && $_GET['admin'] === 'true'; // Vista de gestión CRUD para admin

// Manejo de peticiones POST desde formularios del panel de administración
$mensaje_alerta = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'admin') {
    $accion = $_POST['accion'] ?? '';
    
    if ($accion === 'crear') {
        if ($productoController->crearProducto($_POST)) {
            $mensaje_alerta = "<div class='alert success'>Producto creado exitosamente en la base de datos.</div>";
        } else {
            $mensaje_alerta = "<div class='alert error'>Error al crear el producto. Verifique los campos.</div>";
        }
    } elseif ($accion === 'actualizar') {
        $id_act = intval($_POST['id_producto']);
        if ($productoController->actualizarProducto($id_act, $_POST)) {
            $mensaje_alerta = "<div class='alert success'>Producto actualizado exitosamente.</div>";
        } else {
            $mensaje_alerta = "<div class='alert error'>Error al actualizar el producto.</div>";
        }
    } elseif ($accion === 'eliminar') {
        $id_del = intval($_POST['id_producto']);
        if ($productoController->eliminarProducto($id_del)) {
            $mensaje_alerta = "<div class='alert success'>Producto eliminado lógicamente (estado cambiado a 0).</div>";
        } else {
            $mensaje_alerta = "<div class='alert error'>Error al eliminar el producto.</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ferretería del Constructor - Gestión de Productos</title>
    <link rel="stylesheet" href="<?= BASE_URL; ?>assets/css/styles.css">
</head>
<body>

    <!-- Navegación Constante -->
    <header>
        <div class="nav-container container">
            <a href="../views/index.php" class="logo">Ferretería del Constructor</a>
            <nav style="display: flex; gap: 1rem; align-items: center;">
                <a href="index.php" style="color: white; text-decoration: none;">Catálogo</a>
                <a href="carrito.php" style="color: white; text-decoration: none;">Carrito (<span id="contador-carrito">0</span>)</a>
                <?php if (isset($_SESSION['usuario'])): ?>
                    <a href="productos.php?admin=true" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.85rem;">Admin CRUD</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="container" style="padding: 2rem 1rem;">
        <?= $mensaje_alerta; ?>

        <?php if ($modo_admin && isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'admin'): ?>
            <!-- ========================================== -->
            <!-- PANEL CRUD ADMINISTRATIVO (CON CARDS)      -->
            <!-- ========================================== -->
            <section>
                <h1 class="section-title">Administración de Productos</h1>
                
                <!-- Formulario Create -->
                <div class="admin-card-form" style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 2rem;">
                    <h3>Registrar Nuevo Producto (CREATE)</h3>
                    <form action="productos.php?admin=true" method="POST" style="display: grid; gap: 1rem; margin-top: 1rem;">
                        <input type="hidden" name="accion" value="crear">
                        <div class="admin-form-grid-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div>
                                <label style="font-size: 0.85rem; font-weight: bold; display: block; margin-bottom: 0.25rem;">Nombre del Producto:</label>
                                <input type="text" name="nombre" placeholder="Ej. Taladro Inalámbrico" required class="admin-input" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                            </div>
                            <div>
                                <label style="font-size: 0.85rem; font-weight: bold; display: block; margin-bottom: 0.25rem;">Categoría:</label>
                                <select name="id_categoria" required class="admin-input" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                                    <option value="">Seleccione una categoría...</option>
                                    <?php foreach ($categoriasNombres as $catId => $catNombre): ?>
                                        <option value="<?= $catId; ?>"><?= $catNombre; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label style="font-size: 0.85rem; font-weight: bold; display: block; margin-bottom: 0.25rem;">Descripción:</label>
                            <textarea name="descripcion" placeholder="Descripción detallada del producto..." required class="admin-input" rows="3" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;"></textarea>
                        </div>
                        <div class="admin-form-grid-3" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                            <div>
                                <label style="font-size: 0.85rem; font-weight: bold; display: block; margin-bottom: 0.25rem;">Precio (Q):</label>
                                <input type="number" step="0.01" name="precio" placeholder="0.00" required class="admin-input" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                            </div>
                            <div>
                                <label style="font-size: 0.85rem; font-weight: bold; display: block; margin-bottom: 0.25rem;">Cantidad (Stock):</label>
                                <input type="number" name="cantidad" placeholder="15" required class="admin-input" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                            </div>
                            <div>
                                <label style="font-size: 0.85rem; font-weight: bold; display: block; margin-bottom: 0.25rem;">Ruta Imagen:</label>
                                <input type="text" name="imagen" placeholder="/assets/imgs/taladro.jpg" value="/assets/imgs/default.jpg" required class="admin-input" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                            </div>
                        </div>
                        <button type="submit" class="btn" style="width: fit-content; margin-top: 0.5rem;">Guardar en Base de Datos</button>
                    </form>
                </div>

                <!-- Contenedor de Tarjetas (Cards) para Administrar Registros -->
                <h3 style="margin-bottom: 1rem;">Registros Actuales (Vista en Tarjetas)</h3>
                <div class="grid-productos" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem;">
                    <?php 
                    $todosAdmin = $productoController->listarProductos();
                    foreach($todosAdmin as $prod): 
                        $nombreCategoria = $prod['categoria'] ?? ($categoriasNombres[$prod['id_categoria']] ?? 'Sin Categoría');
                    ?>
                    <div class="card-producto" style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; display: flex; flex-direction: column; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <div style="position: relative;">
                            <img src="..<?= htmlspecialchars($prod['imagen']); ?>" alt="<?= htmlspecialchars($prod['nombre']); ?>" style="width: 100%; height: 160px; object-fit: cover;" onerror="this.src='../assets/imgs/default.jpg'">
                            <span style="position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.7); color: white; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.75rem;">ID: <?= $prod['id_producto']; ?></span>
                        </div>
                        <div class="card-body" style="padding: 1rem; display: flex; flex-direction: column; flex: 1;">
                            <span class="badge-cat" style="font-size: 0.75rem; color: var(--primary-dark, #0284c7); font-weight: 600; text-transform: uppercase; margin-bottom: 0.25rem; display: inline-block;"><?= htmlspecialchars($nombreCategoria); ?></span>
                            <h3 class="card-title" style="font-size: 1.1rem; margin-bottom: 0.5rem;"><?= htmlspecialchars($prod['nombre']); ?></h3>
                            <p style="font-size: 0.85rem; color: #4b5563; margin-bottom: 0.75rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;" title="<?= htmlspecialchars($prod['descripcion']); ?>">
                                <?= htmlspecialchars($prod['descripcion']); ?>
                            </p>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                <span class="card-precio" style="font-weight: bold; color: #111827; font-size: 1.05rem;">Q.<?= number_format($prod['precio'], 2); ?></span>
                                <span style="font-size: 0.85rem; color: #6b7280;">Stock: <strong><?= $prod['cantidad']; ?></strong></span>
                            </div>
                            <div style="margin-top: auto; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f3f4f6; padding-top: 0.75rem;">
                                <span style="background: #d1fae5; color: #065f46; padding: 0.15rem 0.4rem; border-radius: 4px; font-size: 0.75rem; font-weight: 500;">Estado: <?= $prod['estado']; ?></span>
                                
                                <!-- Botón Eliminar / Baja Lógica -->
                                <form action="productos.php?admin=true" method="POST" onsubmit="return confirm('¿Desea cambiar el estado de este producto a inactivo (estado=0)?');" style="margin: 0;">
                                    <input type="hidden" name="accion" value="eliminar">
                                    <input type="hidden" name="id_producto" value="<?= $prod['id_producto']; ?>">
                                    <button type="submit" class="btn" style="background-color: #ef4444; color: white; padding: 0.3rem 0.6rem; font-size: 0.8rem; border: none; border-radius: 4px; cursor: pointer;">Eliminar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>

        <?php elseif ($id_producto): ?>
            <!-- ========================================== -->
            <!-- VISTA DETALLE DE UN PRODUCTO ESPECÍFICO    -->
            <!-- ========================================== -->
            <?php 
            $producto = $productoController->obtenerProducto($id_producto);
            if ($producto): 
                $nombreCategoriaDetalle = $producto['categoria'] ?? ($categoriasNombres[$producto['id_categoria']] ?? 'Herramientas');
            ?>
                <div class="detalle-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; align-items: start; background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                    <div>
                        <img src="..<?= htmlspecialchars($producto['imagen']); ?>" alt="<?= htmlspecialchars($producto['nombre']); ?>" class="detalle-img" style="width: 100%; border-radius: 8px; object-fit: cover;" onerror="this.src='../assets/imgs/default.jpg'">
                    </div>
                    <div class="detalle-info">
                        <span class="badge-cat" style="font-size: 0.8rem; color: var(--primary-dark, #0284c7); font-weight: 600; text-transform: uppercase;"><?= htmlspecialchars($nombreCategoriaDetalle); ?></span>
                        <h1 style="margin: 0.5rem 0 1rem 0; font-size: 2rem;"><?= htmlspecialchars($producto['nombre']); ?></h1>
                        <p class="detalle-precio" style="font-size: 1.5rem; font-weight: bold; color: #111827; margin-bottom: 1rem;">Q.<?= number_format($producto['precio'], 2); ?></p>
                        <p class="detalle-descripcion" style="color: #4b5563; line-height: 1.6; margin-bottom: 1.5rem;"><?= nl2br(htmlspecialchars($producto['descripcion'])); ?></p>
                        <p style="margin-bottom: 1.5rem; font-weight: 500;">Disponibles en stock: <strong><?= $producto['cantidad']; ?> unidades</strong></p>
                        
                        <div style="display: flex; gap: 1rem;">
                            <button class="btn" onclick="agregarAlCarrito(<?= $producto['id_producto']; ?>, '<?= htmlspecialchars($producto['nombre'], ENT_QUOTES); ?>', <?= $producto['precio']; ?>)">Agregar al Carrito</button>
                            <a href="index.php" class="btn btn-outline">Volver al Catálogo</a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 3rem;">
                    <h2>Producto no encontrado</h2>
                    <p>El registro solicitado no existe o fue desactivado.</p>
                    <a href="index.php" class="btn" style="margin-top: 1rem;">Regresar</a>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- ========================================== -->
            <!-- VISTA DE CATÁLOGO GENERAL                  -->
            <!-- ========================================== -->
            <h1 class="section-title">Catálogo General de Herramientas</h1>
            <div class="grid-productos" id="contenedor-productos">
                <?php 
                $lista = $productoController->listarProductos();
                foreach($lista as $prod): 
                    $nombreCategoriaCard = $prod['categoria'] ?? ($categoriasNombres[$prod['id_categoria']] ?? 'Herramientas');
                ?>
                <div class="card-producto">
                    <img src="..<?= htmlspecialchars($prod['imagen']); ?>" alt="<?= htmlspecialchars($prod['nombre']); ?>" class="card-img" onerror="this.src='../assets/imgs/default.jpg'">
                    <div class="card-body">
                        <span class="badge-cat"><?= htmlspecialchars($nombreCategoriaCard); ?></span>
                        <h3 class="card-title"><?= htmlspecialchars($prod['nombre']); ?></h3>
                        <p class="card-precio">Q.<?= number_format($prod['precio'], 2); ?></p>
                        <p style="font-size: 0.85rem; color: var(--gray);">Stock: <?= $prod['cantidad']; ?></p>
                        <div style="display: flex; gap: 0.5rem; margin-top: auto;">
                            <a href="productos.php?id=<?= $prod['id_producto']; ?>" class="btn btn-outline" style="flex: 1; text-align: center;">Detalles</a>
                            <button class="btn" onclick="agregarAlCarrito(<?= $prod['id_producto']; ?>, '<?= htmlspecialchars($prod['nombre'], ENT_QUOTES); ?>', <?= $prod['precio']; ?>)" style="flex: 1;">Comprar</button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <script src="../assets/js/app.js"></script>
</body>
</html>