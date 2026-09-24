/**
 * Lógica del Carrito de Compras - Ferretería del Constructor
 * Maneja el almacenamiento local (localStorage), actualización de interfaz y contadores.
 */

document.addEventListener('DOMContentLoaded', () => {
    actualizarContadorCarrito();
    
    // Si estamos en la página del carrito, renderizar la vista detallada
    if (document.getElementById('tabla-carrito-body')) {
        renderizarCarrito();
    }
});

/**
 * Obtiene los elementos actuales del carrito desde localStorage
 */
function obtenerCarrito() {
    let carrito = localStorage.getItem('ferreteria_carrito');
    return carrito ? JSON.parse(carrito) : [];
}

/**
 * Guarda los elementos en localStorage
 */
function guardarCarrito(carrito) {
    localStorage.setItem('ferreteria_carrito', JSON.stringify(carrito));
    actualizarContadorCarrito();
}

/**
 * Agrega un producto al carrito o incrementa su cantidad si ya existe
 * @param {Object} producto - { id, nombre, precio, imagen }
 */
function agregarAlCarrito(producto) {
    let carrito = obtenerCarrito();
    let index = carrito.findIndex(item => item.id == producto.id);

    if (index !== -1) {
        carrito[index].cantidad += (producto.cantidad ? parseInt(producto.cantidad) : 1);
    } else {
        carrito.push({
            id: producto.id,
            nombre: producto.nombre,
            precio: parseFloat(producto.precio),
            imagen: producto.imagen || '',
            cantidad: producto.cantidad ? parseInt(producto.cantidad) : 1
        });
    }

    guardarCarrito(carrito);
    
    // Mostrar notificación visual simple
    alert(`¡"${producto.nombre}" se ha agregado al carrito!`);
}

/**
 * Actualiza el número total de productos en la insignia del header
 */
function actualizarContadorCarrito() {
    let carrito = obtenerCarrito();
    let totalItems = carrito.reduce((sum, item) => sum + item.cantidad, 0);
    
    let contadorBadge = document.getElementById('contador-carrito');
    if (contadorBadge) {
        contadorBadge.textContent = totalItems;
    }
}

/**
 * Modifica la cantidad de un producto específico en el carrito
 */
function cambiarCantidad(id, nuevaCantidad) {
    let carrito = obtenerCarrito();
    let index = carrito.findIndex(item => item.id == id);

    if (index !== -1) {
        nuevaCantidad = parseInt(nuevaCantidad);
        if (nuevaCantidad > 0) {
            carrito[index].cantidad = nuevaCantidad;
        } else {
            carrito.splice(index, 1);
        }
        guardarCarrito(carrito);
        renderizarCarrito();
    }
}

/**
 * Elimina un producto por completo del carrito
 */
function eliminarDelCarrito(id) {
    let carrito = obtenerCarrito();
    carrito = carrito.filter(item => item.id != id);
    guardarCarrito(carrito);
    renderizarCarrito();
}

/**
 * Vacía todo el contenido del carrito
 */
function vaciarCarrito() {
    if (confirm('¿Estás seguro de vaciar todo el carrito de compras?')) {
        localStorage.removeItem('ferreteria_carrito');
        actualizarContadorCarrito();
        renderizarCarrito();
    }
}

/**
 * Renderiza la tabla y resumen de costos en la vista del carrito (/views/carrito.php)
 */
function renderizarCarrito() {
    let carrito = obtenerCarrito();
    let tbody = document.getElementById('tabla-carrito-body');
    let resumenContainer = document.getElementById('resumen-carrito-container');
    let totalGeneralElement = document.getElementById('total-general');

    if (!tbody) return;

    if (carrito.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" style="padding: 2rem; text-align: center; color: #64748b;">Tu carrito está vacío. ¡Explora nuestros productos y añade herramientas o materiales!</td></tr>`;
        if (resumenContainer) resumenContainer.style.display = 'none';
        if (totalGeneralElement) totalGeneralElement.textContent = 'Q.0.00';
        return;
    }

    if (resumenContainer) resumenContainer.style.display = 'block';
    let html = '';
    let subtotalGeneral = 0;

    carrito.forEach(item => {
        let subtotalItem = item.precio * item.cantidad;
        subtotalGeneral += subtotalItem;

        html += `
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 1rem; display: flex; align-items: center; gap: 1rem;">
                    <span style="font-weight: 500; color: #1e293b;">${item.nombre}</span>
                </td>
                <td style="padding: 1rem; color: #0284c7; font-weight: 600;">Q.${item.precio.toFixed(2)}</td>
                <td style="padding: 1rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <button onclick="cambiarCantidad(${item.id}, ${item.cantidad - 1})" style="background: #e2e8f0; border: none; width: 25px; height: 25px; border-radius: 4px; cursor: pointer; font-weight: bold;">-</button>
                        <span style="min-width: 20px; text-align: center;">${item.cantidad}</span>
                        <button onclick="cambiarCantidad(${item.id}, ${item.cantidad + 1})" style="background: #e2e8f0; border: none; width: 25px; height: 25px; border-radius: 4px; cursor: pointer; font-weight: bold;">+</button>
                    </div>
                </td>
                <td style="padding: 1rem; font-weight: bold; color: #0f172a;">Q.${subtotalItem.toFixed(2)}</td>
                <td style="padding: 1rem; text-align: right;">
                    <button onclick="eliminarDelCarrito(${item.id})" style="background: #ef4444; color: white; border: none; padding: 0.3rem 0.6rem; border-radius: 4px; font-size: 0.8rem; cursor: pointer;">Eliminar</button>
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
    if (totalGeneralElement) {
        totalGeneralElement.textContent = `Q.${subtotalGeneral.toFixed(2)}`;
    }
}