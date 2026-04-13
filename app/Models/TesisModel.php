<?php
require_once 'Database.php';

class TesisModel {
    private $conn;

    public function __construct() {
        $this->conn = (new Database())->getConnection();
    }

    /* ===============================
       OBTENER TODOS LOS REGISTROS DE TESIS
    =============================== */
    public function getAll() {
        $sql = "SELECT i.*, c.nombre AS categoria_nombre
                FROM items_biblioteca i
                JOIN categorias c ON i.categoria_id = c.id
                WHERE i.tipo_id = 2 AND i.deleted_at IS NULL AND i.estado = 'ACTIVO'
                ORDER BY i.id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ===============================
       OBTENER TODAS LAS TESIS (ADMIN)
    =============================== */
    public function getAllAdmin() {
        $sql = "SELECT i.*, c.nombre AS categoria_nombre
                FROM items_biblioteca i
                JOIN categorias c ON i.categoria_id = c.id
                WHERE i.tipo_id = 2 AND i.deleted_at IS NULL
                ORDER BY i.id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ===============================
       OBTENER TESIS POR ID
    =============================== */
    public function getById($id) {
        $sql = "SELECT * FROM items_biblioteca WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* ===============================
       INSERTAR NUEVA TESIS
    =============================== */
    public function insert($data) {
        $sql = "INSERT INTO items_biblioteca 
                (tipo_id, categoria_id, codigo, titulo, autor, tutor, universidad, anio, descripcion, portada, link_archivo) 
                VALUES (2, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            $data['categoria_id'],
            $data['codigo'],
            $data['titulo'],
            $data['autor'],
            $data['tutor'],
            $data['universidad'],
            $data['anio'],
            $data['descripcion'],
            $data['portada'],
            $data['link_archivo']
        ]);
    }

    /* ===============================
       ACTUALIZAR TESIS
    =============================== */
    public function updateItem($data) {
        $sql = "UPDATE items_biblioteca SET
                categoria_id = ?,
                
                titulo = ?,
                autor = ?,
                tutor = ?,
                universidad = ?,
                anio = ?,
                descripcion = ?,
                portada = ?,
                link_archivo = ?,
                estado = ?
                WHERE id = ?";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            $data['categoria_id'],
            
            $data['titulo'],
            $data['autor'],
            $data['tutor'],
            $data['universidad'],
            $data['anio'],
            $data['descripcion'],
            $data['portada'],
            $data['link_archivo'],
            $data['estado'] ?? 'ACTIVO',
            $data['id']
        ]);
    }

    /* ===============================
       ELIMINACIÓN SUAVE (SOFT DELETE)
    =============================== */
    public function delete($id) {
        $sql = "UPDATE items_biblioteca SET deleted_at = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    /* ===============================
       CONTADOR DE VISITAS
    =============================== */
public function incrementarVisitas($id)
{
    try {
        $this->conn->beginTransaction();

        // 1️⃣ Incrementar contador en items_biblioteca
        $sql = "UPDATE items_biblioteca 
                SET visitas = visitas + 1 
                WHERE id = ? AND tipo_id = 2";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);

        // 2️⃣ Registrar fecha real de la visita
        $sql = "INSERT INTO visitas_items (item_id, fecha_visita)
                VALUES (?, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);

        // 3️⃣ Obtener visitas actualizadas
        $stmt = $this->conn->prepare(
            "SELECT visitas 
             FROM items_biblioteca 
             WHERE id = ? AND tipo_id = 2"
        );
        $stmt->execute([$id]);
        $visitas = (int)$stmt->fetchColumn();

        $this->conn->commit();
        return $visitas;

    } catch (PDOException $e) {
        $this->conn->rollBack();
        error_log("Error al incrementar visitas Tesis: " . $e->getMessage());
        return false;
    }
}
    /* ===============================
       TOP 10 MÁS VISTOS
    =============================== */
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
            error_log("Error getMasVistosPorRango (Tesis): " . $e->getMessage());
            return [];
        }
    }
    /* ===============================
   GENERAR CÓDIGO AUTOMÁTICO TESIS
================================ */
public function generarCodigo($anio, $autor, $categoria_id)
{
    // 1️⃣ Obtener nombre de la carrera
    $sql = "SELECT nombre FROM categorias WHERE id = ? LIMIT 1";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([$categoria_id]);
    $categoria = $stmt->fetchColumn();

    // Seguridad
    if (!$categoria) {
        throw new Exception("Categoría no encontrada");
    }

    // 2️⃣ Iniciales del autor (Juan Perez → JP)
    $inicialesAutor = '';
    $palabras = preg_split('/\s+/', trim($autor));
    foreach ($palabras as $p) {
        $inicialesAutor .= strtoupper(substr($p, 0, 1));
    }

    // 3️⃣ Abreviatura de la carrera (Sistemas → SIS)
    $abreviaturaCarrera = strtoupper(substr(preg_replace('/\s+/', '', $categoria), 0, 3));

    // 4️⃣ Contar tesis del mismo año y carrera
    $sql = "
        SELECT COUNT(*) 
        FROM items_biblioteca
        WHERE tipo_id = 2
          AND anio = ?
          AND categoria_id = ?
          AND deleted_at IS NULL
    ";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([$anio, $categoria_id]);

    $consecutivo = (int)$stmt->fetchColumn() + 1;
    $consecutivo = str_pad($consecutivo, 3, '0', STR_PAD_LEFT);

    // 5️⃣ Código final
    return "{$anio}-{$abreviaturaCarrera}-{$inicialesAutor}-{$consecutivo}";
}

    /* ===============================
       OBTENER ÚLTIMO ID INSERTADO
    =============================== */
    public function getUltimoID() {
        return $this->conn->lastInsertId();
    }
}
