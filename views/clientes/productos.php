<?php
// Iniciar sesión para control de acceso y carrito
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir configuración de base de datos, modelo y controlador necesarios
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../controllers/ProductosController.php';

$productosController = new ProductosController();

// Mapeo auxiliar de categorías para mostrar nombres legibles
$categoriasNombres = [
    1 => 'Herramientas Eléctricas',
    2 => 'Accesorios de Ferretería',
    3 => 'Limpieza y Presión',
    4 => 'Construcción y Medición'
];

$id_producto = isset($_GET['id']) ? intval($_GET['id']) : null;
$modo_admin = isset($_GET['admin']) && $_GET['admin'] === 'true';

// Parámetros de búsqueda y filtros del lado del cliente
$busqueda = trim($_GET['q'] ?? '');
$filtro_categoria = isset($_GET['categoria']) && $_GET['categoria'] !== '' ? intval($_GET['categoria']) : null;
$precio_min = isset($_GET['precio_min']) && $_GET['precio_min'] !== '' ? floatval($_GET['precio_min']) : null;
$precio_max = isset($_GET['precio_max']) && $_GET['precio_max'] !== '' ? floatval($_GET['precio_max']) : null;
$orden = $_GET['orden'] ?? ''; // popularidad, precio_asc, precio_desc
$disponibilidad = $_GET['disponibilidad'] ?? ''; // stock

