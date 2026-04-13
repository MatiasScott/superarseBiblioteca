<?php
// app/Controllers/LibrosController.php
require_once __DIR__ . "/../Models/LibrosModel.php";
require_once __DIR__ . "/../Models/CategoriaModel.php";
require_once __DIR__ . "/../Models/PrestamoModel.php";
require_once __DIR__ . "/../Models/HistorialModel.php";
require_once __DIR__ . "/../Helpers/AuditoriaHelper.php";
require_once __DIR__ . '/../Helpers/AuthHelper.php';
require_once __DIR__ . '/../Helpers/RequestSecurityHelper.php';

class LibrosController {

    private $model;
    private $categoryModel;
    private $prestamoModel;
    private $historialModel;

    public function __construct() {
        $this->model = new LibrosModel();
        $this->categoryModel = new CategoriaModel();
        $this->prestamoModel = new PrestamoModel();
        $this->historialModel = new HistorialModel();

        AuthHelper::startSession();
    }

    // LISTADO JSON
    public function indexJson() {
        AuthHelper::requireAdminJson();
        header("Content-Type: application/json");

        $libros = $this->model->getAllAdmin();
        $categorias = $this->categoryModel->getCategoriasPorTipo(1);

        echo json_encode([
            "success" => true,
            "libros" => $libros,
            "categorias" => $categorias
        ]);
        exit;
    }

    // CREATE (INSERT)
    public function createJson() {
        AuthHelper::requireAdminJson();
        RequestSecurityHelper::enforceSameOriginJson();
        header("Content-Type: application/json");
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['titulo']) || !isset($data['codigo'])) {
            echo json_encode(["success" => false, "message" => "Título y código son obligatorios"]);
            exit;
        }

        $nuevoId = $this->model->insert($data);

        // HISTORIAL
        $this->historialModel->registrar(
            $nuevoId,
            $_SESSION['usuario_id'] ?? null,
            "CREACION",
            "Se creó el libro: " . ($data['titulo'] ?? '')
        );

        // AUDITORÍA
        AuditoriaHelper::registrar(
            $_SESSION['usuario_id'] ?? null,
            "items_biblioteca",
            "INSERT",
            $nuevoId,
            null,
            $data,
            "Creación de un nuevo libro"
        );

        echo json_encode(["success" => $nuevoId]);
        exit;
    }

    // UPDATE
    public function updateJson() {
        AuthHelper::requireAdminJson();
        RequestSecurityHelper::enforceSameOriginJson();
        header("Content-Type: application/json");
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['id'])) {
            echo json_encode(["success" => false, "message" => "ID obligatorio"]);
            exit;
        }

        $id = $data['id'];

        $antes = $this->model->getById($id);
        $ok = $this->model->update($data);
        $despues = $this->model->getById($id);

        // HISTORIAL
        $this->historialModel->registrar(
            $id,
            $_SESSION['usuario_id'] ?? null,
            "EDICION",
            "Cambios realizados en el libro"
        );

        // AUDITORÍA
        AuditoriaHelper::registrar(
            $_SESSION['usuario_id'] ?? null,
            "items_biblioteca",
            "UPDATE",
            $id,
            $antes,
            $despues,
            "Actualización de libro"
        );

        echo json_encode(["success" => $ok]);
        exit;
    }

    // DELETE
    public function deleteJson() {
        AuthHelper::requireAdminJson();
        RequestSecurityHelper::enforceSameOriginJson();
        header("Content-Type: application/json");
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['id'])) {
            echo json_encode(["success" => false, "message" => "ID obligatorio"]);
            exit;
        }

        $id = $data['id'];
        $antes = $this->model->getById($id);
        $ok = $this->model->delete($id);

        // HISTORIAL
        $this->historialModel->registrar(
            $id,
            $_SESSION['usuario_id'] ?? null,
            "ELIMINACION",
            "Se eliminó el libro"
        );

        // AUDITORÍA
        AuditoriaHelper::registrar(
            $_SESSION['usuario_id'] ?? null,
            "items_biblioteca",
            "DELETE",
            $id,
            $antes,
            null,
            "Eliminación de libro"
        );

        echo json_encode(["success" => $ok]);
        exit;
    }

    // CATALOGO PARA ESTUDIANTES
    public function catalogoEstudiante() {
    $libros = $this->model->getAll();
    require_once __DIR__ . '/../Views/Estudiantes/libros_catalogo.php';
}

    public function catalogoStudentLayout() {
        AuthHelper::requireStudentPage(defined('BASE_URL') ? BASE_URL : '');
        $libros = $this->model->getAll();
        require_once __DIR__ . '/../Views/student/libros.php';
    }

    // SUMAR VISITA
    public function sumarVisita($id) {

        $id = intval($id);

        $antes = $this->model->getById($id);
        $nuevasVisitas = $this->model->incrementarVisitas($id);
        $despues = $this->model->getById($id);

        // HISTORIAL
        $this->historialModel->registrar(
            $id,
            $_SESSION['usuario_id'] ?? null,
            "EDICION",
            "Se incrementó la visita del libro"
        );

        // AUDITORÍA
        AuditoriaHelper::registrar(
            $_SESSION['usuario_id'] ?? null,
            "items_biblioteca",
            "UPDATE",
            $id,
            $antes,
            $despues,
            "Incremento de visitas del libro"
        );

        echo json_encode(["ok" => true, "visitas" => $nuevasVisitas]);
        exit;
    }

 public function masVistosJson()
{
    AuthHelper::requireAdminJson();
    header("Content-Type: application/json");

    $fechaInicio = $_GET['fechaInicio'] ?? null;
    $fechaFin    = $_GET['fechaFin'] ?? null;

    if (!$fechaInicio || !$fechaFin) {
        echo json_encode([
            "success" => false,
            "message" => "Rango de fechas obligatorio",
            "libros" => []
        ]);
        exit;
    }

    $libros = $this->model
        ->getMasVistosPorRango(1, $fechaInicio, $fechaFin);

    echo json_encode([
        "success" => true,
        "libros" => $libros
    ]);
    exit;
}


}
