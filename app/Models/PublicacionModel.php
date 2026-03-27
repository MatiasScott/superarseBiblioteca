<?php
require_once 'Database.php';

class PublicacionModel
{
    private $conn;
    private $usuario_id; // Usuario logueado

    public function __construct()
    {
        $this->conn = (new Database())->getConnection();

        // Capturar automáticamente el usuario logueado desde la sesión
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->usuario_id = $_SESSION['user_id'] ?? null;
    }

    /* ===============================
       MÉTODO PRIVADO: REGISTRAR AUDITORÍA
    =============================== */
    private function registrarAuditoria($tabla, $registro_id, $accion, $datos_anteriores = null, $datos_nuevos = null, $descripcion = null)
    {
        if (!$this->usuario_id) return; // No hay usuario logueado

        $sql = "INSERT INTO auditoria 
                (usuario_id, tabla_afectada, registro_id, accion, datos_anteriores, datos_nuevos, ip_usuario, user_agent, descripcion) 
                VALUES 
                (:usuario_id, :tabla, :registro_id, :accion, :datos_anteriores, :datos_nuevos, :ip_usuario, :user_agent, :descripcion)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':usuario_id' => $this->usuario_id,
            ':tabla' => $tabla,
            ':registro_id' => $registro_id,
            ':accion' => $accion,
            ':datos_anteriores' => $datos_anteriores,
            ':datos_nuevos' => $datos_nuevos,
            ':ip_usuario' => $_SERVER['REMOTE_ADDR'] ?? null,
            ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            ':descripcion' => $descripcion
        ]);
    }

    /* ===============================
       OBTENER TODAS LAS PUBLICACIONES
    =============================== */
    public function getAll()
    {
        try {
            $sql = "SELECT ib.*, c.nombre AS categoria_nombre 
                    FROM items_biblioteca ib
                    INNER JOIN categorias c ON ib.categoria_id = c.id
                    WHERE ib.tipo_id = 3 AND ib.deleted_at IS NULL AND ib.estado = 'ACTIVO'
                    ORDER BY ib.id DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener publicaciones: " . $e->getMessage());
            return [];
        }
    }

    /* ===============================
       OBTENER TODAS LAS PUBLICACIONES (ADMIN)
    =============================== */
    public function getAllAdmin()
    {
        try {
            $sql = "SELECT ib.*, c.nombre AS categoria_nombre 
                    FROM items_biblioteca ib
                    INNER JOIN categorias c ON ib.categoria_id = c.id
                    WHERE ib.tipo_id = 3 AND ib.deleted_at IS NULL
                    ORDER BY ib.id DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener publicaciones: " . $e->getMessage());
            return [];
        }
    }

    /* ===============================
       OBTENER PUBLICACIÓN POR ID
    =============================== */
    public function getById($id)
    {
        try {
            $sql = "SELECT * FROM items_biblioteca WHERE id = ? LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener publicación por ID: " . $e->getMessage());
            return null;
        }
    }

    /* ===============================
       INSERTAR NUEVA PUBLICACIÓN
    =============================== */
    public function insert($data)
    {
        try {
            $sql = "INSERT INTO items_biblioteca 
                    (tipo_id, categoria_id, codigo, titulo, autor, revista, anio, descripcion, portada, link_archivo) 
                    VALUES (3, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $success = $stmt->execute([
                $data['categoria_id'],
                $data['codigo'],
                $data['titulo'],
                $data['autor'],
                $data['revista'],
                $data['anio'],
                $data['descripcion'],
                $data['portada'],
                $data['link_archivo']
            ]);

            if ($success) {
                $this->registrarAuditoria(
                    'items_biblioteca',
                    $this->conn->lastInsertId(),
                    'INSERT',
                    null,
                    json_encode($data),
                    'Creación de publicación'
                );
            }

            return $success;
        } catch (PDOException $e) {
            error_log("Error al insertar publicación: " . $e->getMessage());
            return false;
        }
    }

    /* ===============================
       ACTUALIZAR PUBLICACIÓN
    =============================== */
    public function updateItem($data)
    {
        try {
            $oldData = $this->getById($data['id']);

            $sql = "UPDATE items_biblioteca SET
                    categoria_id = ?,
                    titulo = ?,
                    autor = ?,
                    revista = ?,
                    anio = ?,
                    descripcion = ?,
                    portada = ?,
                    link_archivo = ?,
                    estado = ?
                    WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $success = $stmt->execute([
                $data['categoria_id'],
                $data['titulo'],
                $data['autor'],
                $data['revista'],
                $data['anio'],
                $data['descripcion'],
                $data['portada'],
                $data['link_archivo'],
                $data['estado'] ?? 'ACTIVO',
                $data['id']
            ]);

            if ($success) {
                $this->registrarAuditoria(
                    'items_biblioteca',
                    $data['id'],
                    'UPDATE',
                    json_encode($oldData),
                    json_encode($data),
                    'Actualización de publicación'
                );
            }

            return $success;
        } catch (PDOException $e) {
            error_log("Error al actualizar publicación: " . $e->getMessage());
            return false;
        }
    }

    /* ===============================
       ELIMINAR PUBLICACIÓN (SOFT DELETE)
    =============================== */
    public function delete($id)
    {
        try {
            $oldData = $this->getById($id);
            $sql = "UPDATE items_biblioteca SET deleted_at = NOW() WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $success = $stmt->execute([$id]);

            if ($success) {
                $this->registrarAuditoria(
                    'items_biblioteca',
                    $id,
                    'DELETE',
                    json_encode($oldData),
                    null,
                    'Eliminación de publicación'
                );
            }

            return $success;
        } catch (PDOException $e) {
            error_log("Error al eliminar publicación: " . $e->getMessage());
            return false;
        }
    }

    /* ===============================
       OBTENER CATEGORÍAS DE PUBLICACIONES
    =============================== */
    public function getCategorias()
    {
        try {
            $sql = "SELECT id, nombre FROM categorias WHERE tipo_id = 3 AND estado='ACTIVO' ORDER BY nombre";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener categorías de publicaciones: " . $e->getMessage());
            return [];
        }
    }

   public function incrementarVisitas($id)
{
    try {
        $this->conn->beginTransaction();

        // 1️⃣ Incrementar contador en items_biblioteca
        $sql = "UPDATE items_biblioteca 
                SET visitas = visitas + 1 
                WHERE id = ? AND tipo_id = 3";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);

        // 2️⃣ Registrar fecha real de la visita
        $sql = "INSERT INTO visitas_items (item_id, fecha_visita)
                VALUES (?, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);

        // 3️⃣ Obtener total actualizado
        $stmt = $this->conn->prepare(
            "SELECT visitas 
             FROM items_biblioteca 
             WHERE id = ? AND tipo_id = 3"
        );
        $stmt->execute([$id]);
        $visitas = (int)$stmt->fetchColumn();

        $this->conn->commit();
        return $visitas;

    } catch (PDOException $e) {
        $this->conn->rollBack();
        error_log("Error al incrementar visitas publicaciones: " . $e->getMessage());
        return false;
    }
}

public function getMasVistosPorRango($tipoId, $fechaInicio, $fechaFin, $limit = 10)
    {
        try {
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

    public function generarCodigoPublicacion($id, $anio, $categoria_id)
{
    // Obtener categoría
    $sql = "SELECT nombre FROM categorias WHERE id = ? LIMIT 1";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([$categoria_id]);
    $categoria = $stmt->fetchColumn();

    if (!$categoria) {
        throw new Exception("Categoría no encontrada");
    }

    // Abreviatura categoría (INGENIERÍA → ING)
    $abreviatura = strtoupper(
        substr(preg_replace('/\s+/', '', $categoria), 0, 3)
    );

    return "{$id}-{$anio}-{$abreviatura}";
}

/* ===============================
   GENERAR CÓDIGO AUTOMÁTICO PUBLICACIÓN
   PK lógica: categoria + año
================================ */
public function generarCodigo($anio, $categoria_id)
{
    // 1️⃣ Obtener nombre de la categoría
    $sql = "SELECT nombre FROM categorias WHERE id = ? LIMIT 1";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([$categoria_id]);
    $categoria = $stmt->fetchColumn();

    if (!$categoria) {
        throw new Exception("Categoría no encontrada");
    }

    // 2️⃣ Abreviatura categoría (REVISTA → REV)
    $abreviatura = strtoupper(substr(preg_replace('/\s+/', '', $categoria), 0, 3));

    // 3️⃣ Contar publicaciones del mismo año y categoría
    $sql = "
        SELECT COUNT(*) 
        FROM items_biblioteca
        WHERE tipo_id = 3
          AND anio = ?
          AND categoria_id = ?
          AND deleted_at IS NULL
    ";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([$anio, $categoria_id]);

    $consecutivo = (int)$stmt->fetchColumn() + 1;
    $consecutivo = str_pad($consecutivo, 3, '0', STR_PAD_LEFT);

    // 4️⃣ Código final
    return "{$anio}-{$abreviatura}-{$consecutivo}";
}




}


