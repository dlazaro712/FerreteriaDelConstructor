<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/UsuariosController.php';

$usuariosController = new UsuariosController();
$mensaje_alerta = "";
$accion = $_GET['action'] ?? 'login';

// ==========================================
// 1. MANEJO DE CIERRE DE SESIÓN (LOGOUT)
// ==========================================
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

// ==========================================
// 2. MANEJO DE PETICIONES POST (REGISTRO, LOGIN, RECUPERACIÓN)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo_form = $_POST['form_type'] ?? '';

    if ($tipo_form === 'registro') {
        $datos = [
            'nombre' => $_POST['nombre'] ?? '',
            'apellido' => $_POST['apellido'] ?? '',
            'correo' => $_POST['correo'] ?? '',
            'password' => $_POST['password'] ?? '',
            'telefono' => $_POST['telefono'] ?? '',
            'direccion' => $_POST['direccion'] ?? '',
            'tipo_usuario' => 'cliente'
        ];
        
        if ($usuariosController->crear($datos)) {
            $mensaje_alerta = "<div class='modern-alert modern-alert-success'>✨ ¡Cuenta creada con éxito! Ya puedes iniciar sesión.</div>";
            $accion = 'login';
        } else {
            $mensaje_alerta = "<div class='modern-alert modern-alert-error'>⚠️ Error al registrarse. El correo podría ya estar en uso.</div>";
            $accion = 'register';
        }
    } elseif ($tipo_form === 'login') {
        $correo = $_POST['correo'] ?? '';
        $password = $_POST['password'] ?? '';
        
        $lista = $usuariosController->listar();
        $encontrado = null;
        
        // Verificación de credenciales y contraseña encriptada
        if (is_array($lista)) {
            foreach($lista as $u) {
                if (isset($u['correo']) && $u['correo'] === $correo) {
                    $clave_db = $u['password'] ?? '';
                    if (password_verify($password, $clave_db)) {
                        $encontrado = $u;
                        break;
                    }
                }
            }
        }

        if ($encontrado) {
            $_SESSION['usuario'] = $encontrado['nombre'];
            $_SESSION['tipo_usuario'] = $encontrado['tipo_usuario'] ?? $encontrado['rol'];
            $_SESSION['id_usuario'] = $encontrado['id_usuario'];

            // Redirección basada estrictamente en el tipo de usuario
            if ($_SESSION['tipo_usuario'] === 'admin') {
                header("Location: " . BASE_URL . "views/admin/dashboard.php");
                exit();
            } else {
                header("Location: " . BASE_URL . "views/clientes/index.php");
                exit();
            }
        } else {
            $mensaje_alerta = "<div class='modern-alert modern-alert-error'>❌ Credenciales incorrectas. Verifica tu correo y contraseña.</div>";
            $accion = 'login';
        }
    } elseif ($tipo_form === 'recuperar') {
        $correo = $_POST['correo'] ?? '';
        $mensaje_alerta = "<div class='modern-alert modern-alert-success'>✉️ Si el correo está registrado, se han enviado las instrucciones de recuperación.</div>";
        $accion = 'recover';
    }
}

include_once __DIR__ . '/layouts/header.php';
?>

