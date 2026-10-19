<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Estad&iacute;sticas &mdash; TodoF1</title>
  <link rel="icon" href="/Images/favicon.ico?v=2" sizes="any">
  <link rel="icon" type="image/png" sizes="32x32" href="/Images/favicon-32x32.png?v=2">
  <link rel="icon" type="image/png" sizes="16x16" href="/Images/favicon-16x16.png?v=2">
  <link rel="stylesheet" href="css/todof1.css?v=<?= @filemtime(__DIR__ . '/css/todof1.css') ?: '1' ?>">
  <link rel="stylesheet" href="css/navbar.css?v=<?= @filemtime(__DIR__ . '/css/navbar.css') ?: '1' ?>">
  <link rel="stylesheet" href="css/PageEstadisticas.css?v=<?= @filemtime(__DIR__ . '/css/PageEstadisticas.css') ?: '1' ?>">
  <!-- Chart.js: misma dependencia que ya usa el proyecto en PageInfo -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="js/PageEstadisticas.js?v=<?= @filemtime(__DIR__ . '/js/PageEstadisticas.js') ?: '1' ?>" defer></script>
</head>

<body>
  <?php include __DIR__ . '/../api/navbar.php'; ?>

  <main class="tf-main">
    <div class="tf-container">

      <div class="tf-section__head">
        <div>
          <span class="tf-eyebrow">Temporada <span id="year-label">&mdash;</span></span>
          <h1 class="tf-section__title tf-page-title">Estad&iacute;sticas</h1>
          <p class="tf-section__sub">Dashboard de la temporada: resumen, evoluci&oacute;n del campeonato y comparador de pilotos.</p>
        </div>
        <a class="tf-back" id="btn-volver" href="/">P&aacute;gina principal</a>
      </div>

      <!-- ===================== PESTAÑAS ===================== -->
      <div class="tabs" role="tablist" aria-label="Secciones de estad&iacute;sticas">
        <button type="button" class="tabs__tab is-active" id="tab-resumen" role="tab"
                aria-controls="panel-resumen" aria-selected="true">Resumen</button>
        <button type="button" class="tabs__tab" id="tab-evolucion" role="tab"
                aria-controls="panel-evolucion" aria-selected="false" tabindex="-1">Evoluci&oacute;n</button>
        <button type="button" class="tabs__tab" id="tab-comparador" role="tab"
                aria-controls="panel-comparador" aria-selected="false" tabindex="-1">Comparador</button>
      </div>

      <!-- ===================== 1. RESUMEN ===================== -->
      <section class="tabs__panel" id="panel-resumen" role="tabpanel" aria-labelledby="tab-resumen" tabindex="0">

        <div class="tf-grid tf-grid--4" id="kpis" aria-live="polite">
          <div class="tf-loading" style="grid-column:1/-1;">
            <span class="tf-spinner"></span><span>Cargando resumen&hellip;</span>
          </div>
        </div>

        <div class="dash-grid">
          <div class="tf-panel dash-card">
            <div class="dash-card__head">
              <h2 class="dash-card__title">Puntos por piloto</h2>
              <span class="tf-mute dash-card__meta" id="meta-puntos"></span>
            </div>
            <div class="chart-box">
              <canvas id="chart-puntos" role="img" aria-label="Puntos por piloto en la temporada"></canvas>
            </div>
            <div class="chart-fallback" id="fb-puntos" hidden></div>
          </div>

          <div class="tf-panel dash-card">
            <div class="dash-card__head">
              <h2 class="dash-card__title">Victorias y podios</h2>
              <span class="tf-mute dash-card__meta" id="meta-victorias"></span>
            </div>
            <div class="chart-box">
              <canvas id="chart-victorias" role="img" aria-label="Victorias y podios por piloto"></canvas>
            </div>
            <div class="chart-fallback" id="fb-victorias" hidden></div>
          </div>
        </div>

        <section class="tf-section">
          <div class="tf-section__head">
            <div>
              <h2 class="tf-section__title">Campeonato de pilotos</h2>
              <p class="tf-section__sub">Pulsa en un piloto para ver su ficha completa.</p>
            </div>
            <span class="tf-badge" id="cuenta-pilotos"></span>
          </div>
          <div class="tf-table-wrap">
            <table class="tf-table tf-table--standings">
              <thead>
                <tr>
                  <th scope="col">Pos</th>
                  <th scope="col">Piloto</th>
                  <th scope="col" class="col-oculta-movil">Nacionalidad</th>
                  <th scope="col" class="col-oculta-movil">Escuder&iacute;a</th>
                  <th scope="col" class="tf-td-right">Victorias</th>
                  <th scope="col" class="tf-td-right">Puntos</th>
                </tr>
              </thead>
              <tbody id="standings-pilotos">
                <tr class="tf-table__empty"><td colspan="6">Cargando&hellip;</td></tr>
              </tbody>
            </table>
          </div>
        </section>

        <section class="tf-section">
          <div class="tf-section__head">
            <div>
              <h2 class="tf-section__title">Copa de constructores</h2>
              <p class="tf-section__sub">Pulsa en una escuder&iacute;a para ver su ficha completa.</p>
            </div>
            <span class="tf-badge" id="cuenta-escuderias"></span>
          </div>
          <div class="tf-table-wrap">
            <table class="tf-table tf-table--standings">
              <thead>
                <tr>
                  <th scope="col">Pos</th>
                  <th scope="col">Escuder&iacute;a</th>
                  <th scope="col" class="col-oculta-movil">Nacionalidad</th>
                  <th scope="col" class="tf-td-right">Victorias</th>
                  <th scope="col" class="tf-td-right">Puntos</th>
                </tr>
              </thead>
              <tbody id="standings-escuderias">
                <tr class="tf-table__empty"><td colspan="5">Cargando&hellip;</td></tr>
              </tbody>
            </table>
          </div>
        </section>

      </section>

      <!-- ===================== 2. EVOLUCIÓN ===================== -->
      <section class="tabs__panel" id="panel-evolucion" role="tabpanel" aria-labelledby="tab-evolucion" tabindex="0" hidden>

        <div class="tf-panel dash-card">
          <div class="dash-card__head">
            <div>
              <h2 class="dash-card__title">Evoluci&oacute;n de puntos</h2>
              <p class="dash-card__sub">Puntos oficiales del campeonato despu&eacute;s de cada Gran Premio.</p>
            </div>
            <span class="tf-badge" id="evol-badge"></span>
          </div>

          <div class="dash-toolbar">
            <span class="dash-toolbar__label" id="evol-etiqueta">Elige hasta 5 pilotos</span>
            <div class="chips" id="evol-pilotos" role="group" aria-labelledby="evol-etiqueta">
              <span class="tf-mute">Cargando&hellip;</span>
            </div>
          </div>

          <div class="chart-box chart-box--line">
            <canvas id="chart-evolucion" role="img" aria-label="Evoluci&oacute;n de puntos por Gran Premio"></canvas>
          </div>
          <div class="chart-fallback" id="fb-evolucion" hidden></div>
        </div>

      </section>

      <!-- ===================== 3. COMPARADOR ===================== -->
      <section class="tabs__panel" id="panel-comparador" role="tabpanel" aria-labelledby="tab-comparador" tabindex="0" hidden>

        <div class="tf-panel dash-card">
          <div class="dash-card__head">
            <div>
              <h2 class="dash-card__title">Comparador de pilotos</h2>
              <p class="dash-card__sub">Dos pilotos de la misma temporada, con sus datos oficiales.</p>
            </div>
            <span class="tf-badge" id="cmp-badge"></span>
          </div>

          <div class="cmp-selects">
            <label class="cmp-selects__item">
              <span class="cmp-selects__label">Piloto A</span>
              <select class="tf-select" id="cmp-a"></select>
            </label>
            <button type="button" class="tf-btn tf-btn--ghost cmp-selects__swap" id="cmp-swap"
                    title="Intercambiar pilotos" aria-label="Intercambiar pilotos">
              <span aria-hidden="true">&#8646;</span>
            </button>
            <label class="cmp-selects__item">
              <span class="cmp-selects__label">Piloto B</span>
              <select class="tf-select" id="cmp-b"></select>
            </label>
          </div>

          <div id="cmp-cuerpo" aria-live="polite">
            <div class="tf-loading"><span class="tf-spinner"></span><span>Cargando comparador&hellip;</span></div>
          </div>
        </div>

      </section>

    </div>
  </main>
</body>

</html>
