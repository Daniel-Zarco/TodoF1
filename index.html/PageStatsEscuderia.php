<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Estad&iacute;sticas de la Escuder&iacute;a &mdash; TodoF1</title>
  <link rel="stylesheet" href="css/todof1.css">
  <link rel="stylesheet" href="css/navbar.css">
  <link rel="stylesheet" href="css/PageStatsEscuderia.css">
  <script src="js/PageStatsEscuderia.js" defer></script>
</head>

<body>
  <?php include __DIR__ . '/../api/navbar.php'; ?>

  <main class="tf-main">
    <div class="tf-container">

      <a class="tf-back" id="btn-volver" href="/index.html/PageEscuderias.php">Volver a Escuder&iacute;as</a>

      <section class="tf-profile tf-mt-4" id="team-profile">
        <div class="tf-profile__media tf-profile__media--team">
          <span id="team-initial">F1</span>
        </div>
        <div class="tf-profile__body">
          <span class="tf-eyebrow">Escuder&iacute;a &middot; Temporada <span id="year-label">—</span></span>
          <h1 class="tf-profile__name" id="titulo">Estad&iacute;sticas de la Escuder&iacute;a</h1>
          <div class="tf-profile__meta">
            <span class="tf-badge" id="nacionalidad">Cargando&hellip;</span>
          </div>
        </div>
      </section>

      <section class="tf-section">
        <div class="tf-section__head">
          <div>
            <h2 class="tf-section__title">Rendimiento en la temporada</h2>
            <p class="tf-section__sub">M&eacute;tricas del equipo en el campeonato seleccionado.</p>
          </div>
        </div>

        <div class="tf-grid tf-grid--4" id="stats-container" aria-live="polite">
          <div class="tf-loading" style="grid-column:1/-1;">
            <span class="tf-spinner"></span><span>Cargando estad&iacute;sticas&hellip;</span>
          </div>
        </div>

        <div class="tf-panel tf-mt-5">
          <span class="tf-kpi__label">Pilotos ese a&ntilde;o</span>
          <div class="tf-pilot-teams" id="pilotos-list">Cargando&hellip;</div>
        </div>
      </section>

    </div>
  </main>
</body>

</html>
