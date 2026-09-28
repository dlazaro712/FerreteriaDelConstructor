<?php
// views/admin/pedidos.php
// Forzar reporte de errores para depuración
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
@ini_set('html_errors', '1');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validación de seguridad estricta corregida con operadores lógicos normales
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'admin') {
    header("Location: " . BASE_URL . "views/usuarios.php?action=login");
    exit();
}

// Inclusión de archivos de configuración, base de datos y controlador
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../controllers/PedidosController.php';

$mensaje = '';$error = '';

// Instanciar el controlador de pedidos
try {
    $pedidosController = new PedidosController();
} catch (Exception $e) {$pedidosController = null;
    $error = "Error al inicializar el controlador de pedidos: " . $e->getMessage();
}

// Procesar peticiones por POST (Crear pedido o Actualizar estado)
if ($_SERVER['REQUEST_METHOD'] === 'POST' &&$pedidosController) {
    $accion =$_POST['accion'] ?? '';
    
    // 1. Crear un nuevo pedido
    if ($accion === 'crear_pedido') {
        $id_usuario = intval($_POST['id_usuario'] ?? 0);
        $totalPedido = floatval($_POST['total'] ?? 0);
        $estadoPedido = trim($_POST['estado'] ?? 'Pendiente');

        $datosPedido = [
            'id_usuario' => $id_usuario,
            'total' => $totalPedido,
            'estado' => $estadoPedido
        ];

        // Ítems de respaldo para la creación administrativa
        $itemsCarrito = [
            ['id_producto' => 1, 'cantidad' => 1, 'precio' => $totalPedido]
        ];

        if ($id_usuario <= 0 || $totalPedido <= 0) {$error = "Debe proporcionar un ID de usuario válido y un total mayor a 0.";
        } else {
            $resultado =$pedidosController->crearPedido($datosPedido,$itemsCarrito);
            
            if (isset($resultado['status']) &&$resultado['status'] === 'success') {
                $mensaje = ($resultado['mensaje'] ?? 'Pedido creado exitosamente.') . " (ID: #" . ($resultado['id_pedido'] ?? '') . ")";
            } else {
                $error =$resultado['mensaje'] ?? 'No se pudo crear el pedido.';
            }
        }
    }
    
    // 2. Actualizar estado del pedido
    if ($accion === 'actualizar_estado') {
        $id_pedido = intval($_POST['id_pedido'] ?? 0);
        $estado = trim($_POST['estado'] ?? '');

        // Validar que el estado enviado pertenezca a los permitidos
        $estadosPermitidos = ['Pendiente', 'Procesando', 'Completado', 'Cancelado'];

        if ($id_pedido <= 0 || !in_array($estado, $estadosPermitidos, true)) {
            $error = "Debe proporcionar un ID de pedido válido y un estado permitido.";
        } else {
            $resultado = $pedidosController->actualizarEstadoPedido($id_pedido, $estado);
            
            // Verificamos si el resultado es true (booleano) o un arreglo con status success
            if ($resultado === true || (is_array($resultado) && isset($resultado['status']) && $resultado['status'] === 'success')) {
                $mensaje = is_array($resultado) ? ($resultado['message'] ?? 'Estado actualizado correctamente.') : 'Estado actualizado correctamente.';
            } else {
                $error = is_array($resultado) ? ($resultado['message'] ?? 'No se pudo actualizar el estado.') : 'No se pudo actualizar el estado.';
            }
        }
    }
}

// Procesar eliminación de pedido por GET
if (isset($_GET['accion']) && $_GET['accion'] === 'eliminar' && isset($_GET['id']) && $pedidosController) {$id_pedido_eliminar = intval($_GET['id']);$resultado = $pedidosController->eliminarPedido($id_pedido_eliminar);
    
    if (isset($resultado['status']) &&$resultado['status'] === 'success') {
        $mensaje =$resultado['message'] ?? 'Pedido eliminado correctamente.';
    } else {
        $error =$resultado['message'] ?? 'No se pudo eliminar el pedido.';
    }
}

// Obtener la lista completa de pedidos para el panel administrativo
$pedidos = $pedidosController ? $pedidosController->listarPedidos() : [];

// Incluir el encabezado centralizado
include_once __DIR__ . '/../layouts/header.php';
?>

