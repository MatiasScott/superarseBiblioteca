<?php

require_once 'Database.php';

class HistorialModel {

    private $conn;

    public function __construct()
    {
        $this->conn = (new Database())->getConnection();
    }

    public function registrar($item_id, $usuario_id, $accion, $detalle = null)
    {
        $sql = "INSERT INTO historial_items (item_id, usuario_id, accion, detalle)
                VALUES (:item_id, :usuario_id, :accion, :detalle)";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute([
            ':item_id' => $item_id,
            ':usuario_id' => $usuario_id,
            ':accion' => $accion,
            ':detalle' => $detalle
        ]);
    }
}
