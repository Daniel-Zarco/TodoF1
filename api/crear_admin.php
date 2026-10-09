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
 *   2) Prompt oculto en terminal interactiva tipo Unix (stty -echo).
 *
 * El eco del terminal se restaura SIEMPRE: en 'finally' (errores/excepciones),
 * al finalizar el script (register_shutdown_function) y, si pcntl está
 * disponible, ante interrupciones (SIGINT/SIGTERM).
 *
 * Si no se puede garantizar una lectura segura (no es una TTY, no hay 'stty' o
 * no se puede desactivar el eco), devuelve '' para que el llamador aborte.
 */
function tf_read_secret(string $prompt, string $envName): string
{
    $env = getenv($envName);
    if (is_string($env) && $env !== '') {
        return $env;
    }

    // Solo Unix, con shell_exec y entrada interactiva (TTY).
    if (PHP_OS_FAMILY === 'Windows' || !function_exists('shell_exec')) {
        return '';
    }
    if (!defined('STDIN') || !function_exists('stream_isatty') || !@stream_isatty(STDIN)) {
        return '';
    }

    $stty = trim((string) @shell_exec('command -v stty 2>/dev/null'));
    if ($stty === '') {
        return '';
    }
    $sttyCmd = escapeshellarg($stty);

    // Restaura el eco (idempotente): en 'finally' y al finalizar el script.
    $restoreEcho = static function () use ($sttyCmd): void {
        @exec($sttyCmd . ' echo 2>/dev/null');
    };
    register_shutdown_function($restoreEcho);

    // Restaurar también ante interrupciones, si pcntl está disponible.
    if (function_exists('pcntl_signal') && function_exists('pcntl_async_signals')) {
        pcntl_async_signals(true);
        $onSignal = static function () use ($restoreEcho): void {
            $restoreEcho();
            fwrite(STDERR, PHP_EOL . "Operación cancelada.\n");
            exit(1);
        };
        if (defined('SIGINT')) {
            pcntl_signal(SIGINT, $onSignal);
        }
        if (defined('SIGTERM')) {
            pcntl_signal(SIGTERM, $onSignal);
        }
    }

    // Desactivar el eco. Si no se consigue, NO se lee (evita mostrar el secreto).
    $rc = 0;
    @exec($sttyCmd . ' -echo 2>/dev/null', $unused, $rc);
    if ($rc !== 0) {
        return '';
    }

    fwrite(STDOUT, $prompt);

    $value = '';
    try {
        $line = fgets(STDIN);
        if ($line !== false) {
            $value = trim($line);
        }
    } finally {
        $restoreEcho();
        fwrite(STDOUT, PHP_EOL);
    }

    return $value;
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
    fwrite(STDERR, "No se pudo obtener la contraseña de forma segura. Define TODOF1_ADMIN_USER y TODOF1_ADMIN_PASS, o ejecútalo en una terminal interactiva (Unix) para el prompt oculto.\n");
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
