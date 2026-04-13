<?php
// app/Controllers/LoginController.php

require_once '../app/Models/UserModel.php';
require_once __DIR__ . '/../Helpers/AuthHelper.php';

class LoginController
{
    private $basePath;
    private $userModel;

    public function __construct()
    {
        $this->basePath = defined('BASE_URL') ? BASE_URL : '';
        $this->userModel = new UserModel();
        AuthHelper::startSession();
    }

    public function index()
    {
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
            header("Location: " . $this->basePath . "/dashboard");
            exit();
        }
        include '../app/Views/login/index.php';
    }

    public function check()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . $this->basePath . "/login?error=metodo_invalido");
            exit();
        }

        if (empty($_POST['cedula']) || empty($_POST['contrasena'])) {
            header("Location: " . $this->basePath . "/login?error=campos_vacios");
            exit();
        }

        $cedula = trim($_POST['cedula']);
        $contrasena = trim($_POST['contrasena']);

        $user = $this->userModel->verifyCredentials($cedula, $contrasena);

        if (is_array($user) && ($user['inactivo'] ?? false) === true) {
            header("Location: " . $this->basePath . "/login?error=usuario_inactivo");
            exit();
        }

        if ($user) {
            session_regenerate_id(true);

            $_SESSION['logged_in'] = true;
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['id_usuario'] = $user['id'];

            $_SESSION['cedula'] = $user['cedula'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['nombres_completos'] = $user['nombre'] . ' ' . $user['apellido'];

            $_SESSION['rol_id'] = $user['rol_id'];
            $_SESSION['rol_nombre'] = $user['rol_nombre'];
            $_SESSION['carrera'] = $user['carrera'];
            $_SESSION['curso'] = $user['curso'];
            $_SESSION['login_time'] = time();

            header("Location: " . $this->basePath . "/dashboard");
            exit();
        }

        header("Location: " . $this->basePath . "/login?error=credenciales_invalidas");
        exit();
    }

    public function logout()
    {
        $userId = $_SESSION['usuario_id'] ?? null;

        if ($userId) {
            $this->userModel->logout($userId);
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();
        header("Location: " . $this->basePath . "/login?success=logout");
        exit();
    }

    public static function isAuthenticated()
    {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    public static function getAuthenticatedUserId()
    {
        return $_SESSION['usuario_id'] ?? null;
    }
}
