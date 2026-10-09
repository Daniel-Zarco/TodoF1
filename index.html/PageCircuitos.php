<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Circuitos &mdash; TodoF1</title>
  <link rel="stylesheet" href="css/todof1.css">
  <link rel="stylesheet" href="css/navbar.css">
  <link rel="stylesheet" href="css/PageCircuitos.css">
  <script src="js/PageCircuitos.js" defer></script>
</head>

<body>
  <?php include __DIR__ . '/../api/navbar.php'; ?>

  <main class="tf-main">
    <div class="tf-container">

      <div class="tf-section__head">
        <div>
          <span class="tf-eyebrow">Temporada <span id="year-label">—</span></span>
          <h1 class="tf-section__title tf-page-title">Circuitos</h1>
          <p class="tf-section__sub">Calendario de Grandes Premios de la temporada seleccionada.</p>
        </div>
        <a class="tf-back" id="btn-volver" href="/index.html/PagePrincipal.php">P&aacute;gina principal</a>
      </div>

      <div class="tf-toolbar">
        <div class="tf-toolbar__group">
          <div class="tf-input-icon tf-search">
            <input type="search" id="search" class="tf-input" placeholder="Buscar circuito o pa&iacute;s&hellip;"
                   autocomplete="off" aria-label="Buscar circuito">
          </div>
        </div>
        <span class="tf-badge" id="count-badge">Cargando&hellip;</span>
      </div>

      <div class="tf-grid tf-grid--cards" id="circuitos-container" aria-live="polite">
        <div class="tf-loading" style="grid-column:1/-1;">
          <span class="tf-spinner"></span><span>Cargando circuitos&hellip;</span>
        </div>
      </div>

    </div>
  </main>
</body>

</html>
