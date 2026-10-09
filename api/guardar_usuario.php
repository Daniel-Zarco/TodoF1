<?php
// Conexión a la base de datos (resuelta por api/db.php en local y en producción)
require_once __DIR__ . '/db.php';

/**
 * Respuesta de error genérica para el visitante:
 * nunca muestra excepciones, rutas internas ni credenciales.
 */
function tf_user_error(string $mensaje, int $codigo = 400): void
{
    http_response_code($codigo);
    header('Content-Type: text/plain; charset=UTF-8');
    echo $mensaje;
    exit;
}

// Recoger y normalizar los datos del formulario (POST)
$gender  = isset($_POST['gender'])  ? trim((string) $_POST['gender'])  : '';
$country = isset($_POST['country']) ? trim((string) $_POST['country']) : '';
$age     = isset($_POST['age'])     ? (int) $_POST['age']              : 0;

// Validación de los datos recibidos
$generosValidos = ['hombre', 'mujer', 'otro'];

if ($gender === '' || !in_array($gender, $generosValidos, true)) {
    tf_user_error('Datos inválidos. Selecciona un género válido.');
}

if ($country === '' || strlen($country) > 100) {
    tf_user_error('Datos inválidos. Selecciona un país válido.');
}

if ($age < 1 || $age > 120) {
    tf_user_error('Datos inválidos. Introduce una edad entre 1 y 120.');
}

// Guardar en la base de datos capturando cualquier error sin exponerlo.
try {
    if (!isset($conn) || !($conn instanceof mysqli) || $conn->connect_error) {
        throw new RuntimeException('Conexión no disponible');
    }

    $stmt = $conn->prepare('INSERT INTO info (gender, country, age) VALUES (?, ?, ?)');
    if ($stmt === false) {
        throw new RuntimeException('No se pudo preparar la consulta');
    }

    $stmt->bind_param('ssi', $gender, $country, $age);
    $stmt->execute();
    $stmt->close();

    // Redirigir a la página principal tras guardar correctamente
    header('Location: /index.html/PagePrincipal.php');
    exit;
} catch (Throwable $e) {
    // Registro interno sin credenciales ni datos sensibles; respuesta genérica.
    error_log('TodoF1 guardar_usuario: no se pudo guardar el registro.');
    tf_user_error('No se pudieron guardar los datos. Inténtalo de nuevo más tarde.', 500);
}
