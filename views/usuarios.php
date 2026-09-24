<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/UsuariosController.php';

$usuariosController = new UsuariosController();
$mensaje_alerta = "";
$accion = $_GET['action'] ?? 'list';

// Manejo de la acción de Cerrar Sesión (Logout) vía parámetro GET
if ($accion === 'logout') {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    header("Location: " . BASE_URL . "views/usuarios.php?action=login");
    exit();
}

// Manejo de peticiones POST (Registro, Login, Recuperación, Edición o Eliminación de usuarios)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo_form = $_POST['form_type'] ?? '';

    if ($tipo_form === 'registro') {
        $datos = [
            'nombre' => $_POST['nombre'] ?? '',
            'correo' => $_POST['correo'] ?? '',
            'password' => $_POST['password'] ?? '',
            'tipo_usuario' => 'cliente'
        ];
        if ($usuariosController->crear($datos)) {
            $mensaje_alerta = "<div class='alert alert-success'>¡Cuenta creada con éxito! Ya puedes iniciar sesión.</div>";
            $accion = 'login';
        } else {
            $mensaje_alerta = "<div class='alert alert-error'>Error al registrarse. El correo podría estar en uso.</div>";
        }
    } elseif ($tipo_form === 'login') {
        $correo = $_POST['correo'] ?? '';
        $password = $_POST['password'] ?? '';
        
        $lista = $usuariosController->listar();
        $encontrado = null;
        foreach($lista as $u) {
            if ($u['correo'] === $correo) {
                $encontrado = $u;
                break;
            }
        }

        if ($encontrado) {
            $_SESSION['usuario'] = $encontrado['nombre'];
            $_SESSION['tipo_usuario'] = $encontrado['tipo_usuario'];
            $_SESSION['id_usuario'] = $encontrado['id_usuario'];
            header("Location: " . BASE_URL . "views/index.php");
            exit();
        } else {
            $mensaje_alerta = "<div class='alert alert-error'>Credenciales incorrectas.</div>";
        }
    } elseif ($tipo_form === 'recuperar') {
        $correo = $_POST['correo'] ?? '';
        
        $lista = $usuariosController->listar();
        $existe_correo = false;
        foreach($lista as $u) {
            if ($u['correo'] === $correo) {
                $existe_correo = true;
                break;
            }
        }

        // Por seguridad, se muestra un mensaje genérico o de éxito simulando el envío de instrucciones
        if ($existe_correo) {
            $mensaje_alerta = "<div class='alert alert-success'>Si el correo está registrado, se han enviado las instrucciones de recuperación a <strong>" . htmlspecialchars($correo) . "</strong>.</div>";
        } else {
            $mensaje_alerta = "<div class='alert alert-success'>Si el correo está registrado, se han enviado las instrucciones de recuperación.</div>";
        }
        $accion = 'recover';
    } elseif ($tipo_form === 'admin_eliminar' && isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'admin') {
        $id_del = intval($_POST['id_usuario']);
        if ($usuariosController->eliminar($id_del)) {
            $mensaje_alerta = "<div class='alert alert-success'>Usuario eliminado correctamente.</div>";
        }
    }
}

include_once __DIR__ . '/layouts/header.php';
?>

<?= $mensaje_alerta; ?>

<?php if ($accion === 'register'): ?>
    <!-- FORMULARIO DE REGISTRO DE CLIENTE -->
    <div class="card-form">
        <h2 class="form-title">Crear Cuenta Nueva</h2>
        <form action="<?= BASE_URL; ?>views/usuarios.php?action=register" method="POST">
            <input type="hidden" name="form_type" value="registro">
            
            <div class="form-group">
                <label class="form-label">Nombre Completo</label>
                <input type="text" name="nombre" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Correo Electrónico</label>
                <input type="email" name="correo" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Contraseña</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Registrarse</button>
        </form>
        <p class="form-footer-text"><a href="<?= BASE_URL; ?>views/usuarios.php?action=login" class="text-link">¿Ya tienes cuenta? Inicia sesión aquí</a></p>
    </div>

