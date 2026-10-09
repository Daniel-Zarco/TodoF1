<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Pilotos &mdash; TodoF1</title>
  <link rel="stylesheet" href="css/todof1.css">
  <link rel="stylesheet" href="css/navbar.css">
  <link rel="stylesheet" href="css/PagePilotos.css">
  <script src="js/PagePilotos.js" defer></script>
</head>

<body>
  <?php include __DIR__ . '/../api/navbar.php'; ?>

  <main class="tf-main">
    <div class="tf-container">

      <div class="tf-section__head">
        <div>
          <span class="tf-eyebrow">Temporada <span id="year-label">—</span></span>
          <h1 class="tf-section__title tf-page-title">Pilotos</h1>
          <p class="tf-section__sub">Clasificaci&oacute;n y plantilla de pilotos de la temporada seleccionada.</p>
        </div>
        <a class="tf-back" id="btn-volver" href="/index.html/PagePrincipal.php">P&aacute;gina principal</a>
      </div>

      <div class="tf-toolbar">
        <div class="tf-toolbar__group">
          <div class="tf-input-icon tf-search">
            <input type="search" id="search" class="tf-input" placeholder="Buscar piloto&hellip;"
                   autocomplete="off" aria-label="Buscar piloto por nombre">
          </div>
          <select id="filter-team" class="tf-select" aria-label="Filtrar por escuder&iacute;a">
            <option value="">Todas las escuder&iacute;as</option>
          </select>
        </div>
        <span class="tf-badge" id="count-badge">Cargando&hellip;</span>
      </div>

      <div class="tf-table-wrap">
        <table class="tf-table">
          <thead>
            <tr>
              <th scope="col">Pos</th>
              <th scope="col">Piloto</th>
              <th scope="col">Nacionalidad</th>
              <th scope="col">Escuder&iacute;a</th>
              <th scope="col" class="tf-td-right">Puntos</th>
            </tr>
          </thead>
          <tbody id="pilotos-tbody">
            <tr class="tf-table__empty">
              <td colspan="5">Cargando pilotos&hellip;</td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>
  </main>
</body>

</html>
