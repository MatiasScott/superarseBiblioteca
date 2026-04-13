<?php
// app/Controllers/DashboardController.php

require_once '../app/Models/UserModel.php';
require_once __DIR__ . '/../Helpers/AuthHelper.php';

class DashboardController
{
    private $basePath;
    private $userModel;

    public function __construct()
    {
        $this->basePath = defined('BASE_URL') ? BASE_URL : '';
        $this->userModel = new UserModel();
        AuthHelper::startSession();

        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            header("Location: " . $this->basePath . "/login?error=no_autenticado");
            exit();
        }
    }

    /* -------------------------------------------------------
       Dashboard principal (overview con estadísticas)
    ------------------------------------------------------- */
    public function index(): void
    {
        $userId = $_SESSION['id_usuario'] ?? null;

        if (!$userId) {
            header("Location: " . $this->basePath . "/login");
            exit();
        }

        $user = $this->userModel->findById($userId);

        if (!$user) {
            session_destroy();
            header("Location: " . $this->basePath . "/login?error=usuario_no_encontrado");
            exit();
        }

        $rolId = $user['rol_id'] ?? 0;

        if ($rolId == 1) {
            include '../app/Views/dashboard/admin.php';
        } elseif ($rolId == 2) {
            include '../app/Views/student/dashboard.php';
        } else {
            session_destroy();
            header("Location: " . $this->basePath . "/login?error=rol_no_definido");
            exit();
        }
    }

    /* -------------------------------------------------------
       Módulos admin individuales
    ------------------------------------------------------- */
    private function moduleView(string $view): void
    {
        AuthHelper::requireAdminPage();
        include '../app/Views/admin/' . $view . '.php';
    }

    public function modulePrestamos():     void { $this->moduleView('prestamos'); }
    public function moduleLibros():        void { $this->moduleView('libros'); }
    public function modulePublicaciones(): void { $this->moduleView('publicaciones'); }
    public function moduleTesis():         void { $this->moduleView('tesis'); }
    public function moduleUsuarios():      void { $this->moduleView('usuarios'); }
    public function moduleCategorias():    void { $this->moduleView('categorias'); }
    public function moduleEstadisticas():  void { $this->moduleView('estadisticas'); }
    public function moduleMasVistos():     void { $this->moduleView('mas_vistos'); }

    /* -------------------------------------------------------
       Módulos student individuales
    ------------------------------------------------------- */
    private function studentModuleView(string $view): void
    {
        AuthHelper::requireStudentPage($this->basePath);
        include '../app/Views/student/' . $view . '.php';
    }

    public function studentDashboard(): void { $this->studentModuleView('dashboard'); }
    public function studentPrestamos(): void { $this->studentModuleView('prestamos'); }
    public function studentPerfil():    void { $this->studentModuleView('perfil'); }

    /* -------------------------------------------------------
       API: datos del usuario autenticado
    ------------------------------------------------------- */
    public function getUserData(): void
    {
        header('Content-Type: application/json');

        $userId = $_SESSION['id_usuario'] ?? null;

        if (!$userId) {
            http_response_code(401);
            echo json_encode(['error' => 'No autenticado']);
            return;
        }

        $user = $this->userModel->findById($userId);

        if (!$user) {
            http_response_code(404);
            echo json_encode(['error' => 'Usuario no encontrado']);
            return;
        }

        unset($user['contrasena']);

        echo json_encode(['success' => true, 'data' => $user]);
    }
}
