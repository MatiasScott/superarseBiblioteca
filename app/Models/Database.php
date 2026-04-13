<?php
// app/Models/Database.php

class Database
{
    private static $host = "localhost";
    private static $db_name = "superar1_biblioteca_sistema_superarse";
    private static $username = "root";
    private static $password = "Superarse.2025";
    private static $conn = null;

    public static function getConnection()
    {
        if (self::$conn === null) {
            try {
                self::$conn = new PDO(
                    "mysql:host=" . self::$host . ";dbname=" . self::$db_name . ";charset=utf8",
                    self::$username,
                    self::$password
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
