<?php
// tools/check_user_hash.php
// Uso: php tools/check_user_hash.php <cedula> <contrasena> [update]
// Si se pasa 'update' como tercer parámetro, se ofrecerá actualizar la contraseña (requiere confirmación en el script).

require_once __DIR__ . '/../app/Models/Database.php';

if ($argc < 3) {
    echo "Uso: php tools/check_user_hash.php <cedula> <contrasena> [update]\n";
    exit(1);
}

$cedula = $argv[1];
$contrasena = $argv[2];
$doUpdate = isset($argv[3]) && $argv[3] === 'update';

try {
    $db = new Database();
    $conn = $db->getConnection();

    $stmt = $conn->prepare("SELECT id, cedula, nombre, apellido, contrasena, estado FROM usuarios WHERE cedula = :cedula LIMIT 1");
    $stmt->bindParam(':cedula', $cedula);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "Usuario con cédula {$cedula} no encontrado.\n";
        exit(2);
    }

    echo "Usuario encontrado:\n";
    echo "ID: " . $user['id'] . "\n";
    echo "Nombre: " . $user['nombre'] . " " . $user['apellido'] . "\n";
    echo "Cédula: " . $user['cedula'] . "\n";
    echo "Estado: " . $user['estado'] . "\n\n";

    $stored = $user['contrasena'];
    echo "Valor almacenado en BD (contrasena):\n" . $stored . "\n\n";

    // Detectar si parece un hash bcrypt
    $isHash = (bool) preg_match('/^\$2[ayb]\$\d{2}\$[\.\/A-Za-z0-9]{53}$/', $stored);
    echo "¿Parece hash bcrypt? " . ($isHash ? 'Sí' : 'No') . "\n";

    // Resultado de password_verify
    $verify = password_verify($contrasena, $stored);
    echo "password_verify(\"{$contrasena}\", stored) => " . ($verify ? 'true' : 'false') . "\n";

    if (!$isHash) {
        echo "\nADVERTENCIA: El valor almacenado no parece un hash bcrypt. Es probable que la contraseña esté en texto plano.\n";
        echo "Si éste es el caso, debes reemplazarla por un hash seguro para que la verificación funcione correctamente.\n";
    }

    if (!$verify && $isHash) {
        echo "\nLa contraseña proporcionada no coincide con el hash almacenado. Verifica que la contraseña ingresada sea correcta.\n";
    }

    if (!$verify && !$isHash) {
        echo "\nLa contraseña almacenada no está hasheada. Puedes actualizarla con un hash seguro ahora. Para hacerlo, vuelve a ejecutar este script con el tercer parámetro 'update'.\n";
    }

    if ($doUpdate) {
        // Confirmación interactiva (solo si se ejecuta desde terminal interactivo)
        echo "\nSe iniciará el proceso para actualizar la contraseña en la BD con un hash bcrypt. Esto modificará los datos. Continuar? (si/no): ";
        $handle = fopen('php://stdin', 'r');
        $line = trim(fgets($handle));
        if (strtolower($line) !== 'si' && strtolower($line) !== 's') {
            echo "Operación cancelada por usuario. Ningún cambio realizado.\n";
            exit(0);
        }

        $newHash = password_hash($contrasena, PASSWORD_BCRYPT);
        $upd = $conn->prepare("UPDATE usuarios SET contrasena = :hash WHERE id = :id");
        $upd->bindParam(':hash', $newHash);
        $upd->bindParam(':id', $user['id']);
        $upd->execute();

        echo "Contraseña actualizada con éxito (hash almacenado). Ahora intenta iniciar sesión nuevamente.\n";
        echo "Nuevo hash: " . $newHash . "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(3);
}
