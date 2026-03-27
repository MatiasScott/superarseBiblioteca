<?php
require_once 'Database.php';

class PrestamoModel {
    private $conn;

    public function __construct() {
        $this->conn = (new Database())->getConnection();
    }

    /* ===============================
       MÉTODO PRIVADO: REGISTRAR AUDITORÍA
    =============================== */
    private function registrarAuditoria($usuario_id, $tabla, $registro_id, $accion, $datos_anteriores = null, $datos_nuevos = null, $descripcion = null) {
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

    /* ===============================
       OBTENER TODOS LOS PRÉSTAMOS (ADMIN)
    =============================== */
    public function getAll() {
        try {
            $sql = "SELECT p.id, p.fecha_prestamo, p.fecha_entrega, p.fecha_devolucion, p.estado,
                           u.nombre AS estudiante_nombre, u.apellido AS estudiante_apellido,
                           i.titulo AS libro_titulo, i.stock, i.numero_ejemplares
                    FROM prestamos p
                    JOIN usuarios u ON p.usuario_id = u.id
                    JOIN items_biblioteca i ON p.item_id = i.id
                    WHERE p.deleted_at IS NULL
                    ORDER BY p.fecha_prestamo DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener préstamos: " . $e->getMessage());
            return [];
        }
    }

    /* ===============================
       OBTENER PRÉSTAMOS POR USUARIO (MULTIUSUARIO REAL)
    =============================== */
    public function getByUsuario($usuario_id) {
        try {
            $sql = "SELECT p.id, p.fecha_prestamo, p.fecha_entrega, p.fecha_devolucion, p.estado,
                           u.nombre AS estudiante_nombre, u.apellido AS estudiante_apellido,
                           i.titulo AS libro_titulo, i.stock, i.numero_ejemplares
                    FROM prestamos p
                    JOIN usuarios u ON p.usuario_id = u.id
                    JOIN items_biblioteca i ON p.item_id = i.id
                    WHERE p.deleted_at IS NULL
                    AND p.usuario_id = ?
                    ORDER BY p.fecha_prestamo DESC";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$usuario_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener préstamos por usuario: " . $e->getMessage());
            return [];
        }
    }

    /* ===============================
       REGISTRAR DEVOLUCIÓN
    =============================== */
    public function registrarDevolucion($id, $usuario_id = null) {
        try {
            $sql = "SELECT item_id, estado FROM prestamos WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$id]);
            $prestamo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$prestamo || $prestamo['estado'] === 'DEVUELTO') return false;

            $oldData = json_encode($prestamo);

            $sql = "UPDATE prestamos SET fecha_devolucion = NOW(), estado = 'DEVUELTO' WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $ok = $stmt->execute([$id]);

            if ($ok) {
                $sql = "UPDATE items_biblioteca SET stock = LEAST(stock + 1, numero_ejemplares) WHERE id = ?";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$prestamo['item_id']]);

                if ($usuario_id) {
                    $this->registrarAuditoria(
                        $usuario_id,
                        'prestamos',
                        $id,
                        'DEVOLUCION',
                        $oldData,
                        json_encode(['estado' => 'DEVUELTO']),
                        'Devolución de préstamo'
                    );
                }
            }

            return $ok;
        } catch (PDOException $e) {
            error_log("Error al registrar devolución: " . $e->getMessage());
            return false;
        }
    }

    /* ===============================
       APROBAR SOLICITUD DE PRÉSTAMO
    =============================== */
    public function aprobarSolicitud($solicitud_id, $usuario_id = null) {
        try {
            $sql = "SELECT sp.item_id, sp.estado, i.stock 
                    FROM solicitudes_prestamo sp
                    JOIN items_biblioteca i ON sp.item_id = i.id
                    WHERE sp.id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$solicitud_id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$data || $data['estado'] !== 'PENDIENTE' || $data['stock'] <= 0) return false;

            $oldData = json_encode($data);

            $sql = "UPDATE solicitudes_prestamo 
                    SET estado = 'APROBADA', fecha_respuesta = NOW() 
                    WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $ok = $stmt->execute([$solicitud_id]);

            if ($ok) {
                $sql = "UPDATE items_biblioteca SET stock = GREATEST(stock - 1, 0) WHERE id = ?";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$data['item_id']]);

                if ($usuario_id) {
                    $this->registrarAuditoria(
                        $usuario_id,
                        'solicitudes_prestamo',
                        $solicitud_id,
                        'APROBAR',
                        $oldData,
                        json_encode(['estado' => 'APROBADA']),
                        'Aprobación de solicitud'
                    );
                }
            }

            return $ok;
        } catch (PDOException $e) {
            error_log("Error al aprobar solicitud: " . $e->getMessage());
            return false;
        }
    }

    /* ===============================
       RECHAZAR SOLICITUD DE PRÉSTAMO
    =============================== */
    public function rechazarSolicitud($solicitud_id, $motivo, $usuario_id = null) {
        try {
            $sql = "SELECT estado FROM solicitudes_prestamo WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$solicitud_id]);
            $oldData = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$oldData || $oldData['estado'] !== 'PENDIENTE') return false;

            $stmt = $this->conn->prepare(
                "UPDATE solicitudes_prestamo 
                 SET estado = 'RECHAZADA', motivo_rechazo = ?, fecha_respuesta = NOW() 
                 WHERE id = ?"
            );
            $ok = $stmt->execute([$motivo, $solicitud_id]);

            if ($ok && $usuario_id) {
                $this->registrarAuditoria(
                    $usuario_id,
                    'solicitudes_prestamo',
                    $solicitud_id,
                    'RECHAZAR',
                    json_encode($oldData),
                    json_encode(['estado' => 'RECHAZADA', 'motivo_rechazo' => $motivo]),
                    'Rechazo de solicitud'
                );
            }

            return $ok;
        } catch (PDOException $e) {
            error_log("Error al rechazar solicitud: " . $e->getMessage());
            return false;
        }
    }
}
