<?php
require_once __DIR__ . "/../Models/PrestamoModel.php";

class PrestamosController {
    private $model;

    public function __construct() {
        $this->model = new PrestamoModel();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Listado JSON para tabla
    public function indexJson() {
        header("Content-Type: application/json");
        $prestamos = $this->model->getAll();
        echo json_encode(["success" => true, "prestamos" => $prestamos]);
        exit;
    }

    // Registrar devolución
    public function registrarDevolucionJson() {
        header("Content-Type: application/json");
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['id'])) {
            echo json_encode(["success" => false, "message" => "ID obligatorio"]);
            exit;
        }

        $ok = $this->model->registrarDevolucion($data['id']);
        if ($ok) {
            echo json_encode(["success" => true, "message" => "Devolución registrada correctamente"]);
        } else {
            echo json_encode(["success" => false, "message" => "Error al registrar la devolución"]);
        }
        exit;
    }

    // Aprobar solicitud de préstamo
    public function aprobarSolicitudJson() {
        header("Content-Type: application/json");
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['solicitud_id'])) {
            echo json_encode(["success" => false, "message" => "ID de solicitud obligatorio"]);
            exit;
        }

        $ok = $this->model->aprobarSolicitud($data['solicitud_id']);

        if ($ok) {
            echo json_encode(["success" => true, "message" => "Solicitud aprobada y stock actualizado"]);
        } else {
            echo json_encode(["success" => false, "message" => "No hay stock disponible o error al aprobar"]);
        }
        exit;
    }

    // Rechazar solicitud de préstamo
    public function rechazarSolicitudJson() {
        header("Content-Type: application/json");
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['solicitud_id']) || !isset($data['motivo'])) {
            echo json_encode(["success" => false, "message" => "ID de solicitud y motivo obligatorios"]);
            exit;
        }

        $ok = $this->model->rechazarSolicitud($data['solicitud_id'], $data['motivo']);

        if ($ok) {
            echo json_encode(["success" => true, "message" => "Solicitud rechazada correctamente"]);
        } else {
            echo json_encode(["success" => false, "message" => "Error al rechazar la solicitud"]);
        }
        exit;
    }
}