<!-- Estilos CSS3 Modernos embebidos para la vista de autenticación -->
<style>
    .auth-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 75vh;
        padding: 1rem;
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    }

    .modern-card {
        background: #ffffff;
        width: 100%;
        max-width: 480px;
        padding: 2.5rem;
        border-radius: 16px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.08);
        transition: transform 0.3s ease;
    }

    .modern-card:hover {
        transform: translateY(-2px);
    }

    .modern-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: #0f172a;
        margin-top: 0;
        margin-bottom: 0.5rem;
        text-align: center;
        letter-spacing: -0.025em;
    }

    .modern-subtitle {
        font-size: 0.9rem;
        color: #64748b;
        text-align: center;
        margin-bottom: 2rem;
    }

    .modern-form-group {
        margin-bottom: 1.25rem;
    }

    .modern-label {
        display: block;
        font-size: 0.85rem;
        font-weight: 600;
        color: #334155;
        margin-bottom: 0.5rem;
    }

    .modern-control {
        width: 100%;
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
        border: 1.5px solid #cbd5e1;
        border-radius: 8px;
        background-color: #f8fafc;
        transition: all 0.2s ease-in-out;
        box-sizing: border-box;
    }

    .modern-control:focus {
        outline: none;
        border-color: #2563eb;
        background-color: #ffffff;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
    }

    .modern-btn {
        display: block;
        width: 100%;
        padding: 0.8rem 1rem;
        font-size: 1rem;
        font-weight: 600;
        text-align: center;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.2s, transform 0.1s;
        box-sizing: border-box;
        margin-top: 1.5rem;
    }

    .modern-btn-primary {
        background-color: #2563eb;
        color: #ffffff;
    }

    .modern-btn-primary:hover {
        background-color: #1d4ed8;
    }

    .modern-btn-dark {
        background-color: #0f172a;
        color: #ffffff;
    }

    .modern-btn-dark:hover {
        background-color: #1e293b;
    }

    .modern-btn:active {
        transform: scale(0.98);
    }

    .modern-alert {
        padding: 1rem;
        border-radius: 8px;
        font-size: 0.9rem;
        margin-bottom: 1.5rem;
        font-weight: 500;
        text-align: center;
    }

    .modern-alert-success {
        background-color: #ecfdf5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }

    .modern-alert-error {
        background-color: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    .modern-footer-links {
        margin-top: 1.5rem;
        text-align: center;
        font-size: 0.9rem;
    }

    .modern-link {
        color: #2563eb;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.2s;
    }

    .modern-link:hover {
        color: #1d4ed8;
        text-decoration: underline;
    }

    .grid-2-cols {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    @media (max-width: 640px) {
        .grid-2-cols {
            grid-template-columns: 1fr;
        }
        .modern-card {
            padding: 1.5rem;
        }
    }
</style>

<div class="auth-wrapper">
    <div class="modern-card">
        
        <?= $mensaje_alerta; ?>

        <?php if ($accion === 'register'): ?>
            <!-- FORMULARIO DE REGISTRO DE CLIENTE -->
            <h2 class="modern-title">Crear Cuenta</h2>
            <p class="modern-subtitle">Únete a Ferretería del Constructor</p>
            
            <form action="<?= BASE_URL; ?>views/usuarios.php?action=register" method="POST">
                <input type="hidden" name="form_type" value="registro">
                
                <div class="grid-2-cols">
                    <div class="modern-form-group">
                        <label class="modern-label">Nombre</label>
                        <input type="text" name="nombre" class="modern-control" placeholder="Ej. Juan" required>
                    </div>
                    <div class="modern-form-group">
                        <label class="modern-label">Apellido</label>
                        <input type="text" name="apellido" class="modern-control" placeholder="Ej. Pérez" required>
                    </div>
                </div>

                <div class="modern-form-group">
                    <label class="modern-label">Correo Electrónico</label>
                    <input type="email" name="correo" class="modern-control" placeholder="nombre@correo.com" required>
                </div>

                <div class="modern-form-group">
                    <label class="modern-label">Contraseña</label>
                    <input type="password" name="password" class="modern-control" placeholder="••••••••" required>
                </div>

                <div class="modern-form-group">
                    <label class="modern-label">Teléfono</label>
                    <input type="text" name="telefono" class="modern-control" placeholder="5555-5555">
                </div>

                <div class="modern-form-group">
                    <label class="modern-label">Dirección</label>
                    <textarea name="direccion" class="modern-control" rows="2" placeholder="Ciudad, Zona, Dirección exacta"></textarea>
                </div>

                <button type="submit" class="modern-btn modern-btn-primary">Registrarse Ahora</button>
            </form>

            <div class="modern-footer-links">
                <p>¿Ya tienes cuenta? <a href="<?= BASE_URL; ?>views/usuarios.php?action=login" class="modern-link">Inicia sesión</a></p>
            </div>

        <?php elseif ($accion === 'recover'): ?>
            <!-- FORMULARIO DE RECUPERACIÓN DE CONTRASEÑA -->
            <h2 class="modern-title">Recuperar Acceso</h2>
            <p class="modern-subtitle">Te enviaremos instrucciones a tu correo</p>

            <form action="<?= BASE_URL; ?>views/usuarios.php?action=recover" method="POST">
                <input type="hidden" name="form_type" value="recuperar">
                
                <div class="modern-form-group">
                    <label class="modern-label">Correo Electrónico Registrado</label>
                    <input type="email" name="correo" class="modern-control" placeholder="nombre@correo.com" required>
                </div>

                <button type="submit" class="modern-btn modern-btn-primary">Enviar Instrucciones</button>
            </form>

            <div class="modern-footer-links">
                <p><a href="<?= BASE_URL; ?>views/usuarios.php?action=login" class="modern-link">← Volver al inicio de sesión</a></p>
            </div>

        <?php else: ?>
            <!-- FORMULARIO DE INICIO DE SESIÓN (POR DEFECTO) -->
            <h2 class="modern-title">Bienvenido</h2>
            <p class="modern-subtitle">Ingresa tus credenciales para continuar</p>

            <form action="<?= BASE_URL; ?>views/usuarios.php?action=login" method="POST">
                <input type="hidden" name="form_type" value="login">
                
                <div class="modern-form-group">
                    <label class="modern-label">Correo Electrónico</label>
                    <input type="email" name="correo" class="modern-control" placeholder="nombre@correo.com" required>
                </div>

                <div class="modern-form-group">
                    <label class="modern-label">Contraseña</label>
                    <input type="password" name="password" class="modern-control" placeholder="••••••••" required>
                </div>

                <div style="display: flex; justify-content: flex-end; margin-top: -0.5rem; margin-bottom: 0.5rem;">
                    <a href="<?= BASE_URL; ?>views/usuarios.php?action=recover" class="modern-link" style="font-size: 0.85rem;">¿Olvidaste tu contraseña?</a>
                </div>

                <button type="submit" class="modern-btn modern-btn-dark">Iniciar Sesión</button>
            </form>

            <div class="modern-footer-links">
                <p>¿No tienes una cuenta? <a href="<?= BASE_URL; ?>views/usuarios.php?action=register" class="modern-link">Regístrate aquí</a></p>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php 
include_once __DIR__ . '/layouts/footer.php'; 
?>