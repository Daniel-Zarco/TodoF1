<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Circuitos &mdash; TodoF1</title>
  <link rel="icon" href="/Images/favicon.ico?v=2" sizes="any">
  <link rel="icon" type="image/png" sizes="32x32" href="/Images/favicon-32x32.png?v=2">
  <link rel="icon" type="image/png" sizes="16x16" href="/Images/favicon-16x16.png?v=2">
  <link rel="stylesheet" href="css/todof1.css?v=<?= @filemtime(__DIR__ . '/css/todof1.css') ?: '1' ?>">
  <link rel="stylesheet" href="css/navbar.css?v=<?= @filemtime(__DIR__ . '/css/navbar.css') ?: '1' ?>">
  <link rel="stylesheet" href="css/PageCircuitos.css?v=<?= @filemtime(__DIR__ . '/css/PageCircuitos.css') ?: '1' ?>">
  <script src="js/CircuitosTrazados.js?v=<?= @filemtime(__DIR__ . '/js/CircuitosTrazados.js') ?: '1' ?>" defer></script>
  <script src="js/f1-horarios.js?v=<?= @filemtime(__DIR__ . '/js/f1-horarios.js') ?: '1' ?>" defer></script>
  <script src="js/PageCircuitos.js?v=<?= @filemtime(__DIR__ . '/js/PageCircuitos.js') ?: '1' ?>" defer></script>
</head>

<body>
  <?php include __DIR__ . '/../api/navbar.php'; ?>

  <main class="tf-main">
    <div class="tf-container">

      <div class="tf-section__head">
        <div>
          <span class="tf-eyebrow">Temporada <span id="year-label">&mdash;</span></span>
          <h1 class="tf-section__title tf-page-title">Circuitos</h1>
          <p class="tf-section__sub" id="season-sub">Trazados y Grandes Premios de la temporada seleccionada.</p>
        </div>
        <a class="tf-back" id="btn-volver" href="/">P&aacute;gina principal</a>
      </div>

      <!-- Buscador, filtro por pa&iacute;s y ordenaci&oacute;n -->
      <section class="circuitos-controls" aria-label="Buscar, filtrar y ordenar circuitos">
        <div class="circuitos-controls__search tf-input-icon">
          <input type="search" id="search" class="tf-input" placeholder="Buscar circuito, pa&iacute;s o ciudad&hellip;"
                 autocomplete="off" spellcheck="false" aria-label="Buscar circuito por nombre, pa&iacute;s o ciudad">
          <button type="button" class="circuitos-controls__clear" id="search-clear" hidden
                  aria-label="Limpiar b&uacute;squeda">&times;</button>
        </div>

        <div class="circuitos-controls__fields">
          <label class="circuitos-controls__field">
            <span class="circuitos-controls__label">Temporada</span>
            <select id="filter-year" class="tf-select" aria-label="Cambiar de temporada"></select>
          </label>

          <label class="circuitos-controls__field">
            <span class="circuitos-controls__label">Pa&iacute;s</span>
            <select id="filter-country" class="tf-select" aria-label="Filtrar por pa&iacute;s">
              <option value="">Todos los pa&iacute;ses</option>
            </select>
          </label>

          <label class="circuitos-controls__field">
            <span class="circuitos-controls__label">Ordenar por</span>
            <select id="sort-by" class="tf-select" aria-label="Ordenar circuitos">
              <option value="round">Ronda del calendario</option>
              <option value="name-asc">Nombre (A&ndash;Z)</option>
              <option value="name-desc">Nombre (Z&ndash;A)</option>
              <option value="country">Pa&iacute;s (A&ndash;Z)</option>
              <option value="gp-desc">M&aacute;s Grandes Premios</option>
            </select>
          </label>
        </div>

        <p class="circuitos-controls__count" id="count-badge" role="status" aria-live="polite">Cargando&hellip;</p>
      </section>

      <p class="circuitos-note" id="season-note" hidden></p>

      <!-- Cuadr&iacute;cula: dos columnas en escritorio, una en m&oacute;vil -->
      <div class="circuitos-grid" id="circuitos-grid">
        <div class="tf-loading"><span class="tf-spinner" aria-hidden="true"></span><span>Cargando circuitos&hellip;</span></div>
      </div>

      <!-- Procedencia de los trazados (licencias libres) -->
      <p class="circuitos-note circuitos-note--soft" id="trazados-note" hidden></p>

    </div>
  </main>
</body>

</html>
