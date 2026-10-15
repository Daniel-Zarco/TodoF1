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
    <link rel="stylesheet" href="css/todof1.css?v=<?= @filemtime(__DIR__ . '/css/todof1.css') ?: '1' ?>">
    <link rel="stylesheet" href="css/navbar.css?v=<?= @filemtime(__DIR__ . '/css/navbar.css') ?: '1' ?>">
    <link rel="stylesheet" href="css/PagePrincipal.css?v=<?= @filemtime(__DIR__ . '/css/PagePrincipal.css') ?: '1' ?>">
    <script src="js/PagePrincipal.js?v=<?= @filemtime(__DIR__ . '/js/PagePrincipal.js') ?: '1' ?>" defer></script>
</head>

<body class="tf-page-dashboard">
    <?php include __DIR__ . '/../api/navbar.php'; ?>

    <main class="tf-main">
        <div class="tf-container">

            <!-- ================= HERO ================= -->
            <section class="hero" aria-labelledby="hero-title">
                <div class="hero__media" aria-hidden="true">
                    <img id="hero-img" class="hero__img" src="/Images/heroes/default/a.jpg" data-general="/Images/heroes/default/a.jpg"
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
                            var general = im.getAttribute('data-general') || im.getAttribute('src');
                            im.onerror = function () { im.onerror = null; im.setAttribute('src', general); };
                            var y = parseInt(new URLSearchParams(location.search).get('year'), 10);
                            if (!y || y < 1950 || y > 2026) {
                                try { y = parseInt(sessionStorage.getItem('todof1:year'), 10); } catch (e1) { y = NaN; }
                            }
                            if (!y || y < 1950 || y > 2026) y = 2026;
                            var act = JSON.parse(localStorage.getItem('todof1:teams-active') || 'null');
                            if (act && act.hero && String(act.year) === String(y)) im.setAttribute('src', act.hero);
                        } catch (e) { /* sin almacenamiento */ }
                    })();
                </script>

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
                        <span class="stat__arrow" aria-hidden="true">&#8594;</span>
                    </a>
                    <a class="stat" id="stat-pilotos" href="/index.html/PagePilotos.php">
                        <span class="stat__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="3.2"></circle><path d="M5.5 20a6.5 6.5 0 0 1 13 0"></path></svg>
                        </span>
                        <span class="stat__num tf-num" id="kpi-pilotos">&mdash;</span>
                        <span class="stat__label">Pilotos</span>
                        <span class="stat__meta">Participantes</span>
                        <span class="stat__arrow" aria-hidden="true">&#8594;</span>
                    </a>
                    <a class="stat" id="stat-escuderias" href="/index.html/PageEscuderias.php">
                        <span class="stat__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3"></circle><path d="M3.5 19a5.5 5.5 0 0 1 11 0"></path><circle cx="17" cy="9" r="2.3"></circle><path d="M15 19a4.6 4.6 0 0 1 6.2-4.3"></path></svg>
                        </span>
                        <span class="stat__num tf-num" id="kpi-escuderias">&mdash;</span>
                        <span class="stat__label">Escuder&iacute;as</span>
                        <span class="stat__meta">Constructores</span>
                        <span class="stat__arrow" aria-hidden="true">&#8594;</span>
                    </a>
                    <a class="stat" id="stat-resultados" href="/index.html/PageEstadisticas.php">
                        <span class="stat__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20V11"></path><path d="M9 20V4"></path><path d="M14 20v-7"></path><path d="M19 20V7"></path></svg>
                        </span>
                        <span class="stat__num tf-num" id="kpi-resultados">&mdash;</span>
                        <span class="stat__label">Resultados</span>
                        <span class="stat__meta">Registros de carrera</span>
                        <span class="stat__arrow" aria-hidden="true">&#8594;</span>
                    </a>
                </div>
            </section>

            <!-- ================= ACCESO RÁPIDO ================= -->
            <section class="tf-section" aria-labelledby="explore-title">
                <div class="head">
                    <div>
                        <span class="tf-eyebrow">Explorar</span>
                        <h2 class="head__title" id="explore-title">Acceso r&aacute;pido</h2>
                    </div>
                    <span class="head__tag">Todo lo que necesitas de la F1</span>
                </div>

                <div class="qa-grid">
                    <a class="qa" id="access-pilotos" href="/index.html/PagePilotos.php">
                        <span class="qa__media qa__media--pilotos" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="3.2"></circle><path d="M5.5 20a6.5 6.5 0 0 1 13 0"></path></svg>
                        </span>
                        <span class="qa__label">Pilotos <span aria-hidden="true">&#8594;</span></span>
                    </a>
                    <a class="qa" id="access-escuderias" href="/index.html/PageEscuderias.php">
                        <span class="qa__media qa__media--escuderias" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3"></circle><path d="M3.5 19a5.5 5.5 0 0 1 11 0"></path><circle cx="17" cy="9" r="2.3"></circle><path d="M15 19a4.6 4.6 0 0 1 6.2-4.3"></path></svg>
                        </span>
                        <span class="qa__label">Escuder&iacute;as <span aria-hidden="true">&#8594;</span></span>
                    </a>
                    <a class="qa" id="access-circuitos" href="/index.html/PageCircuitos.php">
                        <span class="qa__media qa__media--circuitos" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 17c0-6 15-7 15-1 0 4-6 4-8 1-2-3-7-2-7-5"></path><circle cx="7" cy="8" r="1.4"></circle><circle cx="18" cy="14" r="1.4"></circle></svg>
                        </span>
                        <span class="qa__label">Circuitos <span aria-hidden="true">&#8594;</span></span>
                    </a>
                    <a class="qa" id="access-resultados" href="/index.html/PageEstadisticas.php">
                        <span class="qa__media qa__media--resultados" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20V11"></path><path d="M9 20V4"></path><path d="M14 20v-7"></path><path d="M19 20V7"></path></svg>
                        </span>
                        <span class="qa__label">Resultados <span aria-hidden="true">&#8594;</span></span>
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
