<?php
session_start();

if (isset($_SESSION['username'])) {
    header("Location: PagePrincipal.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Datos enviados
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Conexión a BBDD (resuelta por api/db.php en local y en producción)
    require_once __DIR__ . '/../api/db.php';
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    // Buscar usuario admin
    $stmt = $conn->prepare("SELECT password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($hashed_password, $role);
        $stmt->fetch();

        // Verificar contraseña
        if (password_verify($password, $hashed_password)) {
            if ($role === 'admin') {
                $_SESSION['user_role'] = 'admin';
                $_SESSION['username'] = $username;
                header("Location: PagePrincipal.php");
                exit();
            } else {
                $error = "No tienes permisos para acceder.";
            }
        } else {
            $error = "Usuario o contraseña incorrectos.";
        }
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Acceso administrador &mdash; TodoF1</title>
    <link rel="stylesheet" href="css/todof1.css">
    <link rel="stylesheet" href="css/login.css">
    <script src="js/login.js" defer></script>
</head>

<body class="tf-auth">
    <video id="background-video" autoplay loop muted playsinline>
        <source src="/Images/InitSes.mp4" type="video/mp4" />
        Tu navegador no soporta video HTML5.
    </video>
    <div class="tf-auth__scrim"></div>

    <main class="tf-auth__card">
        <a class="tf-brand tf-brand--auth" href="/index.html/index.html">
            <span class="tf-brand__mark" aria-hidden="true">F1</span>
            <span class="tf-brand__name">Todo<span>F1</span></span>
        </a>

        <span class="tf-eyebrow">Acceso administrador</span>
        <h1 class="tf-auth__title">Iniciar sesión</h1>
        <p class="tf-auth__sub">Accede al panel de administraci&oacute;n y a los datos registrados.</p>

        <form method="POST" action="" class="tf-auth__form">
            <div class="tf-field">
                <label class="tf-field__label" for="username">Usuario</label>
                <input type="text" id="username" name="username" class="tf-input" placeholder="Tu usuario" autocomplete="username" required />
            </div>

            <div class="tf-field">
                <label class="tf-field__label" for="password">Contrase&ntilde;a</label>
                <div class="tf-input-group">
                    <input type="password" name="password" id="password" class="tf-input" placeholder="Tu contrase&ntilde;a" autocomplete="current-password" required />
                    <button id="ojo" type="button" class="toggle-password" onclick="togglePassword()" aria-label="Mostrar u ocultar contrase&ntilde;a">&#128065;</button>
                </div>
            </div>

            <button type="submit" class="tf-btn tf-btn--primary tf-btn--block tf-mt-4">Entrar</button>
        </form>

        <?php if ($error): ?>
            <div class="tf-auth__error" role="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <a class="tf-back tf-auth__back" href="/index.html/InitSes.html">Volver al inicio de sesi&oacute;n</a>
    </main>
</body>
</html>
