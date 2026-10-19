<?php
session_start();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="color-scheme" content="dark" />
    <title>TodoF1 — Estad&iacute;sticas de F&oacute;rmula 1</title>
    <link rel="icon" href="/Images/favicon.ico" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="/Images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/Images/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/Images/icon-192.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/Images/apple-touch-icon.png">
    <link rel="manifest" href="/Images/site.webmanifest">
    <meta name="theme-color" content="#0B0B0D">
    <meta property="og:type" content="website">
    <meta property="og:title" content="TodoF1 - Estad&iacute;sticas hist&oacute;ricas de F&oacute;rmula 1">
    <meta property="og:image" content="https://todof1.alwaysdata.net/Images/og-image.png">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="https://todof1.alwaysdata.net/Images/og-image.png">
    <script>
        /* Anti-parpadeo: aplica los colores del tema elegido antes de pintar. */
        (function () {
            try {
                /* Personalización por escudería: usa los colores cacheados del tema activo. */
                var y = parseInt(new URLSearchParams(location.search).get('year'), 10);
                if (!y || y < 1950 || y > 2026) {
                    try { y = parseInt(sessionStorage.getItem('todof1:year'), 10); } catch (e1) { y = NaN; }
                }
                if (!y || y < 1950 || y > 2026) y = 2026;
                var act = JSON.parse(localStorage.getItem('todof1:teams-active') || 'null');
                if (act && act.vars && String(act.year) === String(y)) {
                    for (var k in act.vars) {
                        if (act.vars.hasOwnProperty(k)) document.documentElement.style.setProperty(k, act.vars[k]);
                    }
                    if (act.stripe) document.documentElement.setAttribute('data-team-stripe', act.stripe);
                }
            } catch (e) { /* sin almacenamiento */ }
        })();
    </script>
    <!-- Rutas absolutas: la página principal se sirve tanto en "/" (raíz
         del sitio) como en /index.html/PagePrincipal.php. -->
    <link rel="stylesheet" href="/index.html/css/todof1.css?v=<?= @filemtime(__DIR__ . '/css/todof1.css') ?: '1' ?>">
    <link rel="stylesheet" href="/index.html/css/navbar.css?v=<?= @filemtime(__DIR__ . '/css/navbar.css') ?: '1' ?>">
    <link rel="stylesheet" href="/index.html/css/PagePrincipal.css?v=<?= @filemtime(__DIR__ . '/css/PagePrincipal.css') ?: '1' ?>">
    <script src="/index.html/js/CircuitosTrazados.js?v=<?= @filemtime(__DIR__ . '/js/CircuitosTrazados.js') ?: '1' ?>" defer></script>
    <script src="/index.html/js/f1-horarios.js?v=<?= @filemtime(__DIR__ . '/js/f1-horarios.js') ?: '1' ?>" defer></script>
    <script src="/index.html/js/PagePrincipal.js?v=<?= @filemtime(__DIR__ . '/js/PagePrincipal.js') ?: '1' ?>" defer></script>
    <?php
    /* Versiones (fecha de modificación) y lista de imágenes del hero. */
    $tf_hero_versions = [];
    $tf_default_images = [];
    foreach (['default', 'escuderias'] as $tf_dir) {
        $tf_files = glob(__DIR__ . '/../Images/heroes/' . $tf_dir . '/*');
        if (!is_array($tf_files)) {
            continue;
        }
        sort($tf_files, SORT_NATURAL | SORT_FLAG_CASE);
        foreach ($tf_files as $tf_file) {
            if (!is_file($tf_file)) {
                continue;
            }
            $tf_web = '/Images/heroes/' . $tf_dir . '/' . basename($tf_file);
            $tf_hero_versions[$tf_web] = (string) @filemtime($tf_file);
            if ($tf_dir === 'default') {
                $tf_default_images[] = $tf_web;
            }
        }
    }
    ?>
    <script>window.TodoF1DefaultImages = <?= json_encode($tf_default_images) ?>;</script>
    <script>window.TodoF1HeroVersions = <?= json_encode($tf_hero_versions) ?>;</script>
</head>

