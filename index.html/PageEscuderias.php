<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Escuder&iacute;as &mdash; TodoF1</title>
  <link rel="icon" href="/Images/favicon.ico?v=2" sizes="any">
  <link rel="icon" type="image/png" sizes="32x32" href="/Images/favicon-32x32.png?v=2">
  <link rel="icon" type="image/png" sizes="16x16" href="/Images/favicon-16x16.png?v=2">
  <script src="js/teams.js?v=<?= @filemtime(__DIR__ . '/js/teams.js') ?: '1' ?>"></script>
  <link rel="stylesheet" href="css/todof1.css?v=<?= @filemtime(__DIR__ . '/css/todof1.css') ?: '1' ?>">
  <link rel="stylesheet" href="css/navbar.css?v=<?= @filemtime(__DIR__ . '/css/navbar.css') ?: '1' ?>">
  <link rel="stylesheet" href="css/PageEscuderias.css?v=<?= @filemtime(__DIR__ . '/css/PageEscuderias.css') ?: '1' ?>">
  <link rel="stylesheet" href="css/mobile-filters.css?v=<?= @filemtime(__DIR__ . '/css/mobile-filters.css') ?: '1' ?>">
  <script src="js/escuderia-logos.js?v=<?= @filemtime(__DIR__ . '/js/escuderia-logos.js') ?: '1' ?>" defer></script>
  <script src="js/car-photos.js?v=<?= @filemtime(__DIR__ . '/js/car-photos.js') ?: '1' ?>" defer></script>
  <script src="js/PageEscuderias.js?v=<?= @filemtime(__DIR__ . '/js/PageEscuderias.js') ?: '1' ?>" defer></script>
  <script src="js/mobile-filters.js?v=<?= @filemtime(__DIR__ . '/js/mobile-filters.js') ?: '1' ?>" defer></script>
  <?php
  /* Versiones (fecha de modificación) de las fotografías, para que el caché del
     navegador se refresque igual que en la página principal. El mapa lo consume
     teams.js (versionedSrc) desde PageEscuderias.js. Incluye las imágenes del
     hero por escudería y el catálogo de monoplazas por temporada. */
  $tf_hero_versions = [];
  $tf_foto_dirs = ['/Images/heroes/escuderias' => __DIR__ . '/../Images/heroes/escuderias/*'];
  $tf_monoplazas = glob(__DIR__ . '/../Images/monoplazas/*', GLOB_ONLYDIR);
  if (is_array($tf_monoplazas)) {
    foreach ($tf_monoplazas as $tf_dir) {
      $tf_foto_dirs['/Images/monoplazas/' . basename($tf_dir)] = $tf_dir . '/*';
    }
  }
  foreach ($tf_foto_dirs as $tf_web_dir => $tf_glob) {
    $tf_files = glob($tf_glob);
    if (!is_array($tf_files)) {
      continue;
    }
    foreach ($tf_files as $tf_file) {
      if (!is_file($tf_file)) {
        continue;
      }
      $tf_hero_versions[$tf_web_dir . '/' . basename($tf_file)] = (string) @filemtime($tf_file);
    }
  }
  ?>
  <script>window.TodoF1HeroVersions = <?= json_encode($tf_hero_versions) ?>;</script>
</head>

<body>
  <?php include __DIR__ . '/../api/navbar.php'; ?>

  <main class="tf-main">
    <div class="tf-container">

      <div class="tf-section__head">
        <div>
          <span class="tf-eyebrow">Temporada <span id="year-label">&mdash;</span></span>
          <h1 class="tf-section__title tf-page-title">Escuder&iacute;as</h1>
          <p class="tf-section__sub" id="season-sub">Constructores, pilotos y estad&iacute;sticas de la temporada seleccionada.</p>
        </div>
        <a class="tf-back" id="btn-volver" href="/">P&aacute;gina principal</a>
      </div>

      <!-- Buscador y ordenaci&oacute;n de la temporada activa -->
      <section class="teams-controls" aria-label="Buscar y ordenar escuder&iacute;as">
        <button type="button" class="tf-filters-toggle" aria-expanded="false" aria-controls="teams-filters" hidden>
          <span class="tf-filters-toggle__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" focusable="false"><path d="M3 5h18l-7 8v5l-4 2v-7L3 5Z"></path></svg>
          </span>
          <span class="tf-filters-toggle__label">Buscar y filtrar</span>
          <span class="tf-filters-toggle__dot" aria-hidden="true" hidden></span>
          <span class="tf-filters-toggle__chev" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false"><polyline points="6 9 12 15 18 9"></polyline></svg>
          </span>
        </button>
        <div class="tf-filters-body" id="teams-filters">
        <div class="teams-controls__search tf-input-icon">
          <input type="search" id="search" class="tf-input" placeholder="Buscar escuder&iacute;a&hellip;"
                 autocomplete="off" spellcheck="false" aria-label="Buscar escuder&iacute;a por nombre">
          <button type="button" class="teams-controls__clear" id="search-clear" hidden
                  aria-label="Limpiar b&uacute;squeda">&times;</button>
        </div>

        <div class="teams-controls__fields">
          <label class="teams-controls__field">
            <span class="teams-controls__label">Ordenar por</span>
            <select id="sort-by" class="tf-select" aria-label="Ordenar escuder&iacute;as">
              <option value="championship">Clasificaci&oacute;n del campeonato</option>
              <option value="name-asc">Nombre (A&ndash;Z)</option>
              <option value="name-desc">Nombre (Z&ndash;A)</option>
              <option value="wins-desc">M&aacute;s victorias</option>
              <option value="points-desc">M&aacute;s puntos</option>
            </select>
          </label>
        </div>
        </div>

        <p class="teams-controls__count" id="count-badge" role="status" aria-live="polite">Cargando&hellip;</p>
      </section>

      <p class="teams-note" id="season-note" hidden></p>

      <!-- Cuadr&iacute;cula de tarjetas: dos columnas en escritorio -->
      <div class="teams-grid" id="teams-grid">
        <div class="tf-loading"><span class="tf-spinner" aria-hidden="true"></span><span>Cargando escuder&iacute;as&hellip;</span></div>
      </div>

      <!-- Cr&eacute;ditos de autor&iacute;a de las fotograf&iacute;as de esa temporada -->
      <p class="teams-note teams-note--soft teams-credits" id="car-photo-credits" hidden></p>

    </div>
  </main>
</body>

</html>
