<?php
// views/admin/estadoDetallePedido.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/config.php';

// Validación de seguridad para administradores (ajustar según tu lógica de roles)
// if (!isset($_SESSION['id_usuario']) \vert{}\vert{}$_SESSION['rol'] !== 'admin') {
//     header("Location: " . BASE_URL . "views/usuarios.php?action=login");
//     exit();
// }

include_once __DIR__ . '/../layouts/header.php';
?>

<!-- Estilos CSS3 Modernos para el CRUD Administrativo -->
<style>
    .admin-wrapper {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 1rem;
        font-family: inherit;
    }

    .admin-header-flex {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .admin-header-title {
        font-size: 1.85rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
    }

    .admin-card {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        border: 1px solid #e2e8f0;
    }

    .modern-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .modern-table th {
        background-color: #f8fafc;
        color: #475569;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 1rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .modern-table td {
        padding: 1rem;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        vertical-align: middle;
        font-size: 0.95rem;
    }

    .badge-status {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 9999px;
        background-color: #e0f2fe;
        color: #0369a1;
    }

    .btn-modern {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
        font-weight: 600;
        border-radius: 8px;
        cursor: pointer;
        border: none;
        transition: background 0.2s, transform 0.1s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5s;
    }

    .btn-modern-primary {
        background-color: #2563eb;
        color: #ffffff;
    }

    .btn-modern-primary:hover {
        background-color: #1d4ed8;
    }

    .btn-modern-warning {
        background-color: #d97706;
        color: #ffffff;
        padding: 0.35rem 0.75rem;
        font-size: 0.85rem;
    }

    .btn-modern-warning:hover {
        background-color: #b45309;
    }

    .btn-modern-danger {
        background-color: #dc2626;
        color: #ffffff;
        padding: 0.35rem 0.75rem;
        font-size: 0.85rem;
    }

    .btn-modern-danger:hover {
        background-color: #b91c1c;
    }

    /* Modal Estilos Personalizados */
    .modal-backdrop-custom {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(15, 23, 42, 0.6);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1050;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.25s ease;
    }

    .modal-backdrop-custom.show {
        opacity: 1;
        pointer-events: auto;
    }

    .modal-card {
        background: #ffffff;
        width: 100%;
        max-width: 500px;
        border-radius: 12px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        transform: translateY(-20px);
        transition: transform 0.25s ease;
    }

    .modal-backdrop-custom.show .modal-card {
        transform: translateY(0);
    }

    .modal-header-custom {
        padding: 1.25rem 1.5rem;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-body-custom {
        padding: 1.5rem;
    }

    .form-group-custom {
        margin-bottom: 1.25rem;
    }

    .form-label-custom {
        display: block;
        font-size: 0.85rem;
        font-weight: 600;
        color: #475569;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
    }

    .form-control-custom {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 0.95rem;
        box-sizing: border-box;
        color: #1e293b;
        outline: none;
        transition: border-color 0.2s;
    }

    .form-control-custom:focus {
        border-color: #2563eb;
    }

    .modal-footer-custom {
        padding: 1rem 1.5rem;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
    }
</style>

<div class="admin-wrapper">
    <div class="admin-header-flex">
        <h1 class="admin-header-title">⚙️ Administración: Estados de Detalle de Pedido</h1>
        <button type="button" class="btn-modern btn-modern-primary" onclick="abrirModalCrear()">
            + Nuevo Estado
        </button>
    </div>

    <!-- Tabla de Registros -->
    <div class="admin-card">
        <div style="overflow-x: auto;">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre del Estado</th>
                        <th>Descripción</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla-estados-body">
                    <tr>
                        <td colspan="4" style="text-align: center; color: #94a3b8; padding: 2.5rem;">Cargando estados...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal CRUD (Crear / Editar) -->
<div id="modalEstado" class="modal-backdrop-custom">
    <div class="modal-card">
        <div class="modal-header-custom">
            <h3 id="modal-titulo" style="margin: 0; font-size: 1.2rem; color: #0f172a; font-weight: 700;">Crear Estado</h3>
            <button type="button" onclick="cerrarModal()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: #64748b;">✕</button>
        </div>
        <form id="formEstado" onsubmit="guardarEstado(event)">
            <div class="modal-body-custom">
                <input type="hidden" id="id_estado_detalle_pedido" name="id_estado_detalle_pedido">
                
                <div class="form-group-custom">
                    <label class="form-label-custom" for="nombre">Nombre del Estado</label>
                    <input type="text" id="nombre" name="nombre" class="form-control-custom" required placeholder="Ej. En Proceso, Entregado...">
                </div>

                <div class="form-group-custom">
                    <label class="form-label-custom" for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion" class="form-control-custom" rows="3" placeholder="Detalle breve del significado de este estado..."></textarea>
                </div>
            </div>
            <div class="modal-footer-custom">
                <type="button" class="btn-modern" style="background-color: #e2e8f0; color: #334155;" onclick="cerrarModal()">Cancelar</button>
                <button type="submit" class="btn-modern btn-modern-primary">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- Script de Control CRUD con Fetch API -->
<script>
    const API_URL = "<?= BASE_URL; ?>api/estadoDetallePedido.php"; // Asegúrate de tener este endpoint configurado en tu API

    document.addEventListener("DOMContentLoaded", () => {
        cargarEstados();
    });

    async function cargarEstados() {
        const tbody = document.getElementById("tabla-estados-body");
        try {
            const response = await fetch(API_URL);
            if (!response.ok) throw new Error("Error al obtener los datos de la API.");
            
            const result = await response.json();
            const estados = Array.isArray(result) ? result : (result.data || []);

            if (estados.length === 0) {
                tbody.innerHTML = `<tr><td colspan="4" style="text-align: center; color: #94a3b8; padding: 2.5rem;">No hay estados registrados.</td></tr>`;
                return;
            }

            let html = "";
            estados.forEach(item => {
                let id = item.id_estado_detalle_pedido || item.id;
                html += `
                    <tr>
                        <td style="font-weight: 600; color: #64748b;">#${id}</td>
                        <td><span class="badge-status">${item.nombre}</span></td>
                        <td style="color: #64748b;">${item.descripcion || 'Sin descripción'}</td>
                        <td style="text-align: right; white-space: nowrap;">
                            <button type="button" class="btn-modern btn-modern-warning" onclick='abrirModalEditar(${JSON.stringify(item)})'>✏️ Editar</button>
                            <button type="button" class="btn-modern btn-modern-danger" onclick="eliminarEstado(${id})">🗑️ Eliminar</button>
                        </td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
        } catch (error) {
            console.error(error);
            tbody.innerHTML = `<tr><td colspan="4" style="text-align: center; color: #dc2626; padding: 2.5rem;">Error al cargar la información del servidor.</td></tr>`;
        }
    }

    function abrirModalCrear() {
        document.getElementById("modal-titulo").textContent = "Crear Nuevo Estado";
        document.getElementById("formEstado").reset();
        document.getElementById("id_estado_detalle_pedido").value = "";
        document.getElementById("modalEstado").classList.add("show");
    }

    function abrirModalEditar(item) {
        document.getElementById("modal-titulo").textContent = "Editar Estado de Detalle";
        document.getElementById("id_estado_detalle_pedido").value = item.id_estado_detalle_pedido || item.id;
        document.getElementById("nombre").value = item.nombre || "";
        document.getElementById("descripcion").value = item.descripcion || "";
        document.getElementById("modalEstado").classList.add("show");
    }

    function cerrarModal() {
        document.getElementById("modalEstado").classList.remove("show");
    }

    async function guardarEstado(event) {
        event.preventDefault();
        const id = document.getElementById("id_estado_detalle_pedido").value;
        const nombre = document.getElementById("nombre").value;
        const descripcion = document.getElementById("descripcion").value;

        const method = id ? "PUT" : "POST";
        let payload = { nombre, descripcion };
        if (id) payload.id_estado_detalle_pedido = id;

        try {
            const response = await fetch(API_URL + (id ? `?id=${id}` : ''), {
                method: method,
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
            });

            const result = await response.json();
            if (response.ok) {
                alert(result.mensaje || "Operación realizada con éxito.");
                cerrarModal();
                cargarEstados();
            } else {
                alert(result.mensaje || "Ocurrió un error al procesar la solicitud.");
            }
        } catch (error) {
            console.error(error);
            alert("Error de conexión con el servidor.");
        }
    }

    async function eliminarEstado(id) {
        if (!confirm("¿Estás seguro de que deseas eliminar este estado?")) return;

        try {
            const response = await fetch(`${API_URL}?id=${id}`, {
                method: "DELETE"
            });
            const result = await response.json();

            if (response.ok) {
                alert(result.mensaje || "Registro eliminado correctamente.");
                cargarEstados();
            } else {
                alert(result.mensaje || "No se pudo eliminar el registro.");
            }
        } catch (error) {
            console.error(error);
            alert("Error de red al intentar eliminar.");
        }
    }
</script>

<?php 
include_once __DIR__ . '/../layouts/footer.php'; 
?>