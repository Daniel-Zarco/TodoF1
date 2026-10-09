/* TodoF1 — Dashboard principal */
(function () {
  'use strict';

  var API = 'https://api.jolpi.ca/ergast/f1';
  var YEAR_MIN = 1950;
  var YEAR_MAX = 2026;

  var params = new URLSearchParams(window.location.search);
  var year = parseInt(params.get('year') || '', 10);
  if (!year || year < YEAR_MIN || year > YEAR_MAX) year = YEAR_MAX;

  var select = document.getElementById('anio');
  for (var y = YEAR_MAX; y >= YEAR_MIN; y--) {
    var opt = document.createElement('option');
    opt.value = y;
    opt.textContent = y;
    select.appendChild(opt);
  }
  select.value = year;

  function $(id) { return document.getElementById(id); }

  function esc(value) {
    return String(value == null ? '' : value).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  function setText(id, value) {
    var el = $(id);
    if (el) el.textContent = value;
  }

  function loadingState(el) {
    if (el) el.innerHTML = '<div class="tf-loading"><span class="tf-spinner"></span><span>Cargando…</span></div>';
  }

  function messageState(el, message, type) {
    if (!el) return;
    el.innerHTML = '<div class="tf-' + (type || 'empty') + '">' + esc(message) + '</div>';
  }

  function setLinks(value) {
    var q = '?year=' + value;
    $('link-pilotos-full').href = '/index.html/PagePilotos.php' + q;
    $('link-escuderias-full').href = '/index.html/PageEstadisticas.php' + q;
    $('access-pilotos').href = '/index.html/PagePilotos.php' + q;
    $('access-escuderias').href = '/index.html/PageEscuderias.php' + q;
    $('access-circuitos').href = '/index.html/PageCircuitos.php' + q;
  }

  function standingRow(position, name, team, points) {
    var row = document.createElement('div');
    row.className = 'tf-standing' + (position <= 3 ? ' tf-standing--p' + position : '');
    row.innerHTML =
      '<div class="tf-standing__pos">' + position + '</div>' +
      '<div><div class="tf-standing__name">' + esc(name) + '</div>' +
      (team ? '<div class="tf-standing__team">' + esc(team) + '</div>' : '') +
      '</div>' +
      '<div class="tf-standing__points">' + esc(points) + '<small>PTS</small></div>';
    return row;
  }

  function renderStandings(el, settled, mapper, limit) {
    if (settled.status !== 'fulfilled') {
      messageState(el, 'No se pudo cargar la clasificación.', 'error');
      return;
    }
    var list = settled.value && settled.value.MRData &&
      settled.value.MRData.StandingsTable &&
      settled.value.MRData.StandingsTable.StandingsLists &&
      settled.value.MRData.StandingsTable.StandingsLists[0];

    var rows = list ? (mapper.list(list) || []) : [];
    if (!rows.length) {
      messageState(el, 'Sin datos de clasificación para esta temporada.', 'empty');
      return;
    }

    // limit: número máximo de filas. Si no se indica, se muestran todas.
    var visible = typeof limit === 'number' ? rows.slice(0, limit) : rows;

    var fragment = document.createDocumentFragment();
    visible.forEach(function (entry) {
      var data = mapper.row(entry);
      fragment.appendChild(standingRow(data.position, data.name, data.team, data.points));
    });
    el.innerHTML = '';
    el.appendChild(fragment);
  }

  function standingsCount(settled, key) {
    if (settled.status !== 'fulfilled') return 'N/D';
    var list = settled.value && settled.value.MRData &&
      settled.value.MRData.StandingsTable &&
      settled.value.MRData.StandingsTable.StandingsLists &&
      settled.value.MRData.StandingsTable.StandingsLists[0];
    var rows = list ? (list[key] || []) : [];
    return rows.length;
  }

  async function loadSeason(value) {
    setText('resumen-year', value);
    setLinks(value);
    ['kpi-gp', 'kpi-pilotos', 'kpi-escuderias', 'kpi-resultados'].forEach(function (id) {
      setText(id, '—');
    });
    loadingState($('standings-pilotos'));
    loadingState($('standings-escuderias'));

    // Nota: Jolpica capa "limit" a 100, por lo que los recuentos NO se obtienen
    // paginando resultados, sino del calendario, del total de la API y de las
    // clasificaciones (que ya incluyen a todos los participantes de la temporada).
    var settled = await Promise.allSettled([
      fetch(API + '/' + value + '.json').then(function (r) { return r.json(); }),
      fetch(API + '/' + value + '/results.json?limit=1').then(function (r) { return r.json(); }),
      fetch(API + '/' + value + '/driverStandings.json').then(function (r) { return r.json(); }),
      fetch(API + '/' + value + '/constructorStandings.json').then(function (r) { return r.json(); })
    ]);

    var calendarRes = settled[0];
    var resultsRes = settled[1];
    var driversRes = settled[2];
    var constructorsRes = settled[3];

    var races = calendarRes.status === 'fulfilled'
      ? ((calendarRes.value.MRData && calendarRes.value.MRData.RaceTable && calendarRes.value.MRData.RaceTable.Races) || [])
      : [];
    var totalResults = resultsRes.status === 'fulfilled'
      ? parseInt(resultsRes.value.MRData && resultsRes.value.MRData.total, 10)
      : NaN;

    setText('kpi-gp', calendarRes.status === 'fulfilled' ? races.length : 'N/D');
    setText('kpi-pilotos', standingsCount(driversRes, 'DriverStandings'));
    setText('kpi-escuderias', standingsCount(constructorsRes, 'ConstructorStandings'));
    setText('kpi-resultados', isNaN(totalResults) ? 'N/D' : totalResults.toLocaleString('es-ES'));

    // Pilotos: solo los 10 primeros
    renderStandings($('standings-pilotos'), driversRes, {
      list: function (l) { return l.DriverStandings; },
      row: function (entry) {
        return {
          position: entry.position,
          name: entry.Driver.givenName + ' ' + entry.Driver.familyName,
          team: entry.Constructors && entry.Constructors.length ? entry.Constructors[entry.Constructors.length - 1].name : '',
          points: entry.points
        };
      }
    }, 10);

    // Constructores: todos los equipos de la temporada (sin límite)
    renderStandings($('standings-escuderias'), constructorsRes, {
      list: function (l) { return l.ConstructorStandings; },
      row: function (entry) {
        return {
          position: entry.position,
          name: entry.Constructor.name,
          team: entry.Constructor.nationality,
          points: entry.points
        };
      }
    });
  }

  select.addEventListener('change', function () {
    var value = parseInt(this.value, 10);
    try { sessionStorage.setItem('todof1:year', value); } catch (e) { /* sin almacenamiento */ }
    var url = new URL(window.location.href);
    url.searchParams.set('year', value);
    window.history.replaceState({}, '', url);
    loadSeason(value);
  });

  loadSeason(year);
})();
