<?php
// app/Controllers/LoginController.php

require_once '../app/Models/UserModel.php';

class LoginController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function index()
    {
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
            header("Location: /dashboard");
            exit();
        }
        include '../app/Views/login/index.php';
    }

    public function check()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /login?error=metodo_invalido");
            exit();
        }

        if (empty($_POST['cedula']) || empty($_POST['contrasena'])) {
            header("Location: /login?error=campos_vacios");
            exit();
        }

        $cedula = trim($_POST['cedula']);
        $contrasena = trim($_POST['contrasena']);

        $user = $this->userModel->verifyCredentials($cedula, $contrasena);

        if (is_array($user) && ($user['inactivo'] ?? false) === true) {
            header("Location: /login?error=usuario_inactivo");
            exit();
        }

        if ($user) {

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

            header("Location: /dashboard");
            exit();
        }

        header("Location: /login?error=credenciales_invalidas");
        exit();
    }

    public function logout()
    {
        session_destroy();
        header("Location: /login?success=logout");
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
