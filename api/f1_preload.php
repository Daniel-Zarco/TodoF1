<?php
declare(strict_types=1);

/* ============================================================
   TodoF1 — Precarga de la copia de respaldo
   ------------------------------------------------------------
   Rellena la caché persistente que sirve api/f1.php, para que TodoF1
   funcione incluso para un visitante nuevo que llegue con Jolpica
   caída. Solo guarda respuestas VÁLIDAS (las mismas validaciones que
   el proxy); si Jolpica no responde, no se guarda nada.

   Estrategia por NIVELES:
     Nivel 1 (esencial): temporada en curso; calendario, clasificación
        de constructores y de pilotos (paginada), constructores,
        pilotos y resultados (paginado). Es lo que necesitan la página
        principal, Pilotos, Escuderías, Circuitos y Estadísticas.
     Nivel 2 (histórico prioritario): el nivel 1 para varias
        temporadas (por defecto la actual y las dos anteriores), que
        son las que más se consultan.
     Nivel 3 (detalle, bajo demanda): fichas individuales, es decir
        las clasificaciones y resultados POR constructor y POR piloto
        de cada temporada, más los detalles de piloto. Es el más
        costoso: se pide de forma explícita.

   Uso (SOLO desde línea de comandos, nunca por web):
     php api/f1_preload.php                     # nivel 1 (temporada actual)
     php api/f1_preload.php --nivel=2
     php api/f1_preload.php --nivel=2 --temporadas=2026,2025,2024
     php api/f1_preload.php --nivel=3 --temporadas=2026
     php api/f1_preload.php --nivel=2 --desde=2015 --hasta=2026

   Opciones:
     --nivel=1|2|3    Nivel de precarga (def. 1).
     --temporadas=A,B Temporadas objetivo.
     --desde=A --hasta=B  Rango de temporadas (alternativa).
     --resultados=N   Páginas de resultados por temporada (def. 6, máx. 40).
     --pausa=MS       Espera entre peticiones al origen (def. 500).
     --historial      En nivel 3, añade los historiales completos de cada
                      piloto y constructor (MUY costoso: úsalo con pocas
                      temporadas).
     --forzar         Refresca aunque la copia siga vigente.
     --silencioso     Solo el resumen final.

   Código de salida: 0 si todo fue bien; 1 si el origen no respondió en
   alguna ruta o si falta alguna familia esencial; 2 si no hay caché.
   ============================================================ */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Este script solo puede ejecutarse desde la linea de comandos.\n");
}

define('TODOF1_F1_LIB', 1);
require __DIR__ . '/f1.php';

$temporadas = [];
$nivel = 1;
$paginasResultados = 6;
$pausaMs = 500;
$forzar = false;
$silencioso = false;
$historial = false;

foreach (array_slice($argv, 1) as $arg) {
    if (strpos($arg, '--temporadas=') === 0) {
        foreach (explode(',', substr($arg, 13)) as $valor) {
            $anio = (int) trim($valor);
            if ($anio >= 1950 && $anio <= 2100) {
                $temporadas[] = $anio;
            }
        }
    } elseif (strpos($arg, '--nivel=') === 0) {
        $nivel = max(1, min((int) substr($arg, 8), 3));
    } elseif (strpos($arg, '--desde=') === 0) {
        $desde = (int) substr($arg, 8);
    } elseif (strpos($arg, '--hasta=') === 0) {
        $hasta = (int) substr($arg, 8);
    } elseif (strpos($arg, '--resultados=') === 0) {
        $paginasResultados = max(1, min((int) substr($arg, 13), 40));
    } elseif (strpos($arg, '--pausa=') === 0) {
        $pausaMs = max(0, min((int) substr($arg, 8), 10000));
    } elseif ($arg === '--forzar') {
        $forzar = true;
    } elseif ($arg === '--silencioso') {
        $silencioso = true;
    } elseif ($arg === '--historial') {
        $historial = true;
    } else {
        fwrite(STDERR, "Opcion no reconocida: $arg\n");
        exit(2);
    }
}

if (isset($desde, $hasta) && $hasta >= $desde) {
    for ($anio = $desde; $anio <= $hasta; $anio++) {
        $temporadas[] = $anio;
    }
}
if ($temporadas === []) {
    $actual = (int) date('Y');
    $temporadas = $nivel >= 2 ? [$actual, $actual - 1, $actual - 2] : [$actual];
}
$temporadas = array_values(array_unique($temporadas));
rsort($temporadas);   // de la más reciente a la más antigua

