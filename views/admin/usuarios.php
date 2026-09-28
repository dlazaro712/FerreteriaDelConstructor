<?php
// views/admin/usuarios.php
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
// Inclusión solicitada del controlador de usuarios
require_once __DIR__ . '/../../controllers/UsuariosController.php';

$mensaje = '';
$error = '';

try {
    $database = new Database();
    $db = $database->getConnection();
} catch (Exception $e) {
    $db = null;
    $error = "Error de conexión a la base de datos.";
}

// Procesar acciones de formulario (Crear o Editar Usuario)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $db) {
    $accion = $_POST['accion'] ?? '';
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $tipo_usuario = trim($_POST['tipo_usuario'] ?? 'cliente');

    if ($accion === 'crear') {
        $password = trim($_POST['password'] ?? '');
        
        if (!empty($nombre) && !empty($apellido) && !empty($correo) && !empty($password)) {
            try {
                // Verificar si el correo ya existe
                $stmtCheck = $db->prepare("SELECT COUNT(*) FROM usuarios WHERE correo = :correo");
                $stmtCheck->bindParam(':correo', $correo, PDO::PARAM_STR);
                $stmtCheck->execute();
                
                if ($stmtCheck->fetchColumn() > 0) {
                    $error = "El correo electrónico ya está registrado en el sistema.";
                } else {
                    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $db->prepare("INSERT INTO usuarios (nombre, apellido, correo, password, direccion, telefono, tipo_usuario) VALUES (:nombre, :apellido, :correo, :password, :direccion, :telefono, :tipo_usuario)");
                    $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
                    $stmt->bindParam(':apellido', $apellido, PDO::PARAM_STR);
                    $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
                    $stmt->bindParam(':password', $passwordHash, PDO::PARAM_STR);
                    $stmt->bindParam(':direccion', $direccion, PDO::PARAM_STR);
                    $stmt->bindParam(':telefono', $telefono, PDO::PARAM_STR);
                    $stmt->bindParam(':tipo_usuario', $tipo_usuario, PDO::PARAM_STR);

                    if ($stmt->execute()) {
                        $mensaje = "¡Usuario creado exitosamente!";
                    } else {
                        $error = "No se pudo registrar el usuario.";
                    }
                }
            } catch (Exception $e) {
                $error = "Error al crear el usuario: " . $e->getMessage();
            }
        } else {
            $error = "Todos los campos obligatorios deben ser completados.";
        }
    } elseif ($accion === 'editar') {
        $id_usuario = intval($_POST['id_usuario'] ?? 0);
        $password = trim($_POST['password'] ?? '');

        if ($id_usuario > 0 && !empty($nombre) && !empty($apellido) && !empty($correo)) {
            try {
                if (!empty($password)) {
                    // Actualizar incluyendo nueva contraseña hasheada
                    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $db->prepare("UPDATE usuarios SET nombre = :nombre, apellido = :apellido, correo = :correo, password = :password, direccion = :direccion, telefono = :telefono, tipo_usuario = :tipo_usuario WHERE id_usuario = :id_usuario");
                    $stmt->bindParam(':password', $passwordHash, PDO::PARAM_STR);
                } else {
                    // Actualizar sin modificar la contraseña actual
                    $stmt = $db->prepare("UPDATE usuarios SET nombre = :nombre, apellido = :apellido, correo = :correo, direccion = :direccion, telefono = :telefono, tipo_usuario = :tipo_usuario WHERE id_usuario = :id_usuario");
                }
                
                $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
                $stmt->bindParam(':apellido', $apellido, PDO::PARAM_STR);
                $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
                $stmt->bindParam(':direccion', $direccion, PDO::PARAM_STR);
                $stmt->bindParam(':telefono', $telefono, PDO::PARAM_STR);
                $stmt->bindParam(':tipo_usuario', $tipo_usuario, PDO::PARAM_STR);
                $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    $mensaje = "¡Usuario actualizado exitosamente!";
                } else {
                    $error = "No se pudo actualizar la información del usuario.";
                }
            } catch (Exception $e) {
                $error = "Error al actualizar el usuario: " . $e->getMessage();
            }
        } else {
            $error = "Datos inválidos para la actualización.";
        }
    }
}

// Procesar eliminación de usuario por GET
if (isset($_GET['accion']) && $_GET['accion'] === 'eliminar' && isset($_GET['id']) && $db) {
    $id_usuario_eliminar = intval($_GET['id']);
    
    // Evitar que el administrador se elimine a sí mismo por accidente
    $id_admin_actual = $_SESSION['id_usuario'] ?? $_SESSION['user_id'] ?? 0;

    if ($id_usuario_eliminar === intval($id_admin_actual)) {
        $error = "No puedes eliminar tu propia cuenta de administrador activa.";
    } else {
        try {
            $stmtDel = $db->prepare("DELETE FROM usuarios WHERE id_usuario = :id_usuario");
            $stmtDel->bindParam(':id_usuario', $id_usuario_eliminar, PDO::PARAM_INT);
            if ($stmtDel->execute()) {
                $mensaje = "¡Usuario eliminado exitosamente!";
            } else {
                $error = "No se pudo eliminar el usuario.";
            }
        } catch (Exception $e) {
            $error = "Error al eliminar el usuario: " . $e->getMessage();
        }
    }
}

