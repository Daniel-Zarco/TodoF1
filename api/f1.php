<?php
declare(strict_types=1);

/* ============================================================
   TodoF1 — Proxy con caché persistente para la API de datos
   ------------------------------------------------------------
   Objetivo: que TodoF1 siga mostrando información cuando Jolpica
   no responde, sirviendo la última copia válida guardada en el
   servidor (nunca datos inventados).

   Cómo funciona:
     1. Las páginas piden /api/f1.php?ruta=<endpoint>[&limit=&offset=].
     2. Se valida la ruta contra una LISTA BLANCA estricta.
     3. Si hay copia en disco y está vigente (TTL), se sirve.
     4. Si no, se consulta Jolpica con tiempo máximo; si la respuesta
        es válida, se guarda de forma atómica y se sirve.
     5. Si Jolpica falla, se sirve la última copia válida (respaldo) y
        se activa un cortacircuitos temporal para no insistir.
     6. Si no hay copia, se devuelve 503 con un error comprensible.

   SEGURIDAD:
     - Lista blanca de rutas (gramática cerrada) y de parámetros
       (solo limit/offset, enteros acotados). El nombre del archivo de
       caché es un hash: nunca se usa entrada del usuario como ruta.
     - Sin CORS: no se envía Access-Control-Allow-Origin.
     - Solo se atiende a peticiones del propio sitio (Origin/Referer). Es una
       medida anti-hotlinking, NO un mecanismo de autenticación.
     - Tope de tamaño de respuesta y escritura atómica con bloqueo.
     - No se devuelven rutas del servidor ni credenciales en ningún caso.

   Este archivo también actúa de biblioteca para api/f1_preload.php:
   definiendo TODOF1_F1_LIB antes de incluirlo no se ejecuta el
   manejador de la petición.
   ============================================================ */

if (!defined('TODOF1_F1_PROXY')) {
    define('TODOF1_F1_PROXY', 1);
}

/* ------------------------------- Constantes ------------------------------- */
const TODOF1_F1_MAX_BYTES = 8388608;     // 8 MB por respuesta
const TODOF1_F1_TTL_ABIERTA = 600;       // 10 min (temporada en curso)
const TODOF1_F1_TTL_CERRADA = 2592000;   // 30 días (temporada finalizada)
const TODOF1_F1_TTL_ESTATICA = 2592000;  // 30 días (listas sin temporada)
const TODOF1_F1_TIMEOUT = 8;             // segundos (respuesta completa)
const TODOF1_F1_CONNECT_TIMEOUT = 4;     // segundos (conexión)
const TODOF1_F1_BREAKER_BASE = 60;       // cortacircuitos: 60 s
const TODOF1_F1_BREAKER_MAX = 900;       // cortacircuitos: 15 min
const TODOF1_F1_MIN_GAP = 250000;        // 250 ms entre peticiones al origen (µs)
const TODOF1_F1_WAIT_MS = 3000;          // espera máxima al descargador (single-flight)

/* --------------------------------- Rutas ---------------------------------- */
function todof1_f1_origin(): string
{
    $env = getenv('TODOF1_F1_ORIGIN');
    if (is_string($env) && $env !== '') {
        return rtrim($env, '/');
    }
    return 'https://api.jolpi.ca/ergast/f1';
}

/* Gramática cerrada: solo los endpoints que usan las páginas de TodoF1. */
function todof1_f1_route_ok(string $path): bool
{
    $id = '[A-Za-z0-9_\-]+';
    $patterns = [
        '/^\d{4}\.json$/',                                              // calendario
        '/^\d{4}\/results\.json$/',                                     // resultados de la temporada
        '/^\d{4}\/races\.json$/',                                       // pruebas de la temporada
        '/^\d{4}\/\d{1,2}\/results\.json$/',                            // resultados de una prueba
        '/^\d{4}\/driverStandings\.json$/',
        '/^\d{4}\/constructorStandings\.json$/',
        '/^\d{4}\/constructors\.json$/',
        '/^\d{4}\/drivers\.json$/',
        '/^\d{4}\/circuits\.json$/',
        '/^\d{4}\/constructors\/' . $id . '\/constructorStandings\.json$/',
        '/^\d{4}\/constructors\/' . $id . '\/results\.json$/',
        '/^\d{4}\/drivers\/' . $id . '\/driverStandings\.json$/',
        '/^constructors\.json$/',
        '/^constructors\/' . $id . '\.json$/',
        '/^constructors\/' . $id . '\/results\.json$/',   // historial completo del constructor
        '/^drivers\/' . $id . '\.json$/',
        '/^drivers\/' . $id . '\/results\.json$/',        // historial completo del piloto
        '/^circuits\/' . $id . '\.json$/',
        '/^circuits\.json$/',                                // listado de circuitos
        '/^circuits\/' . $id . '\/results\.json$/',           // resultados completos del circuito
        '/^circuits\/' . $id . '\/results\/\d{1,2}\.json$/',  // historial/podios por circuito
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $path) === 1) {
            return true;
        }
    }
    return false;
}

