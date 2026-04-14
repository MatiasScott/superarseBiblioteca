<?php
// app/Controllers/PublicacionesController.php
require_once __DIR__ . "/../Models/PublicacionModel.php";
require_once __DIR__ . "/../Models/CategoriaModel.php";
require_once __DIR__ . "/../Models/HistorialModel.php";
require_once __DIR__ . "/../Helpers/AuditoriaHelper.php";
require_once __DIR__ . '/../Helpers/AuthHelper.php';
require_once __DIR__ . '/../Helpers/RequestSecurityHelper.php';
require_once __DIR__ . '/../Helpers/UploadHelper.php';

class PublicacionesController
{
    private $model;
    private $categoryModel;
    private $historialModel;

    public function __construct()
    {
        $this->model = new PublicacionModel();
        $this->categoryModel = new CategoriaModel();
        $this->historialModel = new HistorialModel();

        AuthHelper::startSession();
    }

    /**
     * Listado de publicaciones
     */
    public function indexJson()
    {
        AuthHelper::requireAdminJson();
        header("Content-Type: application/json");

        $publicaciones = $this->model->getAllAdmin();
        $categorias = $this->categoryModel->getCategoriasPorTipo(3);

        echo json_encode([
            "success" => true,
            "categorias" => $categorias,
            "publicaciones" => $publicaciones
        ]);
        exit;
    }

    /**
     * Obtener publicación por ID
     */
    public function getJson($id)
    {
        AuthHelper::requireAdminJson();
        header("Content-Type: application/json");

        $item = $this->model->getById($id);

        echo json_encode([
            "success" => $item ? true : false,
            "data" => $item
        ]);
        exit;
    }

    /**
     * Crear publicación (INSERT)
     */
   public function createJson()
{
    AuthHelper::requireAdminJson();
    RequestSecurityHelper::enforceSameOriginJson();
    header("Content-Type: application/json");
    $data = $this->getRequestData();

    /* ===============================
       VALIDACIONES BÁSICAS
    =============================== */
    if (
        empty($data['titulo']) ||
        empty($data['descripcion']) ||
        empty($data['categoria_id']) ||
        empty($data['anio'])
    ) {
        echo json_encode([
            "success" => false,
            "message" => "Datos obligatorios incompletos"
        ]);
        exit;
    }

    /* ===============================
       VALIDAR CATEGORÍA (TIPO PUBLICACIÓN)
    =============================== */
    $categoria = $this->categoryModel->find($data['categoria_id']);
    if (!$categoria || $categoria['tipo_id'] != 3) {
        echo json_encode([
            "success" => false,
            "message" => "Categoría inválida para publicaciones"
        ]);
        exit;
    }

    $noImagen = !isset($_FILES['portada_file']) || (int)($_FILES['portada_file']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE;
    if ($noImagen) {
        echo json_encode(["success" => false, "message" => "La portada es obligatoria al crear la publicación"]);
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

    /* ===============================
       GENERAR CÓDIGO AUTOMÁTICO
       PK lógica: anio + categoria
    =============================== */
    try {
        $codigo = $this->model->generarCodigo(
            $data['anio'],
            $data['categoria_id']
        );
    } catch (Exception $e) {
        echo json_encode([
            "success" => false,
            "message" => $e->getMessage()
        ]);
        exit;
    }

    // Asignar código generado
    $data['codigo'] = $codigo;

    /* ===============================
       INSERTAR PUBLICACIÓN
    =============================== */
    $nuevoId = $this->model->insert($data);

    if (!$nuevoId) {
        echo json_encode([
            "success" => false,
            "message" => "Error al crear la publicación"
        ]);
        exit;
    }

    /* ===============================
       HISTORIAL
    =============================== */
    $this->historialModel->registrar(
        $nuevoId,
        $_SESSION['usuario_id'] ?? null,
        "CREACION",
        "Se creó la publicación: " . $data['titulo']
    );

    /* ===============================
       AUDITORÍA
    =============================== */
    AuditoriaHelper::registrar(
        $_SESSION['usuario_id'] ?? null,
        "items_biblioteca",
        "INSERT",
        $nuevoId,
        null,
        $data,
        "Creación de nueva publicación"
    );

    /* ===============================
       RESPUESTA
    =============================== */
    echo json_encode([
        "success" => true,
        "id" => $nuevoId,
        "codigo" => $codigo
    ]);
    exit;
}


    /**
     * Actualizar publicación (UPDATE)
     */
    public function updateJson()
    {
        AuthHelper::requireAdminJson();
        RequestSecurityHelper::enforceSameOriginJson();
        header("Content-Type: application/json");
        $data = $this->getRequestData();

        if (!isset($data['id']) || !isset($data['titulo']) || !isset($data['descripcion']) || !isset($data['categoria_id'])) {
            echo json_encode(["success" => false, "message" => "Datos obligatorios faltantes"]);
            exit;
        }

        $id = $data['id'];

        // Validación tipo categoría
        $categoria = $this->categoryModel->find($data['categoria_id']);
        if (!$categoria || $categoria['tipo_id'] != 3) {
            echo json_encode(["success" => false, "message" => "Categoría inválida"]);
            exit;
        }

        // Datos antes
        $antes = $this->model->getById($id);
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

        // Actualizar
        $ok = $this->model->updateItem($data);

        // Datos después
        $despues = $this->model->getById($id);

        // HISTORIAL
        $this->historialModel->registrar(
            $id,
            $_SESSION['usuario_id'] ?? null,
            "EDICION",
            "Se actualizó la publicación"
        );

        // AUDITORÍA
        AuditoriaHelper::registrar(
            $_SESSION['usuario_id'] ?? null,
            "items_biblioteca",
            "UPDATE",
            $id,
            $antes,
            $despues,
            "Actualización de publicación"
        );

        echo json_encode(["success" => $ok]);
        exit;
    }

    /**
     * Eliminar publicación (DELETE)
     */
    public function deleteJson()
    {
        AuthHelper::requireAdminJson();
        RequestSecurityHelper::enforceSameOriginJson();
        header("Content-Type: application/json");
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['id'])) {
            echo json_encode(["success" => false, "message" => "ID obligatorio"]);
            exit;
        }

        $id = $data['id'];

        // Antes
        $antes = $this->model->getById($id);

        // Eliminar
        $ok = $this->model->delete($id);

        // HISTORIAL
        $this->historialModel->registrar(
            $id,
            $_SESSION['usuario_id'] ?? null,
            "ELIMINACION",
            "Se eliminó la publicación"
        );

        // AUDITORÍA
        AuditoriaHelper::registrar(
            $_SESSION['usuario_id'] ?? null,
            "items_biblioteca",
            "DELETE",
            $id,
            $antes,
            null,
            "Eliminación de publicación"
        );

        echo json_encode(["success" => $ok]);
        exit;
    }

