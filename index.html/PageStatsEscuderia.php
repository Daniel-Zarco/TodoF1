<?php
$tf_escuderia = isset($_GET['escuderia']) ? trim((string) $_GET['escuderia']) : '';
$tf_year = isset($_GET['year']) && is_numeric($_GET['year']) ? (int) $_GET['year'] : 2026;
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $tf_escuderia !== '' ? htmlspecialchars($tf_escuderia) . ' &mdash; ' : '' ?>Escuder&iacute;a &mdash; TodoF1</title>
  <link rel="icon" href="/Images/favicon.ico?v=2" sizes="any">
  <link rel="icon" type="image/png" sizes="32x32" href="/Images/favicon-32x32.png?v=2">
  <link rel="icon" type="image/png" sizes="16x16" href="/Images/favicon-16x16.png?v=2">
  <link rel="stylesheet" href="css/todof1.css?v=<?= @filemtime(__DIR__ . '/css/todof1.css') ?: '1' ?>">
  <link rel="stylesheet" href="css/navbar.css?v=<?= @filemtime(__DIR__ . '/css/navbar.css') ?: '1' ?>">
  <link rel="stylesheet" href="css/PageStatsEscuderia.css?v=<?= @filemtime(__DIR__ . '/css/PageStatsEscuderia.css') ?: '1' ?>">
  <script src="js/escuderia-logos.js?v=<?= @filemtime(__DIR__ . '/js/escuderia-logos.js') ?: '1' ?>" defer></script>
  <script src="js/PageStatsEscuderia.js?v=<?= @filemtime(__DIR__ . '/js/PageStatsEscuderia.js') ?: '1' ?>" defer></script>
</head>

