<?php
// Conexión a la base de datos (resuelta por api/db.php en local y en producción)
require_once __DIR__ . '/db.php';

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$sql = "SELECT gender, country, age FROM info";
$result = $conn->query($sql);

$datos = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $datos[] = $row;
    }
}

$conn->close();

// Preparar datos para gráficos
$generos = [];
$paises = [];
$edades = [];

foreach ($datos as $dato) {
    // Contar géneros
    if (isset($generos[$dato['gender']])) {
        $generos[$dato['gender']]++;
    } else {
        $generos[$dato['gender']] = 1;
    }

    // Contar países
    if (isset($paises[$dato['country']])) {
        $paises[$dato['country']]++;
    } else {
        $paises[$dato['country']] = 1;
    }

    // Lista de edades
    $edades[] = (int)$dato['age'];
}

// Convertir a JSON para JS
$generos_json = json_encode($generos);
$paises_json = json_encode($paises);
$edades_json = json_encode($edades);

$totalUsuarios = count($datos);
$totalUsuarios_json = json_encode($totalUsuarios);

// Ahora incluimos el HTML que mostrará los datos
include '../index.html/PageInfo.php';