/* Tabla MRData que debe traer cada endpoint. */
function todof1_f1_expected_table(string $path): string
{
    if (preg_match('/Standings\.json$/', $path) === 1) {
        return 'StandingsTable';
    }
    // Cualquier endpoint de resultados/calendario. Incluye las rutas por
    // circuito: circuits/{id}/results.json y circuits/{id}/results/{pos}.json.
    if (preg_match('#/(results|races)(\.json|/\d{1,2}\.json)$#', $path) === 1
        || preg_match('/^\d{4}\.json$/', $path) === 1) {
        return 'RaceTable';
    }
    if (strpos($path, 'constructors') !== false) {
        return 'ConstructorTable';
    }
    if (strpos($path, 'drivers') !== false) {
        return 'DriverTable';
    }
    if (strpos($path, 'circuits') !== false) {
        return 'CircuitTable';
    }
    return '';
}

/* Temporada a la que pertenece la ruta (0 si no depende de temporada). */
function todof1_f1_route_season(string $path): int
{
    if (preg_match('/^(\d{4})/', $path, $m) === 1) {
        return (int) $m[1];
    }
    return 0;
}

/* ------------------------------- Validación -------------------------------- */
/**
 * Valida la respuesta de Jolpica antes de guardarla.
 * Devuelve '' si es válida o el motivo del rechazo.
 */
function todof1_f1_validate(string $path, $data, int $season): string
{
    if (!is_array($data) || $data === []) {
        return 'json_invalido';
    }
    if (!isset($data['MRData']) || !is_array($data['MRData'])) {
        return 'sin_mrdata';
    }
    $mr = $data['MRData'];

    // Total ausente = respuesta truncada o error del proveedor.
    if (!array_key_exists('total', $mr)) {
        return 'sin_total';
    }

    $table = todof1_f1_expected_table($path);
    if ($table !== '' && !array_key_exists($table, $mr)) {
        return 'tabla_ausente:' . $table;
    }

    // Coherencia de temporada: nunca se guarda otra temporada como si fuera la pedida.
    if ($season > 0 && $table !== '') {
        $seasonField = isset($mr[$table]['season']) ? (int) $mr[$table]['season'] : 0;
        if ($seasonField > 0 && $seasonField !== $season) {
            return 'temporada_distinta:' . $seasonField;
        }
    }

    return '';
}

/* ------------------------- Directorio de la caché -------------------------- */
function todof1_f1_repo_root(): string
{
    $dir = __DIR__;
    for ($i = 0; $i < 5; $i++) {
        if (is_dir($dir . '/deploy') && is_dir($dir . '/index.html')) {
            return $dir;
        }
        // Producción: www/api/f1.php -> /home/todof1  ->  /home/todof1/todof1-app
        if (is_dir($dir . '/todof1-app/deploy') && is_dir($dir . '/todof1-app/index.html')) {
            return $dir . '/todof1-app';
        }
        $parent = dirname($dir);
        if ($parent === $dir) {
            break;
        }
        $dir = $parent;
    }
    return '';
}

/**
 * Directorio persistente de la caché.
 * Prioridad: variable de entorno > private/f1-cache (persistente y fuera de
 * la web) > storage/f1-cache (alternativa local).
 * Nunca se usa una ruta enviada por el usuario.
 */
