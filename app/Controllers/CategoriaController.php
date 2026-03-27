<?php
// app/Controllers/CategoriaController.php
require_once __DIR__ . "/../Models/CategoriaModel.php";


class CategoriaController {
    private $model;

    public function __construct() {
        $this->model = new CategoriaModel();
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function getCategoriasJson() {
        header("Content-Type: application/json");
        $data = $this->model->getAll();
        echo json_encode([
            "success" => true,
            "data" => $data
        ]);
        exit;
    }

    public function getTiposJson() {
        header("Content-Type: application/json");
        $data = $this->model->getTipos();
        echo json_encode([
            "success" => true,
            "data" => $data
        ]);
        exit;
    }

    public function createCategoria() {
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
