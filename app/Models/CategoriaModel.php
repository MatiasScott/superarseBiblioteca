<?php
// app/Models/CategoriaModel.php
require_once __DIR__ . "/Database.php";

class CategoriaModel {

    private $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    /* ===============================
       OBTENER TODAS LAS CATEGORÍAS
    =============================== */
    public function getAll() {
        try {
            $stmt = $this->conn->prepare("
                SELECT c.id, c.nombre, c.estado, c.tipo_id, t.nombre AS tipo_nombre
                FROM categorias c
                JOIN tipos_item t ON c.tipo_id = t.id
                ORDER BY c.id DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener categorías: " . $e->getMessage());
            return [];
        }
    }

    /* ===============================
       OBTENER CATEGORÍAS POR TIPO
    =============================== */
    public function getCategoriasPorTipo($tipo_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT c.id, c.nombre, c.estado
                FROM categorias c
                WHERE c.tipo_id = ?
                ORDER BY c.nombre ASC
            ");
            $stmt->execute([$tipo_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener categorías por tipo: " . $e->getMessage());
            return [];
        }
    }

    /* ===============================
       OBTENER TIPOS DE ITEMS
    =============================== */
    public function getTipos() {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM tipos_item ORDER BY id ASC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener tipos de items: " . $e->getMessage());
            return [];
        }
    }

    /* ===============================
       CREAR NUEVA CATEGORÍA
    =============================== */
    public function create($nombre, $tipo_id, $estado = "ACTIVO") {
        try {
            $stmt = $this->conn->prepare("INSERT INTO categorias (nombre, tipo_id, estado) VALUES (?, ?, ?)");
            return $stmt->execute([$nombre, $tipo_id, $estado]);
        } catch (PDOException $e) {
            error_log("Error al crear categoría: " . $e->getMessage());
            return false;
        }
    }

    /* ===============================
       ACTUALIZAR CATEGORÍA EXISTENTE
    =============================== */
    public function update($id, $nombre, $tipo_id, $estado) {
        try {
            $stmt = $this->conn->prepare("UPDATE categorias SET nombre = ?, tipo_id = ?, estado = ? WHERE id = ?");
            return $stmt->execute([$nombre, $tipo_id, $estado, $id]);
        } catch (PDOException $e) {
            error_log("Error al actualizar categoría: " . $e->getMessage());
            return false;
        }
    }

    /* ===============================
       ELIMINAR CATEGORÍA
    =============================== */
    public function delete($id) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM categorias WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error al eliminar categoría: " . $e->getMessage());
            return false;
        }
    }

    /* ===============================
       BUSCAR CATEGORÍA POR ID
    =============================== */
    public function find($id) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM categorias WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al buscar categoría: " . $e->getMessage());
            return null;
        }
    }
}
