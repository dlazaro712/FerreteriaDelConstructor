<?php
// views/admin/productos.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validación de seguridad: solo administradores
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'admin') {
    header("Location: " . BASE_URL . "views/usuarios.php?action=login");
    exit();
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../controllers/ProductosController.php';

$productosController = new ProductosController();
$mensaje = '';
$error = '';

// Función auxiliar para manejar la subida de imágenes de manera segura
function procesarSubidaImagen($fileInputName) {
    if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES[$fileInputName]['tmp_name'];
        $fileName = $_FILES[$fileInputName]['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($fileExtension, $allowedExtensions)) {
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $uploadFileDir = __DIR__ . '/../../assets/imgs/';
            
            // Asegurar que el directorio de destino exista
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }
            
            $dest_path = $uploadFileDir . $newFileName;
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                return $newFileName;
            }
        }
    }
    return null;
}

// Procesar acciones de formulario (Crear o Editar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    
    // Manejar imagen subida (si existe)
    $imagenSubida = procesarSubidaImagen('imagen_archivo');

    if ($accion === 'crear') {
        $datos = [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'precio' => floatval($_POST['precio'] ?? 0),
            'cantidad' => intval($_POST['cantidad'] ?? 0),
            'id_categoria' => intval($_POST['id_categoria'] ?? 0),
            'imagen' => $imagenSubida ?? trim($_POST['imagen_texto'] ?? 'default.jpg')
        ];
        try {
            if (method_exists($productosController, 'crearProducto')) {
                $productosController->crearProducto($datos);
                $mensaje = "¡Producto creado exitosamente!";
            }
        } catch (Exception $e) {
            $error = "Error al crear el producto: " . $e->getMessage();
        }
    } elseif ($accion === 'editar') {
        $id_producto = intval($_POST['id_producto'] ?? 0);
        $imagenActual = trim($_POST['imagen_actual'] ?? 'default.jpg');
        
        $datos = [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'precio' => floatval($_POST['precio'] ?? 0),
            'cantidad' => intval($_POST['cantidad'] ?? 0),
            'id_categoria' => intval($_POST['id_categoria'] ?? 0),
            'imagen' => $imagenSubida ?? ($imagenActual !== '' ? $imagenActual : 'default.jpg')
        ];
        try {
            if (method_exists($productosController, 'actualizarProducto')) {
                // CORRECCIÓN: Se envían exactamente dos argumentos ($id_producto y $datos)
                $productosController->actualizarProducto($id_producto, $datos);
                $mensaje = "¡Producto actualizado exitosamente!";
            }
        } catch (Exception $e) {
            $error = "Error al actualizar el producto: " . $e->getMessage();
        }
    }
}

// Procesar eliminación por GET
if (isset($_GET['accion']) && $_GET['accion'] === 'eliminar' && isset($_GET['id'])) {
    $id_producto = intval($_GET['id']);
    try {
        if (method_exists($productosController, 'eliminarProducto')) {
            $productosController->eliminarProducto($id_producto);
            $mensaje = "¡Producto eliminado exitosamente!";
        }
    } catch (Exception $e) {
        $error = "Error al eliminar el producto: " . $e->getMessage();
    }
}

// Obtener categorías y lista de productos
try {
    $database = new Database();
    $db = method_exists($database, 'conectar') ? $database->conectar() : $database->getConnection();
    
    $stmtCat = $db->query("SELECT id_categoria, descripcion FROM categorias ORDER BY descripcion ASC");
    $categorias = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

    $productos = $productosController->listarProductos() ?? [];
} catch (Exception $e) {
    $categorias = [];
    $productos = [];
}

// Incluir el encabezado centralizado
include_once __DIR__ . '/../layouts/header.php';
?>