<div class="admin-dashboard" style="max-width: 1200px; margin: 0 auto; padding: 1rem;">
    
    <!-- Encabezado de la Sección -->
    <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="color: #0f172a; margin: 0 0 0.5rem 0; font-size: 1.75rem;">Panel de Administración: Gestión de Pedidos</h1>
            <p style="color: #64748b; margin: 0;">Supervisa las compras de los usuarios, registra nuevas órdenes y actualiza su estado.</p>
        </div>
        <button type="button" onclick="abrirModalCrearPedido()" style="padding: 0.75rem 1.25rem; background: #2563eb; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.9rem;">
            ➕ Nuevo Pedido
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

    <!-- Tabla de Listado de Pedidos -->
    <div style="background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
        <div style="padding: 1.5rem; border-bottom: 1px solid #e2e8f0;">
            <h2 style="margin: 0; font-size: 1.25rem; color: #0f172a;">Listado General de Pedidos</h2>
        </div>

        <?php if (!empty($pedidos)): ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                    <thead>
                        <tr style="background: #f8fafc; color: #475569; border-bottom: 1px solid #e2e8f0;">
                            <th style="padding: 1rem; width: 80px;">ID</th>
                            <th style="padding: 1rem;">Cliente</th>
                            <th style="padding: 1rem;">Fecha</th>
                            <th style="padding: 1rem;">Total</th>
                            <th style="padding: 1rem;">Estado</th>
                            <th style="padding: 1rem; text-align: center; width: 220px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pedidos as$ped): ?>
                            <?php 
                                $badgeBg = '#f1f5f9';
                                $badgeColor = '#475569';
                                $estadoPedido =$ped['estado'] ?? 'Pendiente';
                                switch ($estadoPedido) {
                                    case 'Completado':
                                        $badgeBg = '#ecfdf5'; $badgeColor = '#065f46'; break;
                                    case 'Procesando':
                                        $badgeBg = '#eff6ff'; $badgeColor = '#1e40af'; break;
                                    case 'Pendiente':
                                        $badgeBg = '#fffbeb'; $badgeColor = '#b45309'; break;
                                    case 'Cancelado':
                                        $badgeBg = '#fef2f2'; $badgeColor = '#991b1b'; break;
                                }
                            ?>
                            <tr style="border-bottom: 1px solid #e2e8f0;">
                                <td style="padding: 1rem; color: #64748b; font-weight: 600;">#<?= htmlspecialchars($ped['id_pedido'] ?? ''); ?></td>
                                <td style="padding: 1rem;">
                                    <div style="font-weight: 600; color: #0f172a;"><?= htmlspecialchars($ped['cliente'] ?? 'Usuario Desconocido'); ?></div>
                                    <div style="font-size: 0.8rem; color: #64748b;"><?= htmlspecialchars($ped['correo'] ?? ''); ?></div>
                                </td>
                                <td style="padding: 1rem; color: #475569;"><?= htmlspecialchars($ped['fecha'] ?? ''); ?></td>
                                <td style="padding: 1rem; color: #2563eb; font-weight: bold;">Q.<?= number_format($ped['total'] ?? 0, 2); ?></td>
                                <td style="padding: 1rem;">
                                    <span style="padding: 0.25rem 0.6rem; border-radius: 4px; font-size: 0.8rem; font-weight: 600; background: <?= $badgeBg; ?>; color: <?=$badgeColor; ?>;">
                                        <?= htmlspecialchars($estadoPedido); ?>
                                    </span>
                                </td>
                                <td style="padding: 1rem; text-align: center; display: flex; justify-content: center; gap: 0.5rem; align-items: center;">
                                    <button type="button" 
                                            onclick='abrirModalEstado(<?= json_encode($ped); ?>)' 
                                            style="padding: 0.4rem 0.8rem; background: #d97706; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.85rem;" title="Cambiar Estado">
                                        ✏️ Estado
                                    </button>
                                    <a href="<?= BASE_URL; ?>views/admin/pedidos.php?accion=eliminar&id=<?= $ped['id_pedido']; ?>" 
                                       onclick="return confirm('¿Estás seguro de eliminar este pedido del sistema?');" 
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
                No hay pedidos registrados en el sistema actualmente.
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- Modal para Crear Nuevo Pedido (Admin) -->
<div id="modal-crear-pedido" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 1000;">
    <div style="background: white; padding: 2rem; border-radius: 8px; width: 100%; max-width: 500px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin: 1rem;">
        <h3 style="margin-top: 0; color: #0f172a; font-size: 1.25rem; margin-bottom: 0.5rem;">Registrar Nuevo Pedido</h3>
        <p style="color: #64748b; font-size: 0.85rem; margin-bottom: 1.5rem;">Ingrese los detalles de la orden para registrarla a nombre de un usuario.</p>
        
        <form action="<?= BASE_URL; ?>views/admin/pedidos.php" method="POST" style="display: flex; flex-direction: column; gap: 1rem;">
            <input type="hidden" name="accion" value="crear_pedido">
            
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.3rem;">ID del Cliente:</label>
                <input type="number" name="id_usuario" required placeholder="Ej. 2" style="width: 100%; padding: 0.65rem; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box;">
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.3rem;">Total (Q):</label>
                <input type="number" step="0.01" name="total" required placeholder="Ej. 150.00" style="width: 100%; padding: 0.65rem; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box;">
            </div>

            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.3rem;">Estado del Pedido:</label>
                <select name="estado" required style="width: 100%; padding: 0.65rem; border: 1px solid #d1d5db; border-radius: 6px; background: white; box-sizing: border-box;">
                    <option value="Pendiente">Pendiente</option>
                    <option value="Procesando">Procesando</option>
                    <option value="Completado">Completado</option>
                    <option value="Cancelado">Cancelado</option>
                </select>
            </div>

            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1rem;">
                <button type="button" onclick="cerrarModalCrearPedido()" style="padding: 0.6rem 1rem; background: #e2e8f0; color: #475569; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">Cancelar</button>
                <button type="submit" style="padding: 0.6rem 1.5rem; background: #2563eb; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">Guardar Pedido</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal para Cambiar Estado del Pedido -->
