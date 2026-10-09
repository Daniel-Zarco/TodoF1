<?php
// Script administrativo: solo ejecutable por línea de comandos (neutraliza el acceso web).
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Acceso restringido: ejecutar por línea de comandos.');
}

// Conexión a la base de datos (resuelta por api/db.php en local y en producción)
require_once __DIR__ . '/db.php';
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

/**
 * Lee un dato secreto sin mostrarlo por pantalla:
 *   1) Variable de entorno (recomendado; no se imprime ni se registra).
 *   2) Prompt oculto en sistemas tipo Unix (stty -echo).
 * En Windows sin variable de entorno no se lee de forma segura y devuelve ''.
 */
function tf_read_secret(string $prompt, string $envName): string
{
    $env = getenv($envName);
    if (is_string($env) && $env !== '') {
        return $env;
    }

    if (PHP_OS_FAMILY !== 'Windows' && function_exists('shell_exec')) {
        fwrite(STDOUT, $prompt);
        shell_exec('stty -echo');
        $value = fgets(STDIN);
        shell_exec('stty echo');
        fwrite(STDOUT, PHP_EOL);
        return trim((string) $value);
    }

    return '';
}

// Usuario administrador: variable de entorno o prompt visible (no es un secreto).
$newAdminUser = getenv('TODOF1_ADMIN_USER');
if (!is_string($newAdminUser) || $newAdminUser === '') {
    fwrite(STDOUT, 'Usuario administrador: ');
    $newAdminUser = trim((string) fgets(STDIN));
}
$newAdminUser = trim($newAdminUser);

// Contraseña: variable de entorno o prompt oculto. Nunca se imprime.
$newAdminPass = tf_read_secret('Contraseña (no se mostrará): ', 'TODOF1_ADMIN_PASS');

if ($newAdminUser === '' || $newAdminPass === '') {
    fwrite(STDERR, "Datos incompletos. Define TODOF1_ADMIN_USER y TODOF1_ADMIN_PASS, o ejecútalo en una terminal tipo Unix para el prompt oculto.\n");
    exit(1);
}

if (strlen($newAdminPass) < 8) {
    fwrite(STDERR, "La contraseña debe tener al menos 8 caracteres.\n");
    exit(1);
}

// Hashear la contraseña
$hashed_password = password_hash($newAdminPass, PASSWORD_DEFAULT);

// Preparar consulta para insertar admin
$stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')");
if (!$stmt) {
    fwrite(STDERR, "Error en la preparación de la consulta.\n");
    exit(1);
}
$stmt->bind_param("ss", $newAdminUser, $hashed_password);

if ($stmt->execute()) {
    // Se informa únicamente del usuario; la contraseña no se muestra.
    fwrite(STDOUT, "Usuario admin creado exitosamente: {$newAdminUser}\n");
} else {
    fwrite(STDERR, "Error al crear usuario admin: " . $stmt->error . "\n");
}

$stmt->close();
$conn->close();
