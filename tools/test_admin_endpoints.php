<?php
// tools/test_admin_endpoints.php
// Uso: accede desde http://localhost/SuperarseBiblioteca/public/test-admin

// Simular sesión de admin
$_SESSION['rol_id'] = 1;
session_start();

// Incluir modelos
require_once '../app/Models/UserModel.php';
require_once '../app/Models/Database.php';

echo "<h1>Test de Endpoints Admin</h1>";
echo "<hr>";

// Test 1: Obtener usuarios
echo "<h2>Test 1: Obtener todos los usuarios</h2>";
try {
    $userModel = new UserModel();
    $usuarios = $userModel->getAll();
    echo "<pre>" . json_encode($usuarios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";

// Test 2: Obtener roles
echo "<h2>Test 2: Obtener todos los roles</h2>";
try {
    $userModel = new UserModel();
    $roles = $userModel->getAllRoles();
    echo "<pre>" . json_encode($roles, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";

// Test 3: Intentar crear usuario
echo "<h2>Test 3: Crear usuario de prueba</h2>";
try {
    $userModel = new UserModel();
    $userData = [
        'rol_id' => 2,
        'nombre' => 'Test',
        'apellido' => 'Usuario',
        'cedula' => '9999999999',
        'email' => 'test@test.com',
        'telefono' => '0999999999',
        'direccion' => 'Calle Test',
        'carrera' => 'Test Carrera',
        'rol_docente' => null,
        'contrasena' => 'TestPassword123'
    ];
    $id = $userModel->create($userData);
    echo "<p>Usuario creado con ID: " . $id . "</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}
?>
