<?php
session_start();
$isLoggedIn = isset($_SESSION['username']);
$username = $isLoggedIn ? $_SESSION['username'] : '';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>TodoF1 — Estad&iacute;sticas de F&oacute;rmula 1</title>
    <link rel="stylesheet" href="css/todof1.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/PagePrincipal.css">
    <script src="js/PagePrincipal.js" defer></script>
</head>

<body>
    <?php include __DIR__ . '/../api/navbar.php'; ?>

    <main class="tf-main">
        <div class="tf-container">

            <!-- Hero -->
            <section class="tf-hero">
                <div class="tf-hero__inner">
                    <div class="tf-hero__content">
                        <span class="tf-eyebrow">F&oacute;rmula 1 &middot; 1950 &mdash; 2026</span>
                        <h1 class="tf-hero__title">El archivo completo de la <span>F&oacute;rmula 1</span></h1>
                        <p class="tf-hero__sub">
                            Consulta temporadas, pilotos, escuder&iacute;as y circuitos con datos actualizados.
                            Elige una temporada para explorar las clasificaciones y estad&iacute;sticas.
                        </p>
                    </div>
                    <div class="tf-hero__aside">
                        <div class="tf-field">
                            <label class="tf-field__label" for="anio">Temporada</label>
                            <select id="anio" class="tf-select" name="anio" aria-label="Seleccionar temporada"></select>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Resumen de temporada -->
            <section class="tf-section" aria-labelledby="resumen-title">
                <div class="tf-section__head">
                    <div>
                        <h2 class="tf-section__title" id="resumen-title">Resumen de temporada <span class="tf-text-red" id="resumen-year"></span></h2>
                        <p class="tf-section__sub">Datos globales de la temporada seleccionada.</p>
                    </div>
                </div>

                <div class="tf-grid tf-grid--4">
                    <div class="tf-kpi">
                        <span class="tf-kpi__label">Grandes Premios</span>
                        <div class="tf-kpi__value tf-num" id="kpi-gp">&mdash;</div>
                        <div class="tf-kpi__meta">Carreras en el calendario</div>
                    </div>
                    <div class="tf-kpi">
                        <span class="tf-kpi__label">Pilotos</span>
                        <div class="tf-kpi__value tf-num" id="kpi-pilotos">&mdash;</div>
                        <div class="tf-kpi__meta">Participantes en la temporada</div>
                    </div>
                    <div class="tf-kpi">
                        <span class="tf-kpi__label">Escuder&iacute;as</span>
                        <div class="tf-kpi__value tf-num" id="kpi-escuderias">&mdash;</div>
                        <div class="tf-kpi__meta">Constructores en pista</div>
                    </div>
                    <div class="tf-kpi">
                        <span class="tf-kpi__label">Resultados</span>
                        <div class="tf-kpi__value tf-num" id="kpi-resultados">&mdash;</div>
                        <div class="tf-kpi__meta">Registros de carrera</div>
                    </div>
                </div>
            </section>

            <!-- Clasificaciones -->
            <section class="tf-section">
                <div class="tf-grid tf-grid--2 tf-classif">

                    <div class="tf-panel">
                        <div class="tf-section__head">
                            <div>
                                <h2 class="tf-section__title">Clasificaci&oacute;n de pilotos</h2>
                                <p class="tf-section__sub">Campeonato de pilotos</p>
                            </div>
                            <a id="link-pilotos-full" class="tf-btn tf-btn--ghost tf-btn--sm" href="/index.html/PagePilotos.php">
                                Ver completa
                            </a>
                        </div>
                        <div class="tf-standings" id="standings-pilotos" aria-live="polite">
                            <div class="tf-loading"><span class="tf-spinner"></span><span>Cargando clasificaci&oacute;n&hellip;</span></div>
                        </div>
                    </div>

                    <div class="tf-panel">
                        <div class="tf-section__head">
                            <div>
                                <h2 class="tf-section__title">Clasificaci&oacute;n de constructores</h2>
                                <p class="tf-section__sub">Copa de constructores</p>
                            </div>
                            <a id="link-escuderias-full" class="tf-btn tf-btn--ghost tf-btn--sm" href="/index.html/PageEstadisticas.php">
                                Ver completa
                            </a>
                        </div>
                        <div class="tf-standings" id="standings-escuderias" aria-live="polite">
                            <div class="tf-loading"><span class="tf-spinner"></span><span>Cargando clasificaci&oacute;n&hellip;</span></div>
                        </div>
                    </div>

                </div>
            </section>

            <!-- Accesos rápidos -->
            <section class="tf-section">
                <div class="tf-section__head">
                    <div>
                        <h2 class="tf-section__title">Explorar</h2>
                        <p class="tf-section__sub">Accesos directos a la temporada seleccionada.</p>
                    </div>
                </div>

                <div class="tf-grid tf-grid--3">
                    <a class="tf-access" id="access-pilotos" href="/index.html/PagePilotos.php">
                        <span class="tf-access__icon" aria-hidden="true">&#128100;</span>
                        <span class="tf-access__title">Pilotos</span>
                        <p class="tf-access__desc">Plantilla, nacionalidades y clasificaci&oacute;n de la temporada.</p>
                        <span class="tf-access__arrow">Explorar pilotos &rarr;</span>
                    </a>
                    <a class="tf-access" id="access-escuderias" href="/index.html/PageEscuderias.php">
                        <span class="tf-access__icon" aria-hidden="true">&#127937;</span>
                        <span class="tf-access__title">Escuder&iacute;as</span>
                        <p class="tf-access__desc">Equipos, puntos y pilotos que compiten este a&ntilde;o.</p>
                        <span class="tf-access__arrow">Explorar escuder&iacute;as &rarr;</span>
                    </a>
                    <a class="tf-access" id="access-circuitos" href="/index.html/PageCircuitos.php">
                        <span class="tf-access__icon" aria-hidden="true">&#127937;</span>
                        <span class="tf-access__title">Circuitos</span>
                        <p class="tf-access__desc">Calendario de Grandes Premios y sedes de la temporada.</p>
                        <span class="tf-access__arrow">Explorar circuitos &rarr;</span>
                    </a>
                </div>
            </section>

        </div>
    </main>
</body>

</html>