function todof1_f1_cache_dir(): string
{
    static $dir = null;
    if ($dir !== null) {
        return $dir;
    }

    $candidates = [];
    $env = getenv('TODOF1_F1_CACHE');
    if (is_string($env) && $env !== '') {
        $candidates[] = rtrim($env, '/\\');
    }
    $root = todof1_f1_repo_root();
    if ($root !== '') {
        $candidates[] = $root . '/private/f1-cache';
        $candidates[] = $root . '/storage/f1-cache';
    }
    $candidates[] = sys_get_temp_dir() . '/todof1-f1-cache';

    foreach ($candidates as $candidate) {
        if (!is_dir($candidate)) {
            @mkdir($candidate, 0770, true);
        }
        if (is_dir($candidate) && is_writable($candidate)) {
            $dir = $candidate;
            return $dir;
        }
    }

    $dir = '';
    return $dir;
}

function todof1_f1_cache_file(string $key): string
{
    $dir = todof1_f1_cache_dir();
    if ($dir === '') {
        return '';
    }
    return $dir . '/' . substr(hash('sha256', $key), 0, 32) . '.json';
}

function todof1_f1_read(string $file): ?array
{
    if ($file === '' || !is_file($file)) {
        return null;
    }
    $raw = @file_get_contents($file);
    if ($raw === false || $raw === '') {
        return null;
    }
    $record = json_decode($raw, true);
    if (!is_array($record) || !isset($record['payload']) || !isset($record['saved_at'])) {
        return null;   // archivo corrupto: se ignora (no se sirve ni bloquea)
    }
    return $record;
}

function todof1_f1_write(string $file, array $payload, string $path, int $season): bool
{
    if ($file === '') {
        return false;
    }
    $record = [
        'saved_at' => time(),
        'path' => $path,
        'season' => $season,
        'payload' => $payload,
    ];
    $json = json_encode($record, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        return false;
    }
    $tmp = $file . '.' . getmypid() . '.' . bin2hex(random_bytes(4)) . '.tmp';
    if (@file_put_contents($tmp, $json, LOCK_EX) === false) {
        return false;
    }
    if (!@rename($tmp, $file)) {
        @unlink($tmp);
        return false;
    }
    return true;
}

/* ------------------------ Estado de la temporada --------------------------- */
/**
 * 'cerrada' (datos definitivos), 'abierta' (en curso) o 'estatica'.
 * La condición es verificable: si hay calendario guardado se usa la fecha de
 * su última prueba; si no, solo se considera cerrada una temporada anterior
 * al año en curso (nunca la actual por el mero hecho de cambiar el año).
 */
function todof1_f1_season_state(int $season): string
{
    if ($season <= 0) {
        return 'estatica';
    }

    $file = todof1_f1_cache_file($season . '.json');
    $record = todof1_f1_read($file);
    if (is_array($record)) {
        $races = $record['payload']['MRData']['RaceTable']['Races'] ?? null;
        if (is_array($races) && $races !== []) {
            $last = end($races);
            $date = is_array($last) && isset($last['date']) ? (string) $last['date'] : '';
            if ($date !== '') {
                $when = strtotime($date . ' 23:59:59 UTC');
                if ($when !== false) {
                    return $when < time() ? 'cerrada' : 'abierta';
                }
            }
        }
    }

    return $season < (int) date('Y') ? 'cerrada' : 'abierta';
}

function todof1_f1_ttl(string $state): int
{
    if ($state === 'abierta') {
        return TODOF1_F1_TTL_ABIERTA;
    }
    if ($state === 'cerrada') {
        return TODOF1_F1_TTL_CERRADA;
    }
    return TODOF1_F1_TTL_ESTATICA;
}

/**
 * Rutas sin temporada explícita: los historiales completos pueden incluir la
 * temporada en curso, así que se tratan como datos cambiantes.
 */
function todof1_f1_route_state(string $path, int $season): string
{
    if ($season > 0) {
        return todof1_f1_season_state($season);
    }
    if (preg_match('#/(results|races)\.json$#', $path) === 1) {
        return 'abierta';
    }
    return 'estatica';
}

/* --------------------------- Cortacircuitos -------------------------------- */
function todof1_f1_breaker_file(): string
{
    $dir = todof1_f1_cache_dir();
    return $dir === '' ? '' : $dir . '/breaker.json';
}

function todof1_f1_breaker_get(): array
{
    $file = todof1_f1_breaker_file();
    if ($file === '' || !is_file($file)) {
        return ['failures' => 0, 'until' => 0];
    }
    $raw = @file_get_contents($file);
    $state = $raw === false ? null : json_decode($raw, true);
    if (!is_array($state)) {
        return ['failures' => 0, 'until' => 0];
    }
    return [
        'failures' => (int) ($state['failures'] ?? 0),
        'until' => (int) ($state['until'] ?? 0),
    ];
}

