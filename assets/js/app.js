/**
 * assets/js/app.js
 * Módulo de cliente principal: Persistencia Híbrida y Servicios API
 * Proyecto: Ferretería del Constructor
 * Nota: Reutiliza BASE_URL y API_BASE_URL definidas globalmente en api.js
 */

// ==========================================
// 1. SERVICIO API CLIENTE (ApiService)
// ==========================================
window.ApiService = {
    productos: {
        listar: async () => {
            try {
                const response = await fetch(`${window.API_BASE_URL}productos.php`);
                if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);
                const result = await response.json();
                return result.data || result;
            } catch (error) {
                console.error("Error al listar productos:", error);
                return [];
            }
        },
        obtenerPorId: async (id) => {
            try {
                const response = await fetch(`${window.API_BASE_URL}productos.php?id=${id}`);
                if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);
                const result = await response.json();
                return result.data || result;
            } catch (error) {
                console.error(`Error al obtener producto ID ${id}:`, error);
                return null;
            }
        },
        crear: async (dataProducto) => {
            try {
                const response = await fetch(`${window.API_BASE_URL}productos.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(dataProducto)
                });
                return await response.json();
            } catch (error) {
                console.error("Error al crear producto:", error);
                return { status: "error", message: error.message };
            }
        }
    },
    pedidos: {
        crear: async (dataPedido) => {
            try {
                const response = await fetch(`${window.API_BASE_URL}pedidos.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(dataPedido)
                });
                return await response.json();
            } catch (error) {
                console.error("Error al registrar el pedido:", error);
                return { status: "error", mensaje: error.message };
            }
        }
    }
};

window.ApiService = ApiService;

// ==========================================
// 2. FUNCIONES GLOBALES DEL CARRITO DE COMPRAS
// ==========================================

/**
 * Sincroniza un nuevo ítem individual al servidor vía API
 */
async function sincronizarCarritoBD(item) {
    try {
        const respuesta = await fetch(`${window.BASE_URL}views/clientes/carrito.php`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json"
            },
            body: JSON.stringify({
                accion: "agregar_item_individual",
                item_key: item.item_key,
                id_producto: item.id_producto,
                cantidad: item.cantidad,
                precio: item.precio
            })
        });

        if (!respuesta.ok) {
            console.warn(`Sincronización con BD devolvió un estado no correcto: ${respuesta.status}`);
        }
    } catch (error) {
        console.warn("No se pudo sincronizar con la Base de Datos. Persistencia mantenida en LocalStorage.", error);
    }
}

/**
 * Agrega un producto como un registro INDIVIDUAL utilizando LocalStorage y sincronización asíncrona.
 */
window.agregarAlCarrito = function(producto, mostrarAlerta = true) {
    if (!producto) return false;

    const idproducto = producto.id_producto ?? producto.id;
    const idValido = String(idproducto ?? "").trim();

    if (!idValido || idValido === "0" || idValido === "undefined" || idValido === "null") {
        console.error("Error al agregar al carrito: ID de producto no válido.", producto);
        alert("Ocurrió un error al identificar el producto seleccionado.");
        return false;
    }

    const nombreProd = producto.nombre || producto.nombre_producto || "Producto sin nombre";
    const precioProd = parseFloat(producto.precio) || 0;
    const stockMax = parseInt(producto.stockMax || producto.cantidad || producto.stock || 50, 10);
    const imagenProd = producto.imagen ? producto.imagen.trim() : '';

    let carrito = JSON.parse(localStorage.getItem("carrito_ferreteria")) || [];

    const unidadesEnCarrito = carrito
        .filter(item => String(item.id_producto).trim() === idValido)
        .reduce((sum, item) => sum + (parseInt(item.cantidad, 10) || 1), 0);

    if (unidadesEnCarrito + 1 > stockMax) {
        alert(`No puedes agregar más unidades. Límite de stock alcanzado (${stockMax} disponibles).`);
        return false;
    }

    const nuevoRegistro = {
        item_key: 'item_' + Date.now() + '_' + Math.random().toString(36).substring(2, 7),
        id_producto: idValido,
        nombre: nombreProd,
        precio: precioProd,
        imagen: imagenProd,
        cantidad: 1,
        stockMax: stockMax
    };

    carrito.push(nuevoRegistro);
    localStorage.setItem("carrito_ferreteria", JSON.stringify(carrito));

    sincronizarCarritoBD(nuevoRegistro);

    window.actualizarContadorCarrito();

    if (mostrarAlerta) {
        alert(`¡"${nombreProd}" agregado al carrito de compras!`);
    }

    return true;
};