<body class="tf-page-dashboard">
    <?php include __DIR__ . '/../api/navbar.php'; ?>

    <main class="tf-main">
        <div class="tf-container">

            <!-- ================= HERO ================= -->
            <section class="hero" id="hero" aria-labelledby="hero-title">
                <div class="hero__media" aria-hidden="true">
                    <img id="hero-img" class="hero__img" src="/Images/heroes/default/a.jpg?v=<?= @filemtime(__DIR__ . '/../Images/heroes/default/a.jpg') ?: '1' ?>" data-general="/Images/heroes/default/a.jpg"
                         alt="Monoplazas de Fórmula 1 de distintas escuderías compitiendo en la salida de un Gran Premio"
                         fetchpriority="high" decoding="async" width="1280" height="851">
                    <img id="hero-img-next" class="hero__img hero__img--next" alt="" aria-hidden="true" decoding="async" width="1280" height="851">
                    <span class="hero__scrim"></span>
                    <span class="hero__grid"></span>
                    <span class="hero__stripe"></span>
                </div>
                <script>
                    /* Anti-parpadeo del hero: aplica la imagen del tema guardado antes de pintar. */
                    (function () {
                        try {
                            var im = document.getElementById('hero-img');
                            if (!im) return;
                            var versions = window.TodoF1HeroVersions || {};
                            function withVersion(src) {
                                var v = versions[src];
                                return src + (v ? (src.indexOf('?') >= 0 ? '&' : '?') + 'v=' + v : '');
                            }
                            var general = im.getAttribute('data-general') || im.getAttribute('src');
                            im.onerror = function () { im.onerror = null; im.setAttribute('src', withVersion(general)); };
                            var y = parseInt(new URLSearchParams(location.search).get('year'), 10);
                            if (!y || y < 1950 || y > 2026) {
                                try { y = parseInt(sessionStorage.getItem('todof1:year'), 10); } catch (e1) { y = NaN; }
                            }
                            if (!y || y < 1950 || y > 2026) y = 2026;
                            var act = JSON.parse(localStorage.getItem('todof1:teams-active') || 'null');
                            if (act && act.hero && String(act.year) === String(y)) im.setAttribute('src', withVersion(act.hero));
                        } catch (e) { /* sin almacenamiento */ }
                    })();
                </script>

                <button class="hero__nav hero__nav--prev" type="button" aria-label="Fotografía anterior"></button>
                <button class="hero__nav hero__nav--next" type="button" aria-label="Fotografía siguiente"></button>

                <div class="hero__body">
                    <span class="tf-eyebrow">Temporada <b id="hero-year">&mdash;</b></span>
                    <h1 class="hero__title" id="hero-title">El mundo de la <span>F&oacute;rmula 1</span></h1>
                    <p class="hero__sub">Pilotos. Escuder&iacute;as. Circuitos. Resultados. Toda la informaci&oacute;n de la F1, en un solo lugar.</p>
                    <div class="hero__cta">
                        <a class="tf-btn tf-btn--primary hero__btn" id="hero-cta" href="/index.html/PagePilotos.php">
                            Explorar TodoF1 <span aria-hidden="true">&#8594;</span>
                        </a>
                    </div>
                </div>

                <div class="hero__bar">
                    <label class="hero__season">
                        <span class="hero__season-label">Temporada</span>
                        <select id="anio" class="tf-select hero__select" name="anio" aria-label="Seleccionar temporada"></select>
                    </label>
                </div>
            </section>

            <!-- ================= ESTADÍSTICAS ================= -->
            <section class="tf-section" aria-labelledby="stats-title">
                <h2 class="sr-only" id="stats-title">Estad&iacute;sticas de la temporada</h2>
                <div class="stats">
                    <a class="stat" id="stat-gp" href="/index.html/PageCircuitos.php">
                        <span class="stat__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M5 3v18"></path><path d="M5 4l14 2-2.2 5L19 16 5 18"></path></svg>
                        </span>
                        <span class="stat__num tf-num" id="kpi-gp">&mdash;</span>
                        <span class="stat__label">Grandes Premios</span>
                        <span class="stat__meta">Calendario</span>
                        <span class="stat__arrow" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="13 6 19 12 13 18"></polyline></svg>
                        </span>
                    </a>
                    <a class="stat" id="stat-pilotos" href="/index.html/PagePilotos.php">
                        <span class="stat__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="3.2"></circle><path d="M5.5 20a6.5 6.5 0 0 1 13 0"></path></svg>
                        </span>
                        <span class="stat__num tf-num" id="kpi-pilotos">&mdash;</span>
                        <span class="stat__label">Pilotos</span>
                        <span class="stat__meta">Participantes</span>
                        <span class="stat__arrow" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="13 6 19 12 13 18"></polyline></svg>
                        </span>
                    </a>
                    <a class="stat" id="stat-escuderias" href="/index.html/PageEscuderias.php">
                        <span class="stat__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3"></circle><path d="M3.5 19a5.5 5.5 0 0 1 11 0"></path><circle cx="17" cy="9" r="2.3"></circle><path d="M15 19a4.6 4.6 0 0 1 6.2-4.3"></path></svg>
                        </span>
                        <span class="stat__num tf-num" id="kpi-escuderias">&mdash;</span>
                        <span class="stat__label">Escuder&iacute;as</span>
                        <span class="stat__meta">Constructores</span>
                        <span class="stat__arrow" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="13 6 19 12 13 18"></polyline></svg>
                        </span>
                    </a>
                    <a class="stat" id="stat-resultados" href="/index.html/PageEstadisticas.php">
                        <span class="stat__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20V11"></path><path d="M9 20V4"></path><path d="M14 20v-7"></path><path d="M19 20V7"></path></svg>
                        </span>
                        <span class="stat__num tf-num" id="kpi-resultados">&mdash;</span>
                        <span class="stat__label">Resultados</span>
                        <span class="stat__meta">Registros de carrera</span>
                        <span class="stat__arrow" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="13 6 19 12 13 18"></polyline></svg>
                        </span>
                    </a>
                </div>
            </section>

            <!-- ================= PRÓXIMA CARRERA + PILOTOS ================= -->
            <section class="tf-section">
                <div class="two-col">
                    <div class="panel panel--race" aria-labelledby="race-title">
                        <div class="head head--tight">
                            <div>
                                <span class="tf-eyebrow" id="race-kicker">Pr&oacute;xima carrera</span>
                                <h2 class="head__title" id="race-title">Gran Premio</h2>
                            </div>
                        </div>
                        <div id="next-race" aria-live="polite">
                            <div class="tf-loading"><span class="tf-spinner"></span><span>Cargando&hellip;</span></div>
                        </div>
                    </div>

                    <div class="panel" aria-labelledby="standings-title">
                        <div class="head head--tight">
                            <div>
                                <span class="tf-eyebrow">Clasificaci&oacute;n</span>
                                <h2 class="head__title" id="standings-title">Pilotos</h2>
                            </div>
                            <a id="link-pilotos-full" class="head__link" href="/index.html/PagePilotos.php">Ver completa &#8594;</a>
                        </div>
                        <div class="tf-standings" id="standings-pilotos" aria-live="polite">
                            <div class="tf-loading"><span class="tf-spinner"></span><span>Cargando clasificaci&oacute;n&hellip;</span></div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ================= CLASIFICACIÓN CONSTRUCTORES ================= -->
            <section class="tf-section">
                <div class="panel" aria-labelledby="constructors-title">
                    <div class="head head--tight">
                        <div>
                            <span class="tf-eyebrow">Clasificaci&oacute;n</span>
                            <h2 class="head__title" id="constructors-title">Constructores</h2>
                        </div>
                        <a id="link-escuderias-full" class="head__link" href="/index.html/PageEstadisticas.php">Ver completa &#8594;</a>
                    </div>
                    <div class="tf-standings" id="standings-escuderias" aria-live="polite">
                        <div class="tf-loading"><span class="tf-spinner"></span><span>Cargando clasificaci&oacute;n&hellip;</span></div>
                    </div>
                </div>
            </section>

        </div>
    </main>

    <footer class="tf-credits">
        <div class="tf-container">
            <h2 class="tf-credits__title">Créditos fotográficos</h2>
            <p class="tf-credits__pending" id="photo-credits-pending">Atribuciones y licencias de las fotografías del hero pendientes de confirmar.</p>
            <ul class="tf-credits__list" id="photo-credits" aria-label="Créditos de las imágenes del hero" hidden></ul>
        </div>
    </footer>
</body>

</html>