function todof1_f1_breaker_note_failure(): void
{
    $file = todof1_f1_breaker_file();
    if ($file === '') {
        return;
    }
    $state = todof1_f1_breaker_get();
    $state['failures'] = min($state['failures'] + 1, 20);
    $wait = min(TODOF1_F1_BREAKER_BASE * (2 ** ($state['failures'] - 1)), TODOF1_F1_BREAKER_MAX);
    $state['until'] = time() + (int) $wait;
    @file_put_contents($file, json_encode($state), LOCK_EX);
}

function todof1_f1_breaker_reset(): void
{
    $file = todof1_f1_breaker_file();
    if ($file !== '') {
        @file_put_contents($file, json_encode(['failures' => 0, 'until' => 0]), LOCK_EX);
    }
}

/* --------------------- Servir / descargar / single-flight ------------------ */
/** Sirve la copia guardada. Con $error no vacío se marca como respaldo. */
function todof1_f1_serve_cache(array $record, string $state, string $error = ''): array
{
    return [
        'mode' => $error === '' ? ($state === 'abierta' ? 'cache-vigente' : 'cache-cerrada') : 'respaldo',
        'payload' => $record['payload'],
        'saved_at' => (int) $record['saved_at'],
        'error' => $error,
        'status' => 200,
    ];
}

function todof1_f1_no_copy(string $error): array
{
    return ['mode' => 'sin-copia', 'payload' => null, 'saved_at' => 0, 'error' => $error, 'status' => 503];
}

/**
 * Descarga del origen, valida y guarda. Nunca sobrescribe una copia válida con
 * una respuesta incorrecta y nunca inventa datos.
 */
function todof1_f1_fetch_store(string $path, string $file, int $season, int $limit, int $offset, ?array $record): array
{
    $url = todof1_f1_origin() . '/' . $path . '?limit=' . $limit . '&offset=' . $offset;
    todof1_f1_throttle();
    $response = todof1_f1_http_get($url);

    if (!$response['ok']) {
        $status = (int) $response['status'];

        // 4xx (salvo 429): respuesta DEFINITIVA del proveedor para esa ruta
        // (por ejemplo, un identificador que no existe). No es una caída: se
        // devuelve tal cual, sin tocar la copia guardada ni abrir el
        // cortacircuitos.
        if ($status >= 400 && $status < 500 && $status !== 429) {
            return ['mode' => 'definitiva', 'payload' => null, 'saved_at' => 0, 'error' => 'http_' . $status, 'status' => $status];
        }

        // 429, 5xx o fallo de red: se considera caída (cortacircuitos + respaldo).
        todof1_f1_breaker_note_failure();
        $error = $response['error'] !== '' ? $response['error'] : ('http_' . $status);
        return is_array($record) ? todof1_f1_serve_cache($record, 'abierta', $error) : todof1_f1_no_copy($error);
    }

    $data = json_decode($response['body'], true);
    $problem = todof1_f1_validate($path, $data, $season);
    if ($problem !== '') {
        // Respuesta incorrecta: NUNCA sobrescribe una copia válida.
        todof1_f1_breaker_note_failure();
        return is_array($record) ? todof1_f1_serve_cache($record, 'abierta', $problem) : todof1_f1_no_copy($problem);
    }

    todof1_f1_breaker_reset();
    todof1_f1_write($file, $data, $path, $season);

    // Limpieza conservadora: solo con el origen sano y como mucho cada 30 min.
    todof1_f1_cleanup();

    return ['mode' => 'live', 'payload' => $data, 'saved_at' => time(), 'error' => '', 'status' => 200];
}

function todof1_f1_lock_file(string $key): string
{
    $dir = todof1_f1_cache_dir();
    return $dir === '' ? '' : $dir . '/lock-' . substr(hash('sha256', $key), 0, 24);
}

/* ------------------------------ Limpieza ----------------------------------- */
/**
 * Limpieza conservadora de la caché. Reglas:
 *   · Nunca se ejecuta con el origen caído (no se borra nada durante una caída).
 *   · Como mucho una vez cada TODOF1_F1_CLEANUP_SECONDS (por defecto 1800).
 *   · Se eliminan temporales abandonados y archivos corruptos, siempre.
 *   · Solo si se supera el límite de tamaño (TODOF1_F1_CACHE_MAX_MB, def. 64)
 *     se podan las entradas más antiguas, conservando:
 *       - la copia más reciente de CADA temporada (nunca se vacía el respaldo),
 *       - un mínimo de TODOF1_F1_CACHE_KEEP entradas (def. 200).
 *   · La caducidad (TTL) solo decide si un dato es fresco: no se borra nada por
 *     haber superado el TTL.
 */