<div class="admin-dashboard" style="max-width: 1200px; margin: 0 auto; padding: 1rem;">
    
    <!-- Encabezado de la Sección -->
    <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="color: #0f172a; margin: 0 0 0.5rem 0; font-size: 1.75rem;">Panel de Administración: Productos</h1>
            <p style="color: #64748b; margin: 0;">Gestiona el inventario, agrega nuevos artículos, edita precios o da de baja productos del sistema.</p>
        </div>
        <button type="button" onclick="toggleFormularioNuevo()" style="padding: 0.75rem 1.25rem; background: #2563eb; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.9rem;">
            ➕ Nuevo Producto
        </button>
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

    <!-- Formulario Oculto/Visible para Crear Producto (con enctype para archivos) -->
    <div id="form-nuevo-container" style="display: none; background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem;">
        <h3 style="margin-top: 0; color: #0f172a; font-size: 1.25rem; margin-bottom: 1.5rem;">Registrar Nuevo Producto</h3>
        <form action="<?= BASE_URL; ?>views/admin/productos.php" method="POST" enctype="multipart/form-data" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
            <input type="hidden" name="accion" value="crear">
            
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.3rem;">Nombre del Producto:</label>
                <input type="text" name="nombre" required style="width: 100%; padding: 0.65rem; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box;">
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.3rem;">Categoría:</label>
                <select name="id_categoria" required style="width: 100%; padding: 0.65rem; border: 1px solid #d1d5db; border-radius: 6px; background: white; box-sizing: border-box;">
                    <option value="">Seleccione una categoría</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id_categoria']; ?>"><?= htmlspecialchars($cat['descripcion']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.3rem;">Precio (Q.):</label>
                <input type="number" step="0.01" name="precio" required style="width: 100%; padding: 0.65rem; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box;">
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.3rem;">Cantidad en Stock:</label>
                <input type="number" name="cantidad" required style="width: 100%; padding: 0.65rem; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box;">
            </div>

            <div style="grid-column: 1 / -1;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.3rem;">Subir Imagen (Archivo):</label>
                <input type="file" name="imagen_archivo" accept="image/png, image/jpeg, image/webp" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 6px; background: #f8fafc; box-sizing: border-box;">
            </div>

            <div style="grid-column: 1 / -1;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.3rem;">Descripción:</label>
                <textarea name="descripcion" rows="3" style="width: 100%; padding: 0.65rem; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box;"></textarea>
            </div>

            <div style="grid-column: 1 / -1; display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" onclick="toggleFormularioNuevo()" style="padding: 0.6rem 1rem; background: #e2e8f0; color: #475569; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">Cancelar</button>
                <button type="submit" style="padding: 0.6rem 1.5rem; background: #059669; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">Guardar Producto</button>
            </div>
        </form>
    </div>

    <!-- Tabla de Listado de Productos -->
    <div style="background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
        <div style="padding: 1.5rem; border-bottom: 1px solid #e2e8f0;">
            <h2 style="margin: 0; font-size: 1.25rem; color: #0f172a;">Inventario de Productos</h2>
        </div>

        <?php if (!empty($productos)): ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                    <thead>
                        <tr style="background: #f8fafc; color: #475569; border-bottom: 1px solid #e2e8f0;">
                            <th style="padding: 1rem;">ID</th>
                            <th style="padding: 1rem;">Imagen</th>
                            <th style="padding: 1rem;">Nombre</th>
                            <th style="padding: 1rem;">Precio</th>
                            <th style="padding: 1rem;">Stock</th>
                            <th style="padding: 1rem; text-align: center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $prod): ?>
                            <tr style="border-bottom: 1px solid #e2e8f0;">
                                <td style="padding: 1rem; color: #64748b;"><?= $prod['id_producto']; ?></td>
                                <td style="padding: 1rem;">
                                    <?php if (!empty($prod['imagen'])): ?>
                                        <img src="<?= BASE_URL; ?>assets/imgs/<?= htmlspecialchars($prod['imagen']); ?>" alt="" style="width: 40px; height: 40px; object-fit: contain; background: #f1f5f9; border-radius: 4px;">
                                    <?php else: ?>
                                        <span style="color: #94a3b8; font-size: 0.75rem;">N/D</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem; font-weight: 600; color: #0f172a;"><?= htmlspecialchars($prod['nombre']); ?></td>
                                <td style="padding: 1rem; color: #2563eb; font-weight: bold;">Q.<?= number_format($prod['precio'], 2); ?></td>
                                <td style="padding: 1rem; color: <?= $prod['cantidad'] > 0 ? '#059669' : '#dc2626'; ?>; font-weight: 500;">
                                    <?= $prod['cantidad']; ?> unidades
                                </td>
                                <td style="padding: 1rem; text-align: center; display: flex; justify-content: center; gap: 0.5rem;">
                                    <!-- Botón Editar -->
                                    <button type="button" 
                                            onclick='abrirModalEditar(<?= json_encode($prod); ?>)' 
                                            style="padding: 0.4rem 0.8rem; background: #d97706; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.85rem;" title="Editar">
                                        ✏️ Editar
                                    </button>
                                    <!-- Botón Eliminar -->
                                    <a href="<?= BASE_URL; ?>views/admin/productos.php?accion=eliminar&id=<?= $prod['id_producto']; ?>" 
                                       onclick="return confirm('¿Estás seguro de eliminar este producto?');" 
                                       style="padding: 0.4rem 0.8rem; background: #dc2626; color: white; border-radius: 4px; text-decoration: none; font-size: 0.85rem;" title="Eliminar">
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
                No hay productos registrados en el sistema actualmente.
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- Modal / Formulario flotante para Editar Producto (con enctype para archivos) -->
<div id="modal-editar" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 1000;">
    <div style="background: white; padding: 2rem; border-radius: 8px; width: 100%; max-width: 600px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin: 1rem;">
        <h3 style="margin-top: 0; color: #0f172a; font-size: 1.25rem; margin-bottom: 1.5rem;">Editar Producto</h3>
        <form action="<?= BASE_URL; ?>views/admin/productos.php" method="POST" enctype="multipart/form-data" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
            <input type="hidden" name="accion" value="editar">
            <input type="hidden" name="id_producto" id="edit-id">
            <input type="hidden" name="imagen_actual" id="edit-imagen-actual">
            
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.3rem;">Nombre:</label>
                <input type="text" name="nombre" id="edit-nombre" required style="width: 100%; padding: 0.65rem; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box;">
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.3rem;">Categoría:</label>
                <select name="id_categoria" id="edit-categoria" required style="width: 100%; padding: 0.65rem; border: 1px solid #d1d5db; border-radius: 6px; background: white; box-sizing: border-box;">
                    <option value="">Seleccione una categoría</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id_categoria']; ?>"><?= htmlspecialchars($cat['descripcion']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.3rem;">Precio (Q.):</label>
                <input type="number" step="0.01" name="precio" id="edit-precio" required style="width: 100%; padding: 0.65rem; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box;">
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.3rem;">Stock:</label>
                <input type="number" name="cantidad" id="edit-cantidad" required style="width: 100%; padding: 0.65rem; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box;">
            </div>

            <div style="grid-column: 1 / -1;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.3rem;">Actualizar Imagen (Opcional):</label>
                <input type="file" name="imagen_archivo" accept="image/png, image/jpeg, image/webp" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 6px; background: #f8fafc; box-sizing: border-box;">
                <small id="current-img-label" style="color: #64748b; display: block; margin-top: 0.25rem;"></small>
            </div>

            <div style="grid-column: 1 / -1;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.3rem;">Descripción:</label>
                <textarea name="descripcion" id="edit-descripcion" rows="3" style="width: 100%; padding: 0.65rem; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box;"></textarea>
            </div>

            <div style="grid-column: 1 / -1; display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" onclick="cerrarModalEditar()" style="padding: 0.6rem 1rem; background: #e2e8f0; color: #475569; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">Cancelar</button>
                <button type="submit" style="padding: 0.6rem 1.5rem; background: #2563eb; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">Actualizar Producto</button>
            </div>
        </form>
    </div>
