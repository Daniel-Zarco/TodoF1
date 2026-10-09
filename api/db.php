<?php
/**
 * TodoF1 — Resolución de la conexión a MariaDB.
 *
 * Objetivo: que los archivos públicos funcionen igual en Windows/localhost
 * y en Linux/alwaysdata, sin credenciales de producción en el repositorio.
 *
 * Estrategia:
 *   1. Si $conn ya existe (por ejemplo porque un archivo privado ya lo definió),
 *      no se hace nada.
 *   2. Se busca un archivo privado de configuración (NO versionado) que defina
 *      $conn (mysqli). Candidatos, siempre con rutas relativas:
 *        - la ruta indicada en la variable de entorno TODOF1_DB_CONFIG (opcional)
 *        - <proyecto>/private/db.php              (desarrollo local)
 *        - <proyecto>/todof1-app/private/db.php   (alwaysdata: www/api -> todof1-app/private)
 *        - variantes equivalentes
 *      Si un archivo privado EXISTE pero no aporta una conexión $conn válida de
 *      tipo mysqli (o falla al cargarse), se detiene la ejecución con un error
 *      genérico; no se continúa hacia el fallback local.
 *   3. Si NO hay configuración privada, el fallback inseguro de desarrollo
 *      (localhost / root / sin contraseña) solo se permite si el entorno está
 *      declarado EXPLÍCITAMENTE como desarrollo:
 *        - TODOF1_ENV = local | development | dev, o
 *        - TODOF1_ALLOW_DEV_DB = 1 | true | yes | on, o
 *        - existe el marcador local no versionado <proyecto>/private/.local
 *      En cualquier otro caso (producción o entorno no declarado) la conexión
 *      falla de forma segura, sin exponer credenciales ni detalles internos.
 *
 * El entorno se controla por configuración explícita (variables de entorno o
 * un marcador local), nunca por el nombre del host HTTP. Compatible con PHP CLI
 * y web.
 *
 * El archivo privado es el responsable de cargar las credenciales reales
 * (p. ej. desde .env con phpdotenv) y de definir $conn.
 */

if (isset($conn) && $conn instanceof mysqli) {
    return;
}

// Fallo seguro y genérico: sin credenciales ni detalles internos.
$tf_db_fail = static function (): void {
    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, "Error de configuración de base de datos.\n");
        exit(1);
    }
    http_response_code(500);
    exit('Error de configuración de base de datos.');
};

// --- 1) Localizar la configuración privada ---------------------------------
$tf_db_candidates = [];

$tf_db_env = getenv('TODOF1_DB_CONFIG');
if (is_string($tf_db_env) && $tf_db_env !== '') {
    $tf_db_candidates[] = $tf_db_env;
}

$tf_db_candidates[] = __DIR__ . '/../private/db.php';               // repo local: <proyecto>/private/db.php
$tf_db_candidates[] = __DIR__ . '/../../todof1-app/private/db.php'; // alwaysdata: www/api -> /home/<cuenta>/todof1-app/private
$tf_db_candidates[] = __DIR__ . '/../todof1-app/private/db.php';    // variante de layout
$tf_db_candidates[] = __DIR__ . '/../../private/db.php';            // variante de layout

foreach ($tf_db_candidates as $tf_db_file) {
    if (!is_file($tf_db_file)) {
        continue;
    }

    try {
        require $tf_db_file;
    } catch (\Throwable $e) {
        // El archivo privado existe pero no pudo cargarse correctamente.
        $tf_db_fail();
    }

    if (isset($conn) && $conn instanceof mysqli) {
        return;
    }

    // El archivo privado existe pero no definió una conexión mysqli válida:
    // se detiene el proceso y NO se continúa hacia el fallback local.
    $tf_db_fail();
}

// --- 2) Sin configuración privada: decidir si se permite el fallback local --
$tf_env = strtolower(trim((string) getenv('TODOF1_ENV')));
$tf_allow = strtolower(trim((string) getenv('TODOF1_ALLOW_DEV_DB')));
$tf_local_marker = __DIR__ . '/../private/.local';

if (in_array($tf_env, ['production', 'prod'], true)) {
    // Producción declarada explícitamente: nunca se permite el fallback inseguro.
    $tf_is_development = false;
} elseif (in_array($tf_env, ['local', 'development', 'dev'], true)) {
    $tf_is_development = true;
} else {
    // Sin TODOF1_ENV: solo desarrollo si hay un indicador explícito adicional.
    $tf_is_development = in_array($tf_allow, ['1', 'true', 'yes', 'on'], true)
        || is_file($tf_local_marker);
}

if ($tf_is_development) {
    // Fallback de desarrollo local (nunca se usa en producción si private/db.php existe).
    $conn = new mysqli('localhost', 'root', '', 'todof1');
    return;
}

// --- 3) Producción/entorno no declarado sin configuración: fallo seguro ----
$tf_db_fail();
