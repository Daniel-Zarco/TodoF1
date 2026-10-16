/* TodoF1 — Dashboard principal */
(function () {
  'use strict';

  var API = 'https://api.jolpi.ca/ergast/f1';
  var YEAR_MIN = 1950;
  var YEAR_MAX = 2026;
  var MONTHS = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

  var params = new URLSearchParams(window.location.search);
  var year = parseInt(params.get('year') || '', 10);
  if (!year || year < YEAR_MIN || year > YEAR_MAX) year = YEAR_MAX;

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

  function setHref(id, url) {
    var el = $(id);
    if (el) el.href = url;
  }

  function loadingState(el) {
    if (el) el.innerHTML = '<div class="tf-loading"><span class="tf-spinner"></span><span>Cargando…</span></div>';
  }

  function messageState(el, message, type) {
    if (!el) return;
    el.innerHTML = '<div class="tf-' + (type || 'empty') + '">' + esc(message) + '</div>';
  }

  function fmtDate(iso) {
    if (!iso) return '';
    var p = String(iso).split('-');
    if (p.length < 3) return iso;
    var d = parseInt(p[2], 10);
    var m = parseInt(p[1], 10) - 1;
    return d + ' de ' + (MONTHS[m] || '') + ' de ' + p[0];
  }

  function todayISO() {
    var d = new Date();
    var m = d.getMonth() + 1;
    var day = d.getDate();
    return d.getFullYear() + '-' + (m < 10 ? '0' : '') + m + '-' + (day < 10 ? '0' : '') + day;
  }

  function setLinks(value) {
    var q = '?year=' + encodeURIComponent(value);
    setHref('hero-cta', '/index.html/PagePilotos.php' + q);
    setHref('stat-gp', '/index.html/PageCircuitos.php' + q);
    setHref('stat-pilotos', '/index.html/PagePilotos.php' + q);
    setHref('stat-escuderias', '/index.html/PageEscuderias.php' + q);
    setHref('stat-resultados', '/index.html/PageEstadisticas.php' + q);
    setHref('link-pilotos-full', '/index.html/PagePilotos.php' + q);
    setHref('link-escuderias-full', '/index.html/PageEstadisticas.php' + q);
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
    if (!el) return;
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

  var TRACK_SVG =
    '<svg class="race__track" viewBox="0 0 220 110" fill="none" stroke="currentColor" ' +
    'stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' +
    '<path d="M22 82c-6-22 10-38 34-40s40 10 56 4 22-24 44-22 40 20 36 42-28 30-52 26-30-20-52-16-24 26-46 30-16-8-20-24Z"/>' +
    '</svg>';

  function renderNextRace(calendarRes, value) {
    var el = $('next-race');
    if (!el) return;
    if (calendarRes.status !== 'fulfilled') {
      messageState(el, 'No se pudo cargar el calendario.', 'error');
      return;
    }
    var races = (calendarRes.value.MRData && calendarRes.value.MRData.RaceTable && calendarRes.value.MRData.RaceTable.Races) || [];
    if (!races.length) {
      messageState(el, 'Sin calendario para esta temporada.', 'empty');
      return;
    }

    var today = todayISO();
    var next = null;
    for (var i = 0; i < races.length; i++) {
      if (races[i].date >= today) { next = races[i]; break; }
    }
    var past = false;
    if (!next) { next = races[races.length - 1]; past = true; }

    var kicker = $('race-kicker');
    if (kicker) kicker.textContent = past ? 'Última prueba' : 'Próxima carrera';

    var circuit = next.Circuit || {};
    var loc = circuit.Location || {};
    var place = [circuit.circuitName, loc.country].filter(Boolean).join(' · ');

    el.innerHTML =
      '<span class="race__round">Ronda ' + esc(next.round) + '</span>' +
      '<h3 class="race__name">' + esc(next.raceName) + '</h3>' +
      '<p class="race__date">' + esc(fmtDate(next.date)) + '</p>' +
      '<p class="race__circuit">' +
      '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' +
      '<path d="M12 21s7-5.6 7-11a7 7 0 0 0-14 0c0 5.4 7 11 7 11Z"></path><circle cx="12" cy="10" r="2.6"></circle></svg> ' +
      esc(place) + '</p>' +
      TRACK_SVG +
      '<a class="tf-btn tf-btn--primary race__link" href="/index.html/PageCircuitos.php?year=' + encodeURIComponent(value) + '">Ver detalles ' + '&#8594;</a>';
  }

  async function loadSeason(value) {
    setText('hero-year', value);
    setLinks(value);
    ['kpi-gp', 'kpi-pilotos', 'kpi-escuderias', 'kpi-resultados'].forEach(function (id) {
      setText(id, '—');
    });
    loadingState($('next-race'));
    loadingState($('standings-pilotos'));
    loadingState($('standings-escuderias'));

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

    renderNextRace(calendarRes, value);

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
    }, 5);

    // Constructores: todos los equipos de la temporada
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

  var select = $('anio');
  for (var y = YEAR_MAX; y >= YEAR_MIN; y--) {
    var opt = document.createElement('option');
    opt.value = y;
    opt.textContent = y;
    select.appendChild(opt);
  }
  select.value = year;

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
