<?php
ini_set('display_errors', '0');
ini_set('log_errors', '1');

/**
 * Configuración común de sesión.
 *
 * Debe incluirse antes de session_start(). Alwaysdata termina TLS en su
 * proxy, por lo que la petición llega a Apache por HTTP; X-Forwarded-Proto
 * es la cabecera fiable que indica el esquema original.
 */
if (session_status() !== PHP_SESSION_ACTIVE) {
    $proto = isset($_SERVER['HTTP_X_FORWARDED_PROTO'])
        ? strtolower(trim(explode(',', (string) $_SERVER['HTTP_X_FORWARDED_PROTO'])[0]))
        : '';
    $https = (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
        || $proto === 'https'
        || (string) ($_SERVER['SERVER_PORT'] ?? '') === '443';

    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_strict_mode', '1');

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}
