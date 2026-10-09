<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Estad&iacute;sticas &mdash; TodoF1</title>
  <link rel="stylesheet" href="css/todof1.css">
  <link rel="stylesheet" href="css/navbar.css">
  <link rel="stylesheet" href="css/PageEstadisticas.css">
  <script src="js/PageEstadisticas.js" defer></script>
</head>

<body>
  <?php include __DIR__ . '/../api/navbar.php'; ?>

  <main class="tf-main">
    <div class="tf-container">

      <div class="tf-section__head">
        <div>
          <span class="tf-eyebrow">Temporada <span id="year-label">—</span></span>
          <h1 class="tf-section__title tf-page-title">Estad&iacute;sticas</h1>
          <p class="tf-section__sub">Clasificaciones completas del campeonato de la temporada seleccionada.</p>
        </div>
        <a class="tf-back" id="btn-volver" href="/index.html/PagePrincipal.php">P&aacute;gina principal</a>
      </div>

      <div class="tf-grid tf-grid--4" id="kpis" aria-live="polite">
        <div class="tf-loading" style="grid-column:1/-1;">
          <span class="tf-spinner"></span><span>Cargando resumen&hellip;</span>
        </div>
      </div>

      <section class="tf-section">
        <div class="tf-section__head">
          <div>
            <h2 class="tf-section__title">Campeonato de pilotos</h2>
            <p class="tf-section__sub">Pulsa en un piloto para ver su ficha completa.</p>
          </div>
        </div>
        <div class="tf-table-wrap">
          <table class="tf-table">
            <thead>
              <tr>
                <th scope="col">Pos</th>
                <th scope="col">Piloto</th>
                <th scope="col">Nacionalidad</th>
                <th scope="col">Escuder&iacute;a</th>
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
        </div>
        <div class="tf-table-wrap">
          <table class="tf-table">
            <thead>
              <tr>
                <th scope="col">Pos</th>
                <th scope="col">Escuder&iacute;a</th>
                <th scope="col">Nacionalidad</th>
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

    </div>
  </main>
</body>

</html>
