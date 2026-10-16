/* TodoF1 — Pilotos: cuadrícula de fichas estilo F1
   - Datos reales de la temporada seleccionada (Ergast/Jolpica), con paginación.
   - Buscador, filtro por escudería y ordenación combinables, sin recargar.
   - Fotografía del piloto desde Wikipedia (integración ya existente en el
     proyecto, PageStatsPiloto.js) como mejora OPCIONAL: si falla o no existe,
     la ficha usa un fallback visual propio.
   - Los acentos de cada ficha usan el color de la escudería del piloto en esa
     temporada; los controles siguen usando las variables del tema activo. */
(function () {
  'use strict';

  var API = 'https://api.jolpi.ca/ergast/f1';
  var WIKI_API = 'https://en.wikipedia.org/w/api.php';
  var PHOTO_BATCH = 50;   // máximo de títulos por consulta en la API de Wikipedia
  var PHOTO_SIZE = 480;   // ancho del thumbnail solicitado
  var PAGE_LIMIT = 100;   // máximo de resultados por página en Jolpica
  var NEUTRAL_COLOR = '#55555E'; // mismo neutro que usa PageEscuderias.js

  var params = new URLSearchParams(window.location.search);
  var year = parseInt(params.get('year') || '2026', 10);

  var grid = document.getElementById('pilots-grid');
  var search = document.getElementById('search');
  var clearBtn = document.getElementById('search-clear');
  var teamFilter = document.getElementById('filter-team');
  var sortSelect = document.getElementById('sort-by');
  var counter = document.getElementById('count-badge');
  var seasonSub = document.getElementById('season-sub');

  document.getElementById('year-label').textContent = year;
  document.getElementById('btn-volver').href = '/?year=' + year;
  seasonSub.textContent = 'Plantilla y clasificaci\u00f3n de pilotos de la temporada ' + year + '.';

  /* Escuderías históricas por nombre: se reutilizan los colores ya definidos
     en el proyecto (PageEscuderias.js). Las escuderías modernas se resuelven
     antes por constructorId con el sistema de temas (teams.js). */
  var HISTORIC_COLORS = {
    'lotus': '#FFB800',
    'toro rosso': '#4E7C9B',
    'force india': '#F480B5',
    'benetton': '#00A551',
    'brabham': '#FFCC00',
    'tyrrell': '#003399'
  };

  var state = { term: '', team: '', sort: 'championship' };
  var drivers = [];
  var teams = [];

  /* ------------------------------------------------------------ utilidades */
  function esc(value) {
    return String(value == null ? '' : value).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  /* Normaliza para buscar: sin tildes, sin mayúsculas y con espacios simples. */
  function norm(value) {
    return String(value == null ? '' : value)
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .toLowerCase()
      .replace(/\s+/g, ' ')
      .trim();
  }

  function initials(given, family) {
    var g = String(given || '').trim();
    var f = String(family || '').trim();
    var value = (g ? g.charAt(0) : '') + (f ? f.charAt(0) : '');
    return value ? value.toUpperCase() : '?';
  }

  function hexToRgb(hex) {
    var value = String(hex || '').replace('#', '');
    if (value.length === 3) {
      value = value.charAt(0) + value.charAt(0) + value.charAt(1) + value.charAt(1) + value.charAt(2) + value.charAt(2);
    }
    var num = parseInt(value, 16);
    if (isNaN(num)) return { r: 85, g: 85, b: 94 };
    return { r: (num >> 16) & 255, g: (num >> 8) & 255, b: num & 255 };
  }

  function rgba(hex, alpha) {
    var c = hexToRgb(hex);
    return 'rgba(' + c.r + ',' + c.g + ',' + c.b + ',' + alpha + ')';
  }

  /* Texto legible sobre el color de la escudería: reutiliza el cálculo del
     sistema de temas y, si no está disponible, aplica la misma fórmula. */
  function onColor(hex) {
    var helper = window.TodoF1Teams && window.TodoF1Teams._internal && window.TodoF1Teams._internal.onColor;
    if (typeof helper === 'function') return helper(hex);
    var c = hexToRgb(hex);
    function channel(v) { v /= 255; return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4); }
    var l = 0.2126 * channel(c.r) + 0.7152 * channel(c.g) + 0.0722 * channel(c.b);
    return (1.05 / (l + 0.05)) >= ((l + 0.05) / 0.05) ? '#FFFFFF' : '#0B0B0D';
  }

  /* Color de la escudería: identidad del sistema de temas (constructorId) y,
     si no existe, el mapa histórico del proyecto; si tampoco, un neutro. */
  function teamColor(constructorId, teamName) {
    var resolve = window.TodoF1Teams && window.TodoF1Teams._internal && window.TodoF1Teams._internal.resolveTeam;
    if (typeof resolve === 'function') {
      var resolved = resolve(constructorId);
      if (resolved && resolved.primary) return resolved.primary;
    }
    var normalized = norm(teamName).replace(/[^a-z0-9]/g, '');
    for (var key in HISTORIC_COLORS) {
      if (!Object.prototype.hasOwnProperty.call(HISTORIC_COLORS, key)) continue;
      if (normalized && normalized.indexOf(key.replace(/[^a-z0-9]/g, '')) !== -1) return HISTORIC_COLORS[key];
    }
    return NEUTRAL_COLOR;
  }

  function wikiTitle(url) {
    var slug = String(url || '').split('/').pop();
    try { return decodeURIComponent(slug); } catch (e) { return slug; }
  }

  function num(value) {
    var parsed = parseInt(value, 10);
    return isNaN(parsed) ? null : parsed;
  }

  function sortKey(driver) {
    return norm(driver.family) + ' ' + norm(driver.given) + ' ' + norm(driver.name);
  }

  /* ---------------------------------------------------------------- datos */
  function buildDriver(entry) {
    var info = entry.Driver || {};
    var constructors = entry.Constructors || [];
    var lastTeam = constructors.length ? constructors[constructors.length - 1] : null;
    var teamName = lastTeam ? (lastTeam.name || '') : '';
    var color = teamColor(lastTeam ? lastTeam.constructorId : '', teamName);

    return {
      driverId: info.driverId || '',
      given: info.givenName || '',
      family: info.familyName || '',
      name: [info.givenName, info.familyName].filter(Boolean).join(' ') || (info.familyName || ''),
      code: info.code || '',
      number: num(info.permanentNumber),
      nationality: info.nationality || '',
      position: num(entry.position),
      points: entry.points == null ? '' : String(entry.points),
      team: teamName,
      teams: constructors.map(function (c) { return c.name || ''; }).filter(Boolean),
      color: color,
      onColor: onColor(color),
      wikiTitle: wikiTitle(info.url),
      photo: ''
    };
  }

  /* Recorre todas las páginas de la clasificación: la API limita a 100
     resultados por petición y algunas temporadas históricas tienen más
     (p. ej. 1953 tiene 108 pilotos clasificados). */
  function fetchAllStandings() {
    var offset = 0;
    var total = Infinity;
    var all = [];

    function step() {
      return fetch(API + '/' + year + '/driverStandings.json?limit=' + PAGE_LIMIT + '&offset=' + offset)
        .then(function (response) {
          if (!response.ok) throw new Error('HTTP ' + response.status);
          return response.json();
        })
        .then(function (data) {
          var meta = (data && data.MRData) || {};
          var list = meta.StandingsTable && meta.StandingsTable.StandingsLists && meta.StandingsTable.StandingsLists[0];
          var standings = (list && list.DriverStandings) || [];

          total = num(meta.total);
          if (total === null) total = standings.length;
          if (!standings.length) return all;

          all = all.concat(standings);
          offset += PAGE_LIMIT;
          if (offset >= total) return all;
          return step();
        });
    }

    return step();
  }

  function populateTeams() {
    var found = [];
    drivers.forEach(function (driver) {
      driver.teams.forEach(function (name) {
        if (name && found.indexOf(name) === -1) found.push(name);
      });
    });
    found.sort(function (a, b) { return a.localeCompare(b, 'es'); });
    teams = found;

    found.forEach(function (name) {
      var option = document.createElement('option');
      option.value = name;
      option.textContent = name;
      teamFilter.appendChild(option);
    });
  }

  /* Dorsal disponible: si ningún piloto de la temporada lo tiene, las dos
     ordenaciones por dorsal quedan deshabilitadas (no se inventan números). */
  function updateSortAvailability() {
    var anyNumber = drivers.some(function (driver) { return driver.number !== null; });
    Array.prototype.forEach.call(sortSelect.options, function (option) {
      if (option.value.indexOf('number-') !== 0) return;
      option.disabled = !anyNumber;
      if (!anyNumber) option.title = 'Esta temporada no tiene dorsales registrados';
    });
    if (!anyNumber && state.sort.indexOf('number-') === 0) {
      state.sort = 'championship';
      sortSelect.value = 'championship';
    }
  }

  /* ------------------------------------------------------- filtro y orden */
  function matches(driver) {
    if (state.team && driver.teams.indexOf(state.team) === -1) return false;
    if (!state.term) return true;

    var haystack = norm(driver.given + ' ' + driver.family + ' ' + driver.name + ' ' + driver.code);
    var needles = state.term.split(' ').filter(Boolean);
    for (var i = 0; i < needles.length; i++) {
      if (haystack.indexOf(needles[i]) === -1) return false;
    }
    return true;
  }

  function compare(a, b) {
    switch (state.sort) {
      case 'name-asc':
        return sortKey(a).localeCompare(sortKey(b), 'es');
      case 'name-desc':
        return sortKey(b).localeCompare(sortKey(a), 'es');
      case 'number-asc':
      case 'number-desc': {
        var dir = state.sort === 'number-asc' ? 1 : -1;
        var an = a.number === null;
        var bn = b.number === null;
        // Sin dorsal: siempre al final, en cualquiera de las dos ordenaciones.
        if (an && bn) return sortKey(a).localeCompare(sortKey(b), 'es');
        if (an) return 1;
        if (bn) return -1;
        if (a.number !== b.number) return (a.number - b.number) * dir;
        return sortKey(a).localeCompare(sortKey(b), 'es');
      }
      default: {
        // Clasificación del campeonato: los no clasificados, al final.
        var ap = a.position === null;
        var bp = b.position === null;
        if (ap && bp) return sortKey(a).localeCompare(sortKey(b), 'es');
        if (ap) return 1;
        if (bp) return -1;
        if (a.position !== b.position) return a.position - b.position;
        return sortKey(a).localeCompare(sortKey(b), 'es');
      }
    }
  }

  function visibleDrivers() {
    return drivers.filter(matches).sort(compare);
  }

  /* ---------------------------------------------------------------- fichas */
  function statLine(driver) {
    var parts = [];
    if (driver.number !== null) parts.push('#' + driver.number);
    if (driver.position !== null) parts.push('<b>P' + driver.position + '</b>');
    parts.push(driver.points === '' ? '&mdash;' : '<b>' + esc(driver.points) + '</b> pts');
    return parts.join(' &middot; ');
  }

  function card(driver) {
    var link = document.createElement('a');
    link.className = 'pilot-card';
    link.href = 'PageStatsPiloto.php?driverId=' + encodeURIComponent(driver.driverId) +
      '&piloto=' + encodeURIComponent(driver.name) + '&year=' + year;
    link.setAttribute('data-driver-id', driver.driverId);
    link.style.setProperty('--pilot-color', driver.color);
    link.style.setProperty('--pilot-soft', rgba(driver.color, 0.18));
    link.style.setProperty('--pilot-line', rgba(driver.color, 0.42));
    link.style.setProperty('--pilot-glow', rgba(driver.color, 0.30));
    link.style.setProperty('--pilot-on-color', driver.onColor);

    link.innerHTML =
      '<span class="pilot-card__media">' +
        '<span class="pilot-card__initials" aria-hidden="true">' + esc(initials(driver.given, driver.family)) + '</span>' +
        (driver.photo
          ? '<img class="pilot-card__photo is-loaded" src="' + esc(driver.photo) + '" alt="" loading="lazy" decoding="async">'
          : '<img class="pilot-card__photo" alt="" loading="lazy" decoding="async" hidden>') +
        '<span class="pilot-card__scrim" aria-hidden="true"></span>' +
        (driver.number !== null ? '<span class="pilot-card__num" aria-hidden="true">' + esc(driver.number) + '</span>' : '') +
        (driver.code ? '<span class="pilot-card__code" aria-hidden="true">' + esc(driver.code) + '</span>' : '') +
      '</span>' +
      '<span class="pilot-card__body">' +
        '<span class="pilot-card__name">' +
          '<span class="pilot-card__family">' + esc(driver.family || driver.name) + '</span>' +
          (driver.given ? '<span class="pilot-card__given">' + esc(driver.given) + '</span>' : '') +
        '</span>' +
        '<span class="pilot-card__meta">' +
          '<span class="pilot-card__dot" aria-hidden="true"></span>' +
          '<span class="pilot-card__team">' + esc(driver.team || 'Sin escuder\u00eda') + '</span>' +
          (driver.nationality ? '<span class="pilot-card__nat">' + esc(driver.nationality) + '</span>' : '') +
        '</span>' +
      '</span>' +
      '<span class="pilot-card__foot">' +
        '<span class="pilot-card__stat">' + statLine(driver) + '</span>' +
        '<span class="pilot-card__arrow" aria-hidden="true">&#8594;</span>' +
      '</span>';

    return link;
  }

  function emptyState(message, type) {
    var box = document.createElement('div');
    box.className = 'tf-' + (type || 'empty');
    box.innerHTML = '<span>' + esc(message) + '</span>';
    return box;
  }

  function errorState(message) {
    var box = document.createElement('div');
    box.className = 'tf-error';
    box.innerHTML = '<span>' + esc(message) + '</span>' +
      '<button type="button" class="tf-btn tf-btn--sm tf-mt-4" id="pilots-retry">Reintentar</button>';
    return box;
  }

  function render() {
    if (!drivers.length) {
      grid.innerHTML = '';
      grid.appendChild(emptyState('No hay pilotos registrados para la temporada ' + year + '.', 'empty'));
      counter.textContent = '0 pilotos';
      return;
    }

    var list = visibleDrivers();
    counter.textContent = 'Mostrando ' + list.length + ' de ' + drivers.length + ' pilotos';

    grid.innerHTML = '';
    if (!list.length) {
      grid.appendChild(emptyState('No se encontraron pilotos con esos filtros.', 'empty'));
      return;
    }

    var fragment = document.createDocumentFragment();
    list.forEach(function (driver) { fragment.appendChild(card(driver)); });
    grid.appendChild(fragment);
  }

  /* ------------------------------------------------------ fotografías (opcional) */
  function applyPhotos(batch, data) {
    var query = (data && data.query) || {};
    var byTitle = {};
    Object.keys(query.pages || {}).forEach(function (key) {
      var page = query.pages[key];
      if (page && page.title) byTitle[page.title] = page;
    });

    // Wikipedia devuelve "normalized" y "redirects": se encadenan para localizar
    // la página real (p. ej. "Nino_Farina" -> "Giuseppe Farina").
    var alias = {};
    (query.normalized || []).forEach(function (item) { alias[item.from] = item.to; });
    (query.redirects || []).forEach(function (item) { alias[item.from] = item.to; });
    function resolve(title) {
      var current = title;
      for (var i = 0; i < 5 && alias[current]; i++) current = alias[current];
      return current;
    }

    batch.forEach(function (driver) {
      var page = byTitle[resolve(driver.wikiTitle)] || byTitle[driver.wikiTitle];
      var source = page && page.thumbnail && page.thumbnail.source;
      if (!source) return;

      driver.photo = source;
      var node = grid.querySelector('.pilot-card[data-driver-id="' + driver.driverId + '"]');
      if (!node) return;

      var img = node.querySelector('.pilot-card__photo');
      if (!img) return;
      img.addEventListener('load', function () { img.classList.add('is-loaded'); });
      img.addEventListener('error', function () { img.hidden = true; });
      img.src = source;
      img.hidden = false;
    });
  }

  function wikiRequest(titles) {
    return fetch(WIKI_API + '?action=query&format=json&origin=*&prop=pageimages' +
      '&piprop=thumbnail&pithumbsize=' + PHOTO_SIZE + '&redirects=1&titles=' +
      encodeURIComponent(titles.join('|')))
      .then(function (response) {
        if (response.status === 429) {
          // Límite de peticiones: se espera lo que indique el servidor (máx. 3 s)
          // y se reintenta una sola vez. Si vuelve a fallar, se mantiene el fallback.
          var wait = Math.min(parseInt(response.headers.get('Retry-After') || '2', 10) || 2, 3);
          return new Promise(function (resolve, reject) {
            window.setTimeout(function () {
              fetch(WIKI_API + '?action=query&format=json&origin=*&prop=pageimages' +
                '&piprop=thumbnail&pithumbsize=' + PHOTO_SIZE + '&redirects=1&titles=' +
                encodeURIComponent(titles.join('|')))
                .then(function (retry) { return retry.ok ? retry.json() : {}; })
                .then(resolve, reject);
            }, wait * 1000);
          });
        }
        return response.ok ? response.json() : {};
      });
  }

  function loadPhotos() {
    var pending = drivers.filter(function (driver) { return driver.wikiTitle && !driver.photo; });
    var batches = [];
    for (var i = 0; i < pending.length; i += PHOTO_BATCH) {
      batches.push(pending.slice(i, i + PHOTO_BATCH));
    }

    // Secuencial (una consulta por bloque de 50 pilotos) para no saturar la API.
    return batches.reduce(function (chain, batch) {
      return chain.then(function () {
        return wikiRequest(batch.map(function (driver) { return driver.wikiTitle; }))
          .then(function (data) { applyPhotos(batch, data); })
          .catch(function () { /* sin fotografías: se conserva el fallback visual */ });
      });
    }, Promise.resolve());
  }

  /* --------------------------------------------------------------- eventos */
  function syncClearButton() {
    clearBtn.hidden = !search.value;
  }

  search.addEventListener('input', function () {
    state.term = norm(search.value);
    syncClearButton();
    render();
  });

  clearBtn.addEventListener('click', function () {
    search.value = '';
    state.term = '';
    syncClearButton();
    render();
    search.focus();
  });

  search.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && search.value) {
      event.preventDefault();
      clearBtn.click();
    }
  });

  teamFilter.addEventListener('change', function () {
    state.team = teamFilter.value;
    render();
  });

  sortSelect.addEventListener('change', function () {
    state.sort = sortSelect.value;
    render();
  });

  /* ----------------------------------------------------------------- carga */
  function load() {
    grid.innerHTML = '';
    grid.appendChild(emptyState('Cargando pilotos\u2026', 'loading'));
    counter.textContent = 'Cargando\u2026';

    fetchAllStandings()
      .then(function (standings) {
        if (!standings.length) {
          drivers = [];
          render();
          return;
        }
        drivers = standings.map(buildDriver);
        populateTeams();
        updateSortAvailability();
        render();
        return loadPhotos();
      })
      .catch(function (error) {
        console.error(error);
        grid.innerHTML = '';
        var box = errorState('No se pudieron cargar los pilotos de esta temporada.');
        grid.appendChild(box);
        counter.textContent = 'Error';
        var retry = box.querySelector('#pilots-retry');
        if (retry) retry.addEventListener('click', load);
      });
  }

  syncClearButton();
  load();
})();
