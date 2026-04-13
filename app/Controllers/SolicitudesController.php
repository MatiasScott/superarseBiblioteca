<?php
require_once '../app/Models/SolicitudesModel.php';
require_once __DIR__ . '/../Helpers/AuthHelper.php';
require_once __DIR__ . '/../Helpers/RequestSecurityHelper.php';

class SolicitudesController
{
    private $model;

    public function __construct()
    {
        AuthHelper::startSession();

        $this->model = new SolicitudesModel();
    }

    /* =====================================================
       ESTUDIANTE - CREAR SOLICITUD DE PRÉSTAMO
    ====================================================== */
    public function crear()
    {
        header('Content-Type: application/json; charset=utf-8');
        AuthHelper::requireLoginJson();
        RequestSecurityHelper::enforceSameOriginJson();

        if (!isset($_POST['item_id'])) {
            echo json_encode(["ok" => false, "msg" => "Falta item_id"]);
            exit;
        }

        $usuario_id = $_SESSION['usuario_id'];
        $item_id = $_POST['item_id'];

        $ok = $this->model->crearSolicitud($usuario_id, $item_id);

        echo json_encode(
            $ok ? 
            ["ok" => true, "msg" => "Solicitud enviada correctamente"] :
            ["ok" => false, "msg" => "Error al registrar la solicitud"]
        );
        exit;
    }

/* =====================================================
   ADMIN - LISTAR SOLICITUDES CON FILTRO POR MES
===================================================== */
public function listar()
{
    AuthHelper::requireAdminJson();
    header('Content-Type: application/json; charset=utf-8');

    $mes = $_GET['mes'] ?? '';
    $estado = $_GET['estado'] ?? '';

    try {
        $this->model->actualizarRetrasados();

        $data = $this->model->getAllFiltered($mes, $estado);

        foreach ($data as &$s) {
            $s['fecha_solicitud'] = $s['fecha_solicitud'] ? date('Y-m-d H:i', strtotime($s['fecha_solicitud'])) : '-';
            $s['fecha_respuesta'] = $s['fecha_respuesta'] ? date('Y-m-d H:i', strtotime($s['fecha_respuesta'])) : '-';
        }

        echo json_encode(["success" => true, "data" => $data]);

    } catch (Exception $e) {
        echo json_encode(["success" => false, "msg" => $e->getMessage()]);
    }
    exit;
}


    /* =====================================================
       ADMIN - APROBAR SOLICITUD
    ====================================================== */
   public function aprobar()
{
    AuthHelper::requireAdminJson();
    header('Content-Type: application/json; charset=utf-8');
    RequestSecurityHelper::enforceSameOriginJson();

    if (!isset($_POST['id'])) {
        echo json_encode(["ok" => false, "msg" => "Falta ID"]);
        exit;
    }

    $usuario_id = $_SESSION['usuario_id'] ?? null;

    $ok = $this->model->aprobar($_POST['id'], $usuario_id);

    echo json_encode(
        $ok ?
        ["ok" => true, "msg" => "Solicitud aprobada satisfactoriamente"] :
        ["ok" => false, "msg" => "No hay stock suficiente o error al aprobar"]
    );
    exit;
}

    /* =====================================================
       ADMIN - RECHAZAR SOLICITUD
    ====================================================== */
 public function rechazar()
{
    AuthHelper::requireAdminJson();
    header('Content-Type: application/json; charset=utf-8');
    RequestSecurityHelper::enforceSameOriginJson();

    if (!isset($_POST['id']) || !isset($_POST['motivo'])) {
        echo json_encode(["ok" => false, "msg" => "Falta ID o motivo"]);
        exit;
    }

    $usuario_id = $_SESSION['usuario_id'] ?? null;

    $ok = $this->model->rechazar($_POST['id'], $_POST['motivo'], $usuario_id);

    echo json_encode(
        $ok ?
        ["ok" => true, "msg" => "Solicitud rechazada"] :
        ["ok" => false, "msg" => "Error al rechazar"]
    );
    exit;
}

    /* =====================================================
       ADMIN - MARCAR COMO ENTREGADO
    ====================================================== */
 public function entregar()
{
    AuthHelper::requireAdminJson();
    header('Content-Type: application/json; charset=utf-8');
    RequestSecurityHelper::enforceSameOriginJson();

    if (!isset($_POST['id'])) {
        echo json_encode(["ok" => false, "msg" => "Falta ID"]);
        exit;
    }

    $usuario_id = $_SESSION['usuario_id'] ?? null;

    $ok = $this->model->entregar($_POST['id'], $usuario_id);

    echo json_encode(
        $ok ?
        ["ok" => true, "msg" => "Libro marcado como ENTREGADO y stock actualizado"] :
        ["ok" => false, "msg" => "Error al marcar como ENTREGADO"]
    );
    exit;
}

    /* =====================================================
       ESTUDIANTE - MIS SOLICITUDES
    ====================================================== */
    public function mis()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!isset($_SESSION['usuario_id'])) {
            echo json_encode(["error" => "NO AUTENTICADO"]);
            exit;
        }

        $solicitudes = $this->model->getByUsuario($_SESSION['usuario_id']);

        foreach ($solicitudes as &$s) {
            $s['fecha_solicitud'] = $s['fecha_solicitud']
                ? date('Y-m-d H:i', strtotime($s['fecha_solicitud']))
                : '-';

            $s['fecha_respuesta'] = $s['fecha_respuesta']
                ? date('Y-m-d H:i', strtotime($s['fecha_respuesta']))
                : '-';
        }

        echo json_encode($solicitudes);
        exit;
    }

    /* =====================================================
   ADMIN - MARCAR COMO RETRASADO
====================================================== */
public function retrasado()
{
    AuthHelper::requireAdminJson();
    header('Content-Type: application/json; charset=utf-8');
    RequestSecurityHelper::enforceSameOriginJson();

    if (!isset($_POST['id'])) {
        echo json_encode(["ok" => false, "msg" => "Falta ID"]);
        exit;
    }

    $ok = $this->model->marcarRetrasado($_POST['id']);

    echo json_encode(
        $ok ?
        ["ok" => true, "msg" => "Solicitud marcada como RETRASADA"] :
        ["ok" => false, "msg" => "Error al marcar como retrasada"]
    );
    exit;
}

    /* =====================================================
       ADMIN - OBTENER SOLICITUDES PENDIENTES (NOTIFICACIONES)
    ====================================================== */
    public function obtenerPendientes()
    {
        AuthHelper::requireAdminJson();
        header('Content-Type: application/json; charset=utf-8');

        try {
            // Solo trae solicitudes PENDIENTES
            $data = $this->model->getAllFiltered("", "PENDIENTE");

            // Limitar a las últimas 10
            $data = array_slice($data, 0, 10);

            foreach ($data as &$s) {
                $s['fecha_solicitud'] = $s['fecha_solicitud'] ? date('d/m/Y H:i', strtotime($s['fecha_solicitud'])) : '-';
            }

            echo json_encode([
                "success" => true,
                "count" => count($data),
                "data" => $data
            ]);

        } catch (Exception $e) {
            echo json_encode(["success" => false, "msg" => $e->getMessage()]);
        }
        exit;
    }



}