// Obtener la lista completa de usuarios registrados (Incluyendo dirección y teléfono)
$usuarios = [];
if ($db) {
    try {
        $stmtUsr = $db->query("SELECT id_usuario, nombre, apellido, correo, direccion, telefono, tipo_usuario FROM usuarios ORDER BY id_usuario DESC");
        $usuarios = $stmtUsr->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $usuarios = [];
    }
}

// Incluir el encabezado centralizado
include_once __DIR__ . '/../layouts/header.php';
?>

<div class="admin-dashboard" style="max-width: 1300px; margin: 0 auto; padding: 1rem;">
    
    <!-- Encabezado de la Sección -->
    <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="color: #0f172a; margin: 0 0 0.5rem 0; font-size: 1.75rem;">Panel de Administración: Usuarios</h1>
            <p style="color: #64748b; margin: 0;">Administra las cuentas de clientes y administradores registrados en el sistema.</p>
        </div>
        <button type="button" onclick="toggleFormularioNuevoUsuario()" style="padding: 0.75rem 1.25rem; background: #2563eb; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.9rem;">
            ➕ Nuevo Usuario
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

    <!-- Formulario Oculto/Visible para Crear Usuario -->
    <div id="form-nuevo-usuario" style="display: none; background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem;">
        <h3 style="margin-top: 0; color: #0f172a; font-size: 1.25rem; margin-bottom: 1.5rem;">Registrar Nuevo Usuario</h3>
        <form action="<?= BASE_URL; ?>views/admin/usuarios.php" method="POST" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
            <input type="hidden" name="accion" value="crear">
            
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.3rem;">Nombre:</label>
                <input type="text" name="nombre" required style="width: 100%; padding: 0.65rem; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box;">
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.3rem;">Apellido:</label>
                <input type="text" name="apellido" required style="width: 100%; padding: 0.65rem; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box;">
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.3rem;">Correo Electrónico:</label>
                <input type="email" name="correo" required style="width: 100%; padding: 0.65rem; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box;">
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.3rem;">Contraseña:</label>
                <input type="password" name="password" required style="width: 100%; padding: 0.65rem; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box;">
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.3rem;">Teléfono:</label>
                <input type="text" name="telefono" style="width: 100%; padding: 0.65rem; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box;">
            </div>

            <div style="grid-column: span 2;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.3rem;">Dirección:</label>
                <input type="text" name="direccion" style="width: 100%; padding: 0.65rem; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box;">
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.3rem;">Rol / Tipo de Usuario:</label>
                <select name="tipo_usuario" required style="width: 100%; padding: 0.65rem; border: 1px solid #d1d5db; border-radius: 6px; background: white; box-sizing: border-box;">
                    <option value="cliente">Cliente</option>
                    <option value="admin">Administrador</option>
                </select>
            </div>

            <div style="grid-column: 1 / -1; display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1rem;">
                <button type="button" onclick="toggleFormularioNuevoUsuario()" style="padding: 0.6rem 1rem; background: #e2e8f0; color: #475569; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">Cancelar</button>
                <button type="submit" style="padding: 0.6rem 1.5rem; background: #059669; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">Guardar Usuario</button>
            </div>
        </form>
    </div>

    <!-- Tabla de Listado de Usuarios -->
    <div style="background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
        <div style="padding: 1.5rem; border-bottom: 1px solid #e2e8f0;">
            <h2 style="margin: 0; font-size: 1.25rem; color: #0f172a;">Listado de Cuentas Registradas</h2>
        </div>

        <?php if (!empty($usuarios)): ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                    <thead>
                        <tr style="background: #f8fafc; color: #475569; border-bottom: 1px solid #e2e8f0;">
                            <th style="padding: 1rem; width: 60px;">ID</th>
                            <th style="padding: 1rem;">Nombre</th>
                            <th style="padding: 1rem;">Apellido</th>
                            <th style="padding: 1rem;">Correo Electrónico</th>
                            <th style="padding: 1rem;">Teléfono</th>
                            <th style="padding: 1rem;">Dirección</th>
                            <th style="padding: 1rem;">Rol</th>
                            <th style="padding: 1rem; text-align: center; width: 160px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usr): ?>
                            <tr style="border-bottom: 1px solid #e2e8f0;">
                                <td style="padding: 1rem; color: #64748b;"><?= $usr['id_usuario']; ?></td>
                                <td style="padding: 1rem; font-weight: 600; color: #0f172a;"><?= htmlspecialchars($usr['nombre']); ?></td>
                                <td style="padding: 1rem; color: #475569;"><?= htmlspecialchars($usr['apellido']); ?></td>
                                <td style="padding: 1rem; color: #475569;"><?= htmlspecialchars($usr['correo']); ?></td>
                                <td style="padding: 1rem; color: #475569;"><?= htmlspecialchars($usr['telefono'] ?? ''); ?></td>
                                <td style="padding: 1rem; color: #475569;"><?= htmlspecialchars($usr['direccion'] ?? ''); ?></td>
                                <td style="padding: 1rem;">
                                    <span style="padding: 0.25rem 0.6rem; border-radius: 4px; font-size: 0.8rem; font-weight: 600; background: <?= $usr['tipo_usuario'] === 'admin' ? '#dbeafe; color: #1e40af;' : '#f1f5f9; color: #475569;'; ?>">
                                        <?= ucfirst($usr['tipo_usuario']); ?>
                                    </span>
                                </td>
                                <td style="padding: 1rem; text-align: center; display: flex; justify-content: center; gap: 0.5rem;">
                                    <!-- Botón Editar -->
                                    <button type="button" 
                                            onclick='abrirModalEditarUsuario(<?= json_encode($usr); ?>)' 
                                            style="padding: 0.4rem 0.8rem; background: #d97706; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.85rem;" title="Editar">
                                        ✏️ Editar
                                    </button>
                                    <!-- Botón Eliminar -->
                                    <a href="<?= BASE_URL; ?>views/admin/usuarios.php?accion=eliminar&id=<?= $usr['id_usuario']; ?>" 
                                       onclick="return confirm('¿Estás seguro de eliminar este usuario?');" 
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
                No hay usuarios registrados en el sistema actualmente.
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- Modal / Formulario flotante para Editar Usuario -->
<div id="modal-editar-usuario" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 1000;">
    <div style="background: white; padding: 2rem; border-radius: 8px; width: 100%; max-width: 550px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin: 1rem; max-height: 90vh; overflow-y: auto;">
        <h3 style="margin-top: 0; color: #0f172a; font-size: 1.25rem; margin-bottom: 1.5rem;">Editar Usuario</h3>
        <form action="<?= BASE_URL; ?>views/admin/usuarios.php" method="POST" style="display: flex; flex-direction: column; gap: 1rem;">
            <input type="hidden" name="accion" value="editar">
            <input type="hidden" name="id_usuario" id="edit-usr-id">
            
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.3rem;">Nombre:</label>
                <input type="text" name="nombre" id="edit-usr-nombre" required style="width: 100%; padding: 0.65rem; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box;">
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.3rem;">Apellido:</label>
                <input type="text" name="apellido" id="edit-usr-apellido" required style="width: 100%; padding: 0.65rem; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box;">
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.3rem;">Correo Electrónico:</label>
                <input type="email" name="correo" id="edit-usr-correo" required style="width: 100%; padding: 0.65rem; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box;">
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.3rem;">Teléfono:</label>
                <input type="text" name="telefono" id="edit-usr-telefono" style="width: 100%; padding: 0.65rem; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box;">
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.3rem;">Dirección:</label>
                <input type="text" name="direccion" id="edit-usr-direccion" style="width: 100%; padding: 0.65rem; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box;">
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.3rem;">Nueva Contraseña (Opcional):</label>
                <input type="password" name="password" placeholder="Dejar en blanco para mantener la actual" style="width: 100%; padding: 0.65rem; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box;">
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.3rem;">Rol / Tipo de Usuario:</label>
                <select name="tipo_usuario" id="edit-usr-tipo" required style="width: 100%; padding: 0.65rem; border: 1px solid #d1d5db; border-radius: 6px; background: white; box-sizing: border-box;">
                    <option value="cliente">Cliente</option>
                    <option value="admin">Administrador</option>
                </select>
            </div>

            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1rem;">
                <button type="button" onclick="cerrarModalEditarUsuario()" style="padding: 0.6rem 1rem; background: #e2e8f0; color: #475569; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">Cancelar</button>
                <button type="submit" style="padding: 0.6rem 1.5rem; background: #2563eb; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">Actualizar Usuario</button>
            </div>
        </form>
    </div>
</div>

<!-- Scripts de control visual -->
<script>
function toggleFormularioNuevoUsuario() {
    const contenedor = document.getElementById('form-nuevo-usuario');
    if (contenedor.style.display === 'none') {
        contenedor.style.display = 'block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    } else {
        contenedor.style.display = 'none';
    }
}

function abrirModalEditarUsuario(usuario) {
    document.getElementById('edit-usr-id').value = usuario.id_usuario;
    document.getElementById('edit-usr-nombre').value = usuario.nombre;
    document.getElementById('edit-usr-apellido').value = usuario.apellido;
    document.getElementById('edit-usr-correo').value = usuario.correo;
    document.getElementById('edit-usr-telefono').value = usuario.telefono || '';
    document.getElementById('edit-usr-direccion').value = usuario.direccion || '';
    document.getElementById('edit-usr-tipo').value = usuario.tipo_usuario;
    
    document.getElementById('modal-editar-usuario').style.display = 'flex';
}

function cerrarModalEditarUsuario() {
    document.getElementById('modal-editar-usuario').style.display = 'none';
}
</script>

<?php 
// Incluir el pie de página centralizado
include_once __DIR__ . '/../layouts/footer.php'; 
?>