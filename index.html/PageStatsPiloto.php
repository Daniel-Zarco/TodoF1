<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Estad&iacute;sticas del Piloto &mdash; TodoF1</title>
  <link rel="stylesheet" href="css/todof1.css">
  <link rel="stylesheet" href="css/navbar.css">
  <link rel="stylesheet" href="css/PageStatsPiloto.css">
  <script src="js/PageStatsPiloto.js" defer></script>
</head>

<body>
  <?php include __DIR__ . '/../api/navbar.php'; ?>

  <main class="tf-main">
    <div class="tf-container">

      <a class="tf-back" id="btn-volver" href="/index.html/PagePilotos.php">Volver a Pilotos</a>

      <section class="tf-profile tf-mt-4">
        <div class="tf-profile__media">
          <img id="piloto-foto" src="../Images/SinPerfil.jpg" alt="Foto del piloto">
        </div>
        <div class="tf-profile__body">
          <span class="tf-eyebrow">Piloto de F&oacute;rmula 1</span>
          <h1 class="tf-profile__name" id="titulo">Estad&iacute;sticas del Piloto</h1>
          <div class="tf-profile__meta">
            <span class="tf-badge" id="nacionalidad">Cargando&hellip;</span>
            <span class="tf-badge tf-badge--red" id="perfil-temporadas">Cargando&hellip;</span>
          </div>
        </div>
        <a id="wiki" href="#" target="_blank" rel="noopener" class="tf-btn tf-btn--ghost tf-profile__action">Ver en Wikipedia</a>
      </section>

      <section class="tf-section">
        <div class="tf-section__head">
          <div>
            <h2 class="tf-section__title">Trayectoria en F1</h2>
            <p class="tf-section__sub">Resumen de la carrera del piloto con datos hist&oacute;ricos.</p>
          </div>
        </div>

        <div class="tf-grid tf-grid--3">
          <div class="tf-kpi">
            <span class="tf-kpi__label">Edad</span>
            <div class="tf-kpi__value tf-num" id="edad">Cargando&hellip;</div>
            <div class="tf-kpi__meta">Calculada desde la fecha de nacimiento</div>
          </div>
          <div class="tf-kpi">
            <span class="tf-kpi__label">Temporadas</span>
            <div class="tf-kpi__value tf-num" id="temporadas">Cargando&hellip;</div>
            <div class="tf-kpi__meta">A&ntilde;os en el campeonato</div>
          </div>
          <div class="tf-kpi">
            <span class="tf-kpi__label">Carreras ganadas</span>
            <div class="tf-kpi__value tf-num" id="victorias">Cargando&hellip;</div>
            <div class="tf-kpi__meta">Primeros puestos</div>
          </div>
          <div class="tf-kpi">
            <span class="tf-kpi__label">Podios</span>
            <div class="tf-kpi__value tf-num" id="podios">Cargando&hellip;</div>
            <div class="tf-kpi__meta">Top 3 en carrera</div>
          </div>
          <div class="tf-kpi">
            <span class="tf-kpi__label">Mejor resultado</span>
            <div class="tf-kpi__value tf-num" id="mejor-resultado">Cargando&hellip;</div>
            <div class="tf-kpi__meta">Mejor posici&oacute;n en carrera</div>
          </div>
          <div class="tf-kpi">
            <span class="tf-kpi__label">Debut</span>
            <div class="tf-kpi__value tf-num" id="inicio">Cargando&hellip;</div>
            <div class="tf-kpi__meta">Primera temporada</div>
          </div>
        </div>

        <div class="tf-panel tf-mt-5">
          <span class="tf-kpi__label">Escuder&iacute;as</span>
          <div class="tf-pilot-teams" id="escuderias">Cargando&hellip;</div>
        </div>
      </section>

    </div>
  </main>
</body>

</html>
