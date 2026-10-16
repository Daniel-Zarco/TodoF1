<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Escuder&iacute;as &mdash; TodoF1</title>
  <link rel="stylesheet" href="css/todof1.css?v=<?= @filemtime(__DIR__ . '/css/todof1.css') ?: '1' ?>">
  <link rel="stylesheet" href="css/navbar.css?v=<?= @filemtime(__DIR__ . '/css/navbar.css') ?: '1' ?>">
  <link rel="stylesheet" href="css/PageEscuderias.css?v=<?= @filemtime(__DIR__ . '/css/PageEscuderias.css') ?: '1' ?>">
  <script src="js/PageEscuderias.js?v=<?= @filemtime(__DIR__ . '/js/PageEscuderias.js') ?: '1' ?>" defer></script>
</head>

<body>
  <?php include __DIR__ . '/../api/navbar.php'; ?>

  <main class="tf-main">
    <div class="tf-container">

      <div class="tf-section__head">
        <div>
          <span class="tf-eyebrow">Temporada <span id="year-label">—</span></span>
          <h1 class="tf-section__title tf-page-title">Escuder&iacute;as</h1>
          <p class="tf-section__sub">Constructores, nacionalidad y puntos de la temporada seleccionada.</p>
        </div>
        <a class="tf-back" id="btn-volver" href="/">P&aacute;gina principal</a>
      </div>

      <div class="tf-toolbar">
        <div class="tf-toolbar__group">
          <div class="tf-input-icon tf-search">
            <input type="search" id="search" class="tf-input" placeholder="Buscar escuder&iacute;a&hellip;"
                   autocomplete="off" aria-label="Buscar escuder&iacute;a por nombre">
          </div>
        </div>
        <span class="tf-badge" id="count-badge">Cargando&hellip;</span>
      </div>

      <div class="tf-grid tf-grid--cards" id="escuderias-container" aria-live="polite">
        <div class="tf-loading" style="grid-column:1/-1;">
          <span class="tf-spinner"></span><span>Cargando escuder&iacute;as&hellip;</span>
        </div>
      </div>

    </div>
  </main>
</body>

</html>