function todof1_f1_cleanup(): void
{
    $dir = todof1_f1_cache_dir();
    if ($dir === '') {
        return;
    }

    $breaker = todof1_f1_breaker_get();
    if ($breaker['until'] > time()) {
        return;   // origen caído: no se toca el respaldo
    }

    $marker = $dir . '/last-cleanup';
    $last = is_file($marker) ? (int) @file_get_contents($marker) : 0;
    $interval = max(300, (int) (getenv('TODOF1_F1_CLEANUP_SECONDS') ?: 1800));
    if ($last > 0 && (time() - $last) < $interval) {
        return;
    }
    @file_put_contents($marker, (string) time(), LOCK_EX);

    $files = glob($dir . '/*') ?: [];
    $entries = [];
    $total = 0;
    $now = time();

    foreach ($files as $file) {
        $name = basename($file);
        if (!is_file($file)) {
            continue;   // directorios: no se tocan
        }
        if (($name === 'breaker.json') || ($name === 'last-cleanup') || ($name === 'last-fetch')) {
            continue;
        }
        if (preg_match('/\.tmp$/', $name) === 1) {
            @unlink($file);                    // temporales abandonados
            continue;
        }
        if (strpos($name, 'lock-') === 0) {
            if (($now - (int) @filemtime($file)) > 86400) {
                @unlink($file);                // bloqueos viejos
            }
            continue;
        }
        if (!is_array(todof1_f1_read($file))) {
            @unlink($file);                    // entradas corruptas
            continue;
        }
        $size = (int) @filesize($file);
        $entries[] = ['file' => $file, 'size' => $size];
        $total += $size;
    }

    $maxBytes = max(4, (int) (getenv('TODOF1_F1_CACHE_MAX_MB') ?: 64)) * 1048576;
    if ($total <= $maxBytes || $entries === []) {
        return;
    }

    // Podado por antigüedad, protegiendo la copia más reciente de cada temporada.
    $detalle = [];
    foreach ($entries as $entry) {
        $record = todof1_f1_read($entry['file']);
        $detalle[] = [
            'file' => $entry['file'],
            'size' => $entry['size'],
            'saved_at' => (int) ($record['saved_at'] ?? 0),
            'season' => (int) ($record['season'] ?? 0),
        ];
    }
    usort($detalle, function ($a, $b) { return $a['saved_at'] <=> $b['saved_at']; });

    $protegidos = [];
    $porTemporada = [];
    foreach ($detalle as $entry) {
        $porTemporada[(string) $entry['season']] = $entry['file'];
    }
    foreach ($porTemporada as $file) {
        $protegidos[$file] = true;
    }

    $minimo = max(20, (int) (getenv('TODOF1_F1_CACHE_KEEP') ?: 20));
    $restantes = count($detalle);
    foreach ($detalle as $entry) {
        if ($total <= $maxBytes || $restantes <= $minimo) {
            break;
        }
        if (isset($protegidos[$entry['file']])) {
            continue;
        }
        if (@unlink($entry['file'])) {
            $total -= $entry['size'];
            $restantes--;
        }
    }
}

/* ------------------------------ Cobertura ---------------------------------- */
/**
 * Cobertura real del respaldo: qué familias de endpoints están guardadas para
 * la temporada en curso y qué temporadas tienen datos. No declara una temporada
 * completa si falta alguna familia esencial.
 */
