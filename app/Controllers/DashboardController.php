<?php
// app/Controllers/DashboardController.php

require_once '../app/Models/UserModel.php';

class DashboardController
{
    private $basePath;
    private $userModel;

    public function __construct()
    {
        $this->basePath = defined('BASE_URL') ? BASE_URL : '';
        $this->userModel = new UserModel();
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Verificar que el usuario esté autenticado
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            header("Location: " . $this->basePath . "/login?error=no_autenticado");
            exit();
        }
    }

    /**
     * Mostrar dashboard principal
     */
    public function index()
    {
        // Obtener información del usuario autenticado
        $userId = $_SESSION['id_usuario'] ?? null;
        
        if (!$userId) {
            header("Location: " . $this->basePath . "/login");
            exit();
        }

        $user = $this->userModel->findById($userId);
        
        if (!$user) {
            // Si el usuario no se encuentra en DB, destruir sesión y redirigir
            session_destroy();
            header("Location: " . $this->basePath . "/login?error=usuario_no_encontrado");
            exit();
        }

        // --- LÓGICA DE REDIRECCIÓN CORREGIDA ---
        $rolId = $user['rol_id'] ?? 0;
        
        // --- DIAGNÓSTICO TEMPORAL ---
        // Creamos una variable de diagnóstico disponible para las vistas.
        $debugInfo = "<!-- DIAGNÓSTICO ROL: ID de usuario: {$userId}, Rol cargado: {$rolId}, Nombre de rol (BD): " . ($user['rol_nombre'] ?? 'N/A') . " -->";
        error_log("DIAGNOSIS: User ID: {$userId}, Role ID loaded: {$rolId}, Role Name: " . ($user['rol_nombre'] ?? 'N/A'));
        // --- FIN DIAGNÓSTICO TEMPORAL ---


        // Determinar qué dashboard mostrar según el rol (1=Admin, 2=Estudiante)
        if ($rolId == 1) {
            // Rol 1 = Administrador
            echo $debugInfo; // Mostrar diagnóstico en la página
            include '../app/Views/dashboard/admin.php';
        } else if ($rolId == 2) {
            // Rol 2 = Estudiante
            echo $debugInfo; // Mostrar diagnóstico en la página
            include '../app/Views/dashboard/student.php';
        } else {
            // Manejar roles no mapeados o rol 0 por seguridad
            session_destroy();
            header("Location: " . $this->basePath . "/login?error=rol_no_definido");
            exit();
        }
    }

    /**
     * Obtener datos del usuario en JSON
     */
    public function getUserData()
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

        // Remover información sensible
        unset($user['contrasena']);

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $user
        ]);
    }
}