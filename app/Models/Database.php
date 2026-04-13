<?php
// app/Models/Database.php

class Database
{
    private static $conn = null;
    private static $envLoaded = false;

    private static function loadEnv(): void
    {
        if (self::$envLoaded) {
            return;
        }

        $autoloadPath = dirname(__DIR__, 2) . '/vendor/autoload.php';
        if (file_exists($autoloadPath)) {
            require_once $autoloadPath;
        }

        if (class_exists(\Dotenv\Dotenv::class) && file_exists(dirname(__DIR__, 2) . '/.env')) {
            \Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2))->safeLoad();
        }

        self::$envLoaded = true;
    }

    public static function getConnection()
    {
        self::loadEnv();

        if (self::$conn === null) {
            $host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost';
            $dbName = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'superar1_biblioteca_sistema_superarse';
            $username = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root';
            $password = $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: '';
            $charset = $_ENV['DB_CHARSET'] ?? getenv('DB_CHARSET') ?: 'utf8mb4';

            try {
                self::$conn = new PDO(
                    "mysql:host=" . $host . ";dbname=" . $dbName . ";charset=" . $charset,
                    $username,
                    $password
                );
                self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $exception) {
                // 🚫 NO usar echo ni die → rompe el JSON
                error_log("Error de conexión a la base de datos: " . $exception->getMessage());
                throw new Exception("No se pudo conectar a la base de datos. Verifica las credenciales o el servidor.");
            }
        }

        return self::$conn;
    }
}