function todof1_f1_coverage(): array
{
    $year = (int) date('Y');

    // Familias que necesitan realmente las páginas para esa temporada.
    $familias = [
        'calendario' => $year . '.json',
        'clasificacion_constructores' => $year . '/constructorStandings.json',
        'clasificacion_pilotos' => $year . '/driverStandings.json',
        'constructores' => $year . '/constructors.json',
        'resultados' => $year . '/results.json',
    ];
    // Extras útiles pero no imprescindibles (ninguna página los pide).
    $extras = [
        'listado_pilotos' => $year . '/drivers.json',
    ];

    $detalle = [];
    $completas = 0;
    $pendientes = [];
    foreach ($familias as $nombre => $ruta) {
        $record = todof1_f1_read(todof1_f1_cache_file($ruta . '|100|0'));
        if (is_array($record)) {
            $completas++;
            $detalle[$nombre] = ['guardado' => true, 'actualizado' => gmdate('c', (int) $record['saved_at'])];
        } else {
            $pendientes[] = $ruta;
            $detalle[$nombre] = ['guardado' => false];
        }
    }

    $detalleExtras = [];
    foreach ($extras as $nombre => $ruta) {
        $record = todof1_f1_read(todof1_f1_cache_file($ruta . '|100|0'));
        $detalleExtras[$nombre] = is_array($record) ? ['guardado' => true, 'actualizado' => gmdate('c', (int) $record['saved_at'])] : ['guardado' => false];
    }

    // Temporadas presentes (a propósito acotado: el diagnóstico no debe
    // recorrer cachés enormes en cada petición).
    $temporadas = [];
    $dir = todof1_f1_cache_dir();
    if ($dir !== '') {
        $files = array_slice(glob($dir . '/*.json') ?: [], 0, 1000);
        foreach ($files as $file) {
            $record = todof1_f1_read($file);
            if (!is_array($record)) {
                continue;
            }
            $season = (int) ($record['season'] ?? 0);
            if ($season <= 0) {
                continue;
            }
            $temporadas[$season] = ($temporadas[$season] ?? 0) + 1;
        }
        ksort($temporadas);
    }

    return [
        'temporada_en_curso' => $year,
        'familias' => $detalle,
        'familias_completas' => $completas . '/' . count($familias),
        'extras' => $detalleExtras,
        'pendientes' => $pendientes,
        'temporadas_con_datos' => $temporadas,
    ];
}

/* --------------------------- Petición al origen ---------------------------- */
/**
 * Espaciado entre peticiones al origen: evita ráfagas cuando varios
 * visitantes coinciden (compatible con el cortacircuitos).
 */
function todof1_f1_throttle(): void
{
    $dir = todof1_f1_cache_dir();
    if ($dir === '') {
        return;
    }
    $file = $dir . '/last-fetch';
    $fh = @fopen($file, 'c+');
    if ($fh === false) {
        return;
    }
    if (flock($fh, LOCK_EX)) {
        $last = (int) trim((string) stream_get_contents($fh));
        $now = (int) (microtime(true) * 1000000);
        if ($last > 0 && ($now - $last) < TODOF1_F1_MIN_GAP) {
            usleep(TODOF1_F1_MIN_GAP - ($now - $last));
        }
        ftruncate($fh, 0);
        rewind($fh);
        fwrite($fh, (string) (int) (microtime(true) * 1000000));
        fflush($fh);
        flock($fh, LOCK_UN);
    }
    fclose($fh);
}

/** @return array{ok:bool,body:string,status:int,error:string} */
function todof1_f1_http_get(string $url): array
{
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 2,
            CURLOPT_CONNECTTIMEOUT => TODOF1_F1_CONNECT_TIMEOUT,
            CURLOPT_TIMEOUT => TODOF1_F1_TIMEOUT,
            CURLOPT_USERAGENT => 'TodoF1/1.0 (+https://todof1.alwaysdata.net)',
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
            CURLOPT_ENCODING => '',
        ]);
        $body = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = $body === false ? (string) curl_error($ch) : '';
        curl_close($ch);
        if ($body === false) {
            return ['ok' => false, 'body' => '', 'status' => $status, 'error' => $error !== '' ? $error : 'curl'];
        }
        if (strlen((string) $body) > TODOF1_F1_MAX_BYTES) {
            return ['ok' => false, 'body' => '', 'status' => $status, 'error' => 'demasiado_grande'];
        }
        return ['ok' => $status >= 200 && $status < 300, 'body' => (string) $body, 'status' => $status, 'error' => ''];
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => TODOF1_F1_TIMEOUT,
            'ignore_errors' => true,
            'header' => "Accept: application/json\r\nUser-Agent: TodoF1/1.0\r\n",
        ],
    ]);
    $body = @file_get_contents($url, false, $context);
    $status = 0;
    if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', (string) $http_response_header[0], $m) === 1) {
        $status = (int) $m[1];
    }
    if ($body === false) {
        return ['ok' => false, 'body' => '', 'status' => $status, 'error' => 'sin_conexion'];
    }
    if (strlen((string) $body) > TODOF1_F1_MAX_BYTES) {
        return ['ok' => false, 'body' => '', 'status' => $status, 'error' => 'demasiado_grande'];
    }
    return ['ok' => $status >= 200 && $status < 300, 'body' => (string) $body, 'status' => $status, 'error' => ''];
}

