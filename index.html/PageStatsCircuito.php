<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Estad&iacute;sticas del Circuito &mdash; TodoF1</title>
  <link rel="stylesheet" href="css/todof1.css">
  <link rel="stylesheet" href="css/navbar.css">
  <link rel="stylesheet" href="css/PageStatsCircuito.css">
  <script src="js/PageStatsCirucito.js" defer></script>
</head>

<body>
  <?php include __DIR__ . '/../api/navbar.php'; ?>

  <main class="tf-main">
    <div class="tf-container">

      <a class="tf-back" id="btn-volver" href="/index.html/PageCircuitos.php">Volver a Circuitos</a>

      <section class="tf-profile tf-mt-4">
        <div class="tf-profile__media tf-profile__media--wide foto-block">
          <img id="circuito-foto" src="../Images/SinPerfil.jpg" alt="Imagen del circuito">
        </div>
        <div class="tf-profile__body">
          <span class="tf-eyebrow">Gran Premio &middot; Temporada <span id="year-label">—</span></span>
          <h1 class="tf-profile__name" id="titulo">Estad&iacute;sticas del Circuito</h1>
          <div class="tf-profile__meta" id="info-adicional"></div>
        </div>
      </section>

      <section class="tf-section">
        <div class="tf-panel" id="vuelta-rapida"></div>

        <div class="tf-section__head tf-mt-6">
          <div>
            <h2 class="tf-section__title">Clasificaci&oacute;n de la carrera</h2>
            <p class="tf-section__sub">Resultados oficiales del Gran Premio.</p>
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
