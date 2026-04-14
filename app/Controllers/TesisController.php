<?php
require_once __DIR__ . "/../Models/TesisModel.php";
require_once __DIR__ . "/../Models/CategoriaModel.php";
require_once __DIR__ . "/../Helpers/AuditoriaHelper.php";
require_once __DIR__ . "/../Models/HistorialModel.php";
require_once __DIR__ . '/../Helpers/AuthHelper.php';
require_once __DIR__ . '/../Helpers/RequestSecurityHelper.php';
require_once __DIR__ . '/../Helpers/UploadHelper.php';

class TesisController {

    private $model;
    private $categoryModel;
    private $historialModel;

    public function __construct() {
        $this->model = new TesisModel();
        $this->categoryModel = new CategoriaModel();
        $this->historialModel = new HistorialModel();

        AuthHelper::startSession();
    }

    /* ============================================================
       LISTAR TESIS
    ============================================================ */
    public function indexJson() {
        AuthHelper::requireAdminJson();
        header("Content-Type: application/json");

        $tesis = $this->model->getAllAdmin();
        $categorias = $this->categoryModel->getCategoriasPorTipo(2);

        echo json_encode([
            "success" => true,
            "categorias" => $categorias,
            "tesis" => $tesis
        ]);
        exit;
    }

    /* ============================================================
       OBTENER TESIS POR ID
    ============================================================ */
    public function getJson() {
        AuthHelper::requireAdminJson();
        header("Content-Type: application/json");

        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if ($id <= 0) {
            echo json_encode(["success" => false, "message" => "ID inválido"]);
            exit;
        }

        $item = $this->model->getById($id);

        echo json_encode([
            "success" => $item ? true : false,
            "data" => $item
        ]);
        exit;
    }

    /* ============================================================
       CREAR TESIS + HISTORIAL + AUDITORÍA
    ============================================================ */
    public function createJson() {
    AuthHelper::requireAdminJson();
    RequestSecurityHelper::enforceSameOriginJson();
    header("Content-Type: application/json");
        $data = $this->getRequestData();

    if (!isset($data['titulo'], $data['autor'], $data['categoria_id'], $data['anio'])) {
        echo json_encode(["success" => false, "message" => "Faltan campos obligatorios"]);
        exit;
    }

        $noImagen = !isset($_FILES['portada_file']) || (int)($_FILES['portada_file']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE;
        if ($noImagen) {
            echo json_encode(["success" => false, "message" => "La portada es obligatoria al crear la tesis"]);
            exit;
        }

        $upload = UploadHelper::storeImage('portada_file');
        if (!$upload['success']) {
            echo json_encode(["success" => false, "message" => $upload['message'] ?? 'No se pudo subir la portada']);
            exit;
        }
        $data['portada'] = $upload['path'] ?? ($data['portada'] ?? '');

        $pdfUpload = UploadHelper::storePdf('pdf_file');
        if (!$pdfUpload['success']) {
            echo json_encode(["success" => false, "message" => $pdfUpload['message'] ?? 'No se pudo subir el PDF']);
            exit;
        }
        $data['link_archivo'] = $pdfUpload['path'] ?? ($data['link_archivo'] ?? '');

    // Validar categoría tipo 2
    $categoria = $this->categoryModel->find($data['categoria_id']);
    if (!$categoria || $categoria['tipo_id'] != 2) {
        echo json_encode(["success" => false, "message" => "Categoría inválida"]);
        exit;
    }

    // 🔥 GENERAR CÓDIGO AUTOMÁTICO
    $data['codigo'] = $this->model->generarCodigo(
        $data['anio'],
        $data['autor'],
        $data['categoria_id']
    );

    // INSERTAR
    $ok = $this->model->insert($data);

    if ($ok) {
        $nuevoId = $this->model->getUltimoID();

        // HISTORIAL
        $this->historialModel->registrar(
            $nuevoId,
            $_SESSION['usuario_id'] ?? null,
            "CREACION",
            "Se creó la tesis: " . $data['titulo']
        );

        // AUDITORÍA
        AuditoriaHelper::registrar(
            $_SESSION['usuario_id'] ?? null,
            "items_biblioteca",
            "INSERT",
            $nuevoId,
            null,
            $data,
            "Creación de tesis"
        );
    }

    echo json_encode(["success" => $ok, "codigo" => $data['codigo']]);
    exit;
}


    /* ============================================================
       EDITAR TESIS + HISTORIAL + AUDITORÍA
    ============================================================ */
    public function updateJson() {
        AuthHelper::requireAdminJson();
        RequestSecurityHelper::enforceSameOriginJson();
        header("Content-Type: application/json");

        $data = $this->getRequestData();

        if (!isset($data['id'])) {
            echo json_encode(["success" => false, "message" => "ID obligatorio"]);
            exit;
        }

        // Validar categoría tipo 2
        if (isset($data['categoria_id'])) {
            $categoria = $this->categoryModel->find($data['categoria_id']);
            if (!$categoria || $categoria['tipo_id'] != 2) {
                echo json_encode(["success" => false, "message" => "Categoría inválida"]);
                exit;
            }
        }

        $antes = $this->model->getById($data['id']);
        $upload = UploadHelper::storeImage('portada_file', $antes['portada'] ?? null);
        if (!$upload['success']) {
            echo json_encode(["success" => false, "message" => $upload['message'] ?? 'No se pudo subir la portada']);
            exit;
        }
        $data['portada'] = $upload['path'] ?? ($antes['portada'] ?? '');

        $pdfUpload = UploadHelper::storePdf('pdf_file', $antes['link_archivo'] ?? null);
        if (!$pdfUpload['success']) {
            echo json_encode(["success" => false, "message" => $pdfUpload['message'] ?? 'No se pudo subir el PDF']);
            exit;
        }
        $data['link_archivo'] = $pdfUpload['path'] ?? ($antes['link_archivo'] ?? '');

        $ok = $this->model->updateItem($data);

        if ($ok) {
            $despues = $this->model->getById($data['id']);

            // HISTORIAL
            $this->historialModel->registrar(
                $data['id'],
                $_SESSION['usuario_id'] ?? null,
                "EDICION",
                "Se editó la tesis: " . $data['titulo']
            );

            // AUDITORÍA
            AuditoriaHelper::registrar(
                $_SESSION['usuario_id'] ?? null,
                "items_biblioteca",
                "UPDATE",
                $data['id'],
                $antes,
                $despues,
                "Actualización de tesis"
            );
        }

        echo json_encode(["success" => $ok]);
        exit;
    }

    /* ============================================================
       ELIMINAR TESIS + AUDITORÍA
    ============================================================ */
    public function deleteJson() {
        AuthHelper::requireAdminJson();
        RequestSecurityHelper::enforceSameOriginJson();
        header("Content-Type: application/json");

        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['id'])) {
            echo json_encode(["success" => false, "message" => "ID obligatorio"]);
            exit;
        }

