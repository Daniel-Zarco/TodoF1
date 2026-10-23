<?php

$current = basename($_SERVER['SCRIPT_NAME'] ?? ($_SERVER['PHP_SELF'] ?? ''));

// El año por defecto lo resuelve el cliente (sessionStorage o última temporada).
// Aquí solo se propaga si viene explícitamente en la URL.
$yearParam = isset($_GET['year']) && is_numeric($_GET['year']) ? (int) $_GET['year'] : null;
$yearQuery = $yearParam !== null ? '?year=' . $yearParam : '';

// Página de temporada asociada a cada sección (para el selector)
$seasonTargets = [
    'PagePrincipal.php'   => '/',
    'PagePilotos.php'     => '/index.html/PagePilotos.php',
    'PageStatsPiloto.php' => '/index.html/PagePilotos.php',
    'PageEscuderias.php'  => '/index.html/PageEscuderias.php',
    'PageStatsEscuderia.php' => '/index.html/PageEscuderias.php',
    'PageCircuitos.php'   => '/index.html/PageCircuitos.php',
    'PageStatsCircuito.php' => '/index.html/PageCircuitos.php',
    'PageEstadisticas.php' => '/index.html/PageEstadisticas.php',
];
// La página principal se sirve en la raíz ("/"); admite también index.php
// como nombre de entrada cuando el enrutador de la raíz la sirve.
$seasonBase = $seasonTargets[$current] ?? '/';

$items = [
    ['href' => '/', 'label' => 'Inicio', 'match' => ['PagePrincipal.php', 'index.php']],
    ['href' => '/index.html/PagePilotos.php' . $yearQuery, 'label' => 'Pilotos', 'match' => ['PagePilotos.php', 'PageStatsPiloto.php']],
    ['href' => '/index.html/PageEscuderias.php' . $yearQuery, 'label' => 'Escuder&iacute;as', 'match' => ['PageEscuderias.php', 'PageStatsEscuderia.php']],
    ['href' => '/index.html/PageCircuitos.php' . $yearQuery, 'label' => 'Circuitos', 'match' => ['PageCircuitos.php', 'PageStatsCircuito.php']],
    ['href' => '/index.html/PageEstadisticas.php' . $yearQuery, 'label' => 'Estad&iacute;sticas', 'match' => ['PageEstadisticas.php']],
];
?>
<header class="tf-nav" id="tf-nav">
    <div class="tf-nav__inner">
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
                            data-season-picker aria-label="Seleccionar temporada">
                        <?php for ($y = (int) date('Y'); $y >= 1950; $y--): ?>
                            <option value="<?= $y ?>"<?= ($yearParam !== null && $y === $yearParam) ? ' selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </label>

                <button class="tf-theme-picker" id="tf-theme-picker" type="button"
                        aria-haspopup="dialog" aria-expanded="false" aria-label="Elegir tema visual">
                    <span class="tf-theme-picker__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                             stroke-linecap="round" stroke-linejoin="round" focusable="false">
                            <path d="M12 21a9 9 0 1 1 9-9c0 2-1.4 3.2-3.2 3.2H16a2 2 0 0 0 0 4h.6A1.4 1.4 0 0 1 18 21H12Z"></path>
                            <circle cx="7.6" cy="12" r="1.1"></circle>
                            <circle cx="10.4" cy="7.8" r="1.1"></circle>
                            <circle cx="15" cy="8.2" r="1.1"></circle>
                        </svg>
                    </span>
                    <span class="tf-theme-picker__label">Elegir tema</span>
                </button>
            </div>
        </nav>
    </div>
</header>
<?php /* Capa de disponibilidad de datos: debe cargarse antes que cualquier
         script que consulte la API (session.js, teams.js y los scripts de
         cada página), para que todas las peticiones pasen por el respaldo
         propio y tengan tiempo máximo. */ ?>
<script src="/index.html/js/f1-datos.js?v=<?= @filemtime(__DIR__ . '/../index.html/js/f1-datos.js') ?: '1' ?>"></script>
<script src="/index.html/js/session.js?v=<?= @filemtime(__DIR__ . '/../index.html/js/session.js') ?: '1' ?>"></script>
<script src="/index.html/js/season-picker.js?v=<?= @filemtime(__DIR__ . '/../index.html/js/season-picker.js') ?: '1' ?>"></script>
<script src="/index.html/js/loader.js?v=<?= @filemtime(__DIR__ . '/../index.html/js/loader.js') ?: '1' ?>"></script>
<script src="/index.html/js/navbar.js?v=<?= @filemtime(__DIR__ . '/../index.html/js/navbar.js') ?: '1' ?>" defer></script>
