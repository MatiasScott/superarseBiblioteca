<?php
require_once 'Database.php';

class LibrosModel {
    private $conn;

    public function __construct() {
        $this->conn = (new Database())->getConnection();
    }

    /* ===============================
       OBTENER TODOS LOS LIBROS ACTIVOS
    =============================== */
    public function getAll() {
        try {
            $sql = "SELECT b.*, c.nombre AS categoria_nombre
                    FROM items_biblioteca b
                    JOIN categorias c ON b.categoria_id = c.id
                    WHERE b.tipo_id = 1 AND b.deleted_at IS NULL AND b.estado = 'ACTIVO'
                    ORDER BY b.id DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener libros: " . $e->getMessage());
            return [];
        }
    }

    /* ===============================
       OBTENER TODOS LOS LIBROS (ADMIN)
    =============================== */
    public function getAllAdmin() {
        try {
            $sql = "SELECT b.*, c.nombre AS categoria_nombre
                    FROM items_biblioteca b
                    JOIN categorias c ON b.categoria_id = c.id
                    WHERE b.tipo_id = 1 AND b.deleted_at IS NULL
                    ORDER BY b.id DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener libros: " . $e->getMessage());
            return [];
        }
    }

    /* ===============================
       OBTENER LIBRO POR ID
    =============================== */
    public function getById($id) {
        try {
            $sql = "SELECT * FROM items_biblioteca WHERE id = ? LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener libro por ID: " . $e->getMessage());
            return null;
        }
    }

    /* ===============================
       INSERTAR NUEVO LIBRO
    =============================== */
    public function insert($data) {
        try {
            $sql = "INSERT INTO items_biblioteca 
                    (tipo_id, categoria_id, codigo, titulo, autor, descripcion, portada, edicion, revista, codigo_barra, numero_ejemplares, stock, ubicacion, anio)
                    VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                $data['categoria_id'],
                $data['codigo'],
                $data['titulo'],
                $data['autor'],
                $data['descripcion'],
                $data['portada'],
                $data['edicion'],
                $data['revista'],
                $data['codigo_barra'],
                $data['numero_ejemplares'],
                $data['stock'],
                $data['ubicacion'],
                $data['anio']
            ]);
        } catch (PDOException $e) {
            error_log("Error al insertar libro: " . $e->getMessage());
            return false;
        }
    }

    /* ===============================
       ACTUALIZAR LIBRO
    =============================== */
    public function update($data) {
        try {
            $sql = "UPDATE items_biblioteca SET
                        categoria_id = ?,
                        codigo = ?,
                        titulo = ?,
                        autor = ?,
                        descripcion = ?,
                        portada = ?,
                        edicion = ?,
                        revista = ?,
                        codigo_barra = ?,
                        numero_ejemplares = ?,
                        stock = ?,
                        anio = ?,
                        ubicacion = ?,
                        estado = ?
                    WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                $data['categoria_id'],
                $data['codigo'],
                $data['titulo'],
                $data['autor'],
                $data['descripcion'],
                $data['portada'],
                $data['edicion'],
                $data['revista'],
                $data['codigo_barra'],
                $data['numero_ejemplares'],
                $data['stock'],
                $data['anio'],
                $data['ubicacion'],
                $data['estado'] ?? 'ACTIVO',
                $data['id']
            ]);
        } catch (PDOException $e) {
            error_log("Error al actualizar libro: " . $e->getMessage());
            return false;
        }
    }

    /* ===============================
       ELIMINACIÓN SOFT
    =============================== */
    public function delete($id) {
        try {
            $sql = "UPDATE items_biblioteca SET deleted_at = NOW() WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error al eliminar libro: " . $e->getMessage());
            return false;
        }
    }

    /* ===============================
       CONTAR ELEMENTOS POR TIPO
    =============================== */
    public function contarLibros() {
        try {
            $sql = "SELECT COUNT(*) FROM items_biblioteca WHERE tipo_id = 1 AND deleted_at IS NULL";
            return (int) $this->conn->query($sql)->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error al contar libros: " . $e->getMessage());
            return 0;
        }
    }

    public function contarTesis() {
        try {
            $sql = "SELECT COUNT(*) FROM items_biblioteca WHERE tipo_id = 2 AND deleted_at IS NULL";
            return (int) $this->conn->query($sql)->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error al contar tesis: " . $e->getMessage());
            return 0;
        }
    }

    public function contarPublicaciones() {
        try {
            $sql = "SELECT COUNT(*) FROM items_biblioteca WHERE tipo_id = 3 AND deleted_at IS NULL";
            return (int) $this->conn->query($sql)->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error al contar publicaciones: " . $e->getMessage());
            return 0;
        }
    }

public function incrementarVisitas($id)
{
    try {
        $this->conn->beginTransaction();

        // 1️⃣ Incrementar contador general
        $sql = "UPDATE items_biblioteca 
                SET visitas = visitas + 1 
                WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);

        // 2️⃣ Registrar visita con fecha
        $sql = "INSERT INTO visitas_items (item_id, fecha_visita)
                VALUES (?, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);

        // 3️⃣ Obtener total actualizado
        $stmt = $this->conn->prepare(
            "SELECT visitas FROM items_biblioteca WHERE id = ?"
        );
        $stmt->execute([$id]);
        $visitas = (int) $stmt->fetchColumn();

        $this->conn->commit();
        return $visitas;

    } catch (PDOException $e) {
        $this->conn->rollBack();
        error_log("Error incrementarVisitas: " . $e->getMessage());
        return false;
    }
}


public function getMasVistosPorRango($tipoId, $fechaInicio, $fechaFin, $limit = 10)
{
    try {
        $limit = max(1, (int) $limit);

        $sql = "
            SELECT 
                i.id,
                i.titulo,
                COUNT(v.id) AS visitas
            FROM items_biblioteca i
            LEFT JOIN visitas_items v 
                ON v.item_id = i.id
                AND v.fecha_visita BETWEEN ? AND ?
            WHERE i.tipo_id = ?
              AND i.deleted_at IS NULL
            GROUP BY i.id, i.titulo
            ORDER BY visitas DESC
            LIMIT $limit
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            $fechaInicio . ' 00:00:00',
            $fechaFin    . ' 23:59:59',
            $tipoId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log("Error getMasVistosPorRango: " . $e->getMessage());
        return [];
    }
}
public function getCatalogoCompletoAdmin()
{
    try {
        $sql = "SELECT 
                    b.codigo,
                    b.portada,
                    b.titulo,
                    b.autor,
                    b.descripcion,
                    b.edicion,
                    b.revista,
                    b.codigo_barra,
                    c.nombre AS categoria,
                    b.anio,
                    b.numero_ejemplares,
                    b.stock,
                    b.ubicacion,
                    b.estado
                FROM items_biblioteca b
                JOIN categorias c ON b.categoria_id = c.id
                WHERE b.tipo_id = 1
                  AND b.deleted_at IS NULL
                ORDER BY b.titulo ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log("Error exportar catálogo libros: " . $e->getMessage());
        return [];
    }
}

}
