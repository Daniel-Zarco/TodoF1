<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['username']);
$username   = $isLoggedIn ? $_SESSION['username'] : '';
$isAdmin    = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';

$current = basename($_SERVER['SCRIPT_NAME'] ?? ($_SERVER['PHP_SELF'] ?? ''));

// El año por defecto lo resuelve el cliente (sessionStorage o última temporada).
// Aquí solo se propaga si viene explícitamente en la URL.
$yearParam = isset($_GET['year']) && is_numeric($_GET['year']) ? (int) $_GET['year'] : null;
$yearQuery = $yearParam !== null ? '?year=' . $yearParam : '';

// Página de temporada asociada a cada sección (para el selector)
$seasonTargets = [
    'PagePrincipal.php'   => '/index.html/PagePrincipal.php',
    'PagePilotos.php'     => '/index.html/PagePilotos.php',
    'PageStatsPiloto.php' => '/index.html/PagePilotos.php',
    'PageEscuderias.php'  => '/index.html/PageEscuderias.php',
    'PageStatsEscuderia.php' => '/index.html/PageEscuderias.php',
    'PageCircuitos.php'   => '/index.html/PageCircuitos.php',
    'PageStatsCircuito.php' => '/index.html/PageCircuitos.php',
    'PageEstadisticas.php' => '/index.html/PageEstadisticas.php',
];
$seasonBase = $seasonTargets[$current] ?? '/index.html/PagePrincipal.php';

$items = [
    ['href' => '/index.html/PagePrincipal.php', 'label' => 'Inicio', 'match' => ['PagePrincipal.php']],
    ['href' => '/index.html/PagePilotos.php' . $yearQuery, 'label' => 'Pilotos', 'match' => ['PagePilotos.php', 'PageStatsPiloto.php']],
    ['href' => '/index.html/PageEscuderias.php' . $yearQuery, 'label' => 'Escuder&iacute;as', 'match' => ['PageEscuderias.php', 'PageStatsEscuderia.php']],
    ['href' => '/index.html/PageCircuitos.php' . $yearQuery, 'label' => 'Circuitos', 'match' => ['PageCircuitos.php', 'PageStatsCircuito.php']],
    ['href' => '/index.html/PageEstadisticas.php' . $yearQuery, 'label' => 'Estad&iacute;sticas', 'match' => ['PageEstadisticas.php']],
];
?>
<header class="tf-nav" id="tf-nav">
    <div class="tf-nav__inner">
        <a class="tf-brand" href="/index.html/PagePrincipal.php" aria-label="TodoF1, ir al inicio">
            <span class="tf-brand__mark" aria-hidden="true">F1</span>
            <span class="tf-brand__name">Todo<span>F1</span></span>
        </a>

        <button class="tf-nav__toggle" id="tf-nav-toggle" type="button"
                aria-label="Abrir men&uacute;" aria-expanded="false" aria-controls="tf-nav-menu">
            <span></span><span></span><span></span>
        </button>

        <nav class="tf-nav__menu" id="tf-nav-menu" aria-label="Navegaci&oacute;n principal">
            <ul class="tf-nav__links">
                <?php foreach ($items as $item): ?>
                    <?php $active = in_array($current, $item['match'], true); ?>
                    <li>
                        <a class="tf-nav__link<?= $active ? ' is-active' : '' ?>"
                           href="<?= htmlspecialchars($item['href']) ?>"
                           <?= $active ? 'aria-current="page"' : '' ?>>
                            <?= $item['label'] ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="tf-nav__tools">
                <label class="tf-nav__season" title="Cambiar temporada">
                    <span class="tf-nav__season-label">Temp.</span>
                    <select class="tf-select tf-select--sm" id="tf-season-select"
                            data-year-base="<?= htmlspecialchars($seasonBase) ?>"
                            aria-label="Seleccionar temporada">
                        <?php for ($y = 2026; $y >= 1950; $y--): ?>
                            <option value="<?= $y ?>"<?= ($yearParam !== null && $y === $yearParam) ? ' selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </label>

                <?php if ($isAdmin): ?>
                    <a class="tf-nav__ghost" href="/api/PageInfo.php">Panel</a>
                <?php endif; ?>

                <?php if ($isLoggedIn): ?>
                    <div class="tf-nav__user">
                        <span class="tf-nav__user-name" title="<?= htmlspecialchars($username) ?>">
                            <span aria-hidden="true">&#128100;</span>
                            <?= htmlspecialchars($username) ?>
                        </span>
                        <form action="/api/logout.php" method="POST">
                            <button type="submit" class="tf-btn tf-btn--ghost tf-btn--sm">Salir</button>
                        </form>
                    </div>
                <?php else: ?>
                    <a class="tf-btn tf-btn--ghost tf-btn--sm" href="/index.html/login.php">Acceso</a>
                <?php endif; ?>
            </div>
        </nav>
    </div>
</header>
<script src="/index.html/js/session.js"></script>
<script src="/index.html/js/navbar.js" defer></script>
