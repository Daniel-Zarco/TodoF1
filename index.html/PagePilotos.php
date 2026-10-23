<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Pilotos &mdash; TodoF1</title>
  <link rel="icon" href="/Images/favicon.ico?v=2" sizes="any">
  <link rel="icon" type="image/png" sizes="32x32" href="/Images/favicon-32x32.png?v=2">
  <link rel="icon" type="image/png" sizes="16x16" href="/Images/favicon-16x16.png?v=2">
  <script src="js/teams.js?v=<?= @filemtime(__DIR__ . '/js/teams.js') ?: '1' ?>"></script>
  <link rel="stylesheet" href="css/todof1.css?v=<?= @filemtime(__DIR__ . '/css/todof1.css') ?: '1' ?>">
  <link rel="stylesheet" href="css/navbar.css?v=<?= @filemtime(__DIR__ . '/css/navbar.css') ?: '1' ?>">
  <link rel="stylesheet" href="css/PagePilotos.css?v=<?= @filemtime(__DIR__ . '/css/PagePilotos.css') ?: '1' ?>">
  <link rel="stylesheet" href="css/mobile-filters.css?v=<?= @filemtime(__DIR__ . '/css/mobile-filters.css') ?: '1' ?>">
  <script src="js/PagePilotos.js?v=<?= @filemtime(__DIR__ . '/js/PagePilotos.js') ?: '1' ?>" defer></script>
  <script src="js/mobile-filters.js?v=<?= @filemtime(__DIR__ . '/js/mobile-filters.js') ?: '1' ?>" defer></script>
</head>

<body>
  <?php include __DIR__ . '/../api/navbar.php'; ?>

  <main class="tf-main">
    <div class="tf-container">

      <div class="tf-section__head">
        <div>
          <span class="tf-eyebrow">Temporada <span id="year-label">&mdash;</span></span>
          <h1 class="tf-section__title tf-page-title">Pilotos</h1>
          <p class="tf-section__sub" id="season-sub">Plantilla y clasificaci&oacute;n de pilotos de la temporada seleccionada.</p>
        </div>
        <a class="tf-back" id="btn-volver" href="/">P&aacute;gina principal</a>
      </div>

      <!-- Buscador, filtro por escuder&iacute;a y ordenaci&oacute;n de la temporada activa -->
      <section class="pilots-controls" aria-label="Buscar, filtrar y ordenar pilotos">
        <button type="button" class="tf-filters-toggle" aria-expanded="false" aria-controls="pilots-filters" hidden>
          <span class="tf-filters-toggle__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" focusable="false"><path d="M3 5h18l-7 8v5l-4 2v-7L3 5Z"></path></svg>
          </span>
          <span class="tf-filters-toggle__label">Buscar y filtrar</span>
          <span class="tf-filters-toggle__dot" aria-hidden="true" hidden></span>
          <span class="tf-filters-toggle__chev" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false"><polyline points="6 9 12 15 18 9"></polyline></svg>
          </span>
        </button>
        <div class="tf-filters-body" id="pilots-filters">
        <div class="pilots-controls__search tf-input-icon">
          <input type="search" id="search" class="tf-input" placeholder="Buscar piloto&hellip;"
                 autocomplete="off" spellcheck="false" aria-label="Buscar piloto por nombre o apellidos">
          <button type="button" class="pilots-controls__clear" id="search-clear" hidden
                  aria-label="Limpiar b&uacute;squeda">&times;</button>
        </div>

        <div class="pilots-controls__fields">
          <label class="pilots-controls__field">
            <span class="pilots-controls__label">Escuder&iacute;a</span>
            <select id="filter-team" class="tf-select" aria-label="Filtrar por escuder&iacute;a">
              <option value="">Todas las escuder&iacute;as</option>
            </select>
          </label>

          <label class="pilots-controls__field">
            <span class="pilots-controls__label">Ordenar por</span>
            <select id="sort-by" class="tf-select" aria-label="Ordenar pilotos">
              <option value="championship">Clasificaci&oacute;n del campeonato</option>
              <option value="name-asc">Apellido (A&ndash;Z)</option>
              <option value="name-desc">Apellido (Z&ndash;A)</option>
              <option value="number-asc">Dorsal (menor a mayor)</option>
              <option value="number-desc">Dorsal (mayor a menor)</option>
            </select>
          </label>
        </div>
        </div>

        <p class="pilots-controls__count" id="count-badge" role="status" aria-live="polite">Cargando&hellip;</p>
      </section>

      <!-- Cuadr&iacute;cula de fichas -->
      <div class="pilots-grid" id="pilots-grid">
        <div class="tf-loading"><span class="tf-spinner" aria-hidden="true"></span><span>Cargando pilotos&hellip;</span></div>
      </div>

    </div>
  </main>
</body>

</html>