/* ------------------------- Obtención con respaldo -------------------------- */
/**
 * Devuelve el payload para una ruta, usando la caché persistente.
 *
 * @return array{mode:string,payload:?array,saved_at:int,error:string,status:int}
 *   mode: live | cache-vigente | cache-cerrada | respaldo | sin-copia
 */
function todof1_f1_get(string $path, int $limit, int $offset, bool $force = false): array
{
    $season = todof1_f1_route_season($path);
    $key = $path . '|' . $limit . '|' . $offset;
    $file = todof1_f1_cache_file($key);
    $record = todof1_f1_read($file);
    $state = todof1_f1_route_state($path, $season);
    $ttl = todof1_f1_ttl($state);

    // 1) Copia vigente: no se molesta al origen.
    if (is_array($record) && !$force && (time() - (int) $record['saved_at']) <= $ttl) {
        return todof1_f1_serve_cache($record, $state);
    }

    // 2) Cortacircuitos abierto: no se insiste al origen.
    $breaker = todof1_f1_breaker_get();
    if (!$force && $breaker['until'] > time()) {
        return is_array($record) ? todof1_f1_serve_cache($record, $state, 'cortacircuitos') : todof1_f1_no_copy('cortacircuitos');
    }

    // 3) Single-flight: si otro proceso está descargando esta misma clave, se
    //    espera su resultado en lugar de duplicar la petición (una caída no
    //    debe multiplicar por cien las consultas al mismo endpoint).
    $lockFile = todof1_f1_lock_file($key);
    $lock = $lockFile === '' ? null : @fopen($lockFile, 'c+');
    $esDescargador = true;

    if (is_resource($lock)) {
        if (!flock($lock, LOCK_EX | LOCK_NB)) {
            $esDescargador = false;
            $esperado = 0;
            while ($esperado < TODOF1_F1_WAIT_MS) {
                usleep(250000);
                $esperado += 250;
                $fresco = todof1_f1_read($file);
                if (is_array($fresco) && (time() - (int) $fresco['saved_at']) <= $ttl) {
                    fclose($lock);
                    return todof1_f1_serve_cache($fresco, $state);
                }
            }
            // El descargador sigue trabajando: se sirve la copia antigua si la
            // hay (respaldo) en vez de pedir lo mismo otra vez.
            if (is_array($record)) {
                fclose($lock);
                return todof1_f1_serve_cache($record, $state, 'espera_descarga');
            }
            // Sin copia: se asume también la descarga (caso poco frecuente).
        }
    }

    $resultado = todof1_f1_fetch_store($path, $file, $season, $limit, $offset, $record);

    if (is_resource($lock)) {
        if ($esDescargador) {
            flock($lock, LOCK_UN);
        }
        fclose($lock);
    }

    return $resultado;
}

/* ------------------------------ Estado interno ----------------------------- */
function todof1_f1_status(): array
{
    $dir = todof1_f1_cache_dir();
    $entries = 0;
    $newest = 0;
    $oldest = 0;
    $bytes = 0;

    if ($dir !== '' && is_dir($dir)) {
        $files = glob($dir . '/*.json') ?: [];
        foreach ($files as $file) {
            if (basename($file) === 'breaker.json') {
                continue;
            }
            $entries++;
            $bytes += (int) @filesize($file);
            $mtime = (int) @filemtime($file);
            if ($newest === 0 || $mtime > $newest) {
                $newest = $mtime;
            }
            if ($oldest === 0 || $mtime < $oldest) {
                $oldest = $mtime;
            }
        }
    }

    $breaker = todof1_f1_breaker_get();

    return [
        'entradas' => $entries,
        'bytes' => $bytes,
        'guardado_mas_reciente' => $newest > 0 ? gmdate('c', $newest) : null,
        'guardado_mas_antiguo' => $oldest > 0 ? gmdate('c', $oldest) : null,
        'escritura_disponible' => $dir !== '',
        'limite_cache_mb' => max(4, (int) (getenv('TODOF1_F1_CACHE_MAX_MB') ?: 64)),
        'cortacircuitos' => [
            'activo' => $breaker['until'] > time(),
            'hasta' => $breaker['until'] > time() ? gmdate('c', $breaker['until']) : null,
            'fallos' => $breaker['failures'],
        ],
        'temporada_en_curso' => (int) date('Y'),
        'cobertura' => todof1_f1_coverage(),
        'origen' => todof1_f1_origin(),
    ];
}