$dirCache = todof1_f1_cache_dir();
if ($dirCache === '') {
    fwrite(STDERR, "No hay directorio de cache escribible.\n");
    exit(2);
}

function linea(string $texto, bool $silencioso): void
{
    if (!$silencioso) {
        echo $texto . "\n";
    }
}

echo "TodoF1 — precarga de respaldo\n";
echo "  Origen     : " . todof1_f1_origin() . "\n";
echo "  Cache      : " . $dirCache . "\n";
echo "  Nivel      : $nivel" . ($historial ? " (+ historiales completos)" : "") . "\n";
echo "  Temporadas : " . implode(', ', $temporadas) . "\n";
echo "  Pausa      : {$pausaMs} ms | paginas de resultados: {$paginasResultados}\n";
echo str_repeat('-', 74) . "\n";

// Acción explícita de un operador: se parte de un cortacircuitos limpio.
todof1_f1_breaker_reset();
sleep(1);
$breaker = todof1_f1_breaker_get();
if ($breaker['until'] > time()) {
    echo "El origen no responde ahora mismo: se cancela la precarga sin guardar nada.\n";
    exit(1);
}

$resumen = ['live' => 0, 'cache' => 0, 'respaldo' => 0, 'sin-copia' => 0, 'definitiva' => 0];

/**
 * Pide una ruta, la guarda si es válida y contabiliza el resultado.
 * Devuelve el payload de las respuestas válidas (para poder encadenar).
 */
function precargar(string $path, int $limit, int $offset, bool $forzar, bool $silencioso, array &$resumen): ?array
{
    $resultado = todof1_f1_get($path, $limit, $offset, $forzar);
    $modo = (string) $resultado['mode'];
    $resumen[$modo] = ($resumen[$modo] ?? 0) + 1;
    linea(sprintf('  %-56s %-13s %s', $path . ' [' . $offset . ']', $modo, $resultado['error']), $silencioso);
    return is_array($resultado['payload']) ? $resultado['payload'] : null;
}

/** ¿Sigue el origen respondiendo? (cortacircuitos abierto = parar) */
function origenCaido(): bool
{
    $breaker = todof1_f1_breaker_get();
    return $breaker['until'] > time();
}

/** Nivel 1: datos esenciales de una temporada. */
function nivel1(int $anio, int $paginasResultados, int $pausaMs, bool $forzar, bool $silencioso, array &$resumen): void
{
    precargar("$anio.json", 100, 0, $forzar, $silencioso, $resumen);
    precargar("$anio/constructorStandings.json", 100, 0, $forzar, $silencioso, $resumen);
    precargar("$anio/constructors.json", 100, 0, $forzar, $silencioso, $resumen);
    precargar("$anio/drivers.json", 100, 0, $forzar, $silencioso, $resumen);

    // Clasificación de pilotos: puede superar los 100 registros.
    $offset = 0;
    for ($pagina = 0; $pagina < 10; $pagina++) {
        $payload = precargar("$anio/driverStandings.json", 100, $offset, $forzar, $silencioso, $resumen);
        $total = (int) ($payload['MRData']['total'] ?? 0);
        $offset += 100;
        if ($offset >= $total) {
            break;
        }
        if ($pausaMs > 0) {
            usleep($pausaMs * 1000);
        }
    }

    // Resultados de carrera: lo más pesado y lo más usado.
    $offset = 0;
    for ($pagina = 0; $pagina < $paginasResultados; $pagina++) {
        $payload = precargar("$anio/results.json", 100, $offset, $forzar, $silencioso, $resumen);
        $total = (int) ($payload['MRData']['total'] ?? 0);
        $offset += 100;
        if ($offset >= $total) {
            break;
        }
        if ($pausaMs > 0) {
            usleep($pausaMs * 1000);
        }
    }
}

