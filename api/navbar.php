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
            <span class="tf-nav__tag" aria-hidden="true">Racing Intelligence</span>
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

                <button class="tf-theme-toggle" id="tf-theme-toggle" type="button"
                        aria-pressed="false" aria-label="Activar tema claro" title="Tema claro">
                    <span class="tf-theme-toggle__icon tf-theme-toggle__icon--sun" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                             stroke-linecap="round" stroke-linejoin="round" focusable="false">
                            <circle cx="12" cy="12" r="4.2"></circle>
                            <path d="M12 2.5v2.6M12 18.9v2.6M4.2 4.2l1.8 1.8M18 18l1.8 1.8M2.5 12h2.6M18.9 12h2.6M4.2 19.8L6 18M18 6l1.8-1.8"></path>
                        </svg>
                    </span>
                    <span class="tf-theme-toggle__icon tf-theme-toggle__icon--moon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                             stroke-linecap="round" stroke-linejoin="round" focusable="false">
                            <path d="M20 14.5A8.2 8.2 0 0 1 9.5 4 8.5 8.5 0 1 0 20 14.5Z"></path>
                        </svg>
                    </span>
                </button>

                <button class="tf-teams-btn" id="tf-teams-btn" type="button"
                        aria-haspopup="dialog" aria-expanded="false" aria-label="Mis equipos favoritos">
                    <span class="tf-teams-btn__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                             stroke-linecap="round" stroke-linejoin="round" focusable="false">
                            <path d="M12 20.5S4 15 4 9.2A4.2 4.2 0 0 1 12 6.6a4.2 4.2 0 0 1 8 2.6C20 15 12 20.5 12 20.5Z"></path>
                        </svg>
                    </span>
                    <span class="tf-teams-btn__label">Mis equipos</span>
                </button>

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
                    <a class="tf-btn tf-btn--ghost tf-btn--sm" href="/index.html/login.php" data-no-transition>Acceso</a>
                <?php endif; ?>
            </div>
        </nav>
    </div>
</header>
<script src="/index.html/js/theme.js"></script>
<script src="/index.html/js/session.js"></script>
<script src="/index.html/js/teams.js"></script>
<script src="/index.html/js/navbar.js" defer></script>
