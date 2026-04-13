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

    private function resolveMonthRange(?string $month): array
    {
        if (!$month || !preg_match('/^\d{4}-\d{2}$/', $month)) {
            return [null, null];
        }

        $start = $month . '-01 00:00:00';
        $end = date('Y-m-d H:i:s', strtotime($start . ' +1 month'));

        return [$start, $end];
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

    [$fechaInicio, $fechaFin] = $this->resolveMonthRange($mes);

    if ($fechaInicio && $fechaFin) {
        $sql .= " WHERE sp.fecha_solicitud >= ? AND sp.fecha_solicitud < ?";
        $params[] = $fechaInicio;
        $params[] = $fechaFin;
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
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$itemId]);
        return $stmt->rowCount() > 0;
    }

    private function aumentarStock($itemId)
    {
        $sql = "UPDATE items_biblioteca 
                SET stock = LEAST(stock + 1, numero_ejemplares) 
                WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$itemId]);
        return $stmt->rowCount() > 0;
    }

    /* ============================================================
       APROBAR SOLICITUD
    ============================================================ */
    public function aprobar($id, $usuario_id = null)
    {
        try {
            $this->conn->beginTransaction();

            $sql = "SELECT sp.item_id, i.stock 
                    FROM solicitudes_prestamo sp
                    INNER JOIN items_biblioteca i ON i.id = sp.item_id
                    WHERE sp.id = ? AND sp.estado = 'PENDIENTE'
                    FOR UPDATE";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$item || (int) $item['stock'] <= 0) {
                $this->conn->rollBack();
                return false;
            }

            if (!$this->reducirStock($item['item_id'])) {
                $this->conn->rollBack();
                return false;
            }

            $sql = "UPDATE solicitudes_prestamo 
                    SET estado = 'APROBADA',
                        fecha_prestamo = NOW(),
                        fecha_devolucion = DATE_ADD(NOW(), INTERVAL 5 DAY),
                        fecha_respuesta = NOW()
                    WHERE id = ? AND estado = 'PENDIENTE'";
            $stmt = $this->conn->prepare($sql);
            $success = $stmt->execute([$id]);

            if (!$success || $stmt->rowCount() === 0) {
                $this->conn->rollBack();
                return false;
            }

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

            $this->conn->commit();

            return $success;

        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
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
            $this->conn->beginTransaction();

            $sql = "SELECT item_id, estado 
                    FROM solicitudes_prestamo 
                    WHERE id = ?
                    FOR UPDATE";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$item || !in_array($item['estado'], ['APROBADA', 'RETRASADO'], true)) {
                $this->conn->rollBack();
                return false;
            }

            if (!$this->aumentarStock($item['item_id'])) {
                $this->conn->rollBack();
                return false;
            }

            $sql = "UPDATE solicitudes_prestamo 
                    SET estado = 'ENTREGADO', fecha_respuesta = NOW()
                    WHERE id = ? AND estado IN ('APROBADA', 'RETRASADO')";
            $stmt = $this->conn->prepare($sql);
            $success = $stmt->execute([$id]);

            if (!$success || $stmt->rowCount() === 0) {
                $this->conn->rollBack();
                return false;
            }

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

            $this->conn->commit();

            return $success;

        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
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

    [$fechaInicio, $fechaFin] = $this->resolveMonthRange($mes);

    if ($fechaInicio && $fechaFin) {
        $sql .= " AND sp.fecha_solicitud >= ? AND sp.fecha_solicitud < ?";
        $params[] = $fechaInicio;
        $params[] = $fechaFin;
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
