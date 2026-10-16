<?php
/**
 * TodoF1 — Enrutador de la raíz web (www/index.php).
 * Lo publica deploy/deploy.sh en la raíz del sitio.
 *
 * Sirve la página principal (index.html/PagePrincipal.php) para "/" sin
 * duplicar su contenido ni cambiar la URL visible. El .htaccess de la raíz
 * hace lo mismo y añade las redirecciones 301 de las URLs antiguas; este
 * archivo cubre el caso en el que los rewrites no estén disponibles y
 * sustituye a la antigua redirección hacia /index.html/index.html.
 */
declare(strict_types=1);

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri = is_string($uri) ? $uri : '/';

if ($uri === '/' || $uri === '/index.php') {
    // navbar.php determina la sección activa con SCRIPT_NAME.
    $_SERVER['SCRIPT_NAME'] = '/index.html/PagePrincipal.php';
    $_SERVER['PHP_SELF'] = '/index.html/PagePrincipal.php';

    $page = __DIR__ . '/index.html/PagePrincipal.php';

    if (is_file($page)) {
        require $page;
        return;
    }

    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'TodoF1: no se encuentra la página principal (index.html/PagePrincipal.php).';
    return;
}

// Ninguna otra ruta debería llegar hasta aquí: Apache sirve directamente los
// archivos existentes (o responde 404) antes de usar este enrutador.
http_response_code(404);
header('Content-Type: text/plain; charset=utf-8');
echo 'TodoF1: recurso no encontrado.';
