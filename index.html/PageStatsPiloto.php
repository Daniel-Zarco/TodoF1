<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Ficha del piloto &mdash; TodoF1</title>
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
  <link rel="stylesheet" href="css/todof1.css?v=<?= @filemtime(__DIR__ . '/css/todof1.css') ?: '1' ?>">
  <link rel="stylesheet" href="css/navbar.css?v=<?= @filemtime(__DIR__ . '/css/navbar.css') ?: '1' ?>">
  <link rel="stylesheet" href="css/PageStatsPiloto.css?v=<?= @filemtime(__DIR__ . '/css/PageStatsPiloto.css') ?: '1' ?>">
  <script src="js/PageStatsPiloto.js?v=<?= @filemtime(__DIR__ . '/js/PageStatsPiloto.js') ?: '1' ?>" defer></script>
</head>

<body>
  <?php include __DIR__ . '/../api/navbar.php'; ?>

  <main class="tf-main">
    <div class="tf-container">

      <a class="tf-back" id="btn-volver" href="/index.html/PagePilotos.php">Volver a Pilotos</a>

      <!-- ================= CABECERA ================= -->
      <header class="pilot-hero" id="pilot-hero">
        <div class="pilot-hero__media">
          <span class="pilot-hero__initials" id="hero-initials" aria-hidden="true">&mdash;</span>
          <img class="pilot-hero__photo" id="piloto-foto" alt="" decoding="async" hidden>
          <span class="pilot-hero__scrim" aria-hidden="true"></span>
          <span class="pilot-hero__num" id="hero-number" aria-hidden="true"></span>
          <span class="pilot-hero__code" id="hero-code" hidden></span>
        </div>

        <div class="pilot-hero__body">
          <span class="tf-eyebrow" id="hero-eyebrow">Ficha del piloto</span>
          <h1 class="pilot-hero__name" id="titulo">Cargando&hellip;</h1>
          <p class="pilot-hero__sub" id="hero-sub">Cargando informaci&oacute;n del piloto&hellip;</p>
          <ul class="pilot-hero__tags" id="hero-tags"></ul>
          <div class="pilot-hero__actions">
            <a class="tf-btn tf-btn--ghost tf-btn--sm" id="wiki" href="#" target="_blank" rel="noopener" hidden>Ver en Wikipedia</a>
            <span class="pilot-hero__season">Temporada <b id="hero-season">&mdash;</b></span>
          </div>
        </div>
      </header>

      <!-- ================= ESTADÍSTICAS ================= -->
      <section class="tf-section" aria-labelledby="stats-title">
        <div class="tf-section__head pilot-head">
          <div>
            <h2 class="tf-section__title" id="stats-title">Estad&iacute;sticas deportivas</h2>
            <p class="tf-section__sub" id="kpi-scope">Cargando trayectoria&hellip;</p>
          </div>
        </div>
        <div class="pilot-kpis" id="pilot-kpis">
          <div class="tf-loading"><span class="tf-spinner" aria-hidden="true"></span><span>Cargando estad&iacute;sticas&hellip;</span></div>
        </div>
      </section>

      <!-- ================= EVOLUCIÓN ================= -->
      <section class="tf-section" aria-labelledby="evolution-title">
        <div class="tf-section__head pilot-head">
          <div>
            <h2 class="tf-section__title" id="evolution-title">Evoluci&oacute;n en F&oacute;rmula 1</h2>
            <p class="tf-section__sub" id="evolution-sub">Progresi&oacute;n temporada a temporada.</p>
          </div>
          <div class="pilot-switch" role="group" aria-label="M&eacute;trica del gr&aacute;fico">
            <button type="button" class="pilot-switch__btn is-active" id="metric-points" aria-pressed="true">Puntos</button>
            <button type="button" class="pilot-switch__btn" id="metric-position" aria-pressed="false">Posici&oacute;n</button>
          </div>
        </div>

        <div class="pilot-chart" id="pilot-chart">
          <div class="tf-loading"><span class="tf-spinner" aria-hidden="true"></span><span>Cargando evoluci&oacute;n&hellip;</span></div>
        </div>
        <p class="pilot-note" id="evolution-note">
          Puntos oficiales del campeonato de cada temporada. Los sistemas de puntuaci&oacute;n han cambiado a lo largo de la
          historia de la F1, por lo que los valores de &eacute;pocas distintas no son comparables entre s&iacute;.
        </p>
      </section>

      <!-- ================= TRAYECTORIA ================= -->
      <section class="tf-section" aria-labelledby="stints-title">
        <div class="tf-section__head pilot-head">
          <div>
            <h2 class="tf-section__title" id="stints-title">Trayectoria por escuder&iacute;as</h2>
            <p class="tf-section__sub" id="stints-sub">Equipos con los que ha competido, en orden cronol&oacute;gico.</p>
          </div>
        </div>
        <div class="pilot-stints" id="pilot-stints">
          <div class="tf-loading"><span class="tf-spinner" aria-hidden="true"></span><span>Cargando trayectoria&hellip;</span></div>
        </div>
      </section>

      <!-- ================= RESULTADOS ================= -->
      <section class="tf-section" aria-labelledby="results-title">
        <div class="tf-section__head pilot-head">
          <div>
            <h2 class="tf-section__title" id="results-title">Resultados por temporada</h2>
            <p class="tf-section__sub" id="results-sub">Grandes Premios disputados, victorias, podios y puntos.</p>
          </div>
        </div>

        <div class="tf-table-wrap">
          <table class="tf-table pilot-table">
            <caption class="pilot-vh">Resultados del piloto por temporada</caption>
            <thead>
              <tr>
                <th scope="col" id="th-year" aria-sort="descending">
                  <button type="button" class="pilot-table__sort" id="sort-year" aria-label="Ordenar por temporada">
                    Temporada <span aria-hidden="true">&#8595;</span>
                  </button>
                </th>
                <th scope="col">Escuder&iacute;a</th>
                <th scope="col" class="tf-td-right">GP</th>
                <th scope="col" class="tf-td-right">Victorias</th>
                <th scope="col" class="tf-td-right">Podios</th>
                <th scope="col" class="tf-td-right">Puntos</th>
                <th scope="col" class="tf-td-right">Posici&oacute;n</th>
              </tr>
            </thead>
            <tbody id="pilot-results">
              <tr class="tf-table__empty"><td colspan="7">Cargando resultados&hellip;</td></tr>
            </tbody>
          </table>
        </div>

        <p class="pilot-note" id="results-note">
          GP, victorias y podios corresponden a carreras de Gran Premio (sin sprints). Puntos y posici&oacute;n son los
          oficiales del campeonato; un asterisco (*) indica puntos de carrera cuando la clasificaci&oacute;n oficial no
          estaba disponible. El guion (&mdash;) indica dato no disponible.
        </p>
      </section>

    </div>
  </main>
</body>

</html>
