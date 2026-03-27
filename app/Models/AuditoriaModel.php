<?php
require_once 'Database.php';

class AuditoriaModel {
    private $conn;

    public function __construct() {
        $this->conn = (new Database())->getConnection();
    }

    public function insert($data)
    {
        try {
            $sql = "INSERT INTO auditoria 
                (usuario_id, tabla_afectada, registro_id, accion, datos_anteriores, datos_nuevos, ip_usuario, user_agent, descripcion)
                VALUES (:usuario_id, :tabla, :registro_id, :accion, :antes, :despues, :ip, :agent, :descripcion)";
            
            $stmt = $this->conn->prepare($sql);
            
            return $stmt->execute([
                ':usuario_id' => $data['usuario_id'],
                ':tabla'      => $data['tabla_afectada'],
                ':registro_id'=> $data['registro_id'],
                ':accion'     => $data['accion'],
                ':antes'      => $data['datos_anteriores'],
                ':despues'    => $data['datos_nuevos'],
                ':ip'         => $data['ip_usuario'],
                ':agent'      => $data['user_agent'],
                ':descripcion'=> $data['descripcion']
            ]);
        } catch (PDOException $e) {
            error_log("Auditoría error: " . $e->getMessage());
            return false;
        }
    }
}
