<?php
require_once __DIR__ . "/../Models/AuditoriaModel.php";

class AuditoriaHelper {

    public static function registrar($usuarioId, $tabla, $accion, $registroId = null, $datosAntes = null, $datosDespues = null, $descripcion = null)
    {
        $model = new AuditoriaModel();

        $ip = $_SERVER['REMOTE_ADDR'] ?? 'DESCONOCIDA';
        $agente = $_SERVER['HTTP_USER_AGENT'] ?? 'DESCONOCIDO';

        return $model->insert([
            'usuario_id'      => $usuarioId,
            'tabla_afectada'  => $tabla,
            'accion'          => $accion,
            'registro_id'     => $registroId,
            'datos_anteriores'=> $datosAntes ? json_encode($datosAntes, JSON_UNESCAPED_UNICODE) : null,
            'datos_nuevos'    => $datosDespues ? json_encode($datosDespues, JSON_UNESCAPED_UNICODE) : null,
            'ip_usuario'      => $ip,
            'user_agent'      => $agente,
            'descripcion'     => $descripcion
        ]);
    }
}
