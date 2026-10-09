<?php
// Datos de conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "todof1";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Recoger datos del formulario (usar POST)
$gender = isset($_POST['gender']) ? $conn->real_escape_string($_POST['gender']) : '';
$country = isset($_POST['country']) ? $conn->real_escape_string($_POST['country']) : '';
$age = isset($_POST['age']) ? intval($_POST['age']) : 0;

// Validar datos básicos
if (empty($gender) || empty($country) || $age < 1 || $age > 120) {
    die("Datos inválidos. Por favor, completa correctamente el formulario.");
}

// Preparar la consulta para evitar inyección SQL
$stmt = $conn->prepare("INSERT INTO info (gender, country, age) VALUES (?, ?, ?)");
if (!$stmt) {
    die("Error en la preparación de la consulta: " . $conn->error);
}

$stmt->bind_param("ssi", $gender, $country, $age);

// Ejecutar la consulta y verificar
if ($stmt->execute()) {
    // Redirigir a la página principal después de guardar
    header("Location: /index.html/PagePrincipal.php");
    exit();
} else {
    echo "Error al guardar los datos: " . $stmt->error;
}

$stmt->close();
$conn->close();
