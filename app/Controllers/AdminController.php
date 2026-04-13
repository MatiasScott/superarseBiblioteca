<?php
// app/Controllers/AdminController.php

require_once '../app/Models/UserModel.php';
require_once __DIR__ . '/../Helpers/AuthHelper.php';
require_once __DIR__ . '/../Helpers/RequestSecurityHelper.php';

class AdminController
{
    private $basePath;
    private $userModel;

    public function __construct()
    {
        $this->basePath = defined('BASE_URL') ? BASE_URL : '';
        AuthHelper::startSession();

        $this->userModel = new UserModel();

        // SOLO ADMINISTRADORES
        if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 1) {
            header("Location: " . $this->basePath . "/login");
            exit();
        }
    }

    /* =====================================================
     * =============== VISTA PRINCIPAL =====================
     * ===================================================== */
    public function index()
    {
        include '../app/Views/dashboard/admin.php';
    }

    /* =====================================================
     * =============== OBTENER DATOS =======================
     * ===================================================== */

    public function getUsersJson()
    {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => $this->userModel->getAll()
        ]);
        exit;
    }

    public function getRolesJson()
    {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => $this->userModel->getAllRoles()
        ]);
        exit;
    }

    /* =====================================================
     * =============== CREAR USUARIO =======================
     * ===================================================== */
    public function createUser()
    {
        header('Content-Type: application/json');
        RequestSecurityHelper::enforceSameOriginJson();
        $data = json_decode(file_get_contents('php://input'), true);

        // CAMPOS OBLIGATORIOS
        $required = ['nombre', 'apellido', 'cedula', 'email', 'rol_id'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                echo json_encode(['success' => false, 'message' => "El campo $field es obligatorio"]);
                exit;
            }
        }

        $rol = intval($data['rol_id']);

        // Validación específica para estudiantes (rol_id = 2)
        if ($rol === 2) {  
            if (empty($data['carrera']) || empty($data['curso'])) {
                echo json_encode([
                    'success' => false,
                    'message' => "Carrera y curso son obligatorios para estudiantes"
                ]);
                exit;
            }
        } else {
            $data['carrera'] = null;
            $data['curso'] = null;
        }

        // Contraseña: generar si no se envía
        $password = (isset($data['contrasena']) && trim($data['contrasena']) !== "")
            ? $data['contrasena']
            : $this->generateTemporaryPassword();

        $data['contrasena'] = $password;

        $id = $this->userModel->create($data);

        echo json_encode([
            'success' => $id ? true : false,
            'message' => $id
                ? "Usuario creado correctamente. Contraseña: $password"
                : "Error al crear usuario"
        ]);
        exit;
    }

    /* =====================================================
     * =============== ACTUALIZAR USUARIO ==================
     * ===================================================== */
    public function updateUser()
    {
        header("Content-Type: application/json");
        RequestSecurityHelper::enforceSameOriginJson();
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['id'])) {
            echo json_encode(['success' => false, 'message' => "ID requerido"]);
            exit;
        }

        $id = $data['id'];

        // Campos válidos para actualizar
        $permitidos = [
            'rol_id', 'nombre', 'apellido', 'cedula', 'email',
            'telefono', 'direccion', 'carrera', 'curso', 'contrasena', 'estado'
        ];

        $limpio = [];
        foreach ($permitidos as $campo) {
            if (isset($data[$campo])) {
                if ($campo === 'contrasena' && trim($data[$campo]) === "") continue;
                $limpio[$campo] = $data[$campo];
            }
        }

        // Validación para estudiantes
        $rol = isset($limpio['rol_id']) ? intval($limpio['rol_id']) : null;
        if ($rol === 2) {
            if (empty($limpio['carrera']) || empty($limpio['curso'])) {
                echo json_encode([
                    'success' => false,
                    'message' => "Carrera y curso son obligatorios para estudiantes"
                ]);
                exit;
            }
        } else {
            $limpio['carrera'] = null;
            $limpio['curso'] = null;
        }

        $ok = $this->userModel->update($id, $limpio);

        echo json_encode([
            'success' => $ok,
            'message' => $ok ? "Usuario actualizado correctamente" : "Error al actualizar usuario"
        ]);
        exit;
    }

    /* =====================================================
     * =============== ELIMINAR USUARIO ====================
     * ===================================================== */
    public function deleteUser()
    {
        header("Content-Type: application/json");
        RequestSecurityHelper::enforceSameOriginJson();
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['id'])) {
            echo json_encode(['success' => false, 'message' => "ID requerido"]);
            exit;
        }

        $ok = $this->userModel->delete($data['id']);

        echo json_encode([
            'success' => $ok,
            'message' => $ok ? "Usuario eliminado correctamente" : "Error al eliminar usuario"
        ]);
        exit;
    }

    /* =====================================================
     * =============== GENERAR CONTRASEÑA TEMPORAL =========
     * ===================================================== */
    private function generateTemporaryPassword()
    {
        return bin2hex(random_bytes(8));
    }

 /* =====================================================
 * ===============    SOLICITUDES    ====================
 * ===================================================== */

private function cargarModeloSolicitudes()
{
    require_once '../app/Models/SolicitudesModel.php';
    return new SolicitudesModel();
}

/* ==========================================
   ADMIN - LISTAR SOLICITUDES
========================================== */
public function listarSolicitudes()
{
    header('Content-Type: application/json; charset=utf-8');

    $model = $this->cargarModeloSolicitudes();
    $data = $model->getAll();

    echo json_encode($data);
}

/* ==========================================
   ADMIN - APROBAR SOLICITUD
========================================== */
public function aprobarSolicitud()
{
    header('Content-Type: application/json; charset=utf-8');

    if (!isset($_POST['id'])) {
        echo json_encode(["error" => "Falta ID"]);
        return;
    }

    $model = $this->cargarModeloSolicitudes();
    $model->aprobar($_POST['id']);

    echo json_encode([
        "ok" => true,
        "msg" => "Solicitud aprobada"
    ]);
}

/* ==========================================
   ADMIN - RECHAZAR SOLICITUD
========================================== */
public function rechazarSolicitud()
{
    header('Content-Type: application/json; charset=utf-8');

    if (!isset($_POST['id']) || !isset($_POST['motivo'])) {
        echo json_encode(["error" => "Falta ID o motivo"]);
        return;
    }

    $model = $this->cargarModeloSolicitudes();
    $model->rechazar($_POST['id'], $_POST['motivo']);

    echo json_encode([
        "ok" => true,
        "msg" => "Solicitud rechazada"
    ]);
}

/* ==========================================
   ADMIN - MARCAR COMO ENTREGADO
========================================== */
public function registrarEntrega()
{
    header('Content-Type: application/json; charset=utf-8');

    if (!isset($_POST['id'])) {
        echo json_encode(["error" => "Falta ID"]);
        return;
    }

    $model = $this->cargarModeloSolicitudes();
    $model->entregar($_POST['id']);

    echo json_encode([
        "ok" => true,
        "msg" => "Libro marcado como ENTREGADO"
    ]);
}

}
