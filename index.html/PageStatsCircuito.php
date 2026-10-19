<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Circuito &mdash; TodoF1</title>
  <link rel="icon" href="/Images/favicon.ico" sizes="any">
  <link rel="icon" type="image/png" sizes="32x32" href="/Images/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/Images/favicon-16x16.png">
  <link rel="stylesheet" href="css/todof1.css?v=<?= @filemtime(__DIR__ . '/css/todof1.css') ?: '1' ?>">
  <link rel="stylesheet" href="css/navbar.css?v=<?= @filemtime(__DIR__ . '/css/navbar.css') ?: '1' ?>">
  <link rel="stylesheet" href="css/PageStatsCircuito.css?v=<?= @filemtime(__DIR__ . '/css/PageStatsCircuito.css') ?: '1' ?>">
  <script src="js/CircuitosTrazados.js?v=<?= @filemtime(__DIR__ . '/js/CircuitosTrazados.js') ?: '1' ?>" defer></script>
  <script src="js/f1-horarios.js?v=<?= @filemtime(__DIR__ . '/js/f1-horarios.js') ?: '1' ?>" defer></script>
  <script src="js/PageStatsCirucito.js?v=<?= @filemtime(__DIR__ . '/js/PageStatsCirucito.js') ?: '1' ?>" defer></script>
</head>

<body>
  <?php include __DIR__ . '/../api/navbar.php'; ?>

  <main class="tf-main">
    <div class="tf-container">

      <a class="tf-back" id="btn-volver" href="/index.html/PageCircuitos.php">Volver a Circuitos</a>

      <!-- Trazado protagonista + informaci&oacute;n del circuito -->
      <section class="circuito-hero tf-mt-4">
        <div class="circuito-hero__media">
          <span class="circuito-hero__trazo" id="circuito-trazo" aria-hidden="true"></span>
          <div class="circuito-hero__fallback" id="circuito-fallback" hidden>
            <strong id="circuito-monograma">&mdash;</strong>
            <small>Trazado no disponible</small>
          </div>
          <span class="circuito-hero__cargando" id="circuito-cargando">Cargando trazado&hellip;</span>
        </div>

        <div class="circuito-hero__info">
          <span class="tf-eyebrow">Circuito &middot; Temporada <span id="year-label">&mdash;</span></span>
          <h1 class="tf-profile__name" id="titulo">Circuito</h1>
          <div class="tf-profile__meta" id="info-adicional"></div>
          <p class="circuito-hero__nota" id="trazo-nota" hidden></p>
          <p class="circuito-hero__nota circuito-hero__nota--soft" id="trazo-aviso" hidden></p>
        </div>
      </section>

      <!-- Programa del fin de semana (solo en la temporada en curso) -->
      <section class="tf-section tf-mt-6" id="seccion-horarios" hidden>
        <div class="tf-section__head">
          <div>
            <h2 class="tf-section__title">Programa del fin de semana</h2>
            <p class="tf-section__sub">Horarios en hora peninsular espa&ntilde;ola (Europe/Madrid).</p>
          </div>
          <span class="tf-badge" id="horarios-resumen"></span>
        </div>

        <div class="horarios-proxima" id="horarios-proxima" hidden>
          <span class="horarios-proxima__etiqueta" id="horarios-proxima-etiqueta">Pr&oacute;xima sesi&oacute;n</span>
          <span class="horarios-proxima__nombre" id="horarios-proxima-nombre">&mdash;</span>
          <span class="horarios-proxima__cuando" id="horarios-proxima-cuando">&mdash;</span>
          <span class="horarios-proxima__cuenta" id="horarios-proxima-cuenta"></span>
        </div>

        <div class="horarios" id="horarios"></div>
        <p class="horarios__nota" id="horarios-nota"></p>
      </section>

      <!-- Estad&iacute;sticas hist&oacute;ricas -->
      <section class="tf-section tf-mt-6" id="seccion-kpis">
        <div class="tf-section__head">
          <div>
            <h2 class="tf-section__title">Historial en F&oacute;rmula 1</h2>
            <p class="tf-section__sub">Todas las ediciones del Gran Premio en este circuito.</p>
          </div>
        </div>
        <div class="circuito-kpis" id="kpis">
          <div class="tf-skeleton" style="height:92px;"></div>
          <div class="tf-skeleton" style="height:92px;"></div>
          <div class="tf-skeleton" style="height:92px;"></div>
          <div class="tf-skeleton" style="height:92px;"></div>
        </div>
      </section>

      <!-- Ganadores por temporada -->
      <section class="tf-section tf-mt-6">
        <div class="tf-section__head">
          <div>
            <h2 class="tf-section__title">Ganadores por temporada</h2>
            <p class="tf-section__sub">Vencedor de cada edici&oacute;n disputada en este trazado.</p>
          </div>
          <span class="tf-badge" id="ganadores-cuenta"></span>
        </div>

        <div class="tf-table-wrap">
          <table class="tf-table" id="tabla-ganadores">
            <thead>
              <tr>
                <th scope="col">A&ntilde;o</th>
                <th scope="col">Gran Premio</th>
                <th scope="col">Fecha</th>
                <th scope="col">Ganador</th>
                <th scope="col">Escuder&iacute;a</th>
              </tr>
            </thead>
            <tbody>
              <tr class="tf-table__empty">
                <td colspan="5">Cargando historial&hellip;</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <!-- Clasificaci&oacute;n de la edici&oacute;n de la temporada seleccionada -->
      <section class="tf-section tf-mt-6">
        <div class="tf-panel" id="vuelta-rapida"></div>

        <div class="tf-section__head tf-mt-6">
          <div>
            <h2 class="tf-section__title">Clasificaci&oacute;n de la carrera</h2>
            <p class="tf-section__sub">Resultados oficiales del Gran Premio de <span id="race-year">&mdash;</span>.</p>
          </div>
        </div>

        <div class="tf-table-wrap">
          <table class="tf-table" id="tabla-resultados">
            <thead>
              <tr>
                <th scope="col">Pos</th>
                <th scope="col">Piloto</th>
                <th scope="col">Escuder&iacute;a</th>
                <th scope="col" class="tf-td-right">Vueltas</th>
                <th scope="col" class="tf-td-right">Tiempo final</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </section>

    </div>
  </main>
</body>

</html>
