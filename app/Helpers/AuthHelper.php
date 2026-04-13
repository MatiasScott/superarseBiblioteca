<?php

class AuthHelper
{
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function requireAdminJson(): void
    {
        self::startSession();

        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['rol_id']) || (int) $_SESSION['rol_id'] !== 1) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => 'No autorizado'
            ]);
            exit;
        }
    }

    public static function requireLoginJson(): void
    {
        self::startSession();

        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => 'No autenticado'
            ]);
            exit;
        }
    }

    public static function requireAdminPage(string $basePath = ''): void
    {
        self::startSession();

        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['rol_id']) || (int) $_SESSION['rol_id'] !== 1) {
            header('Location: ' . rtrim($basePath, '/') . '/login');
            exit;
        }
    }
}