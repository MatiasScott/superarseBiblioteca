<?php
require_once 'Database.php';

class SolicitudesModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    /* ============================================================
       MÉTODO PRIVADO: REGISTRAR AUDITORÍA
    ============================================================ */
    private function registrarAuditoria($usuario_id, $tabla, $registro_id, $accion, $datos_anteriores = null, $datos_nuevos = null, $descripcion = null)
    {
        $sql = "INSERT INTO auditoria 
                (usuario_id, tabla_afectada, registro_id, accion, datos_anteriores, datos_nuevos, ip_usuario, user_agent, descripcion) 
                VALUES 
                (:usuario_id, :tabla, :registro_id, :accion, :datos_anteriores, :datos_nuevos, :ip_usuario, :user_agent, :descripcion)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':usuario_id' => $usuario_id,
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

    /* ============================================================
       CREAR SOLICITUD DE PRÉSTAMO
    ============================================================ */
    public function crearSolicitud($usuarioId, $itemId)
    {
        try {
            $sql = "INSERT INTO solicitudes_prestamo (usuario_id, item_id) VALUES (?, ?)";
            $stmt = $this->conn->prepare($sql);
            $success = $stmt->execute([$usuarioId, $itemId]);

            if ($success) {
                $this->registrarAuditoria(
                    $usuarioId,
                    'solicitudes_prestamo',
                    $this->conn->lastInsertId(),
                    'INSERT',
                    null,
                    json_encode(['usuario_id' => $usuarioId, 'item_id' => $itemId]),
                    'Creación de solicitud de préstamo'
                );
            }

            return $success;

        } catch (PDOException $e) {
            error_log("Error al crear solicitud: " . $e->getMessage());
            return false;
        }
    }

  /* ============================================================
   LISTAR TODAS LAS SOLICITUDES (OPCIONAL FILTRO POR MES)
============================================================ */
public function getAll($mes = null)
{
    $sql = "SELECT sp.*, 
                   u.nombre AS usuario_nombre,
                   u.apellido AS usuario_apellido,
                   u.carrera,
                   u.telefono,
                   u.curso,
                   i.titulo AS libro_titulo,
                   i.numero_ejemplares,
                   i.stock
            FROM solicitudes_prestamo sp
            INNER JOIN usuarios u ON u.id = sp.usuario_id
            INNER JOIN items_biblioteca i ON i.id = sp.item_id";

    $params = [];

    if ($mes) {
        // Filtrar por mes YYYY-MM
        $sql .= " WHERE DATE_FORMAT(sp.fecha_solicitud, '%Y-%m') = ?";
        $params[] = $mes;
    }

    $sql .= " ORDER BY sp.fecha_solicitud DESC";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    /* ============================================================
       CONTROL DE STOCK
    ============================================================ */
    private function reducirStock($itemId)
    {
        $sql = "UPDATE items_biblioteca 
                SET stock = GREATEST(stock - 1, 0) 
                WHERE id = ? AND stock > 0";
        return $this->conn->prepare($sql)->execute([$itemId]);
    }

    private function aumentarStock($itemId)
    {
        $sql = "UPDATE items_biblioteca 
                SET stock = LEAST(stock + 1, numero_ejemplares) 
                WHERE id = ?";
        return $this->conn->prepare($sql)->execute([$itemId]);
    }

    /* ============================================================
       APROBAR SOLICITUD
    ============================================================ */
    public function aprobar($id, $usuario_id = null)
    {
        try {
            // Verificar stock
            $sql = "SELECT sp.item_id, i.stock 
                    FROM solicitudes_prestamo sp
                    INNER JOIN items_biblioteca i ON i.id = sp.item_id
                    WHERE sp.id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$item || $item['stock'] <= 0) return false;

            $this->reducirStock($item['item_id']);

            // Actualizar solicitud
            $sql = "UPDATE solicitudes_prestamo 
                    SET estado = 'APROBADA',
                        fecha_prestamo = NOW(),
                        fecha_devolucion = DATE_ADD(NOW(), INTERVAL 5 DAY),
                        fecha_respuesta = NOW()
                    WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $success = $stmt->execute([$id]);

            // Auditoría
            if ($success && $usuario_id) {
                $this->registrarAuditoria(
                    $usuario_id,
                    'solicitudes_prestamo',
                    $id,
                    'UPDATE',
                    null,
                    json_encode(['estado' => 'APROBADA']),
                    'Aprobación de solicitud'
                );
            }

            return $success;

        } catch (PDOException $e) {
            error_log("Error al aprobar solicitud: " . $e->getMessage());
            return false;
        }
    }

    /* ============================================================
       RECHAZAR SOLICITUD
    ============================================================ */
    public function rechazar($id, $motivo, $usuario_id = null)
    {
        try {
            $sql = "UPDATE solicitudes_prestamo 
                    SET estado = 'RECHAZADA',
                        motivo_rechazo = ?,
                        fecha_respuesta = NOW()
                    WHERE id = ?";

            $stmt = $this->conn->prepare($sql);
            $success = $stmt->execute([$motivo, $id]);

            if ($success && $usuario_id) {
                $this->registrarAuditoria(
                    $usuario_id,
                    'solicitudes_prestamo',
                    $id,
                    'UPDATE',
                    null,
                    json_encode(['estado' => 'RECHAZADA']),
                    'Rechazo de solicitud'
                );
            }

            return $success;

        } catch (PDOException $e) {
            error_log("Error al rechazar solicitud: " . $e->getMessage());
            return false;
        }
    }

    /* ============================================================
       ENTREGAR SOLICITUD
    ============================================================ */
    public function entregar($id, $usuario_id = null)
    {
        try {
            $sql = "SELECT item_id, estado 
                    FROM solicitudes_prestamo 
                    WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$item || !in_array($item['estado'], ['APROBADA','RETRASADO'])) 
                return false;

            $this->aumentarStock($item['item_id']);

            $sql = "UPDATE solicitudes_prestamo 
                    SET estado = 'ENTREGADO', fecha_respuesta = NOW()
                    WHERE id = ?";
            $success = $this->conn->prepare($sql)->execute([$id]);

            // Auditoría
            if ($success && $usuario_id) {
                $this->registrarAuditoria(
                    $usuario_id,
                    'solicitudes_prestamo',
                    $id,
                    'UPDATE',
                    null,
                    json_encode(['estado' => 'ENTREGADO']),
                    'Entrega de libro'
                );
            }

            return $success;

        } catch (PDOException $e) {
            error_log("Error al entregar solicitud: " . $e->getMessage());
            return false;
        }
    }

    /* ============================================================
       MARCAR COMO RETRASADO
    ============================================================ */
    public function marcarRetrasado($id)
{
    $sql = "UPDATE solicitudes_prestamo
            SET estado = 'RETRASADO'
            WHERE id = ?
              AND estado = 'APROBADA'
              AND fecha_devolucion < NOW()";
              
    return $this->conn->prepare($sql)->execute([$id]);
}


    /* ============================================================
       MARCADO AUTOMÁTICO DE ATRASADOS
    ============================================================ */
    public function actualizarRetrasados()
{
    $sql = "UPDATE solicitudes_prestamo
            SET estado = 'RETRASADO'
            WHERE estado = 'APROBADA'
              AND fecha_devolucion < NOW()
              AND estado != 'ENTREGADO'";
              
    return $this->conn->prepare($sql)->execute();
}


    /* ============================================================
       ESTUDIANTE — MIS SOLICITUDES
    ============================================================ */
    public function getByUsuario($usuarioId)
    {
        $sql = "SELECT sp.*, i.titulo AS libro_titulo, i.autor, i.portada, i.stock
                FROM solicitudes_prestamo sp
                INNER JOIN items_biblioteca i ON i.id = sp.item_id
                WHERE sp.usuario_id = ?
                ORDER BY sp.fecha_solicitud DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ============================================================
       CONTADORES
    ============================================================ */

    public function contarPrestamosActivos()
    {
        $sql = "SELECT COUNT(*) FROM solicitudes_prestamo WHERE estado = 'APROBADA'";
        return intval($this->conn->query($sql)->fetchColumn());
    }

    public function contarAtrasados()
    {
        $sql = "SELECT COUNT(*) 
                FROM solicitudes_prestamo
                WHERE (
                    (estado = 'APROBADA' AND fecha_devolucion < NOW())
                    OR estado = 'RETRASADO'
                )
                AND estado != 'ENTREGADO'";
        return intval($this->conn->query($sql)->fetchColumn());
    }

    public function getAllFiltered($mes = "", $estado = "")
{
    $sql = "SELECT sp.*, 
                   u.nombre AS usuario_nombre,
                   u.apellido AS usuario_apellido,
                   u.carrera,
                   u.telefono,
                   u.curso,
                   i.titulo AS libro_titulo,
                   i.numero_ejemplares,
                   i.stock
            FROM solicitudes_prestamo sp
            INNER JOIN usuarios u ON u.id = sp.usuario_id
            INNER JOIN items_biblioteca i ON i.id = sp.item_id
            WHERE 1=1";

    $params = [];

    if ($mes) {
        $sql .= " AND DATE_FORMAT(sp.fecha_solicitud, '%Y-%m') = ?";
        $params[] = $mes;
    }

    if ($estado) {
        $sql .= " AND sp.estado = ?";
        $params[] = $estado;
    }

    $sql .= " ORDER BY sp.fecha_solicitud DESC";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

}