/**
 * Actualiza el contador/badge visual del carrito en el header
 */
window.actualizarContadorCarrito = function() {
    const carrito = JSON.parse(localStorage.getItem("carrito_ferreteria")) || [];
    const totalItems = carrito.reduce((acc, item) => acc + parseInt(item.cantidad || 0, 10), 0);

    const badge = document.getElementById("cart-count") || document.querySelector(".cart-badge") || document.getElementById("contador-carrito");
    if (badge) {
        badge.textContent = totalItems;
        badge.style.display = totalItems > 0 ? "inline-block" : "none";
    }
};

/**
 * Función global optimizada para procesar el checkout enviando los datos al controlador de pedidos
 */
window.procesarCheckoutPedido = function() {
    let carrito = JSON.parse(localStorage.getItem("carrito_ferreteria")) || [];
    
    if (!carrito || carrito.length === 0) {
        alert("El carrito está vacío. Agrega productos antes de continuar.");
        return;
    }

    // Crear formulario dinámico POST de manera segura
    const form = document.createElement("form");
    form.method = "POST";
    form.action = `${window.BASE_URL}views/clientes/pedidos.php`;

    const inputAccion = document.createElement("input");
    inputAccion.type = "hidden";
    inputAccion.name = "accion";
    inputAccion.value = "registrar_pedido";

    const inputCarrito = document.createElement("input");
    inputCarrito.type = "hidden";
    inputCarrito.name = "carrito_data";
    inputCarrito.value = JSON.stringify(carrito);

    form.appendChild(inputAccion);
    form.appendChild(inputCarrito);
    document.body.appendChild(form);

    form.submit();
};

/**
 * Mueve un elemento desde ListaDeseos al Carrito
 */
window.moverWishlistACarrito = function(producto, idWishlist) {
    const agregado = window.agregarAlCarrito(producto, false);
    if (agregado) {
        const form = document.createElement("form");
        form.method = "POST";
        form.action = "ListaDeseos.php";

        const inputAccion = document.createElement("input");
        inputAccion.type = "hidden";
        inputAccion.name = "accion";
        inputAccion.value = "eliminar";

        const inputIdWishlist = document.createElement("input");
        inputIdWishlist.type = "hidden";
        inputIdWishlist.name = "id_wishlist";
        inputIdWishlist.value = idWishlist;

        form.appendChild(inputAccion);
        form.appendChild(inputIdWishlist);
        document.body.appendChild(form);
        form.submit();
    }
};

// Funciones específicas para la gestión de la vista del carrito
document.addEventListener("DOMContentLoaded", () => {
    if (document.getElementById("tabla-carrito-body")) {
        renderizarTablaCarrito();
    }
});