<body>
  <?php include __DIR__ . '/../api/navbar.php'; ?>

  <main class="tf-main">
    <div class="tf-container">

      <a class="tf-back" id="btn-volver" href="/index.html/PageEscuderias.php?year=<?= $tf_year ?>">Volver a Escuder&iacute;as</a>

      <!-- ================= CABECERA (escudo protagonista) ================= -->
      <header class="team-hero" id="team-hero">
        <div class="team-hero__crest" id="team-crest" aria-hidden="true">
          <span class="team-hero__fallback" id="team-mono">Cargando&hellip;</span>
          <span class="team-hero__crest-line" aria-hidden="true"></span>
        </div>

        <div class="team-hero__main">
          <span class="tf-eyebrow" id="team-eyebrow">Escuder&iacute;a &middot; Temporada <?= $tf_year ?></span>
          <h1 class="team-hero__name" id="titulo"><?= $tf_escuderia !== '' ? htmlspecialchars($tf_escuderia) : 'Cargando&hellip;' ?></h1>
          <p class="team-hero__sub" id="team-sub">Cargando informaci&oacute;n de la escuder&iacute;a&hellip;</p>
          <ul class="team-hero__tags" id="team-tags"></ul>
        </div>

        <div class="team-hero__side" id="team-side">
          <div class="team-hero__stat"><span>Mundial de Constructores</span><b>&mdash;</b></div>
          <div class="team-hero__stat"><span>Puntos <?= $tf_year ?></span><b>&mdash;</b></div>
        </div>
      </header>

      <!-- ================= ESTADÍSTICAS ================= -->
      <section class="tf-section" aria-labelledby="stats-title">
        <div class="tf-section__head team-head">
          <div>
            <h2 class="tf-section__title" id="stats-title">Estad&iacute;sticas deportivas</h2>
            <p class="tf-section__sub" id="kpi-scope">Cargando rendimiento de la temporada&hellip;</p>
          </div>
        </div>
        <div class="team-kpis" id="team-kpis">
          <div class="tf-loading"><span class="tf-spinner" aria-hidden="true"></span><span>Cargando estad&iacute;sticas&hellip;</span></div>
        </div>
      </section>

      <!-- ================= EVOLUCIÓN ================= -->
      <section class="tf-section" aria-labelledby="evolution-title">
        <div class="tf-section__head team-head">
          <div>
            <h2 class="tf-section__title" id="evolution-title">Evoluci&oacute;n en F&oacute;rmula 1</h2>
            <p class="tf-section__sub" id="evolution-sub">Progresi&oacute;n temporada a temporada.</p>
          </div>
          <div class="team-switch" role="group" aria-label="M&eacute;trica del gr&aacute;fico">
            <button type="button" class="team-switch__btn is-active" id="metric-points" aria-pressed="true">Puntos</button>
            <button type="button" class="team-switch__btn" id="metric-position" aria-pressed="false">Posici&oacute;n</button>
          </div>
        </div>

        <div class="team-chart" id="team-chart">
          <div class="tf-loading"><span class="tf-spinner" aria-hidden="true"></span><span>Cargando evoluci&oacute;n&hellip;</span></div>
        </div>
        <p class="team-note" id="evolution-note">
          Puntos y posiciones oficiales del Campeonato de Constructores de cada temporada disputada. Los sistemas de
          puntuaci&oacute;n han cambiado a lo largo de la historia de la F1, por lo que los puntos de &eacute;pocas distintas
          no son directamente comparables entre s&iacute;. Solo aparecen las temporadas realmente disputadas.
        </p>
      </section>

      <!-- ================= PILOTOS ================= -->
      <section class="tf-section" aria-labelledby="drivers-title">
        <div class="tf-section__head team-head">
          <div>
            <h2 class="tf-section__title" id="drivers-title">Pilotos de la temporada</h2>
            <p class="tf-section__sub" id="drivers-sub">Pilotos que compitieron con la escuder&iacute;a.</p>
          </div>
        </div>
        <div class="team-drivers" id="team-drivers">
          <div class="tf-loading"><span class="tf-spinner" aria-hidden="true"></span><span>Cargando pilotos&hellip;</span></div>
        </div>
      </section>

      <!-- ================= PALMARÉS ================= -->
      <section class="tf-section" aria-labelledby="honours-title">
        <div class="tf-section__head team-head">
          <div>
            <h2 class="tf-section__title" id="honours-title">Palmar&eacute;s</h2>
            <p class="tf-section__sub" id="honours-sub">T&iacute;tulos y logros acumulados.</p>
          </div>
        </div>
        <div class="team-honours" id="team-honours">
          <div class="tf-loading"><span class="tf-spinner" aria-hidden="true"></span><span>Cargando palmar&eacute;s&hellip;</span></div>
        </div>
      </section>

      <!-- ================= RESULTADOS ================= -->
      <section class="tf-section" aria-labelledby="results-title">
        <div class="tf-section__head team-head">
          <div>
            <h2 class="tf-section__title" id="results-title">Resultados por temporada</h2>
            <p class="tf-section__sub" id="results-sub">Grandes Premios, victorias, podios, puntos y posici&oacute;n final.</p>
          </div>
        </div>

        <div class="tf-table-wrap">
          <table class="tf-table team-table">
            <caption class="team-vh">Resultados de la escuder&iacute;a por temporada</caption>
            <thead>
              <tr>
                <th scope="col" id="th-year" aria-sort="descending">
                  <button type="button" class="team-table__sort" id="sort-year" aria-label="Ordenar por temporada">
                    Temporada <span aria-hidden="true">&#8595;</span>
                  </button>
                </th>
                <th scope="col">Pilotos</th>
                <th scope="col" class="tf-td-right">GP</th>
                <th scope="col" class="tf-td-right">Victorias</th>
                <th scope="col" class="tf-td-right">Podios</th>
                <th scope="col" class="tf-td-right">Puntos</th>
                <th scope="col" class="tf-td-right">Posici&oacute;n</th>
              </tr>
            </thead>
            <tbody id="team-results">
              <tr class="tf-table__empty"><td colspan="7"><span class="tf-loading-inline">Cargando resultados&hellip;</span></td></tr>
            </tbody>
          </table>
        </div>

        <p class="team-note" id="results-note">
          GP, victorias y podios corresponden a carreras de Gran Premio (sin sprints) realmente disputadas. Puntos y
          posici&oacute;n son los oficiales del Campeonato de Constructores; el guion (&mdash;) indica dato no disponible y
          <strong>0</strong> es un valor real. Las filas con el trofeo corresponden a temporadas en las que la escuder&iacute;a
          gan&oacute; el Mundial de Constructores.
        </p>
      </section>

    </div>
  </main>
</body>

</html>
