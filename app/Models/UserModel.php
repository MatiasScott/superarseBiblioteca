<?php
// app/Models/UserModel.php

require_once 'Database.php';

class UserModel
{
    private $conn;
    private $table_name = "usuarios";

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /* ===============================
       FUNCIONES AUXILIARES
    =============================== */

    private function validarCedula($cedula)
    {
        return preg_match('/^\d{10}$/', $cedula);
    }

    private function registrarAuditoria($usuario_id, $tabla, $registro_id, $accion, $datos_anteriores = null, $datos_nuevos = null, $descripcion = null)
    {
        $sql = "INSERT INTO auditoria (usuario_id, tabla_afectada, registro_id, accion, datos_anteriores, datos_nuevos, ip_usuario, user_agent, descripcion)
                VALUES (:usuario_id, :tabla, :registro_id, :accion, :datos_anteriores, :datos_nuevos, :ip_usuario, :user_agent, :descripcion)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':usuario_id' => $usuario_id,
            ':tabla' => $tabla,
            ':registro_id' => $registro_id,
            ':accion' => $accion,
            ':datos_anteriores' => $datos_anteriores ? json_encode($datos_anteriores) : null,
            ':datos_nuevos' => $datos_nuevos ? json_encode($datos_nuevos) : null,
            ':ip_usuario' => $_SERVER['REMOTE_ADDR'] ?? null,
            ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            ':descripcion' => $descripcion
        ]);
    }

    /* ===============================
       CREAR USUARIO
    =============================== */
    public function create($data)
    {
        if (!$this->validarCedula($data['cedula'])) {
            error_log("Cédula inválida: " . $data['cedula']);
            return false;
        }

        // Verificar duplicado
        $queryCheck = "SELECT id FROM usuarios WHERE cedula = :cedula AND deleted_at IS NULL";
        $stmtCheck = $this->conn->prepare($queryCheck);
        $stmtCheck->bindParam(':cedula', $data['cedula']);
        $stmtCheck->execute();
        if ($stmtCheck->fetch()) {
            error_log("Cédula ya existe: " . $data['cedula']);
            return false;
        }

        $query = "INSERT INTO " . $this->table_name . " 
            (rol_id, nombre, apellido, cedula, email, telefono, direccion, carrera, curso, contrasena, estado)
            VALUES 
            (:rol_id, :nombre, :apellido, :cedula, :email, :telefono, :direccion, :carrera, :curso, :contrasena, 'ACTIVO')";

        $stmt = $this->conn->prepare($query);

        try {
            $stmt->bindParam(':rol_id', $data['rol_id']);
            $stmt->bindParam(':nombre', $data['nombre']);
            $stmt->bindParam(':apellido', $data['apellido']);
            $stmt->bindParam(':cedula', $data['cedula']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':telefono', $data['telefono']);
            $stmt->bindParam(':direccion', $data['direccion']);
            $stmt->bindParam(':carrera', $data['carrera']);
            $stmt->bindParam(':curso', $data['curso']);

            $hashedPassword = password_hash($data['contrasena'], PASSWORD_BCRYPT);
            $stmt->bindParam(':contrasena', $hashedPassword);

            $stmt->execute();
            $id = $this->conn->lastInsertId();

            // Registrar auditoría
            $this->registrarAuditoria($_SESSION['usuario_id'] ?? null, $this->table_name, $id, 'INSERT', null, $data, "Creación de usuario");

            return $id;

        } catch (PDOException $e) {
            error_log("Error al crear usuario: " . $e->getMessage());
            return false;
        }
    }

    /* ===============================
       ACTUALIZAR USUARIO
    =============================== */
    public function update($id, $data)
    {
        $usuario_anterior = $this->findById($id);

        if (isset($data['cedula'])) {
            if (!$this->validarCedula($data['cedula'])) {
                error_log("Cédula inválida en update: " . $data['cedula']);
                return false;
            }

            $queryCheck = "SELECT id FROM usuarios WHERE cedula = :cedula AND id != :id AND deleted_at IS NULL";
            $stmtCheck = $this->conn->prepare($queryCheck);
            $stmtCheck->bindParam(':cedula', $data['cedula']);
            $stmtCheck->bindParam(':id', $id);
            $stmtCheck->execute();
            if ($stmtCheck->fetch()) {
                error_log("Cédula ya existe en update: " . $data['cedula']);
                return false;
            }
        }

        $query = "UPDATE " . $this->table_name . " SET ";
        $fields = [];
        $params = [':id' => $id];

        foreach ($data as $key => $value) {
            if ($key !== 'id') {
                $fields[] = "$key = :$key";
                $params[":$key"] = ($key === 'contrasena') ? password_hash($value, PASSWORD_BCRYPT) : $value;
            }
        }

        if (empty($fields)) return false;

        $query .= implode(', ', $fields) . " WHERE id = :id";

        try {
            $stmt = $this->conn->prepare($query);
            foreach ($params as $p => $val) $stmt->bindValue($p, $val);
            $stmt->execute();

            // Registrar auditoría
            $this->registrarAuditoria($_SESSION['usuario_id'] ?? null, $this->table_name, $id, 'UPDATE', $usuario_anterior, $data, "Actualización de usuario");

            return true;
        } catch (PDOException $e) {
            error_log("Error al actualizar usuario: " . $e->getMessage());
            return false;
        }
    }

    /* ===============================
       ELIMINACIÓN SOFT
    =============================== */
    public function delete($id)
    {
        $usuario_anterior = $this->findById($id);

        $query = "UPDATE " . $this->table_name . " 
                  SET deleted_at = NOW(), estado = 'INACTIVO' 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);

        try {
            $stmt->execute();

            // Registrar auditoría
            $this->registrarAuditoria($_SESSION['usuario_id'] ?? null, $this->table_name, $id, 'DELETE', $usuario_anterior, null, "Eliminación de usuario");

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error al eliminar usuario: " . $e->getMessage());
            return false;
        }
    }

    /* ===============================
       VERIFICAR CREDENCIALES (LOGIN)
    =============================== */
    public function verifyCredentials($cedula, $contrasena)
    {
        // Buscar usuario sin filtro de estado para poder validar si está inactivo
        $user = $this->findByCedulaAnyStatus($cedula);
        if (!$user) return null;

        $stored = $user['contrasena'] ?? null;
        if (!$stored) return null;

        $isHash = (bool) preg_match('/^\$2[ayb]\$\d{2}\$[\.\/A-Za-z0-9]{53}$/', $stored);

        if ($isHash) {
            if (password_verify($contrasena, $stored)) {
                // Verificar si el usuario está inactivo
                if (isset($user['estado']) && $user['estado'] === 'INACTIVO') {
                    return ['inactivo' => true];
                }
                // Registrar auditoría LOGIN
                $this->registrarAuditoria($user['id'], $this->table_name, $user['id'], 'LOGIN', null, null, "Login exitoso");
                return $user;
            }
            return null;
        }

        if ($contrasena === $stored) {
            // Verificar si el usuario está inactivo
            if (isset($user['estado']) && $user['estado'] === 'INACTIVO') {
                return ['inactivo' => true];
            }
            // Re-hashear la contraseña
            try {
                $newHash = password_hash($contrasena, PASSWORD_BCRYPT);
                $upd = $this->conn->prepare("UPDATE " . $this->table_name . " SET contrasena = :hash WHERE id = :id");
                $upd->bindParam(':hash', $newHash);
                $upd->bindParam(':id', $user['id']);
                $upd->execute();
            } catch (PDOException $e) {
                error_log("Error al actualizar hash: " . $e->getMessage());
            }

            $this->registrarAuditoria($user['id'], $this->table_name, $user['id'], 'LOGIN', null, null, "Login exitoso");
            return $user;
        }

        return null;
    }

    /* ===============================
       LOGOUT
    =============================== */
    public function logout($user_id)
    {
        $this->registrarAuditoria($user_id, $this->table_name, $user_id, 'LOGOUT', null, null, "Logout del usuario");
    }

    /* ===============================
       FUNCIONES DE CONSULTA
    =============================== */

    public function findByCedula($cedula)
    {
        $query = "SELECT u.*, r.nombre AS rol_nombre
                  FROM " . $this->table_name . " u
                  LEFT JOIN roles r ON u.rol_id = r.id
                  WHERE u.cedula = :cedula AND u.estado = 'ACTIVO'
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cedula', $cedula);

        try {
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user ? $user : null;
        } catch (PDOException $e) {
            error_log("Error al buscar usuario por cédula: " . $e->getMessage());
            return null;
        }
    }

    public function findByCedulaAnyStatus($cedula)
    {
        $query = "SELECT u.*, r.nombre AS rol_nombre
                  FROM " . $this->table_name . " u
                  LEFT JOIN roles r ON u.rol_id = r.id
                  WHERE u.cedula = :cedula
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cedula', $cedula);

        try {
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user ? $user : null;
        } catch (PDOException $e) {
            error_log("Error al buscar usuario por cédula: " . $e->getMessage());
            return null;
        }
    }

    public function findById($id)
    {
        $query = "SELECT u.*, r.nombre AS rol_nombre
                  FROM " . $this->table_name . " u
                  LEFT JOIN roles r ON u.rol_id = r.id
                  WHERE u.id = :id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);

        try {
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user ? $user : null;
        } catch (PDOException $e) {
            error_log("Error al obtener usuario por ID: " . $e->getMessage());
            return null;
        }
    }

    public function getAll()
    {
        $query = "SELECT u.id, u.rol_id, u.nombre, u.apellido, u.cedula, u.email,
                         u.telefono, u.direccion, u.carrera, u.curso, u.estado,
                         r.nombre AS rol_nombre
                  FROM " . $this->table_name . " u
                  LEFT JOIN roles r ON u.rol_id = r.id
                  WHERE u.deleted_at IS NULL
                  ORDER BY u.nombre, u.apellido ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    

    public function getAllActive()
    {
        $query = "SELECT u.*, r.nombre AS rol_nombre
                  FROM " . $this->table_name . " u
                  LEFT JOIN roles r ON u.rol_id = r.id
                  WHERE u.estado = 'ACTIVO' AND u.deleted_at IS NULL
                  ORDER BY u.nombre, u.apellido ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contarUsuarios()
    {
        $query = "SELECT COUNT(*) AS total 
                  FROM " . $this->table_name . " 
                  WHERE deleted_at IS NULL AND estado = 'ACTIVO'";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function getAllRoles()
    {
        $query = "SELECT id, nombre, descripcion, estado FROM roles ORDER BY nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRoleById($id)
    {
        $query = "SELECT id, nombre, descripcion, estado FROM roles WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUsersByRol($rol_id)
    {
        $query = "SELECT u.*, r.nombre AS rol_nombre
                  FROM " . $this->table_name . " u
                  LEFT JOIN roles r ON u.rol_id = r.id
                  WHERE u.rol_id = :rol_id AND u.estado = 'ACTIVO' AND u.deleted_at IS NULL
                  ORDER BY u.nombre, u.apellido ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':rol_id', $rol_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
