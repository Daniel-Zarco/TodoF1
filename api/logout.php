<?php
require_once __DIR__ . '/../index.html/session.php';
session_start();

$params = session_get_cookie_params();
$_SESSION = [];

if (ini_get('session.use_cookies')) {
    setcookie(session_name(), '', [
        'expires' => time() - 42000,
        'path' => $params['path'] ?: '/',
        'secure' => !empty($params['secure']),
        'httponly' => !empty($params['httponly']),
        'samesite' => $params['samesite'] ?? 'Lax',
    ]);
}

session_destroy();
header("Location: /");
exit();