/* ====================== Manejador de la petición web ======================= */
if (defined('TODOF1_F1_LIB')) {
    return;   // incluido como biblioteca (api/f1_preload.php)
}

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');
header('Referrer-Policy: no-referrer');

/** Responde y termina. */
function todof1_f1_respond(int $status, $payload, array $headers = []): void
{
    http_response_code($status);
    foreach ($headers as $name => $value) {
        header($name . ': ' . $value);
    }
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

/* Modo diagnóstico: /api/f1.php?estado=1 */
if (isset($_GET['estado'])) {
    todof1_f1_respond(200, todof1_f1_status());
}

/* Solo el propio sitio: evita que terceros usen TodoF1 como proxy. */
$host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
$host = preg_replace('/:\d+$/', '', $host) ?? '';
$allowedHosts = [$host, 'todof1.alwaysdata.net', 'localhost', '127.0.0.1'];
foreach (['HTTP_ORIGIN' => true, 'HTTP_REFERER' => true] as $headerName => $unused) {
    $value = (string) ($_SERVER[$headerName] ?? '');
    if ($value === '') {
        continue;
    }
    $valueHost = strtolower((string) parse_url($value, PHP_URL_HOST));
    if ($valueHost !== '' && !in_array($valueHost, $allowedHosts, true)) {
        todof1_f1_respond(403, ['error' => 'origen_no_permitido']);
    }
}

$rawPath = isset($_GET['ruta']) ? (string) $_GET['ruta'] : '';
$path = ltrim($rawPath, '/');
$path = preg_replace('#\.{2,}#', '', $path) ?? '';

if ($path === '' || strlen($path) > 120 || todof1_f1_route_ok($path) !== true) {
    todof1_f1_respond(400, ['error' => 'ruta_no_permitida']);
}

$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 100;
$offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;
$limit = max(1, min($limit, 1000));
$offset = max(0, min($offset, 100000));

/* 'forzar' solo se atiende con la clave interna (variables de entorno); sin
   clave configurada se ignora, así ningún visitante puede saltarse la caché
   y consumir cuota del origen. */
$force = false;
$forceKey = getenv('TODOF1_F1_FORCE_KEY');
if (is_string($forceKey) && $forceKey !== ''
    && isset($_GET['forzar']) && $_GET['forzar'] === '1'
    && hash_equals($forceKey, (string) ($_SERVER['HTTP_X_TODOF1_CLAVE'] ?? ''))) {
    $force = true;
}

$result = todof1_f1_get($path, $limit, $offset, $force);

$headers = [
    'X-TodoF1-Datos' => $result['mode'],
    'X-TodoF1-Ruta' => $path,
];
if ($result['saved_at'] > 0) {
    $headers['X-TodoF1-Guardado'] = gmdate('c', (int) $result['saved_at']);
}
if ($result['error'] !== '') {
    $headers['X-TodoF1-Aviso'] = substr(preg_replace('/[^A-Za-z0-9_:.-]/', '', (string) $result['error']) ?? '', 0, 60);
}

if ($result['payload'] === null) {
    // Respuesta definitiva del proveedor (p. ej. ruta inexistente en la API):
    // se traslada su estado sin inventar datos ni tocar la copia guardada.
    if ($result['mode'] === 'definitiva') {
        todof1_f1_respond($result['status'] > 0 ? (int) $result['status'] : 404, [
            'error' => 'respuesta_del_proveedor',
            'detalle' => 'La API de datos devolvió una respuesta definitiva para esta consulta.',
            'ruta' => $path,
            'copia' => false,
        ], $headers);
    }

    todof1_f1_respond(503, [
        'error' => 'jolpica_no_disponible',
        'detalle' => 'No hay copia guardada de esta consulta y la API de datos no responde.',
        'ruta' => $path,
        'copia' => false,
    ], $headers);
}

todof1_f1_respond(200, $result['payload'], $headers);