<div id="modal-estado" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 1000;">
    <div style="background: white; padding: 2rem; border-radius: 8px; width: 100%; max-width: 450px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin: 1rem;">
        <h3 style="margin-top: 0; color: #0f172a; font-size: 1.25rem; margin-bottom: 0.5rem;">Actualizar Estado del Pedido</h3>
        <p id="modal-pedido-info" style="color: #64748b; font-size: 0.85rem; margin-bottom: 1.5rem;"></p>
        
        <form action="<?= BASE_URL; ?>views/admin/pedidos.php" method="POST" style="display: flex; flex-direction: column; gap: 1rem;">
            <input type="hidden" name="accion" value="actualizar_estado">
            <input type="hidden" name="id_pedido" id="edit-pedido-id">
            
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.3rem;">Nuevo Estado:</label>
                <select name="estado" id="edit-pedido-estado" required style="width: 100%; padding: 0.65rem; border: 1px solid #d1d5db; border-radius: 6px; background: white; box-sizing: border-box;">
                    <option value="Pendiente">Pendiente</option>
                    <option value="Procesando">Procesando</option>
                    <option value="Completado">Completado</option>
                    <option value="Cancelado">Cancelado</option>
                </select>
            </div>

            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1rem;">
                <button type="button" onclick="cerrarModalEstado()" style="padding: 0.6rem 1rem; background: #e2e8f0; color: #475569; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">Cancelar</button>
                <button type="submit" style="padding: 0.6rem 1.5rem; background: #2563eb; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- Scripts de control para las ventanas modales -->
<script>
function abrirModalCrearPedido() {
    document.getElementById('modal-crear-pedido').style.display = 'flex';
}

function cerrarModalCrearPedido() {
    document.getElementById('modal-crear-pedido').style.display = 'none';
}

function abrirModalEstado(pedido) {
    document.getElementById('edit-pedido-id').value = pedido.id_pedido;
    document.getElementById('edit-pedido-estado').value = pedido.estado;
    document.getElementById('modal-pedido-info').innerText = `Pedido #${pedido.id_pedido} - Cliente: ${pedido.cliente || 'N/A'}`;
    
    document.getElementById('modal-estado').style.display = 'flex';
}

function cerrarModalEstado() {
    document.getElementById('modal-estado').style.display = 'none';
}
</script>

<?php 
// Incluir el pie de página centralizado
include_once __DIR__ . '/../layouts/footer.php'; 
?>