$mensaje_alerta = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'admin') {
    $accion = $_POST['accion'] ?? '';
    
    if ($accion === 'crear') {
        if ($productosController->crearProducto($_POST)) {
            $mensaje_alerta = "<div class='alert success'>Producto creado exitosamente en la base de datos.</div>";
        } else {
            $mensaje_alerta = "<div class='alert error'>Error al crear el producto. Verifique los campos.</div>";
        }
    } elseif ($accion === 'actualizar') {
        $id_act = intval($_POST['id_producto']);
        if ($productosController->actualizarProducto($id_act, $_POST)) {
            $mensaje_alerta = "<div class='alert success'>Producto actualizado exitosamente.</div>";
        } else {
            $mensaje_alerta = "<div class='alert error'>Error al actualizar el producto.</div>";
        }
    } elseif ($accion === 'eliminar') {
        $id_del = intval($_POST['id_producto']);
        if ($productosController->eliminarProducto($id_del)) {
            $mensaje_alerta = "<div class='alert success'>Producto cambiado a inactivo (estado=0).</div>";
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
    <title>Ferretería del Constructor - Gestión y Catálogo de Productos</title>
    <link rel="stylesheet" href="<?= BASE_URL; ?>assets/css/styles.css">
</head>
<body>

    <header>
        <div class="nav-container container">
            <a href="../views/index.php" class="logo">Ferretería del Constructor</a>
            <nav style="display: flex; gap: 1rem; align-items: center;">
                <a href="productos.php" style="color: white; text-decoration: none;">Catálogo</a>
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
            <section>
                <h1 class="section-title">Administración de Productos</h1>
                
                <div class="admin-card-form" style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 2rem;">
                    <h3>Registrar Nuevo Producto (CREATE)</h3>
                    <form action="productos.php?admin=true" method="POST" style="display: grid; gap: 1rem; margin-top: 1rem;">
                        <input type="hidden" name="accion" value="crear">
                        <div class="admin-form-grid-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div>
                                <label style="font-size: 0.85rem; font-weight: bold; display: block; margin-bottom: 0.25rem;">Nombre:</label>
                                <input type="text" name="nombre" placeholder="Ej. Taladro Inalámbrico" required style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                            </div>
                            <div>
                                <label style="font-size: 0.85rem; font-weight: bold; display: block; margin-bottom: 0.25rem;">Categoría:</label>
                                <select name="id_categoria" required style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                                    <option value="">Seleccione...</option>
                                    <?php foreach ($categoriasNombres as $catId => $catNombre): ?>
                                        <option value="<?= $catId; ?>"><?= $catNombre; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label style="font-size: 0.85rem; font-weight: bold; display: block; margin-bottom: 0.25rem;">Descripción:</label>
                            <textarea name="descripcion" placeholder="Descripción..." required rows="3" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;"></textarea>
                        </div>
                        <div class="admin-form-grid-3" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                            <div>
                                <label style="font-size: 0.85rem; font-weight: bold; display: block; margin-bottom: 0.25rem;">Precio (Q):</label>
                                <input type="number" step="0.01" name="precio" placeholder="0.00" required style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                            </div>
                            <div>
                                <label style="font-size: 0.85rem; font-weight: bold; display: block; margin-bottom: 0.25rem;">Stock:</label>
                                <input type="number" name="cantidad" placeholder="15" required style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                            </div>
                            <div>
                                <label style="font-size: 0.85rem; font-weight: bold; display: block; margin-bottom: 0.25rem;">Ruta Imagen:</label>
                                <input type="text" name="imagen" value="/assets/imgs/default.jpg" required style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                            </div>
                        </div>
                        <button type="submit" class="btn" style="width: fit-content; margin-top: 0.5rem;">Guardar en BD</button>
                    </form>
                </div>

                <h3 style="margin-bottom: 1rem;">Registros Actuales</h3>
                <div class="grid-productos" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem;">
                    <?php 
                    $todosAdmin = $productosController->listarProductos();
                    foreach($todosAdmin as $prod): 
                        $nombreCategoria = $categoriasNombres[$prod['id_categoria']] ?? 'Sin Categoría';
                    ?>
                    <div class="card-producto" style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; display: flex; flex-direction: column;">
                        <img src="<?= BASE_URL . ltrim($prod['imagen'], '/'); ?>" alt="<?= htmlspecialchars($prod['nombre']); ?>" style="width: 100%; height: 160px; object-fit: cover;" onerror="this.src='<?= BASE_URL; ?>assets/imgs/Taladro-Atornillador-Inalambrico.png'">
                        <div class="card-body" style="padding: 1rem; display: flex; flex-direction: column; flex: 1;">
                            <span style="font-size: 0.75rem; color: #0284c7; font-weight: 600; text-transform: uppercase;"><?= htmlspecialchars($nombreCategoria); ?></span>
                            <h3 style="font-size: 1.1rem; margin-bottom: 0.5rem;"><?= htmlspecialchars($prod['nombre']); ?></h3>
                            <p style="font-size: 0.85rem; color: #4b5563; margin-bottom: 0.75rem;"><?= htmlspecialchars($prod['descripcion']); ?></p>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                <span style="font-weight: bold; font-size: 1.05rem;">Q.<?= number_format($prod['precio'], 2); ?></span>
                                <span style="font-size: 0.85rem; color: #6b7280;">Stock: <strong><?= $prod['cantidad']; ?></strong></span>
                            </div>
                            <div style="margin-top: auto; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f3f4f6; padding-top: 0.75rem;">
                                <span style="background: #d1fae5; color: #065f46; padding: 0.15rem 0.4rem; border-radius: 4px; font-size: 0.75rem;">Estado: <?= $prod['estado']; ?></span>
                                <form action="productos.php?admin=true" method="POST" onsubmit="return confirm('¿Desactivar producto?');" style="margin: 0;">
                                    <input type="hidden" name="accion" value="eliminar">
                                    <input type="hidden" name="id_producto" value="<?= $prod['id_producto']; ?>">
                                    <button type="submit" style="background-color: #ef4444; color: white; padding: 0.3rem 0.6rem; font-size: 0.8rem; border: none; border-radius: 4px; cursor: pointer;">Eliminar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>

        <?php elseif ($id_producto): ?>
            <?php 
            $producto = $productosController->obtenerProducto($id_producto);
            if ($producto): 
                $nombreCategoriaDetalle = $categoriasNombres[$producto['id_categoria']] ?? 'Herramientas';
            ?>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; align-items: start; background: white; padding: 2rem; border-radius: 8px;">
                    <img src="<?= BASE_URL . ltrim($producto['imagen'], '/'); ?>" alt="<?= htmlspecialchars($producto['nombre']); ?>" style="width: 100%; border-radius: 8px; object-fit: cover;">
                    <div>
                        <span style="font-size: 0.8rem; color: #0284c7; font-weight: 600; text-transform: uppercase;"><?= htmlspecialchars($nombreCategoriaDetalle); ?></span>
                        <h1 style="margin: 0.5rem 0 1rem 0; font-size: 2rem;"><?= htmlspecialchars($producto['nombre']); ?></h1>
                        <p style="font-size: 1.5rem; font-weight: bold; margin-bottom: 1rem;">Q.<?= number_format($producto['precio'], 2); ?></p>
                        <p style="color: #4b5563; line-height: 1.6; margin-bottom: 1.5rem;"><?= nl2br(htmlspecialchars($producto['descripcion'])); ?></p>
                        <p style="margin-bottom: 1.5rem;">Disponibles: <strong><?= $producto['cantidad']; ?> unidades</strong></p>
                        <div style="display: flex; gap: 1rem;">
                            <button class="btn" onclick="agregarAlCarrito(<?= $producto['id_producto']; ?>, '<?= htmlspecialchars($producto['nombre'], ENT_QUOTES); ?>', <?= $producto['precio']; ?>)">Agregar al Carrito</button>
                            <a href="productos.php" class="btn btn-outline">Volver</a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 3rem;">
                    <h2>Producto no encontrado</h2>
                    <a href="productos.php" class="btn" style="margin-top: 1rem;">Regresar</a>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- ========================================== -->
            <!-- MODO CLIENTE: BÚSQUEDA Y FILTROS             -->
            <!-- ========================================== -->
            <h1 class="section-title">Catálogo General de Herramientas</h1>

            <div style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 2rem;">
                <form method="GET" action="productos.php" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
                    
                    <!-- Campo de Búsqueda -->
                    <div>
                        <label style="font-size: 0.85rem; font-weight: bold; display: block; margin-bottom: 0.25rem;">Buscar producto:</label>
                        <input type="text" name="q" value="<?= htmlspecialchars($busqueda); ?>" placeholder="Nombre o descripción..." style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                    </div>

                    <!-- Filtro por Categoría -->
                    <div>
                        <label style="font-size: 0.85rem; font-weight: bold; display: block; margin-bottom: 0.25rem;">Categoría:</label>
                        <select name="categoria" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                            <option value="">Todas las categorías</option>
                            <?php foreach ($categoriasNombres as $catId => $catNombre): ?>
                                <option value="<?= $catId; ?>" <?= $filtro_categoria === $catId ? 'selected' : ''; ?>><?= $catNombre; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Rango de Precios -->
                    <div>
                        <label style="font-size: 0.85rem; font-weight: bold; display: block; margin-bottom: 0.25rem;">Precio Mín (Q):</label>
                        <input type="number" step="0.01" name="precio_min" value="<?= $precio_min !== null ? $precio_min : ''; ?>" placeholder="0.00" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                    <div>
                        <label style="font-size: 0.85rem; font-weight: bold; display: block; margin-bottom: 0.25rem;">Precio Máx (Q):</label>
                        <input type="number" step="0.01" name="precio_max" value="<?= $precio_max !== null ? $precio_max : ''; ?>" placeholder="1000.00" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                    </div>

                    <!-- Popularidad y Orden -->
                    <div>
                        <label style="font-size: 0.85rem; font-weight: bold; display: block; margin-bottom: 0.25rem;">Ordenar por:</label>
                        <select name="orden" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                            <option value="">Recientes / Por defecto</option>
                            <option value="popularidad" <?= $orden === 'popularidad' ? 'selected' : ''; ?>>Mayor Popularidad (Stock)</option>
                            <option value="precio_asc" <?= $orden === 'precio_asc' ? 'selected' : ''; ?>>Precio: Menor a Mayor</option>
                            <option value="precio_desc" <?= $orden === 'precio_desc' ? 'selected' : ''; ?>>Precio: Mayor a Menor</option>
                        </select>
                    </div>

                    <!-- Disponibilidad -->
                    <div>
                        <label style="font-size: 0.85rem; font-weight: bold; display: block; margin-bottom: 0.25rem;">Disponibilidad:</label>
                        <select name="disponibilidad" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                            <option value="">Todos</option>
                            <option value="con_stock" <?= $disponibilidad === 'con_stock' ? 'selected' : ''; ?>>En Stock (> 0)</option>
                        </select>
                    </div>

                    <!-- Botones de Acción -->
                    <div style="display: flex; gap: 0.5rem;">
                        <button type="submit" class="btn" style="flex: 1; padding: 0.5rem;">Filtrar</button>
                        <a href="productos.php" class="btn btn-outline" style="padding: 0.5rem; text-align: center;">Limpiar</a>
                    </div>
                </form>
            </div>

            <!-- Listado de Productos Filtrados -->
            <div class="grid-productos" id="contenedor-productos">
                <?php 
                $lista = $productosController->listarProductos();

                // Aplicar filtros lógicos en PHP sobre la lista obtenida
                $listaFiltrada = array_filter($lista, function($prod) use ($busqueda, $filtro_categoria, $precio_min, $precio_max, $disponibilidad) {
                    // Omitir inactivos
                    if (($prod['estado'] ?? 1) == 0) return false;

                    // Filtro de búsqueda por texto (nombre o descripción)
                    if (!empty($busqueda)) {
                        $textoBusqueda = strtolower($busqueda);
                        $nombreProd = strtolower($prod['nombre'] ?? '');
                        $descProd = strtolower($prod['descripcion'] ?? '');
                        if (strpos($nombreProd, $textoBusqueda) === false && strpos($descProd, $textoBusqueda) === false) {
                            return false;
                        }
                    }

                    // Filtro por categoría
                    if ($filtro_categoria !== null && intval($prod['id_categoria']) !== $filtro_categoria) {
                        return false;
                    }

                    // Filtro por precio mínimo
                    if ($precio_min !== null && floatval($prod['precio']) < $precio_min) {
                        return false;
                    }

                    // Filtro por precio máximo
                    if ($precio_max !== null && floatval($prod['precio']) > $precio_max) {
                        return false;
                    }

                    // Filtro por disponibilidad
                    if ($disponibilidad === 'con_stock' && intval($prod['cantidad']) <= 0) {
                        return false;
                    }

                    return true;
                });

                // Aplicar ordenamiento
                if ($orden === 'precio_asc') {
                    usort($listaFiltrada, fn($a, $b) => $a['precio'] <=> $b['precio']);
                } elseif ($orden === 'precio_desc') {
                    usort($listaFiltrada, fn($a, $b) => $b['precio'] <=> $a['precio']);
                } elseif ($orden === 'popularidad') {
                    // Tomando la cantidad o stock como métrica simulada de popularidad/rotación
                    usort($listaFiltrada, fn($a, $b) => $b['cantidad'] <=> $a['cantidad']);
                }

                if (empty($listaFiltrada)):
                ?>
                    <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; background: white; border-radius: 8px;">
                        <h3>No se encontraron productos</h3>
                        <p style="color: #6b7280; margin-top: 0.5rem;">Prueba ajustando los filtros de búsqueda.</p>
                        <a href="productos.php" class="btn" style="margin-top: 1rem; display: inline-block;">Ver todos los productos</a>
                    </div>
                <?php 
                else:
                    foreach($listaFiltrada as $prod): 
                        $nombreCategoriaCard = $categoriasNombres[$prod['id_categoria']] ?? 'Herramientas';
                ?>
                    <div class="card-producto">
                        <img src="<?= BASE_URL . ltrim($prod['imagen'], '/'); ?>" alt="<?= htmlspecialchars($prod['nombre']); ?>" class="card-img" onerror="this.src='<?= BASE_URL; ?>assets/imgs/Taladro-Atornillador-Inalambrico.png'">
                        <div class="card-body">
                            <span class="badge-cat"><?= htmlspecialchars($nombreCategoriaCard); ?></span>
                            <h3 class="card-title"><?= htmlspecialchars($prod['nombre']); ?></h3>
                            <p class="card-precio">Q.<?= number_format($prod['precio'], 2); ?></p>
                            <p style="font-size: 0.85rem; color: #6b7280;">Stock: <?= $prod['cantidad']; ?></p>
                            <div style="display: flex; gap: 0.5rem; margin-top: auto;">
                                <a href="productos.php?id=<?= $prod['id_producto']; ?>" class="btn btn-outline" style="flex: 1; text-align: center;">Detalles</a>
                                <button class="btn" onclick="agregarAlCarrito(<?= $prod['id_producto']; ?>, '<?= htmlspecialchars($prod['nombre'], ENT_QUOTES); ?>', <?= $prod['precio']; ?>)" style="flex: 1;">Comprar</button>
                            </div>
                        </div>
                    </div>
                <?php 
                    endforeach;
                endif; 
                ?>
            </div>
        <?php endif; ?>
    </main>

    <script src="../assets/js/app.js"></script>
</body>
</html>