<?php elseif ($accion === 'login'): ?>
    <!-- FORMULARIO DE INICIO DE SESIÓN -->
    <div class="card-form">
        <h2 class="form-title">Iniciar Sesión</h2>
        <form action="<?= BASE_URL; ?>views/usuarios.php?action=login" method="POST">
            <input type="hidden" name="form_type" value="login">
            
            <div class="form-group">
                <label class="form-label">Correo Electrónico</label>
                <input type="email" name="correo" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Contraseña</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            
            <button type="submit" class="btn btn-dark btn-block">Entrar</button>
        </form>
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem; font-size: 0.85rem;">
            <a href="<?= BASE_URL; ?>views/usuarios.php?action=recover" class="text-link">¿Olvidaste tu contraseña?</a>
        </div>
        
        <p class="form-footer-text"><a href="<?= BASE_URL; ?>views/usuarios.php?action=register" class="text-link">¿No tienes cuenta? Regístrate</a></p>
    </div>

<?php elseif ($accion === 'recover'): ?>
    <!-- FORMULARIO DE RECUPERACIÓN DE CONTRASEÑA -->
    <div class="card-form">
        <h2 class="form-title">Recuperar Contraseña</h2>
        <p class="form-subtitle" style="font-size: 0.9rem; color: #64748b; margin-bottom: 1.5rem;">Ingresa tu correo electrónico y te enviaremos las instrucciones para restablecer tu contraseña.</p>
        
        <form action="<?= BASE_URL; ?>views/usuarios.php?action=recover" method="POST">
            <input type="hidden" name="form_type" value="recuperar">
            
            <div class="form-group">
                <label class="form-label">Correo Electrónico</label>
                <input type="email" name="correo" class="form-control" required>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Enviar Instrucciones</button>
        </form>
        
        <p class="form-footer-text"><a href="<?= BASE_URL; ?>views/usuarios.php?action=login" class="text-link">← Volver al inicio de sesión</a></p>
    </div>

<?php else: ?>
    <!-- PANEL DE GESTIÓN DE USUARIOS (CRUD VISIBLE PARA ADMIN) -->
    <?php if (isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'admin'): ?>
        <h1 class="section-title">Gestión de Usuarios del Sistema</h1>
        
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Fecha Registro</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $usuarios = $usuariosController->listar();
                    if (!empty($usuarios)):
                        foreach ($usuarios as $usr):
                    ?>
                    <tr>
                        <td><?= $usr['id_usuario']; ?></td>
                        <td class="font-medium"><?= htmlspecialchars($usr['nombre']); ?></td>
                        <td class="text-muted"><?= htmlspecialchars($usr['correo']); ?></td>
                        <td>
                            <span class="badge <?= $usr['tipo_usuario'] === 'admin' ? 'badge-danger' : 'badge-info'; ?>">
                                <?= $usr['tipo_usuario']; ?>
                            </span>
                        </td>
                        <td class="text-muted"><?= $usr['fecha_registro']; ?></td>
                        <td class="text-right">
                            <form action="<?= BASE_URL; ?>views/usuarios.php" method="POST" onsubmit="return confirm('¿Desea eliminar este usuario?');" class="inline-form">
                                <input type="hidden" name="form_type" value="admin_eliminar">
                                <input type="hidden" name="id_usuario" value="<?= $usr['id_usuario']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="6" class="text-center text-muted py-3">No hay usuarios registrados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <h2>Área exclusiva para clientes registrados</h2>
            <p>Por favor inicia sesión o regístrate para acceder a tus pedidos, lista de deseos y reseñas de productos.</p>
            <div class="empty-actions">
                <a href="<?= BASE_URL; ?>views/usuarios.php?action=login" class="btn btn-dark">Iniciar Sesión</a>
                <a href="<?= BASE_URL; ?>views/usuarios.php?action=register" class="btn btn-primary">Registrarse</a>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php 
include_once __DIR__ . '/layouts/footer.php'; 
?>