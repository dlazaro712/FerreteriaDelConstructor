<?php
require_once __DIR__ . '/../config/Database.php';

class Categoria {
    private $db;
    private $table = 'categorias';

    // Propiedades del modelo de datos correspondientes a la tabla
    private $id_categoria;
    private $descripcion;

    public function __construct($id_categoria = null, $descripcion = null) {
        $database = new Database();
        $this->db = $database->getConnection();

        $this->id_categoria = $id_categoria;
        $this->descripcion = $descripcion;
    }

    // --- Getters y Setters ---

    public function getIdCategoria() {
        return $this->id_categoria;
    }

    public function setIdCategoria($id_categoria) {
        $this->id_categoria = $id_categoria;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }

    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }

    /**
     * Obtener todas las categorías
     * @return array
     */
    public function listar() {
        try {
            $query = "SELECT * FROM " . $this->table . " ORDER BY descripcion ASC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Categoria::listar -> " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener una categoría por su ID cargando las propiedades en el objeto
     * @param int $id
     * @return self|false
     */
    public function obtenerPorId($id) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE id_categoria = :id LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $this->id_categoria = $row['id_categoria'];
                $this->descripcion = $row['descripcion'];
                return $this;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error en Categoria::obtenerPorId -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crear una nueva categoría
     * @param array|null $data [ 'descripcion' => '...' ] o usa la propiedad interna
     * @return bool|int ID de la categoría creada o false en caso de fallo
     */
    public function crear($data = null) {
        try {
            // Si pasan datos por parámetro, los asignamos a la propiedad
            if (isset($data['descripcion'])) {
                $this->descripcion = $data['descripcion'];
            }

            $query = "INSERT INTO " . $this->table . " (descripcion) VALUES (:descripcion)";
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(':descripcion', $this->descripcion, PDO::PARAM_STR);

            if ($stmt->execute()) {
                $this->id_categoria = $this->db->lastInsertId();
                return $this->id_categoria;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error en Categoria::crear -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar una categoría existente
     * @param int|null $id
     * @param array|null $data [ 'descripcion' => '...' ]
     * @return bool
     */
    public function actualizar($id = null, $data = null) {
        try {
            if ($id !== null) {
                $this->id_categoria = $id;
            }
            if (isset($data['descripcion'])) {
                $this->descripcion = $data['descripcion'];
            }

            $query = "UPDATE " . $this->table . " SET descripcion = :descripcion WHERE id_categoria = :id";
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(':id', $this->id_categoria, PDO::PARAM_INT);
            $stmt->bindParam(':descripcion', $this->descripcion, PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Categoria::actualizar -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar una categoría por su ID
     * @param int|null $id
     * @return bool
     */
    public function eliminar($id = null) {
        try {
            if ($id !== null) {
                $this->id_categoria = $id;
            }

            $query = "DELETE FROM " . $this->table . " WHERE id_categoria = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $this->id_categoria, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Categoria::eliminar -> " . $e->getMessage());
            return false;
        }
    }
}