        $antes = $this->model->getById($data['id']);
        $ok = $this->model->delete($data['id']);

        if ($ok) {
            AuditoriaHelper::registrar(
                $_SESSION['usuario_id'] ?? null,
                "items_biblioteca",
                "DELETE",
                $data['id'],
                $antes,
                null,
                "Eliminación de tesis"
            );
        }

        echo json_encode(["success" => $ok]);
        exit;
    }

    /* ============================================================
       CATÁLOGO
    ============================================================ */
    public function catalogo() {
        $tesis = $this->model->getAll();
        require_once __DIR__ . '/../Views/Estudiantes/tesis_catalogo.php';
    }

    public function catalogoStudentLayout() {
        AuthHelper::requireStudentPage(defined('BASE_URL') ? BASE_URL : '');
        $tesis = $this->model->getAll();
        require_once __DIR__ . '/../Views/student/tesis.php';
    }

    /* ============================================================
       SUMAR VISITA (TIEMPO REAL + HISTORIAL)
    ============================================================ */
    public function sumarVisita($id) {
        $id = intval($id);

        $antes = $this->model->getById($id);
        $visitas = $this->model->incrementarVisitas($id);
        $despues = $this->model->getById($id);

        // HISTORIAL
        $this->historialModel->registrar(
            $id,
            $_SESSION['usuario_id'] ?? null,
            "EDICION",
            "Se incrementó la visita de la tesis"
        );

        // AUDITORÍA
        AuditoriaHelper::registrar(
            $_SESSION['usuario_id'] ?? null,
            "items_biblioteca",
            "UPDATE",
            $id,
            $antes,
            $despues,
            "Incremento de visitas en tesis"
        );

        echo json_encode(["ok" => true, "visitas" => $visitas]);
        exit;
    }

    /* ============================================================
       MÁS VISTOS
    ============================================================ */

    // Ejemplo: Controlador de Tesis
public function masVistosJson() // O tesisMasVistosJson
{
    AuthHelper::requireAdminJson();
    header("Content-Type: application/json");

    $fechaInicio = $_GET['fechaInicio'] ?? null;
    $fechaFin    = $_GET['fechaFin'] ?? null;

    if (!$fechaInicio || !$fechaFin) {
        echo json_encode([
            "success" => false,
            "message" => "Rango de fechas obligatorio",
            "tesis" => [] // Cambiamos el nombre del array a 'tesis'
        ]);
        exit;
    }

    // Usamos el modelo de Tesis ($this->tesisModel) y el ID 2
    $tesis = $this->model
        ->getMasVistosPorRango(2, $fechaInicio, $fechaFin); // <-- ¡ID 2 para Tesis!

    echo json_encode([
        "success" => true,
        "tesis" => $tesis // Cambiamos el nombre de la clave
    ]);
    exit;
}

    private function getRequestData(): array
    {
        if (!empty($_POST)) {
            return $_POST;
        }

        $raw = json_decode(file_get_contents("php://input"), true);
        return is_array($raw) ? $raw : [];
    }
   

}
