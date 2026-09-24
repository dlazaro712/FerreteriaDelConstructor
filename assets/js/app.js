document.addEventListener("DOMContentLoaded", () => {
    // Selectores del DOM
    const contenedorProductos = document.getElementById("contenedor-productos");
    const inputBusqueda = document.getElementById("busqueda");
    const selectCategoria = document.getElementById("filtro-categoria");
    const inputPrecioMax = document.getElementById("filtro-precio");
    const spanPrecioMaxVal = document.getElementById("precio-max-val");
    const contadorCarrito = document.getElementById("contador-carrito");
    const formContacto = document.getElementById("form-contacto"); // Opcional para validaciones

    let listaProductos = [];

    // ==========================================
    // 1. CONSUMO DE API Y SOLICITUDES FETCH()
    // ==========================================
    async function cargarDatosIniciales() {
        try {
            // Cargar productos desde la API REST
            const respuestaProd = await fetch("http://localhost:8080/ferreteriaDelConstructor/api/productos");
            if (!respuestaProd.ok) throw new Error("Error al obtener los productos de la API.");
            listaProductos = await respuestaProd.json();

            renderizarProductos(listaProductos);
            actualizarContadorCarrito();
        } catch (error) {
            console.error("Fallo en la solicitud fetch:", error);
            if (contenedorProductos) {
                contenedorProductos.innerHTML = `
                    <div style="grid-column: 1/-1; text-align: center; color: var(--danger, #ef4444); padding: 2rem;">
                        <p>No se pudo conectar con el servidor o la API. Verifique su conexión local.</p>
                    </div>`;
            }
        }
    }

    // ==========================================
    // 2. MANIPULACIÓN DEL DOM Y RENDERIZADO
    // ==========================================
    function renderizarProductos(productos) {
        if (!contenedorProductos) return;
        contenedorProductos.innerHTML = "";

        if (!productos || productos.length === 0) {
            contenedorProductos.innerHTML = `
                <div style="grid-column: 1/-1; text-align: center; padding: 2rem;">
                    <p>No se encontraron herramientas o accesorios con los criterios seleccionados.</p>
                </div>`;
            return;
        }

        productos.forEach(prod => {
            const card = document.createElement("div");
            card.className = "card-producto";
            
            // Estructura de la tarjeta optimizada con Mobile-First
            card.innerHTML = `
                <img src="assets/img/${prod.imagen}" alt="${prod.nombre}" class="card-img" onerror="this.src='assets/img/default.jpg'">
                <div class="card-body">
                    <span class="badge-cat">${prod.categoria}</span>
                    <h3 class="card-title">${prod.nombre}</h3>
                    <p class="card-precio">Q.${parseFloat(prod.precio).toFixed(2)}</p>
                    <p style="font-size:0.85rem; color: var(--gray, #9ca3af); margin-bottom: 0.5rem;">
                        ${parseInt(prod.cantidad) > 0 ? 'Disponibles: ' + prod.cantidad : '<span style="color:red;">Agotado</span>'}
                    </p>
                    <div style="display:flex; gap:0.5rem; margin-top:auto;">
                        <a href="views/productos.php?id=${prod.id_producto}" class="btn btn-outline" style="flex:1; text-align:center;">Detalles</a>
                        <button class="btn btn-agregar" data-id="${prod.id_producto}" style="flex:1;" ${prod.cantidad <= 0 ? 'disabled style="opacity:0.5; cursor:not-allowed;"' : ''}>Comprar</button>
                    </div>
                </div>
            `;
            contenedorProductos.appendChild(card);
        });

        // Registrar eventos para los botones de agregar dinámicos
        activarBotonesAgregar();
    }

    // ==========================================
    // 3. EVENTOS, BÚSQUEDA Y FILTROS
    // ==========================================
    function aplicarFiltrosYBusqueda() {
        const textoBusqueda = inputBusqueda ? inputBusqueda.value.toLowerCase().trim() : "";
        const categoriaSeleccionada = selectCategoria ? selectCategoria.value : "";
        const precioMaximo = inputPrecioMax ? parseFloat(inputPrecioMax.value) : Infinity;

        if (spanPrecioMaxVal && inputPrecioMax) {
            spanPrecioMaxVal.textContent = `Q.${inputPrecioMax.value}`;
        }

        const productosFiltrados = listaProductos.filter(prod => {
            const coincideTexto = prod.nombre.toLowerCase().includes(textoBusqueda) || 
                                  prod.descripcion.toLowerCase().includes(textoBusqueda) ||
                                  prod.categoria.toLowerCase().includes(textoBusqueda);
            
            const coincideCategoria = categoriaSeleccionada === "" || prod.id_categoria == categoriaSeleccionada;
            const coincidePrecio = parseFloat(prod.precio) <= precioMaximo;

            return coincideTexto && coincideCategoria && coincidePrecio;
        });

        renderizarProductos(productosFiltrados);
    }

    // Eventos de escucha en tiempo real
    if (inputBusqueda) {
        inputBusqueda.addEventListener("input", aplicarFiltrosYBusqueda);
    }
    if (selectCategoria) {
        selectCategoria.addEventListener("change", aplicarFiltrosYBusqueda);
    }
    if (inputPrecioMax) {
        inputPrecioMax.addEventListener("input", aplicarFiltrosYBusqueda);
    }

    // ==========================================
    // 4. CARRITO DE COMPRAS Y LOCALSTORAGE
    // ==========================================
    function activarBotonesAgregar() {
        const botonesAgregar = document.querySelectorAll(".btn-agregar");
        botonesAgregar.forEach(btn => {
            btn.addEventListener("click", (e) => {
                const idProd = e.target.getAttribute("data-id");
                const productoEncontrado = listaProductos.find(p => p.id_producto == idProd);
                
                if (productoEncontrado) {
                    agregarAlCarrito(productoEncontrado);
                }
            });
        });
    }

    function agregarAlCarrito(producto) {
        let carrito = JSON.parse(localStorage.getItem("carrito_ferreteria")) || [];
        const index = carrito.findIndex(item => item.id_producto == producto.id_producto);

        if (index > -1) {
            if (carrito[index].cantidad < producto.cantidad) {
                carrito[index].cantidad += 1;
            } else {
                alert("Has alcanzado el límite de stock disponible para este producto.");
                return;
            }
        } else {
            carrito.push({
                id_producto: producto.id_producto,
                nombre: producto.nombre,
                precio: producto.precio,
                imagen: producto.imagen,
                cantidad: 1,
                stockMax: producto.cantidad
            });
        }

        localStorage.setItem("carrito_ferreteria", JSON.stringify(carrito));
        actualizarContadorCarrito();
        
        // Efecto visual rápido o notificación
        alert(`¡"${producto.nombre}" agregado al carrito de compras!`);
    }

    function actualizarContadorCarrito() {
        if (!contadorCarrito) return;
        let carrito = JSON.parse(localStorage.getItem("carrito_ferreteria")) || [];
        const totalItems = carrito.reduce((acc, item) => acc + item.cantidad, 0);
        contadorCarrito.textContent = totalItems;
    }

    // ==========================================
    // 5. VALIDACIONES DE FORMULARIOS (Ej: Registro o Checkout)
    // ==========================================
    if (formContacto) {
        formContacto.addEventListener("submit", (e) => {
            e.preventDefault();
            let esValido = true;
            const correoInput = formContacto.querySelector('input[type="email"]');
            const telefonoInput = formContacto.querySelector('input[name="telefono"]');

            // Validación de correo electrónico por expresiones regulares (RegEx)
            if (correoInput) {
                const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!regexEmail.test(correoInput.value)) {
                    alert("Por favor, ingrese un correo electrónico válido.");
                    esValido = false;
                }
            }

            // Validación básica de teléfono (mínimo 7 u 8 dígitos numéricos)
            if (telefonoInput) {
                const regexTel = /^[0-9\- ]{7,15}$/;
                if (!regexTel.test(telefonoInput.value)) {
                    alert("Por favor, ingrese un número de teléfono válido.");
                    esValido = false;
                }
            }

            if (esValido) {
                alert("Formulario validado y procesado exitosamente.");
                formContacto.submit();
            }
        });
    }

    // Inicializar carga de datos al cargar la vista
    cargarDatosIniciales();
});