    /**
     * Catalogo para estudiantes
     */
    public function catalogo()
    {
        $publicaciones = $this->model->getAll();
        require_once __DIR__ . '/../Views/Estudiantes/publicaciones_catalogo.php';
    }

    public function catalogoStudentLayout()
    {
        AuthHelper::requireStudentPage(defined('BASE_URL') ? BASE_URL : '');
        $publicaciones = $this->model->getAll();
        require_once __DIR__ . '/../Views/student/publicaciones.php';
    }

    /**
     * Sumar visitas
     */
    public function sumarVisita($id)
    {
        $id = intval($id);

        $antes = $this->model->getById($id);
        $nuevasVisitas = $this->model->incrementarVisitas($id);
        $despues = $this->model->getById($id);

        // HISTORIAL
        $this->historialModel->registrar(
            $id,
            $_SESSION['usuario_id'] ?? null,
            "EDICION",
            "Se incrementó la visita de esta publicación"
        );

        // AUDITORÍA
        AuditoriaHelper::registrar(
            $_SESSION['usuario_id'] ?? null,
            "items_biblioteca",
            "UPDATE",
            $id,
            $antes,
            $despues,
            "Incremento de visitas en publicación"
        );

        echo json_encode(['ok' => true, 'visitas' => $nuevasVisitas]);
        exit;
    }
    // Ejemplo: Controlador de Publicaciones
public function masVistosJson() // O publicacionesMasVistosJson
{
    AuthHelper::requireAdminJson();
    header("Content-Type: application/json");

    $fechaInicio = $_GET['fechaInicio'] ?? null;
    $fechaFin    = $_GET['fechaFin'] ?? null;

    if (!$fechaInicio || !$fechaFin) {
        echo json_encode([
            "success" => false,
            "message" => "Rango de fechas obligatorio",
            "publicaciones" => [] // Cambiamos el nombre del array a 'publicaciones'
        ]);
        exit;
    }

    // Usamos el modelo de Publicaciones ($this->publicacionesModel) y el ID 3
    $publicaciones = $this->model
        ->getMasVistosPorRango(3, $fechaInicio, $fechaFin); // <-- ¡ID 3 para Publicaciones!

    echo json_encode([
        "success" => true,
        "publicaciones" => $publicaciones // Cambiamos el nombre de la clave
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