function renderizarTablaCarrito() {
    const tbody = document.getElementById("tabla-carrito-body");
    const spanTotal = document.getElementById("total-general");
    const spanTotalFinal = document.getElementById("total-general-final");
    
    let carrito = JSON.parse(localStorage.getItem("carrito_ferreteria")) || [];

    if (!tbody) return;

    if (carrito.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" style="text-align: center; color: #94a3b8; padding: 2.5rem;">Tu carrito está vacío.</td></tr>`;
        if (spanTotal) spanTotal.textContent = "Q.0.00";
        if (spanTotalFinal) spanTotalFinal.textContent = "Q.0.00";
        return;
    }

    let html = "";
    let acumuladoTotal = 0;

    carrito.forEach((item, index) => {
        let subtotal = parseFloat(item.precio) * parseInt(item.cantidad);
        acumuladoTotal += subtotal;

        let imgFile = item.imagen ? item.imagen.trim() : '';
        let imagenSrc = imgFile !== '' ? `${window.BASE_URL}assets/imgs/${imgFile}` : `${window.BASE_URL}assets/imgs/Taladro-Atornillador-Inalambrico.png`;

        html += `
            <tr>
                <td>
                    <div class="cart-product-cell">
                        <img src="${imagenSrc}" alt="${item.nombre}" class="cart-product-img" onerror="this.onerror=null; this.src='${window.BASE_URL}assets/imgs/Taladro-Atornillador-Inalambrico.png';">
                        <span style="font-weight: 600; color: #1e293b;">${item.nombre}</span>
                    </div>
                </td>
                <td>Q.${parseFloat(item.precio).toFixed(2)}</td>
                <td>
                    <div class="qty-input-group">
                        <button type="button" class="qty-control-btn" onclick="cambiarCantidadCarrito(${index}, -1)">-</button>
                        <span class="qty-display">${item.cantidad}</span>
                        <button type="button" class="qty-control-btn" onclick="cambiarCantidadCarrito(${index}, 1)">+</button>
                    </div>
                </td>
                <td style="font-weight: 600; color: #0f172a;">Q.${subtotal.toFixed(2)}</td>
                <td style="text-align: right;">
                    <button type="button" class="btn-delete-item" onclick="eliminarItemCarrito(${index})" title="Eliminar producto">🗑️</button>
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
    if (spanTotal) spanTotal.textContent = `Q.${acumuladoTotal.toFixed(2)}`;
    if (spanTotalFinal) spanTotalFinal.textContent = `Q.${acumuladoTotal.toFixed(2)}`;
}

function cambiarCantidadCarrito(index, delta) {
    let carrito = JSON.parse(localStorage.getItem("carrito_ferreteria")) || [];
    if (carrito[index]) {
        let nuevaCantidad = carrito[index].cantidad + delta;
        let stockMax = carrito[index].stockMax || 50;

        if (nuevaCantidad > 0 && nuevaCantidad <= stockMax) {
            carrito[index].cantidad = nuevaCantidad;
            localStorage.setItem("carrito_ferreteria", JSON.stringify(carrito));
            renderizarTablaCarrito();
            if (typeof actualizarContadorCarrito === 'function') actualizarContadorCarrito();
        } else if (nuevaCantidad > stockMax) {
            alert("Has alcanzado el límite máximo de stock disponible.");
        }
    }
}

function eliminarItemCarrito(index) {
    let carrito = JSON.parse(localStorage.getItem("carrito_ferreteria")) || [];
    carrito.splice(index, 1);
    localStorage.setItem("carrito_ferreteria", JSON.stringify(carrito));
    renderizarTablaCarrito();
    if (typeof actualizarContadorCarrito === 'function') actualizarContadorCarrito();
}

function vaciarCarrito() {
    if (confirm("¿Estás seguro de que deseas vaciar todo el carrito?")) {
        localStorage.removeItem("carrito_ferreteria");
        renderizarTablaCarrito();
        if (typeof actualizarContadorCarrito === 'function') actualizarContadorCarrito();
    }
}

// ==========================================
// 3. INICIALIZACIÓN Y EVENTOS DEL DOM
// ==========================================
document.addEventListener("DOMContentLoaded", () => {
    const contenedorProductos = document.getElementById("contenedor-productos");
    const inputBusqueda = document.getElementById("busqueda");
    const selectCategoria = document.getElementById("filtro-categoria");
    const inputPrecioMax = document.getElementById("filtro-precio");
    const spanPrecioMaxVal = document.getElementById("precio-max-val");

    let listaProductos = [];

    async function cargarDatosIniciales() {
        if (!contenedorProductos) {
            window.actualizarContadorCarrito();
            return;
        }

        try {
            const respuestaProd = await fetch(`${window.API_BASE_URL}productos.php`);
            if (!respuestaProd.ok) throw new Error(`HTTP ${respuestaProd.status}: No se pudo obtener el catálogo.`);
            
            listaProductos = await respuestaProd.json();
            renderizarProductos(listaProductos);
        } catch (error) {
            console.warn("No se pudo cargar el catálogo dinámico desde la API:", error);
            if (contenedorProductos) {
                contenedorProductos.innerHTML = `
                    <div style="grid-column: 1/-1; text-align: center; color: var(--danger, #ef4444); padding: 2rem;">
                        <p>Catálogo servido desde la Base de Datos mediante el servidor local.</p>
                    </div>`;
            }
        } finally {
            window.actualizarContadorCarrito();
        }
    }

    function renderizarProductos(productos) {
        if (!contenedorProductos) return;
        contenedorProductos.innerHTML = "";

        if (!productos || productos.length === 0) {
            contenedorProductos.innerHTML = `
                <div style="grid-column: 1/-1; text-align: center; padding: 2rem;">
                    <p>No se encontraron productos con los criterios seleccionados.</p>
                </div>`;
            return;
        }

        productos.forEach(prod => {
            const card = document.createElement("div");
            card.className = "card-producto";

            const stockDisponible = parseInt(prod.cantidad || prod.stock || 0, 10);

            card.innerHTML = `
                <div class="card-body">
                    <span class="badge-cat">${prod.categoria || 'General'}</span>
                    <h3 class="card-title">${prod.nombre}</h3>
                    <p class="card-precio">Q.${parseFloat(prod.precio).toFixed(2)}</p>
                    <p style="font-size:0.85rem; color: var(--gray, #9ca3af); margin-bottom: 0.5rem;">
                        ${stockDisponible > 0 ? 'Disponibles: ' + stockDisponible : '<span style="color:red;">Agotado</span>'}
                    </p>
                    <div style="display:flex; gap:0.5rem; margin-top:auto; flex-wrap:wrap;">
                        <a href="views/clientes/productos.php?id=${prod.id_producto}" class="btn btn-outline" style="flex:1; text-align:center;">Detalles</a>
                        <a href="views/clientes/ListaDeseos.php?id_producto=${prod.id_producto}" class="btn btn-outline btn-guardar" style="flex:1; text-align:center; text-decoration:none;" title="Guardar en lista de deseos">Guardar</a>
                        <button class="btn btn-agregar" data-id="${prod.id_producto}" style="flex:100%;" ${stockDisponible <= 0 ? 'disabled style="opacity:0.5; cursor:not-allowed;"' : ''}>Comprar</button>
                    </div>
                </div>
            `;
            contenedorProductos.appendChild(card);
        });

        activarBotonesAgregar();
    }

    function aplicarFiltrosYBusqueda() {
        const textoBusqueda = inputBusqueda ? inputBusqueda.value.toLowerCase().trim() : "";
        const categoriaSeleccionada = selectCategoria ? selectCategoria.value : "";
        const precioMaximo = inputPrecioMax ? parseFloat(inputPrecioMax.value) : Infinity;

        if (spanPrecioMaxVal && inputPrecioMax) {
            spanPrecioMaxVal.textContent = `Q.${inputPrecioMax.value}`;
        }

        const productosFiltrados = listaProductos.filter(prod => {
            const coincideTexto = prod.nombre.toLowerCase().includes(textoBusqueda) || 
                                  (prod.descripcion && prod.descripcion.toLowerCase().includes(textoBusqueda)) ||
                                  (prod.categoria && prod.categoria.toLowerCase().includes(textoBusqueda));
            
            const coincideCategoria = categoriaSeleccionada === "" || prod.id_categoria == categoriaSeleccionada;
            const coincidePrecio = parseFloat(prod.precio) <= precioMaximo;

            return coincideTexto && coincideCategoria && coincidePrecio;
        });

        renderizarProductos(productosFiltrados);
    }

    if (inputBusqueda) inputBusqueda.addEventListener("input", aplicarFiltrosYBusqueda);
    if (selectCategoria) selectCategoria.addEventListener("change", aplicarFiltrosYBusqueda);
    if (inputPrecioMax) inputPrecioMax.addEventListener("input", aplicarFiltrosYBusqueda);

    function activarBotonesAgregar() {
        document.querySelectorAll(".btn-agregar").forEach(btn => {
            btn.addEventListener("click", (e) => {
                const idProd = e.target.getAttribute("data-id");
                const productoEncontrado = listaProductos.find(p => p.id_producto == idProd);
                if (productoEncontrado) {
                    window.agregarAlCarrito(productoEncontrado);
                }
            });
        });
    }

    // Inicializar carga de datos
    cargarDatosIniciales();
});