</div>

<!-- Scripts de control visual -->
<script>
function toggleFormularioNuevo() {
    const contenedor = document.getElementById('form-nuevo-container');
    if (contenedor.style.display === 'none') {
        contenedor.style.display = 'block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    } else {
        contenedor.style.display = 'none';
    }
}

function abrirModalEditar(producto) {
    document.getElementById('edit-id').value = producto.id_producto;
    document.getElementById('edit-nombre').value = producto.nombre;
    document.getElementById('edit-categoria').value = producto.id_categoria;
    document.getElementById('edit-precio').value = producto.precio;
    document.getElementById('edit-cantidad').value = producto.cantidad;
    document.getElementById('edit-imagen-actual').value = producto.imagen || 'default.jpg';
    document.getElementById('edit-descripcion').value = producto.descripcion || '';
    
    const labelImg = document.getElementById('current-img-label');
    if (producto.imagen) {
        labelImg.innerText = "Imagen actual: " + producto.imagen;
    } else {
        labelImg.innerText = "Sin imagen registrada.";
    }

    document.getElementById('modal-editar').style.display = 'flex';
}

function cerrarModalEditar() {
    document.getElementById('modal-editar').style.display = 'none';
}
</script>

<?php 
// Incluir el pie de página centralizado
include_once __DIR__ . '/../layouts/footer.php'; 
?>