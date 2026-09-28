<?php
// views/admin/categorias.php
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

$mensaje = '';
$error = '';

try {
    $database = new Database();
    $db = $database->getConnection();
} catch (Exception $e) {
    $db = null;
    $error = "Error de conexión a la base de datos.";
}

// Procesar acciones de formulario (Crear o Editar Categoría)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $db) {
    $accion = $_POST['accion'] ?? '';
    $descripcion = trim($_POST['descripcion'] ?? '');

    if ($accion === 'crear') {
        if (!empty($descripcion)) {
            try {
                $stmt = $db->prepare("INSERT INTO categorias (descripcion) VALUES (:descripcion)");
                $stmt->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
                if ($stmt->execute()) {
                    $mensaje = "¡Categoría creada exitosamente!";
                } else {
                    $error = "No se pudo registrar la categoría.";
                }
            } catch (Exception $e) {
                $error = "Error al crear la categoría: " . $e->getMessage();
            }
        } else {
            $error = "La descripción de la categoría no puede estar vacía.";
        }
    } elseif ($accion === 'editar') {
        $id_categoria = intval($_POST['id_categoria'] ?? 0);
        if ($id_categoria > 0 && !empty($descripcion)) {
            try {
                $stmt = $db->prepare("UPDATE categorias SET descripcion = :descripcion WHERE id_categoria = :id_categoria");
                $stmt->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
                $stmt->bindParam(':id_categoria', $id_categoria, PDO::PARAM_INT);
                if ($stmt->execute()) {
                    $mensaje = "¡Categoría actualizada exitosamente!";
                } else {
                    $error = "No se pudo actualizar la categoría.";
                }
            } catch (Exception $e) {
                $error = "Error al actualizar la categoría: " . $e->getMessage();
            }
        } else {
            $error = "Datos inválidos para la actualización.";
        }
    }
}

// Procesar eliminación de categoría por GET
if (isset($_GET['accion']) && $_GET['accion'] === 'eliminar' && isset($_GET['id']) && $db) {
    $id_categoria = intval($_GET['id']);
    try {
        // Verificar opcionalmente si hay productos asociados antes de eliminar
        $stmtCheck = $db->prepare("SELECT COUNT(*) FROM productos WHERE id_categoria = :id_categoria");
        $stmtCheck->bindParam(':id_categoria', $id_categoria, PDO::PARAM_INT);
        $stmtCheck->execute();
        $totalProductos = $stmtCheck->fetchColumn();

        if ($totalProductos > 0) {
            $error = "No se puede eliminar la categoría porque hay {$totalProductos} producto(s) asociado(s) a ella.";
        } else {
            $stmtDel = $db->prepare("DELETE FROM categorias WHERE id_categoria = :id_categoria");
            $stmtDel->bindParam(':id_categoria', $id_categoria, PDO::PARAM_INT);
            if ($stmtDel->execute()) {
                $mensaje = "¡Categoría eliminada exitosamente!";
            } else {
                $error = "No se pudo eliminar la categoría.";
            }
        }
    } catch (Exception $e) {
        $error = "Error al eliminar la categoría: " . $e->getMessage();
    }
}

// Obtener la lista completa de categorías ordenadas alfabéticamente
$categorias = [];
if ($db) {
    try {
        $stmtCat = $db->query("SELECT id_categoria, descripcion FROM categorias ORDER BY descripcion ASC");
        $categorias = $stmtCat->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $categorias = [];
    }
}

// Incluir el encabezado centralizado
include_once __DIR__ . '/../layouts/header.php';
?>

