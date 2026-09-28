/**
 * Librería de servicios API para Ferretería del Constructor
 * Centraliza las peticiones HTTP (GET, POST, PUT, DELETE) hacia el backend.
 */

const API_BASE_URL = 'http://localhost:8080/FerreteriaDelConstructor/api/'; // Ajusta según la estructura de tus rutas relativas

const ApiService = {
    // ==========================================
    // MÉTODOS PARA PRODUCTOS (/api/productos.php)
    // ==========================================
    
    productos: {
        listar: async () => {
            try {
                const response = await fetch(`${API_BASE_URL}productos.php`);
                const result = await response.json();
                return result.data || result;
            } catch (error) {
                console.error("Error al listar productos:", error);
                return [];
            }
        },

        obtenerPorId: async (id) => {
            try {
                const response = await fetch(`${API_BASE_URL}productos.php?id=${id}`);
                const result = await response.json();
                return result.data || result;
            } catch (error) {
                console.error(`Error al obtener el producto ID ${id}:`, error);
                return null;
            }
        },

        crear: async (dataProducto) => {
            try {
                const response = await fetch(`${API_BASE_URL}productos.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(dataProducto)
                });
                return await response.json();
            } catch (error) {
                console.error("Error al crear producto:", error);
                return { status: "error", message: error.message };
            }
        },

        actualizar: async (id, dataProducto) => {
            try {
                const response = await fetch(`${API_BASE_URL}productos.php?id=${id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(dataProducto)
                });
                return await response.json();
            } catch (error) {
                console.error(`Error al actualizar el producto ID ${id}:`, error);
                return { status: "error", message: error.message };
            }
        },

        eliminar: async (id) => {
            try {
                const response = await fetch(`${API_BASE_URL}productos.php?id=${id}`, {
                    method: 'DELETE'
                });
                return await response.json();
            } catch (error) {
                console.error(`Error al eliminar el producto ID ${id}:`, error);
                return { status: "error", message: error.message };
            }
        }
    },

    // ==========================================
    // MÉTODOS PARA CATEGORÍAS (/api/categorias.php)
    // ==========================================

    categorias: {
        listar: async () => {
            try {
                const response = await fetch(`${API_BASE_URL}categorias.php`);
                const result = await response.json();
                return result.data || result;
            } catch (error) {
                console.error("Error al listar categorías:", error);
                return [];
            }
        },

        obtenerPorId: async (id) => {
            try {
                const response = await fetch(`${API_BASE_URL}categorias.php?id=${id}`);
                const result = await response.json();
                return result.data || result;
            } catch (error) {
                console.error(`Error al obtener la categoría ID ${id}:`, error);
                return null;
            }
        },

        crear: async (dataCategoria) => {
            try {
                const response = await fetch(`${API_BASE_URL}categorias.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(dataCategoria)
                });
                return await response.json();
            } catch (error) {
                console.error("Error al crear categoría:", error);
                return { status: "error", message: error.message };
            }
        },

        actualizar: async (id, dataCategoria) => {
            try {
                const response = await fetch(`${API_BASE_URL}categorias.php?id=${id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(dataCategoria)
                });
                return await response.json();
            } catch (error) {
                console.error(`Error al actualizar la categoría ID ${id}:`, error);
                return { status: "error", message: error.message };
            }
        },

        eliminar: async (id) => {
            try {
                const response = await fetch(`${API_BASE_URL}categorias.php?id=${id}`, {
                    method: 'DELETE'
                });
                return await response.json();
            } catch (error) {
                console.error(`Error al eliminar la categoría ID ${id}:`, error);
                return { status: "error", message: error.message };
            }
        }
    },

    // ==========================================
    // MÉTODOS PARA PEDIDOS (/api/pedidos.php)
    // ==========================================

    pedidos: {
        listar: async () => {
            try {
                const response = await fetch(`${API_BASE_URL}pedidos.php`);
                const result = await response.json();
                return result.data || result;
            } catch (error) {
                console.error("Error al listar pedidos (admin):", error);
                return [];
            }
        },

        historial: async (usuarioId = null) => {
            try {
                let url = `${API_BASE_URL}pedidos.php?action=historial`;
                if (usuarioId) {
                    url += `&usuario_id=${usuarioId}`;
                }
                const response = await fetch(url);
                const result = await response.json();
                return result.data || result;
            } catch (error) {
                console.error("Error al obtener el historial de pedidos:", error);
                return [];
            }
        },

        obtenerDetalle: async (id) => {
            try {
                const response = await fetch(`${API_BASE_URL}pedidos.php?action=detalle&id=${id}`);
                const result = await response.json();
                return result.data || result;
            } catch (error) {
                console.error(`Error al obtener el detalle del pedido ID ${id}:`, error);
                return null;
            }
        },

        crear: async (dataPedido) => {
            try {
                const response = await fetch(`${API_BASE_URL}pedidos.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(dataPedido)
                });
                return await response.json();
            } catch (error) {
                console.error("Error al registrar pedido:", error);
                return { status: "error", mensaje: error.message };
            }
        },

        actualizar: async (id, dataPedido) => {
            try {
                const response = await fetch(`${API_BASE_URL}pedidos.php?id=${id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(dataPedido)
                });
                return await response.json();
            } catch (error) {
                console.error(`Error al actualizar el pedido ID ${id}:`, error);
                return { status: "error", mensaje: error.message };
            }
        },

        eliminar: async (id) => {
            try {
                const response = await fetch(`${API_BASE_URL}pedidos.php?id=${id}`, {
                    method: 'DELETE'
                });
                return await response.json();
            } catch (error) {
                console.error(`Error al eliminar el pedido ID ${id}:`, error);
                return { status: "error", mensaje: error.message };
            }
        }
    },

    // ==========================================
    // MÉTODOS PARA ESTADO DETALLE PEDIDO (/api/estadoDetallePedido.php)
    // ==========================================

    estadoDetallePedido: {
        listar: async () => {
            try {
                const response = await fetch(`${API_BASE_URL}estadoDetallePedido.php`);
                const result = await response.json();
                return result.data || result;
            } catch (error) {
                console.error("Error al listar los detalles de pedidos:", error);
                return [];
            }
        },
        obtenerPorId: async (idDetalle) => {
            try {
                const response = await fetch(`${API_BASE_URL}estadoDetallePedido.php?id_detalle=${idDetalle}`);
                const result = await response.json();
                return result.data || result;
            } catch (error) {
                console.error(`Error al obtener el detalle de pedido ID ${idDetalle}:`, error);
                return null;
            }
        },

        listarPorPedido: async (idPedido) => {
            try {
                const response = await fetch(`${API_BASE_URL}estadoDetallePedido.php?id_pedido=${idPedido}`);
                const result = await response.json();
                return result.data || result;
            } catch (error) {
                console.error(`Error al obtener los detalles para el pedido ID ${idPedido}:`, error);
                return [];
            }
        },

        crear: async (dataDetalle) => {
            try {
                const response = await fetch(`${API_BASE_URL}estadoDetallePedido.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(dataDetalle)
                });
                return await response.json();
            } catch (error) {
                console.error("Error al registrar el detalle de pedido:", error);
                return { status: "error", mensaje: error.message };
            }
        },

        actualizar: async (idDetalle, dataDetalle) => {
            try {
                // Combinamos el ID con los datos para asegurar que la API reciba el id_detalle en el cuerpo JSON
                const payload = { id_detalle: idDetalle, ...dataDetalle };
                const response = await fetch(`${API_BASE_URL}estadoDetallePedido.php`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                return await response.json();
            } catch (error) {
                console.error(`Error al actualizar el detalle de pedido ID ${idDetalle}:`, error);
                return { status: "error", mensaje: error.message };
            }
        },

        eliminar: async (idDetalle) => {
            try {
                const response = await fetch(`${API_BASE_URL}estadoDetallePedido.php?id_detalle=${idDetalle}`, {
                    method: 'DELETE'
                });
                return await response.json();
            } catch (error) {
                console.error(`Error al eliminar el detalle de pedido ID ${idDetalle}:`, error);
                return { status: "error", mensaje: error.message };
            }
        }
    }
};

// Exportar globalmente para uso directo en scripts front-end tradicionales
window.ApiService = ApiService;