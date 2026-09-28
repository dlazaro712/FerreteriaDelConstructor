document.addEventListener("DOMContentLoaded", () => {
    // Selectores del DOM
    const contenedorProductos = document.getElementById("contenedor-productos");
    const inputBusqueda = document.getElementById("busqueda");
    const selectCategoria = document.getElementById("filtro-categoria");
    const inputPrecioMax = document.getElementById("filtro-precio");
    const spanPrecioMaxVal = document.getElementById("precio-max-val");
    const contadorCarrito = document.getElementById("contador-carrito");
    const formContacto = document.getElementById("form-contacto");

    let listaProductos = [];

    // ==========================================
    // 1. CONSUMO DE API Y SOLICITUDES FETCH()
    // ==========================================
    async function cargarDatosIniciales() {
        try {
            const respuestaProd = await fetch("/FerreteriaDelConstructor/api/productos.php");
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
            
            // Estructura optimizada con Botón de Detalles, Guardar (Wishlist) y Comprar
            card.innerHTML = `
                <div class="card-body">
                    <span class="badge-cat">${prod.categoria}</span>
                    <h3 class="card-title">${prod.nombre}</h3>
                    <p class="card-precio">Q.${parseFloat(prod.precio).toFixed(2)}</p>
                    <p style="font-size:0.85rem; color: var(--gray, #9ca3af); margin-bottom: 0.5rem;">
                        ${parseInt(prod.cantidad) > 0 ? 'Disponibles: ' + prod.cantidad : '<span style="color:red;">Agotado</span>'}
                    </p>
                    <div style="display:flex; gap:0.5rem; margin-top:auto; flex-wrap:wrap;">
                        <a href="views/clientes/productos.php?id=${prod.id_producto}" class="btn btn-outline" style="flex:1; text-align:center;">Detalles</a>
                        <button class="btn btn-outline btn-guardar" data-id="${prod.id_producto}" style="flex:1;" title="Guardar en lista de deseos">Guardar</button>
                        <button class="btn btn-agregar" data-id="${prod.id_producto}" style="flex:100%;" ${prod.cantidad <= 0 ? 'disabled style="opacity:0.5; cursor:not-allowed;"' : ''}>Comprar</button>
                    </div>
                </div>
            `;
            contenedorProductos.appendChild(card);
        });

        // Registrar eventos para botones dinámicos
        activarBotonesAgregar();
        activarBotonesGuardar();
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

    if (inputBusqueda) inputBusqueda.addEventListener("input", aplicarFiltrosYBusqueda);
    if (selectCategoria) selectCategoria.addEventListener("change", aplicarFiltrosYBusqueda);
    if (inputPrecioMax) inputPrecioMax.addEventListener("input", aplicarFiltrosYBusqueda);

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
        alert(`¡"${producto.nombre}" agregado al carrito de compras!`);
    }

    function actualizarContadorCarrito() {
        if (!contadorCarrito) return;
        let carrito = JSON.parse(localStorage.getItem("carrito_ferreteria")) || [];
        const totalItems = carrito.reduce((acc, item) => acc + item.cantidad, 0);
        contadorCarrito.textContent = totalItems;
    }

    <!-- Script de soporte para renderizar los elementos del carrito en la tabla -->
    // Definimos la URL base desde PHP para que JavaScript la use de forma segura
    const BASE_URL = "<?= BASE_URL; ?>";

    document.addEventListener("DOMContentLoaded", () => {
        renderizarTablaCarrito();
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
            
            // Limpiamos el nombre de la imagen por si viene con rutas previas o solo el nombre de archivo
            let nombreImagen = item.imagen ? item.imagen.split('/').pop() : 'Taladro-Atornillador-Inalambrico.png';
            
            // Construimos la ruta absoluta correcta utilizando la BASE_URL de PHP
            let imgRuta = `${BASE_URL}assets/imgs/${nombreImagen}`;

            html += `
                <tr>
                    <td>
                        <div class="cart-product-info">
                            
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
            let stockMax = carrito[index].stockMax || 999;

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

    function procesarCheckoutPedido() {
        let carrito = JSON.parse(localStorage.getItem("carrito_ferreteria")) || [];
        if (carrito.length === 0) {
            alert("El carrito está vacío.");
            return;
        }
        alert("¡Pedido preparado para procesar!");
    }

    // ==========================================
    // 5. LISTA DE DESEOS (WISHLIST)
    // ==========================================
    function activarBotonesGuardar() {
        const botonesGuardar = document.querySelectorAll(".btn-guardar");
        botonesGuardar.forEach(btn => {
            btn.addEventListener("click", (e) => {
                const idProd = e.target.getAttribute("data-id");
                const productoEncontrado = listaProductos.find(p => p.id_producto == idProd);
                
                if (productoEncontrado) {
                    agregarAListaDeseos(productoEncontrado);
                }
            });
        });
    }

    function agregarAListaDeseos(producto) {
        let wishlist = JSON.parse(localStorage.getItem("wishlist_ferreteria")) || [];
        const existe = wishlist.some(item => item.id_producto == producto.id_producto);

        if (!existe) {
            wishlist.push({
                id_producto: producto.id_producto,
                nombre: producto.nombre,
                precio: producto.precio,
                imagen: producto.imagen,
                descripcion: producto.descripcion
            });
            localStorage.setItem("wishlist_ferreteria", JSON.stringify(wishlist));
            alert(`¡"${producto.nombre}" ha sido guardado en tu lista de deseos para consultar o comprar después!`);
        } else {
            alert(`Este producto ya se encuentra registrado en tu lista de deseos.`);
        }
    }

    function guardarEnWishlist(producto) {
    let wishlist = JSON.parse(localStorage.getItem("wishlist_ferreteria")) || [];
    
    // Validar si el producto ya existe en la lista de deseos
    const existe = wishlist.some(item => item.id_producto == producto.id);

    if (existe) {
        alert(`¡"${producto.nombre}" ya se encuentra en tu lista de deseos!`);
        return;
    }

    // Agregar el producto a la lista de deseos local
    wishlist.push({
        id_producto: producto.id,
        nombre: producto.nombre,
        precio: producto.precio,
        imagen: producto.imagen
    });

    localStorage.setItem("wishlist_ferreteria", JSON.stringify(wishlist));
    alert(`¡"${producto.nombre}" ha sido guardado en tu lista de deseos!`);
    }

    // ==========================================
    // 6. RESEÑAS Y CALIFICACIONES DE PRODUCTOS
    // ==========================================
    window.registrarResenaProducto = function(idProducto, calificacion, comentario) {
        if (!calificacion || calificacion < 1 || calificacion > 5) {
            alert("Por favor, seleccione una calificación válida entre 1 y 5 estrellas.");
            return false;
        }

        if (!comentario || comentario.trim() === "") {
            alert("El comentario no puede estar vacío.");
            return false;
        }

        let resenas = JSON.parse(localStorage.getItem("resenas_ferreteria")) || [];
        
        resenas.push({
            id_producto: idProducto,
            calificacion: parseInt(calificacion),
            comentario: comentario.trim(),
            fecha: new Date().toLocaleDateString()
        });

        localStorage.setItem("resenas_ferreteria", JSON.stringify(resenas));
        alert("¡Muchas gracias! Su reseña y calificación han sido registradas exitosamente.");
        return true;
    }

    // ==========================================
    // 7. VALIDACIONES DE FORMULARIOS
    // ==========================================
    if (formContacto) {
        formContacto.addEventListener("submit", (e) => {
            e.preventDefault();
            let esValido = true;
            const correoInput = formContacto.querySelector('input[type="email"]');
            const telefonoInput = formContacto.querySelector('input[name="telefono"]');

            if (correoInput) {
                const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!regexEmail.test(correoInput.value)) {
                    alert("Por favor, ingrese un correo electrónico válido.");
                    esValido = false;
                }
            }

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

    // ==========================================
// MÓDULO DE ADMINISTRACIÓN: LISTA DE DESEOS
// ==========================================
document.addEventListener("DOMContentLoaded", () => {
    const tablaBody = document.getElementById("tabla-admin-wishlist-body");
    const inputBusquedaAdmin = document.getElementById("busqueda-admin-wishlist");

    // Si no estamos en la vista de administración de wishlist, omitir ejecución
    if (!tablaBody) return;

    function cargarWishlistAdmin(filtro = "") {
        let wishlist = JSON.parse(localStorage.getItem("wishlist_ferreteria")) || [];
        tablaBody.innerHTML = "";

        const resultados = wishlist.filter(item => 
            item.nombre.toLowerCase().includes(filtro.toLowerCase()) ||
            item.id_producto.toString().includes(filtro)
        );

        if (resultados.length === 0) {
            tablaBody.innerHTML = `
                <tr>
                    <td colspan="5" style="text-align: center; padding: 3rem; color: #6b7280;">
                        No se encontraron registros de listas de deseos activas.
                    </td>
                </tr>`;
            return;
        }

        resultados.forEach((prod) => {
            const indiceReal = wishlist.findIndex(p => p.id_producto == prod.id_producto);
            
            const fila = document.createElement("tr");
            fila.style.borderBottom = "1px solid #e5e7eb";
            
            fila.innerHTML = `
                <td style="padding: 1rem; color: #4b5563; font-weight: bold;">#${prod.id_producto}</td>
                <td style="padding: 1rem;">
                    <img src="../../${prod.imagen}" alt="${prod.nombre}" style="width: 40px; height: 40px; object-fit: contain; border-radius: 4px; background: #f3f4f6;" onerror="this.src='../../assets/imgs/Taladro-Atornillador-Inalambrico.png'">
                </td>
                <td style="padding: 1rem; color: #111827; font-weight: 500;">${prod.nombre}</td>
                <td style="padding: 1rem; color: #2563eb; font-weight: bold;">Q.${parseFloat(prod.precio).toFixed(2)}</td>
                <td style="padding: 1rem; text-align: center;">
                    <button class="btn-eliminar-admin" data-index="${indiceReal}" style="background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; padding: 0.4rem 0.8rem; border-radius: 4px; cursor: pointer; font-size: 0.85rem;">Remover del Sistema</button>
                </td>
            `;
            tablaBody.appendChild(fila);
        });

        activarAccionesAdmin();
    }

    function activarAccionesAdmin() {
        document.querySelectorAll(".btn-eliminar-admin").forEach(btn => {
            btn.addEventListener("click", (e) => {
                const index = e.target.getAttribute("data-index");
                let wishlist = JSON.parse(localStorage.getItem("wishlist_ferreteria")) || [];
                
                if (confirm("¿Estás seguro de remover este producto de la lista de deseos global?")) {
                    wishlist.splice(index, 1);
                    localStorage.setItem("wishlist_ferreteria", JSON.stringify(wishlist));
                    cargarWishlistAdmin(inputBusquedaAdmin ? inputBusquedaAdmin.value : "");
                }
            });
        });
    }

    if (inputBusquedaAdmin) {
        inputBusquedaAdmin.addEventListener("input", (e) => {
            cargarWishlistAdmin(e.target.value);
        });
    }

    // Inicializar carga de datos
    cargarWishlistAdmin();
});

// ==========================================
// MÓDULO DE CLIENTES: LISTA DE DESEOS
// ==========================================
document.addEventListener("DOMContentLoaded", () => {
    const contenedorWishlist = document.getElementById("contenedor-wishlist-cliente");

    // Si no estamos en la vista de lista de deseos del cliente, omitir ejecución
    if (!contenedorWishlist) return;

    function renderizarWishlistCliente() {
        let wishlist = JSON.parse(localStorage.getItem("wishlist_ferreteria")) || [];
        
        contenedorWishlist.innerHTML = "";

        if (wishlist.length === 0) {
            contenedorWishlist.innerHTML = `
                <div style="grid-column: 1/-1; text-align: center; padding: 4rem 2rem; background: #f9fafb; border-radius: 8px;">
                    <p style="font-size: 1.1rem; color: #6b7280; margin-bottom: 1rem;">Tu lista de deseos está vacía.</p>
                    <a href="../../index.php" class="btn" style="text-decoration: none; display: inline-block; padding: 0.75rem 1.5rem; background: #2563eb; color: white; border-radius: 4px;">Explorar productos</a>
                </div>`;
            return;
        }

        wishlist.forEach((prod, index) => {
            const card = document.createElement("div");
            card.className = "card-producto";
            card.style.cssText = "background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 1rem; display: flex; flex-direction: column; box-shadow: 0 1px 3px rgba(0,0,0,0.1);";
            
            card.innerHTML = `
                <img src="../../${prod.imagen}" alt="${prod.nombre}" class="card-img" style="width: 100%; height: 180px; object-fit: contain; margin-bottom: 1rem;" onerror="this.src='../../assets/imgs/Taladro-Atornillador-Inalambrico.png'">
                <div class="card-body" style="display: flex; flex-direction: column; flex: 1;">
                    <h3 class="card-title" style="font-size: 1.1rem; margin-bottom: 0.5rem; color: #111827;">${prod.nombre}</h3>
                    <p class="card-precio" style="font-size: 1.25rem; font-weight: bold; color: #2563eb; margin-bottom: 0.5rem;">Q.${parseFloat(prod.precio).toFixed(2)}</p>
                    <p style="font-size: 0.85rem; color: #4b5563; margin-bottom: 1rem; flex: 1;">${prod.descripcion ? prod.descripcion : 'Sin descripción detallada.'}</p>
                    
                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: auto;">
                        <button class="btn btn-mover-carrito" data-id="${prod.id_producto}" style="flex: 1; background: #2563eb; color: white; border: none; padding: 0.5rem; border-radius: 4px; cursor: pointer; text-align: center;">Comprar</button>
                        <button class="btn-quitar-wishlist" data-index="${index}" style="background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; padding: 0.5rem 0.75rem; border-radius: 4px; cursor: pointer;" title="Quitar de la lista">Quitar</button>
                    </div>
                </div>
            `;
            contenedorWishlist.appendChild(card);
        });

        activarBotonesAccionCliente();
    }

    function activarBotonesAccionCliente() {
        // Quitar de lista de deseos
        document.querySelectorAll(".btn-quitar-wishlist").forEach(btn => {
            btn.addEventListener("click", (e) => {
                const index = e.target.getAttribute("data-index");
                let wishlist = JSON.parse(localStorage.getItem("wishlist_ferreteria")) || [];
                
                wishlist.splice(index, 1);
                localStorage.setItem("wishlist_ferreteria", JSON.stringify(wishlist));
                renderizarWishlistCliente();
            });
        });

        // Mover al carrito de compras utilizando la lógica del proyecto
        document.querySelectorAll(".btn-mover-carrito").forEach(btn => {
            btn.addEventListener("click", (e) => {
                const idProd = e.target.getAttribute("data-id");
                let wishlist = JSON.parse(localStorage.getItem("wishlist_ferreteria")) || [];
                const producto = wishlist.find(p => p.id_producto == idProd);

                if (producto) {
                    let carrito = JSON.parse(localStorage.getItem("carrito_ferreteria")) || [];
                    const idx = carrito.findIndex(item => item.id_producto == producto.id_producto);

                    if (idx > -1) {
                        carrito[idx].cantidad += 1;
                    } else {
                        carrito.push({
                            id_producto: producto.id_producto,
                            nombre: producto.nombre,
                            precio: producto.precio,
                            imagen: producto.imagen,
                            cantidad: 1
                        });
                    }

                    localStorage.setItem("carrito_ferreteria", JSON.stringify(carrito));
                    
                    // Actualizar el contador general del carrito si está disponible
                    const contador = document.getElementById("contador-carrito");
                    if(contador) {
                        const totalItems = carrito.reduce((acc, item) => acc + item.cantidad, 0);
                        contador.textContent = totalItems;
                    }

                    alert(`¡"${producto.nombre}" se ha pasado al carrito de compras!`);
                }
            });
        });
    }

    async function procesarCheckoutPedido() {
        const carrito = obtenerCarrito();
        if (!carrito || carrito.length === 0) {
            alert("Tu carrito de compras está vacío.");
            return;
        }

        let total = carrito.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);

        let datosPedido = {
            items: carrito.map(item => ({
                id_producto: item.id,
                cantidad: item.cantidad,
                precio: item.precio
            })),
            total: total,
            direccion: "Dirección principal del cliente"
        };

        if (window.ApiService && window.ApiService.pedidos) {
            try {
                let resultado = await window.ApiService.pedidos.crear(datosPedido);
                if (resultado && resultado.status === 'success') {
                    alert("¡Pedido realizado con éxito!");
                    localStorage.removeItem('ferreteria_carrito');
                    actualizarContadorCarrito();
                    window.location.href = "<?= BASE_URL; ?>views/usuarios.php?action=historial";
                } else {
                    alert("Error al procesar el pedido: " + (resultado.mensaje || "Inténtalo de nuevo."));
                }
            } catch (error) {
                console.error("Error en checkout:", error);
                alert("Ocurrió un error al conectar con el servidor.");
            }
        } else {
            alert("Servicio de API no disponible temporalmente.");
        }
        }


        // Inicializar renderizado
        renderizarWishlistCliente();
    });

        // ==========================================
        // MÓDULO DE CLIENTES: RESEÑAS
        // ==========================================
        document.addEventListener("DOMContentLoaded", () => {
        const formResena = document.getElementById("form-resena");

        if (formResena) {
            formResena.addEventListener("submit", (e) => {
                const idUsuario = formResena.querySelector('input[name="id_usuario"]').value;
                const idProducto = formResena.querySelector('input[name="id_producto"]').value;
                const comentario = formResena.querySelector('textarea[name="comentario"]').value.trim();

                if (!idUsuario || !idProducto || comentario === "") {
                    e.preventDefault();
                    alert("Por favor, completa todos los campos requeridos para enviar tu reseña.");
                }
            });
    }
});

    // Inicializar carga de datos iniciales
    cargarDatosIniciales();
});