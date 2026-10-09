/* TodoF1 — Estadísticas de la escudería */
(function () {
  'use strict';

  var API = 'https://api.jolpi.ca/ergast/f1';
  var params = new URLSearchParams(window.location.search);
  var escuderia = params.get('escuderia') || '';
  var year = params.get('year') || '2026';

  var container = document.getElementById('stats-container');
  var pilotosList = document.getElementById('pilotos-list');
  var titulo = document.getElementById('titulo');
  var nacionalidadElem = document.getElementById('nacionalidad');
  var teamInitial = document.getElementById('team-initial');
  var btnVolver = document.getElementById('btn-volver');

  titulo.textContent = escuderia ? 'Estadísticas de ' + escuderia : 'Estadísticas de la Escudería';
  document.getElementById('year-label').textContent = year;
  btnVolver.href = '/index.html/PageEscuderias.php?year=' + encodeURIComponent(year);
  teamInitial.textContent = (escuderia.match(/[A-Za-z0-9]/g) || ['F']).slice(0, 2).join('').toUpperCase();

  function esc(value) {
    return String(value == null ? '' : value).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  function kpi(label, value, meta) {
    var block = document.createElement('div');
    block.className = 'tf-kpi';
    block.innerHTML =
      '<span class="tf-kpi__label">' + esc(label) + '</span>' +
      '<div class="tf-kpi__value tf-num">' + esc(value) + '</div>' +
      (meta ? '<div class="tf-kpi__meta">' + esc(meta) + '</div>' : '');
    return block;
  }

  // Jolpica capa "limit" a 100: hay que paginar con offset para obtener toda la temporada.
  async function fetchAllRaces(baseUrl) {
    var limit = 100;
    var offset = 0;
    var total = Infinity;
    var all = [];

    while (offset < total) {
      var sep = baseUrl.indexOf('?') === -1 ? '?' : '&';
      var data = await fetch(baseUrl + sep + 'limit=' + limit + '&offset=' + offset)
        .then(function (res) { return res.json(); });
      var meta = data.MRData || {};

      total = parseInt(meta.total, 10);
      if (isNaN(total)) total = 0;

      var page = (meta.RaceTable && meta.RaceTable.Races) || [];
      if (!page.length) break;

      all = all.concat(page);
      offset += limit;
    }

    return all;
  }

  Promise.all([
    fetch(API + '/' + year + '.json').then(function (res) { return res.json(); }),
    fetchAllRaces(API + '/' + year + '/results.json')
  ])
    .then(function (responses) {
      var temporadaData = responses[0];
      var races = responses[1] || [];
      var totalCarrerasTemporada = ((temporadaData.MRData && temporadaData.MRData.RaceTable && temporadaData.MRData.RaceTable.Races) || []).length;

      var resultadosEscuderia = [];
      races.forEach(function (race) {
        (race.Results || []).forEach(function (result) {
          if (result.Constructor.name.toLowerCase() === escuderia.toLowerCase()) {
            resultadosEscuderia.push({ race: race, result: result });
          }
        });
      });

      if (!resultadosEscuderia.length) {
        container.innerHTML = '<div class="tf-empty" style="grid-column:1/-1;">No hay información disponible para la escudería "' + esc(escuderia) + '" en el año ' + esc(year) + '.</div>';
        pilotosList.innerHTML = '<span class="tf-dim">Sin datos.</span>';
        nacionalidadElem.textContent = 'N/D';
        return;
      }

      var victorias = 0;
      var podios = 0;
      var nacionalidad = resultadosEscuderia[0].result.Constructor.nationality;
      var totalPuntos = 0;
      var pilotos = [];
      var posiciones = [];

      resultadosEscuderia.forEach(function (item) {
        var name = item.result.Driver.givenName + ' ' + item.result.Driver.familyName;
        if (pilotos.indexOf(name) === -1) pilotos.push(name);
        totalPuntos += parseFloat(item.result.points);
        var pos = parseInt(item.result.position, 10);
        if (pos === 1) victorias++;
        if (pos <= 3) podios++;
        posiciones.push(pos);
      });

      var posMedia = posiciones.length > 0
        ? (posiciones.reduce(function (a, b) { return a + b; }, 0) / posiciones.length).toFixed(2)
        : 'N/A';

      nacionalidadElem.textContent = nacionalidad || 'N/D';

      container.innerHTML = '';
      container.appendChild(kpi('Carreras', totalCarrerasTemporada, 'Grandes Premios en ' + year));
      container.appendChild(kpi('Victorias', victorias, 'Primeros puestos'));
      container.appendChild(kpi('Podios', podios, 'Top 3 en carrera'));
      container.appendChild(kpi('Puntos', totalPuntos, 'Total acumulado'));
      container.appendChild(kpi('Posición media', posMedia, 'Media de resultados'));

      pilotosList.innerHTML = pilotos.length
        ? pilotos.map(function (p) { return '<span class="tf-badge">' + esc(p) + '</span>'; }).join('')
        : '<span class="tf-dim">Sin datos.</span>';
    })
    .catch(function (error) {
      console.error(error);
      container.innerHTML = '<div class="tf-error" style="grid-column:1/-1;">Error al cargar las estadísticas de la escudería.</div>';
      pilotosList.innerHTML = '<span class="tf-dim">Sin datos.</span>';
    });
})();