/** Nivel 3: clasificaciones y resultados por constructor y por piloto. */
function nivel3(int $anio, bool $historial, int $pausaMs, bool $forzar, bool $silencioso, array &$resumen): void
{
    $constructores = precargar("$anio/constructors.json", 100, 0, $forzar, $silencioso, $resumen);
    $pilotos = precargar("$anio/driverStandings.json", 100, 0, $forzar, $silencioso, $resumen);

    $listaConstructores = $constructores['MRData']['ConstructorTable']['Constructors'] ?? [];
    $listaPilotos = $pilotos['MRData']['StandingsTable']['StandingsLists'][0]['DriverStandings'] ?? [];

    foreach ($listaConstructores as $constructor) {
        $id = (string) ($constructor['constructorId'] ?? '');
        if ($id === '') {
            continue;
        }
        precargar("$anio/constructors/$id/constructorStandings.json", 100, 0, $forzar, $silencioso, $resumen);
        precargar("$anio/constructors/$id/results.json", 100, 0, $forzar, $silencioso, $resumen);
        if ($historial) {
            precargar("constructors/$id/results.json", 100, 0, $forzar, $silencioso, $resumen);
        }
        if (origenCaido()) {
            return;
        }
        if ($pausaMs > 0) {
            usleep($pausaMs * 1000);
        }
    }

    foreach ($listaPilotos as $entry) {
        $driver = $entry['Driver'] ?? [];
        $id = (string) ($driver['driverId'] ?? '');
        if ($id === '') {
            continue;
        }
        precargar("$anio/drivers/$id/driverStandings.json", 100, 0, $forzar, $silencioso, $resumen);
        precargar("drivers/$id.json", 100, 0, $forzar, $silencioso, $resumen);
        if ($historial) {
            precargar("drivers/$id/results.json", 100, 0, $forzar, $silencioso, $resumen);
        }
        if (origenCaido()) {
            return;
        }
        if ($pausaMs > 0) {
            usleep($pausaMs * 1000);
        }
    }
}

foreach ($temporadas as $anio) {
    linea("Temporada $anio", $silencioso);
    nivel1($anio, $paginasResultados, $pausaMs, $forzar, $silencioso, $resumen);
    if (origenCaido()) {
        echo "El origen ha dejado de responder: se detiene la precarga (lo descargado queda guardado).\n";
        break;
    }
    if ($nivel >= 3) {
        nivel3($anio, $historial, $pausaMs, $forzar, $silencioso, $resumen);
        if (origenCaido()) {
            echo "El origen ha dejado de responder: se detiene la precarga (lo descargado queda guardado).\n";
            break;
        }
    }
    if ($pausaMs > 0) {
        usleep($pausaMs * 1000);
    }
}

// Lista completa de constructores (la usa la ficha al resolver por identificador).
precargar('constructors.json', 100, 0, $forzar, $silencioso, $resumen);

echo str_repeat('-', 74) . "\n";
printf("  Guardadas ahora (live)   : %d\n", $resumen['live']);
printf("  Ya estaban en la cache   : %d\n", $resumen['cache']);
printf("  Servidas de respaldo     : %d\n", $resumen['respaldo']);
printf("  Sin copia (origen caido) : %d\n", $resumen['sin-copia']);
printf("  Respuesta definitiva 4xx : %d\n", $resumen['definitiva']);

$estado = todof1_f1_status();
$cobertura = $estado['cobertura'];
echo str_repeat('-', 74) . "\n";
echo "COBERTURA REAL DEL RESPALDO\n";
printf("  Entradas en la cache        : %d (%.1f KB)\n", $estado['entradas'], $estado['bytes'] / 1024);
printf("  Temporada en curso (%d) : %s familias esenciales\n", $cobertura['temporada_en_curso'], $cobertura['familias_completas']);
foreach ($cobertura['familias'] as $nombre => $info) {
    printf("    %-30s %s\n", $nombre, $info['guardado'] ? 'guardado (' . $info['actualizado'] . ')' : 'PENDIENTE');
}
if ($cobertura['pendientes'] !== []) {
    echo "  Pendientes: " . implode(', ', $cobertura['pendientes']) . "\n";
}
$temporadasConDatos = $cobertura['temporadas_con_datos'];
echo "  Temporadas con datos: " . ($temporadasConDatos === [] ? 'ninguna' : count($temporadasConDatos) . ' (' . implode(', ', array_slice(array_keys($temporadasConDatos), -12)) . ')') . "\n";

if ($resumen['live'] === 0 && $resumen['cache'] === 0) {
    echo "RESULTADO: no se pudo guardar nada (Jolpica no responde).\n";
    exit(1);
}
if ($cobertura['pendientes'] !== []) {
    echo "RESULTADO: precarga parcial; la temporada en curso no está completa.\n";
    exit(1);
}
echo "RESULTADO: precarga completada (nivel $nivel).\n";
exit(0);