<div class="admin-dashboard" style="max-width: 1000px; margin: 0 auto; padding: 1rem;">
    
    <!-- Encabezado de la Sección -->
    <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="color: #0f172a; margin: 0 0 0.5rem 0; font-size: 1.75rem;">Panel de Administración: Categorías</h1>
            <p style="color: #64748b; margin: 0;">Organiza y administra las categorías disponibles para clasificar los productos del inventario.</p>
        </div>
        <button type="button" onclick="toggleFormularioNuevaCategoria()" style="padding: 0.75rem 1.25rem; background: #2563eb; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.9rem;">
            ➕ Nueva Categoría
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

    <!-- Formulario Oculto/Visible para Crear Categoría -->
    <div id="form-nueva-categoria" style="display: none; background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem;">
        <h3 style="margin-top: 0; color: #0f172a; font-size: 1.25rem; margin-bottom: 1.5rem;">Registrar Nueva Categoría</h3>
        <form action="<?= BASE_URL; ?>views/admin/categorias.php" method="POST" style="display: flex; flex-direction: column; gap: 1rem;">
            <input type="hidden" name="accion" value="crear">
            
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.3rem;">Descripción de la Categoría:</label>
                <input type="text" name="descripcion" placeholder="Ej. Herramientas eléctricas, Construcción..." required style="width: 100%; padding: 0.65rem; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box;">
            </div>

            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" onclick="toggleFormularioNuevaCategoria()" style="padding: 0.6rem 1rem; background: #e2e8f0; color: #475569; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">Cancelar</button>
                <button type="submit" style="padding: 0.6rem 1.5rem; background: #059669; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">Guardar Categoría</button>
            </div>
        </form>
    </div>

    <!-- Tabla de Listado de Categorías -->
    <div style="background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
        <div style="padding: 1.5rem; border-bottom: 1px solid #e2e8f0;">
            <h2 style="margin: 0; font-size: 1.25rem; color: #0f172a;">Listado de Categorías</h2>
        </div>

        <?php if (!empty($categorias)): ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                    <thead>
                        <tr style="background: #f8fafc; color: #475569; border-bottom: 1px solid #e2e8f0;">
                            <th style="padding: 1rem; width: 80px;">ID</th>
                            <th style="padding: 1rem;">Descripción</th>
                            <th style="padding: 1rem; text-align: center; width: 180px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categorias as $cat): ?>
                            <tr style="border-bottom: 1px solid #e2e8f0;">
                                <td style="padding: 1rem; color: #64748b;"><?= $cat['id_categoria']; ?></td>
                                <td style="padding: 1rem; font-weight: 600; color: #0f172a;"><?= htmlspecialchars($cat['descripcion']); ?></td>
                                <td style="padding: 1rem; text-align: center; display: flex; justify-content: center; gap: 0.5rem;">
                                    <!-- Botón Editar -->
                                    <button type="button" 
                                            onclick='abrirModalEditarCategoria(<?= json_encode($cat); ?>)' 
                                            style="padding: 0.4rem 0.8rem; background: #d97706; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.85rem;" title="Editar">
                                        ✏️ Editar
                                    </button>
                                    <!-- Botón Eliminar -->
                                    <a href="<?= BASE_URL; ?>views/admin/categorias.php?accion=eliminar&id=<?= $cat['id_categoria']; ?>" 
                                       onclick="return confirm('¿Estás seguro de eliminar esta categoría?');" 
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
                No hay categorías registradas en el sistema actualmente.
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- Modal / Formulario flotante para Editar Categoría -->
<div id="modal-editar-categoria" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 1000;">
    <div style="background: white; padding: 2rem; border-radius: 8px; width: 100%; max-width: 450px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin: 1rem;">
        <h3 style="margin-top: 0; color: #0f172a; font-size: 1.25rem; margin-bottom: 1.5rem;">Editar Categoría</h3>
        <form action="<?= BASE_URL; ?>views/admin/categorias.php" method="POST" style="display: flex; flex-direction: column; gap: 1rem;">
            <input type="hidden" name="accion" value="editar">
            <input type="hidden" name="id_categoria" id="edit-cat-id">
            
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.3rem;">Descripción:</label>
                <input type="text" name="descripcion" id="edit-cat-descripcion" required style="width: 100%; padding: 0.65rem; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box;">
            </div>

            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1rem;">
                <button type="button" onclick="cerrarModalEditarCategoria()" style="padding: 0.6rem 1rem; background: #e2e8f0; color: #475569; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">Cancelar</button>
                <button type="submit" style="padding: 0.6rem 1.5rem; background: #2563eb; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">Actualizar Categoría</button>
            </div>
        </form>
    </div>
</div>

<!-- Scripts de control visual -->
<script>
function toggleFormularioNuevaCategoria() {
    const contenedor = document.getElementById('form-nueva-categoria');
    if (contenedor.style.display === 'none') {
        contenedor.style.display = 'block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    } else {
        contenedor.style.display = 'none';
    }
}

function abrirModalEditarCategoria(categoria) {
    document.getElementById('edit-cat-id').value = categoria.id_categoria;
    document.getElementById('edit-cat-descripcion').value = categoria.descripcion;
    
    document.getElementById('modal-editar-categoria').style.display = 'flex';
}

function cerrarModalEditarCategoria() {
    document.getElementById('modal-editar-categoria').style.display = 'none';
}
</script>

<?php 
// Incluir el pie de página centralizado
include_once __DIR__ . '/../layouts/footer.php'; 
?>