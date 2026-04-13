<?php
// app/Controllers/CategoriaController.php
require_once __DIR__ . "/../Models/CategoriaModel.php";
require_once __DIR__ . '/../Helpers/AuthHelper.php';
require_once __DIR__ . '/../Helpers/RequestSecurityHelper.php';


class CategoriaController {
    private $model;

    public function __construct() {
        $this->model = new CategoriaModel();
        AuthHelper::startSession();
    }

    public function getCategoriasJson() {
        AuthHelper::requireAdminJson();
        header("Content-Type: application/json");
        $data = $this->model->getAll();
        echo json_encode([
            "success" => true,
            "data" => $data
        ]);
        exit;
    }

    public function getTiposJson() {
        AuthHelper::requireAdminJson();
        header("Content-Type: application/json");
        $data = $this->model->getTipos();
        echo json_encode([
            "success" => true,
            "data" => $data
        ]);
        exit;
    }

    public function createCategoria() {
        AuthHelper::requireAdminJson();
        RequestSecurityHelper::enforceSameOriginJson();
        header("Content-Type: application/json");
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['nombre']) || !isset($data['tipo_id'])) {
            echo json_encode(['success' => false, 'message' => "Nombre y tipo son obligatorios"]);
            exit;
        }
        $ok = $this->model->create($data['nombre'], $data['tipo_id'], $data['estado'] ?? "ACTIVO");
        echo json_encode(['success' => $ok]);
        exit;
    }

    public function updateCategoria() {
        AuthHelper::requireAdminJson();
        RequestSecurityHelper::enforceSameOriginJson();
        header("Content-Type: application/json");
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['id']) || !isset($data['nombre']) || !isset($data['tipo_id']) || !isset($data['estado'])) {
            echo json_encode(['success' => false, 'message' => "Todos los campos son obligatorios"]);
            exit;
        }
        $ok = $this->model->update($data['id'], $data['nombre'], $data['tipo_id'], $data['estado']);
        echo json_encode(['success' => $ok]);
        exit;
    }

    public function deleteCategoria() {
        AuthHelper::requireAdminJson();
        RequestSecurityHelper::enforceSameOriginJson();
        header("Content-Type: application/json");
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['id'])) {
            echo json_encode(['success' => false, 'message' => "ID obligatorio"]);
            exit;
        }
        $ok = $this->model->delete($data['id']);
        echo json_encode(['success' => $ok]);
        exit;
    }
}
