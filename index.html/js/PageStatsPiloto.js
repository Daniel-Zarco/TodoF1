/* ============================================================
   TodoF1 — Ficha individual del piloto
   ------------------------------------------------------------
   PROCEDENCIA Y DEFINICIÓN DE CADA MÉTRICA (auditado):
     · Ficha (nombre, dorsal, código, nacionalidad, nacimiento, Wikipedia):
       GET /drivers/{driverId}.json
     · Trayectoria real (temporadas disputadas, GP, victorias, podios, mejor
       resultado y escuderías por temporada): GET /drivers/{driverId}/results.json
       paginado a 100. Son resultados de CARRERA: el endpoint no incluye
       sprints, por lo que las victorias/podios son de Gran Premio.
       - "Temporada disputada" = temporada con al menos un Gran Premio
         (se descartan inscripciones sin carrera, p. ej. Antonelli 2024).
       - "Debut" = primera temporada con carrera.
       - GP = entradas que tomaron la salida (se descartan retirados por
         retirada/descalificación no son baja: se excluyen "W" y "F").
     · Puntos y posición final por temporada: /{año}/drivers/{driverId}/driverStandings.json
       (puntos oficiales del campeonato, que sí incluyen los sprints).
     · Fotografía y fecha de fallecimiento: API de Wikipedia/Wikidata
       (misma integración que ya usaba esta página).
   ALCANCE: la trayectoria que se muestra se limita al año seleccionado.
   ============================================================ */
(function () {
  'use strict';

  var API = 'https://api.jolpi.ca/ergast/f1';
  var WIKI_API = 'https://en.wikipedia.org/w/api.php';
  var WIKIDATA_API = 'https://www.wikidata.org/w/api.php';
  var PHOTO_SIZE = 720;
  var PAGE_LIMIT = 100;
  var MAX_PAGES = 40;
  var NEUTRAL_COLOR = '#55555E';
  var POSITIONS_CACHE = 'todof1:pilot-positions:';
  var POSITION_DELAY = 450;   // espaciado entre consultas de clasificación
  var POSITION_RETRY_DELAYS = [1400, 3000];

  /* Colores de escuderías históricas: mismos valores ya usados en el proyecto
     (PageEscuderias.js / PagePilotos.js). No se inventan colores. */
  var HISTORIC_COLORS = {
    'lotus': '#FFB800',
    'toro rosso': '#4E7C9B',
    'force india': '#F480B5',
    'benetton': '#00A551',
    'brabham': '#FFCC00',
    'tyrrell': '#003399'
  };

  var params = new URLSearchParams(window.location.search);
  var pilotoNombre = params.get('piloto') || '';
  var driverIdParam = params.get('driverId') || '';
  var year = parseInt(params.get('year') || '2026', 10);
  if (!year || year < 1950 || year > 2100) year = 2026;

  var el = {
    volver: document.getElementById('btn-volver'),
    hero: document.getElementById('pilot-hero'),
    initials: document.getElementById('hero-initials'),
    photo: document.getElementById('piloto-foto'),
    number: document.getElementById('hero-number'),
    code: document.getElementById('hero-code'),
    eyebrow: document.getElementById('hero-eyebrow'),
    title: document.getElementById('titulo'),
    sub: document.getElementById('hero-sub'),
    tags: document.getElementById('hero-tags'),
    wiki: document.getElementById('wiki'),
    season: document.getElementById('hero-season'),
    kpiScope: document.getElementById('kpi-scope'),
    kpis: document.getElementById('pilot-kpis'),
    chart: document.getElementById('pilot-chart'),
    evolutionSub: document.getElementById('evolution-sub'),
    metricPoints: document.getElementById('metric-points'),
    metricPosition: document.getElementById('metric-position'),
    stints: document.getElementById('pilot-stints'),
    stintsSub: document.getElementById('stints-sub'),
    resultsSub: document.getElementById('results-sub'),
    resultsBody: document.getElementById('pilot-results'),
    sortYear: document.getElementById('sort-year'),
    thYear: document.getElementById('th-year')
  };

  var state = {
    driverId: '',
    metric: 'points',
    sortAsc: false,
    positions: {},     // { year: {position: n|null, points: string|null} | {unavailable:true} }
    positionsDone: false,
    profile: null
  };

  /* ------------------------------------------------------------ utilidades */
  function esc(value) {
    return String(value == null ? '' : value).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  function norm(value) {
    return String(value == null ? '' : value)
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .toLowerCase()
      .replace(/\s+/g, ' ')
      .trim();
  }

  function num(value) {
    var parsed = parseInt(value, 10);
    return isNaN(parsed) ? null : parsed;
  }

  function numFloat(value) {
    var parsed = parseFloat(value);
    return isNaN(parsed) ? null : parsed;
  }

  /* Puntos: 340 -> "340" · 34.5 -> "34,5" */
  function fmtPoints(value) {
    if (value === null || value === undefined) return null;
    var n = typeof value === 'number' ? value : numFloat(value);
    if (n === null) return null;
    return String(Math.round(n * 100) / 100).replace('.', ',');
  }

  function fmtDate(iso) {
    if (!iso) return '';
    var p = String(iso).split('-');
    return p.length === 3 ? p[2] + '/' + p[1] + '/' + p[0] : iso;
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
    var parsed = parseInt(value, 16);
    if (isNaN(parsed)) return { r: 85, g: 85, b: 94 };
    return { r: (parsed >> 16) & 255, g: (parsed >> 8) & 255, b: parsed & 255 };
  }

  function rgba(hex, alpha) {
    var c = hexToRgb(hex);
    return 'rgba(' + c.r + ',' + c.g + ',' + c.b + ',' + alpha + ')';
  }

  function onColor(hex) {
    var helper = window.TodoF1Teams && window.TodoF1Teams._internal && window.TodoF1Teams._internal.onColor;
    if (typeof helper === 'function') return helper(hex);
    var c = hexToRgb(hex);
    function channel(v) { v /= 255; return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4); }
    var l = 0.2126 * channel(c.r) + 0.7152 * channel(c.g) + 0.0722 * channel(c.b);
    return (1.05 / (l + 0.05)) >= ((l + 0.05) / 0.05) ? '#FFFFFF' : '#0B0B0D';
  }

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

  function age(birthIso) {
    var birth = new Date(birthIso);
    if (isNaN(birth.getTime())) return null;
    var today = new Date();
    var years = today.getFullYear() - birth.getFullYear();
    if (today.getMonth() < birth.getMonth() ||
      (today.getMonth() === birth.getMonth() && today.getDate() < birth.getDate())) years--;
    return years;
  }

  function el_(tag, className, html) {
    var node = document.createElement(tag);
    if (className) node.className = className;
    if (html != null) node.innerHTML = html;
    return node;
  }

  function loading(message) {
    return el_('div', 'tf-loading', '<span class="tf-spinner" aria-hidden="true"></span><span>' + esc(message) + '</span>');
  }

  function messageBox(message, type) {
    return el_('div', 'tf-' + (type || 'empty'), '<span>' + esc(message) + '</span>');
  }

  function errorBox(message, onRetry) {
    var box = el_('div', 'tf-error', '<span>' + esc(message) + '</span>' +
      '<button type="button" class="tf-btn tf-btn--sm tf-mt-4" id="pilot-retry">Reintentar</button>');
    var button = box.querySelector('#pilot-retry');
    if (button) button.addEventListener('click', onRetry);
    return box;
  }

  /* --------------------------------------------------------- peticiones API */
  function fetchJson(url) {
    return fetch(url).then(function (response) {
      if (!response.ok) {
        var error = new Error('HTTP ' + response.status);
        error.status = response.status;
        error.retryAfter = num(response.headers.get('Retry-After'));
        throw error;
      }
      return response.json();
    });
  }

  /* Paginación a 100 registros (evita respuestas truncadas). */
  function fetchAllRaces(url) {
    var offset = 0;
    var total = Infinity;
    var all = [];
    var pages = 0;

    function step() {
      var sep = url.indexOf('?') === -1 ? '?' : '&';
      return fetchJson(url + sep + 'limit=' + PAGE_LIMIT + '&offset=' + offset)
        .then(function (data) {
          var meta = (data && data.MRData) || {};
          var races = (meta.RaceTable && meta.RaceTable.Races) || [];
          total = num(meta.total);
          if (total === null) total = races.length;
          if (!races.length) return all;

          all = all.concat(races);
          offset += PAGE_LIMIT;
          pages++;
          if (offset >= total || pages >= MAX_PAGES) return all;
          return step();
        });
    }

    return step();
  }

  /* Posiciones finales por temporada: 1 consulta por temporada, con espaciado
     y reintento único ante límite de peticiones. Se cachea en sessionStorage. */
  function readPositionsCache() {
    try {
      var raw = window.sessionStorage.getItem(POSITIONS_CACHE + state.driverId);
      return raw ? JSON.parse(raw) : {};
    } catch (e) { return {}; }
  }

  function writePositionsCache(cache) {
    try { window.sessionStorage.setItem(POSITIONS_CACHE + state.driverId, JSON.stringify(cache)); } catch (e) { /* sin almacenamiento */ }
  }

  function fetchSeasonPosition(y, attempt) {
    return fetchJson(API + '/' + y + '/drivers/' + state.driverId + '/driverStandings.json')
      .then(function (data) {
        var list = data.MRData && data.MRData.StandingsTable && data.MRData.StandingsTable.StandingsLists;
        var entry = list && list[0] && list[0].DriverStandings && list[0].DriverStandings[0];
        if (!entry) return { unavailable: true };
        return { position: num(entry.position), points: entry.points == null ? null : String(entry.points) };
      })
      .catch(function (error) {
        // Se reintenta ante límite de peticiones, errores del servidor o fallos
        // de red (una respuesta limitada puede llegar sin cabeceras CORS).
        var retryable = !error.status || error.status === 429 || error.status >= 500;
        if (retryable && attempt < POSITION_RETRY_DELAYS.length) {
          var wait = error.retryAfter ? Math.min(error.retryAfter, 4) * 1000 : POSITION_RETRY_DELAYS[attempt];
          return new Promise(function (resolve) {
            window.setTimeout(function () { resolve(fetchSeasonPosition(y, attempt + 1)); }, wait);
          });
        }
        return { unavailable: true };
      });
  }

  function loadPositions(years) {
    var cache = readPositionsCache();
    // La temporada en curso cambia a lo largo del año: no se memoriza.
    var liveYear = years.length ? Math.max.apply(null, years) : null;

    Object.keys(cache).forEach(function (y) { state.positions[y] = cache[y]; });

    var pending = years.filter(function (y) {
      return y === liveYear || !state.positions[y];
    });

    if (!pending.length) {
      state.positionsDone = true;
      return Promise.resolve();
    }

    return new Promise(function (resolve) {
      var index = 0;
      function next() {
        if (index >= pending.length) {
          state.positionsDone = true;
          renderTable();
          renderChart();
          return resolve();
        }
        var y = pending[index++];
        fetchSeasonPosition(y, 0).then(function (data) {
          state.positions[y] = data;
          // Solo se memorizan los datos válidos: un fallo puntual no debe
          // quedar cacheado y ocultar la posición en visitas posteriores.
          if (y !== liveYear && !data.unavailable) {
            cache[y] = data;
            writePositionsCache(cache);
          }
          renderTable();
          renderChart();
          window.setTimeout(next, POSITION_DELAY);
        });
      }
      next();
    });
  }

  /* --------------------------------------------------------- perfil del piloto */
  function buildProfile(driver, races) {
    var perSeason = {};

    races.forEach(function (race) {
      (race.Results || []).forEach(function (result) {
        var season = race.season;
        perSeason[season] = perSeason[season] || {
          year: parseInt(season, 10), races: 0, wins: 0, podiums: 0,
          best: null, teams: [], teamIds: [], numbers: []
        };
        var s = perSeason[season];
        var positionText = String(result.positionText || '');
        var position = num(result.position);

        // "W" (retirado antes de la salida) y "F" (no se clasificó) no son carreras disputadas.
        if (positionText !== 'W' && positionText !== 'F') s.races++;
        if (position === 1) s.wins++;
        if (position !== null && position >= 1 && position <= 3) s.podiums++;
        if (position !== null && (s.best === null || position < s.best)) s.best = position;

        var team = result.Constructor && result.Constructor.name;
        var teamId = result.Constructor && result.Constructor.constructorId;
        if (team && s.teams.indexOf(team) === -1) s.teams.push(team);
        if (teamId && s.teamIds.indexOf(teamId) === -1) s.teamIds.push(teamId);

        // Número de coche realmente usado en esa temporada (el API expone el
        // dorsal permanente actual incluso en temporadas antiguas, así que no
        // se usa como dorsal histórico).
        var carNumber = result.number == null ? '' : String(result.number).trim();
        if (carNumber && s.numbers.indexOf(carNumber) === -1) s.numbers.push(carNumber);
      });
    });

    var seasons = Object.keys(perSeason)
      .map(function (key) { return perSeason[key]; })
      .filter(function (s) { return s.races > 0; })
      .sort(function (a, b) { return a.year - b.year; });

    seasons.forEach(function (s) {
      s.colors = s.teams.map(function (name, i) { return teamColor(s.teamIds[i] || '', name); });
      s.onColor = onColor(s.colors[s.colors.length - 1] || NEUTRAL_COLOR);
      // Un único número en toda la temporada: es su dorsal de ese año. Si
      // cambió de número, no se muestra ninguno (no se elige uno al azar).
      s.fixedNumber = s.numbers.length === 1 ? s.numbers[0] : null;
    });

    var upto = seasons.filter(function (s) { return s.year <= year; });
    var totals = {
      seasons: upto.length,
      races: upto.reduce(function (a, s) { return a + s.races; }, 0),
      wins: upto.reduce(function (a, s) { return a + s.wins; }, 0),
      podiums: upto.reduce(function (a, s) { return a + s.podiums; }, 0),
      best: upto.reduce(function (a, s) { return (s.best !== null && (a === null || s.best < a)) ? s.best : a; }, null),
      teams: []
    };
    upto.forEach(function (s) {
      s.teams.forEach(function (name) { if (totals.teams.indexOf(name) === -1) totals.teams.push(name); });
    });

    return {
      driver: driver,
      seasons: seasons,
      upto: upto,
      debut: seasons.length ? seasons[0].year : null,
      lastSeason: seasons.length ? seasons[seasons.length - 1].year : null,
      totals: totals,
      selected: seasons.filter(function (s) { return s.year === year; })[0] || null
    };
  }

  function selectedColor() {
    var p = state.profile;
    if (!p) return NEUTRAL_COLOR;
    if (p.selected) return p.selected.colors[p.selected.colors.length - 1];
    var previous = p.upto[p.upto.length - 1];
    return previous ? previous.colors[previous.colors.length - 1] : NEUTRAL_COLOR;
  }

  function applyTeamColor(node, color) {
    node.style.setProperty('--pilot-color', color);
    node.style.setProperty('--pilot-soft', rgba(color, 0.18));
    node.style.setProperty('--pilot-line', rgba(color, 0.42));
    node.style.setProperty('--pilot-glow', rgba(color, 0.30));
    node.style.setProperty('--pilot-on-color', onColor(color));
  }

  /* ------------------------------------------------------------- cabecera */
  function renderHeader() {
    var p = state.profile;
    var driver = p.driver;
    var fullName = [driver.givenName, driver.familyName].filter(Boolean).join(' ');

    applyTeamColor(el.hero, selectedColor());
    el.title.textContent = fullName || pilotoNombre || 'Piloto';
    el.eyebrow.textContent = 'Piloto de Fórmula 1';
    el.initials.textContent = initials(driver.givenName, driver.familyName);
    el.season.textContent = year;
    document.title = (fullName ? fullName + ' — ' : '') + 'TodoF1';

    // Dorsal y código: solo cuando corresponden a la temporada consultada
    // (el número permanente del API es el actual, no el histórico).
    var selected = p.selected;
    var carNumber = selected && selected.fixedNumber ? selected.fixedNumber : '';
    el.number.textContent = carNumber;
    var showCode = !!(driver.code && selected && selected.year >= 2014);
    if (showCode) {
      el.code.textContent = driver.code;
      el.code.hidden = false;
    } else {
      el.code.hidden = true;
    }

    if (driver.url) {
      el.wiki.href = driver.url;
      el.wiki.hidden = false;
    } else {
      el.wiki.hidden = true;
    }

    // Escudería(s) de la temporada seleccionada
    var teamText = p.selected
      ? (p.selected.teams.length > 1
        ? p.selected.teams.slice(0, -1).join(', ') + ' y ' + p.selected.teams[p.selected.teams.length - 1]
        : p.selected.teams[0])
      : '';
    el.sub.textContent = p.selected
      ? 'Con ' + teamText + ' en la temporada ' + year + '.'
      : (p.lastSeason !== null && year > p.lastSeason
        ? 'Su última temporada disputada fue ' + p.lastSeason + '.'
        : 'Temporada ' + year + '.');

    // Etiquetas secundarias
    var tags = [];
    if (driver.nationality) tags.push({ text: driver.nationality, dot: false });
    if (p.totals.seasons) tags.push({ text: p.totals.seasons + (p.totals.seasons === 1 ? ' temporada' : ' temporadas') + ' hasta ' + year, dot: true });
    if (p.debut) tags.push({ text: 'Debut ' + p.debut, dot: false });
    if (p.totals.teams.length) tags.push({ text: p.totals.teams.length + (p.totals.teams.length === 1 ? ' escudería' : ' escuderías'), dot: false });

    el.tags.innerHTML = tags.map(function (tag) {
      return '<li class="pilot-hero__tag">' + (tag.dot ? '<span class="pilot-dot" aria-hidden="true"></span>' : '') + esc(tag.text) + '</li>';
    }).join('');

    if (p.selected) {
      var color = p.selected.colors[p.selected.colors.length - 1];
      el.tags.innerHTML += '<li class="pilot-hero__tag" style="--pilot-color:' + esc(color) + '"><span class="pilot-dot" aria-hidden="true"></span>' + esc(p.selected.teams.join(' · ')) + '</li>';
    }
  }

  /* Foto del piloto (Wikipedia) y fecha de fallecimiento (Wikidata). */
  function loadPhotoAndDeath(driver) {
    var title = wikiTitle(driver.url);
    if (!title) return Promise.resolve();

    return fetch(WIKI_API + '?action=query&format=json&origin=*&prop=pageimages|pageprops&piprop=thumbnail&pithumbsize=' + PHOTO_SIZE + '&redirects=1&titles=' + encodeURIComponent(title))
      .then(function (response) { return response.ok ? response.json() : {}; })
      .then(function (data) {
        var query = (data && data.query) || {};
        var byTitle = {};
        Object.keys(query.pages || {}).forEach(function (key) {
          var page = query.pages[key];
          if (page && page.title) byTitle[page.title] = page;
        });
        var alias = {};
        (query.normalized || []).forEach(function (item) { alias[item.from] = item.to; });
        (query.redirects || []).forEach(function (item) { alias[item.from] = item.to; });
        var current = title;
        for (var i = 0; i < 5 && alias[current]; i++) current = alias[current];
        var page = byTitle[current] || byTitle[title];
        if (!page) return null;

        var source = page.thumbnail && page.thumbnail.source;
        if (source) {
          el.photo.addEventListener('load', function () { el.photo.classList.add('is-loaded'); });
          el.photo.addEventListener('error', function () { el.photo.hidden = true; });
          el.photo.src = source;   // validada: procede del artículo del piloto
          el.photo.hidden = false;
        }
        return page.pageprops && page.pageprops.wikibase_item ? page.pageprops.wikibase_item : null;
      })
      .then(function (qid) {
        if (!qid) return null;
        return fetch(WIKIDATA_API + '?action=wbgetclaims&entity=' + encodeURIComponent(qid) + '&property=P570&format=json&origin=*')
          .then(function (response) { return response.ok ? response.json() : {}; })
          .then(function (data) {
            var claims = data && data.claims && data.claims.P570;
            if (!claims || !claims.length) return null;
            var value = claims[0].mainsnak && claims[0].mainsnak.datavalue && claims[0].mainsnak.datavalue.value;
            if (!value || !value.time) return null;
            return String(value.time).replace('+', '').slice(0, 4);
          });
      })
      .catch(function () { return null; })   // sin foto/edad extra: se mantiene el fallback
      .then(function (deathYear) { state.deathYear = deathYear ? parseInt(deathYear, 10) : null; });
  }

  /* --------------------------------------------------------- estadísticas */
  var ICONS = {
    wins: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 4h8v5a4 4 0 0 1-8 0Z"></path><path d="M8 5H5.5A2.5 2.5 0 0 0 8 9.5"></path><path d="M16 5h2.5A2.5 2.5 0 0 1 16 9.5"></path><path d="M12 13v4"></path><path d="M8.5 20h7"></path><path d="M10 17h4l.8 3h-5.6Z"></path></svg>',
    podiums: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 20h16"></path><path d="M9 20v-8"></path><path d="M15 20V8"></path><path d="M12 20v-5"></path><path d="M6 12l3-2 3 2 3-4 3 2" opacity=".55"></path></svg>',
    seasons: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3.5" y="5" width="17" height="15" rx="2.5"></rect><path d="M3.5 9.5h17"></path><path d="M8 3.5v3"></path><path d="M16 3.5v3"></path></svg>',
    best: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="14.5" r="5.5"></circle><path d="M12 12.2l.9 1.9 2 .3-1.5 1.4.4 2-1.8-1-1.8 1 .4-2-1.5-1.4 2-.3Z"></path><path d="M8.5 9 6 3.5h12L15.5 9"></path></svg>',
    debut: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 3.5v17"></path><path d="M6 4.5h11l-2 3.5 2 3.5H6"></path></svg>',
    age: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="8.5"></circle><path d="M12 7.5V12l3 2"></path></svg>'
  };

  function kpiCard(opts) {
    var card = el_('article', 'pilot-kpi' + (opts.lead ? ' pilot-kpi--lead' : ''));
    card.innerHTML =
      '<span class="pilot-kpi__icon">' + ICONS[opts.icon] + '</span>' +
      '<span class="pilot-kpi__label">' + esc(opts.label) + '</span>' +
      '<span class="pilot-kpi__value' + (opts.text ? ' is-text' : '') + '">' + esc(opts.value) + '</span>' +
      '<span class="pilot-kpi__note">' + esc(opts.note) + '</span>';
    return card;
  }

  function renderKpis() {
    var p = state.profile;
    var totals = p.totals;
    var driverAge = age(p.driver.dateOfBirth);

    el.kpiScope.textContent = totals.seasons
      ? 'Trayectoria acumulada hasta ' + year + ' (' + totals.seasons +
        (totals.seasons === 1 ? ' temporada disputada).' : ' temporadas disputadas).')
      : 'Sin temporadas disputadas hasta ' + year + '.';

    el.kpis.innerHTML = '';
    var frag = document.createDocumentFragment();

    frag.appendChild(kpiCard({
      icon: 'wins', label: 'Victorias',
      value: totals.wins === 0 ? '0' : String(totals.wins), lead: totals.wins > 0,
      note: 'Grandes Premios ganados (sin sprints)' + (totals.wins > 0 ? '' : ' · todavía sin victorias')
    }));
    frag.appendChild(kpiCard({
      icon: 'podiums', label: 'Podios',
      value: String(totals.podiums), lead: totals.podiums > 0,
      note: 'Resultados en el podio (1º a 3º)'
    }));
    frag.appendChild(kpiCard({
      icon: 'seasons', label: 'Temporadas',
      value: String(totals.seasons), lead: totals.seasons > 0,
      note: 'Temporadas con al menos un Gran Premio'
    }));
    frag.appendChild(kpiCard({
      icon: 'best', label: 'Mejor resultado',
      value: totals.best === null ? '—' : totals.best + 'º',
      note: 'Mejor posición final en carrera'
    }));
    frag.appendChild(kpiCard({
      icon: 'debut', label: 'Debut',
      value: p.debut === null ? '—' : String(p.debut),
      note: 'Primera temporada con carrera disputada'
    }));
    frag.appendChild(kpiCard({
      icon: 'age', label: 'Edad',
      value: driverAge === null ? '—' : String(driverAge),
      text: state.deathYear !== null,
      note: state.deathYear !== null
        ? 'Fallecido en ' + state.deathYear + ' · Nacido el ' + fmtDate(p.driver.dateOfBirth)
        : 'Nacido el ' + fmtDate(p.driver.dateOfBirth)
    }));

    el.kpis.appendChild(frag);

    // Si el año consultado es anterior al debut, aviso informativo
    if (p.debut !== null && year < p.debut) {
      var note = el_('div', 'tf-empty');
      note.innerHTML = '<span>' + esc('No hay datos de competición en ' + year +
        ': el debut de ' + (p.driver.familyName || 'este piloto') + ' fue en ' + p.debut + '.') + '</span>' +
        '<a class="tf-btn tf-btn--sm tf-mt-4" href="PageStatsPiloto.php?driverId=' + encodeURIComponent(state.driverId) +
        '&piloto=' + encodeURIComponent(pilotoNombre) + '&year=' + p.debut + '">Ver la temporada ' + p.debut + '</a>';
      el.kpis.appendChild(note);
    }
  }

  /* ------------------------------------------------------------- gráfico */
  function niceMax(value) {
    if (!value || value <= 0) return 1;
    var pow = Math.pow(10, Math.floor(Math.log10(value)));
    var candidates = [1, 2, 2.5, 5, 10];
    for (var i = 0; i < candidates.length; i++) {
      if (candidates[i] * pow >= value) return candidates[i] * pow;
    }
    return 10 * pow;
  }

  function metricValue(season, metric) {
    if (metric === 'points') {
      var pos = state.positions[season.year];
      if (pos && pos.points != null) {
        var parsed = numFloat(pos.points);
        return parsed === null ? null : parsed;
      }
      // Sin puntos oficiales: se usa la suma de puntos de carrera (sin sprint)
      return season.racePoints === undefined ? null : season.racePoints;
    }
    var data = state.positions[season.year];
    if (!data || data.unavailable || data.position === null) return null;
    return data.position;
  }

  function seasonPointsLabel(season) {
    var pos = state.positions[season.year];
    if (pos && pos.points != null) return { text: fmtPoints(pos.points), official: true };
    if (season.racePoints !== undefined) return { text: fmtPoints(season.racePoints), official: false };
    return { text: null, official: false };
  }

  function renderChart() {
    var p = state.profile;
    if (!p) return;
    var seasons = p.upto;

    el.chart.innerHTML = '';
    if (!seasons.length) {
      el.chart.appendChild(messageBox('Sin temporadas disputadas hasta ' + year + '.', 'empty'));
      return;
    }
    if (seasons.length === 1 && el.metricPosition.disabled) {
      // Con una sola temporada el gráfico sigue siendo válido (un punto)
    }

    var metric = state.metric;
    var points = seasons.map(function (s) {
      return { year: s.year, season: s, value: metricValue(s, metric) };
    });
    var valid = points.filter(function (p2) { return p2.value !== null; });

    var positionsPending = metric === 'position' && !state.positionsDone && valid.length < points.length;
    if (!valid.length) {
      el.chart.appendChild(positionsPending
        ? loading('Cargando posiciones finales…')
        : messageBox('No hay datos de ' + (metric === 'points' ? 'puntos' : 'posición') + ' para mostrar.', 'empty'));
      return;
    }

    // Lienzo: se mide el contenedor para que el texto no se escale.
    var svgNS = 'http://www.w3.org/2000/svg';
    var svg = document.createElementNS(svgNS, 'svg');
    svg.setAttribute('role', 'img');
    svg.setAttribute('aria-label',
      'Evolución de ' + (metric === 'points' ? 'puntos' : 'posición en el campeonato') + ' de ' +
      (p.driver.familyName || 'el piloto') + ' entre ' + seasons[0].year + ' y ' + seasons[seasons.length - 1].year + '.');
    el.chart.appendChild(svg);

    var width = Math.max(svg.clientWidth || el.chart.clientWidth || 640, 280);
    var height = width < 560 ? 230 : 300;
    var padL = 46, padR = 18, padT = 22, padB = 36;
    var innerW = Math.max(width - padL - padR, 40);
    var innerH = Math.max(height - padT - padB, 40);

    var values = valid.map(function (p2) { return p2.value; });
    var maxValue;
    var minValue;
    if (metric === 'points') {
      maxValue = niceMax(Math.max.apply(null, values));
      minValue = 0;
    } else {
      maxValue = Math.max.apply(null, values);
      minValue = 1;
    }

    function xAt(index) {
      if (seasons.length === 1) return padL + innerW / 2;
      return padL + (index * innerW) / (seasons.length - 1);
    }
    function yAt(value) {
      if (metric === 'points') return padT + innerH - (value / maxValue) * innerH;
      if (maxValue === minValue) return padT + innerH / 2;
      return padT + ((value - minValue) / (maxValue - minValue)) * innerH;
    }

    svg.setAttribute('viewBox', '0 0 ' + width + ' ' + height);
    svg.setAttribute('width', '100%');
    svg.setAttribute('height', height);

    // Rejilla y eje Y
    var tickCount = 4;
    var tickValues = [];
    if (metric === 'points') {
      for (var t = 0; t <= tickCount; t++) tickValues.push(maxValue * (1 - t / tickCount));
    } else {
      // Posición: valores enteros donde 1 es la mejor
      var step = Math.max(1, Math.ceil((maxValue - minValue) / tickCount));
      for (var v = minValue; v <= maxValue; v += step) tickValues.push(v);
      if (tickValues[tickValues.length - 1] !== maxValue) tickValues.push(maxValue);
    }

    tickValues.forEach(function (value, index) {
      var frac = tickValues.length > 1 ? index / (tickValues.length - 1) : 0;
      var y = padT + innerH * frac;
      var line = document.createElementNS(svgNS, 'line');
      line.setAttribute('class', 'pilot-chart__grid');
      line.setAttribute('x1', padL); line.setAttribute('x2', padL + innerW);
      line.setAttribute('y1', y); line.setAttribute('y2', y);
      svg.appendChild(line);

      var label = document.createElementNS(svgNS, 'text');
      label.setAttribute('class', 'pilot-chart__axis');
      label.setAttribute('x', padL - 8);
      label.setAttribute('y', y + 4);
      label.setAttribute('text-anchor', 'end');
      label.textContent = String(Math.round(value));
      svg.appendChild(label);
    });

    var axisTitle = document.createElementNS(svgNS, 'text');
    axisTitle.setAttribute('class', 'pilot-chart__axis pilot-chart__axis--title');
    axisTitle.setAttribute('x', padL);
    axisTitle.setAttribute('y', 12);
    axisTitle.textContent = metric === 'points' ? 'Puntos' : 'Posición (1 = mejor)';
    svg.appendChild(axisTitle);

    // Etiquetas del eje X (todas si son pocas; si no, cada N, evitando que la
    // última se solape con la anterior)
    var step = seasons.length > 14 ? Math.ceil(seasons.length / 8) : 1;
    var shownIndices = [];
    for (var k = 0; k < seasons.length; k += step) shownIndices.push(k);
    var lastIndex = seasons.length - 1;
    if (shownIndices[shownIndices.length - 1] !== lastIndex) {
      if (lastIndex - shownIndices[shownIndices.length - 1] < step) shownIndices.pop();
      shownIndices.push(lastIndex);
    }
    shownIndices.forEach(function (i) {
      var xLabel = document.createElementNS(svgNS, 'text');
      xLabel.setAttribute('class', 'pilot-chart__axis');
      xLabel.setAttribute('x', xAt(i));
      xLabel.setAttribute('y', height - 12);
      xLabel.setAttribute('text-anchor', 'middle');
      xLabel.textContent = String(seasons[i].year);
      svg.appendChild(xLabel);
    });

    // Segmentos (color del equipo de la temporada de destino; discontinuas si hay hueco)
    for (var i = 1; i < seasons.length; i++) {
      var a = points[i - 1];
      var b = points[i];
      if (a.value === null || b.value === null) continue;
      var segment = document.createElementNS(svgNS, 'line');
      var gap = seasons[i].year - seasons[i - 1].year > 1;
      segment.setAttribute('class', 'pilot-chart__segment' + (gap ? ' pilot-chart__segment--gap' : ''));
      segment.setAttribute('x1', xAt(i - 1)); segment.setAttribute('y1', yAt(a.value));
      segment.setAttribute('x2', xAt(i)); segment.setAttribute('y2', yAt(b.value));
      segment.setAttribute('stroke', seasons[i].colors[seasons[i].colors.length - 1] || NEUTRAL_COLOR);
      svg.appendChild(segment);
    }

    // Puntos con tooltip accesible
    var tip = el_('div', 'pilot-chart__tip');
    tip.setAttribute('role', 'status');
    var tooltipRows = function (season, value) {
      var rows = [];
      rows.push('<div class="pilot-chart__tip-row"><span>Temporada</span><b>' + season.year + '</b></div>');
      rows.push('<div class="pilot-chart__tip-row"><span>Escudería</span><b>' + esc(season.teams.join(' · ')) + '</b></div>');
      var pts = seasonPointsLabel(season);
      rows.push('<div class="pilot-chart__tip-row"><span>Puntos</span><b>' + (pts.text === null ? '—' : esc(pts.text)) + '</b></div>');
      var pos = state.positions[season.year];
      var positionText = pos && !pos.unavailable && pos.position !== null ? String(pos.position) + 'º' : '—';
      rows.push('<div class="pilot-chart__tip-row"><span>Posición</span><b>' + positionText + '</b></div>');
      return rows.join('');
    };

    points.forEach(function (point, index) {
      if (point.value === null) return;
      var circle = document.createElementNS(svgNS, 'circle');
      circle.setAttribute('class', 'pilot-chart__point');
      circle.setAttribute('cx', xAt(index));
      circle.setAttribute('cy', yAt(point.value));
      circle.setAttribute('r', 5);
      circle.setAttribute('fill', point.season.colors[point.season.colors.length - 1] || NEUTRAL_COLOR);
      circle.setAttribute('tabindex', '0');
      circle.setAttribute('aria-label', point.season.year + ': ' +
        (metric === 'points' ? (fmtPoints(point.value) || '—') + ' puntos' : 'posición ' + point.value) +
        ', ' + point.season.teams.join(' y '));
      var show = function () {
        tip.innerHTML = tooltipRows(point.season, point.value);
        tip.style.left = xAt(index) + 'px';
        tip.style.top = yAt(point.value) + 'px';
        tip.classList.add('is-visible');
      };
      var hide = function () { tip.classList.remove('is-visible'); };
      circle.addEventListener('pointerenter', show);
      circle.addEventListener('pointerleave', hide);
      circle.addEventListener('focus', show);
      circle.addEventListener('blur', hide);
      svg.appendChild(circle);
    });

    el.chart.appendChild(tip);
  }

  /* -------------------------------------------------------- trayectoria */
  function buildStints(seasons) {
    var stints = [];
    seasons.forEach(function (season) {
      var multi = season.teams.length > 1;
      season.teams.forEach(function (team, i) {
        var color = season.colors[i] || NEUTRAL_COLOR;
        var last = stints[stints.length - 1];
        // Se une a la etapa anterior solo si es el mismo equipo y el año es
        // consecutivo Y el equipo es el primero de esa temporada. Los períodos
        // discontinuos y los cambios a mitad de temporada no se agrupan.
        var canMerge = last && i === 0 &&
          last.team === team && last.endYear === season.year - 1;
        if (canMerge) {
          last.endYear = season.year;
          last.years.push(season.year);
          last.races += season.races;
          return;
        }
        stints.push({
          team: team, color: color, startYear: season.year, endYear: season.year,
          years: [season.year], races: season.races, midSeason: multi
        });
      });
    });
    return stints;
  }

  function renderStints() {
    var p = state.profile;
    var stints = buildStints(p.upto);

    el.stints.innerHTML = '';
    if (!stints.length) {
      el.stints.appendChild(messageBox('Sin escuderías registradas hasta ' + year + '.', 'empty'));
      return;
    }

    el.stintsSub.textContent = stints.length === 1
      ? 'Un único equipo en su trayectoria hasta ' + year + '.'
      : 'Equipos con los que ha competido hasta ' + year + ', en orden cronológico.';

    var frag = document.createDocumentFragment();
    stints.forEach(function (stint, index) {
      var card = el_('article', 'pilot-stint');
      applyTeamColor(card, stint.color);
      var years = stint.startYear === stint.endYear
        ? String(stint.startYear)
        : stint.startYear + ' – ' + stint.endYear;
      card.innerHTML =
        '<span class="pilot-stint__order">Etapa ' + (index + 1) + '</span>' +
        '<span class="pilot-stint__team">' + esc(stint.team) + '</span>' +
        '<span class="pilot-stint__years">' + years + (stint.midSeason ? ' · llegada a mitad de temporada' : '') + '</span>' +
        '<span class="pilot-stint__meta">' + stint.years.length + (stint.years.length === 1 ? ' temporada' : ' temporadas') +
        ' · ' + stint.races + (stint.races === 1 ? ' GP' : ' GPs') + '</span>';
      frag.appendChild(card);
    });
    el.stints.appendChild(frag);
  }

  /* ---------------------------------------------------- tabla de resultados */
  function pointsCell(season) {
    var label = seasonPointsLabel(season);
    if (label.text !== null) {
      return label.official
        ? '<span title="Puntos oficiales del campeonato">' + esc(label.text) + '</span>'
        : '<span title="Puntos de carrera (sin sprints): no se pudo consultar la clasificación oficial">' +
          esc(label.text) + '<sup aria-hidden="true">*</sup></span>';
    }
    if (!state.positionsDone) return '<span class="pilot-table__dash" title="Cargando">…</span>';
    return '<span class="pilot-table__dash" title="Dato no disponible">—</span>';
  }

  function positionCell(season) {
    var data = state.positions[season.year];
    if (data && !data.unavailable) {
      return data.position === null
        ? '<span class="pilot-table__dash">—</span>'
        : '<span class="pilot-table__pos">' + data.position + 'º</span>';
    }
    if (!state.positionsDone) return '<span class="pilot-table__dash" title="Cargando">…</span>';
    return '<span class="pilot-table__dash" title="Dato no disponible">—</span>';
  }

  function renderTable() {
    var p = state.profile;
    var seasons = p.upto.slice().sort(function (a, b) {
      return state.sortAsc ? a.year - b.year : b.year - a.year;
    });

    el.resultsBody.innerHTML = '';
    if (!seasons.length) {
      var row = el_('tr', 'tf-table__empty');
      row.innerHTML = '<td colspan="7">Sin resultados hasta ' + year + '.</td>';
      el.resultsBody.appendChild(row);
      return;
    }

    var frag = document.createDocumentFragment();
    seasons.forEach(function (season) {
      var tr = el_('tr', season.year === year ? 'pilot-table__row--current' : '');
      var teams = season.teams.map(function (name, i) {
        return '<span class="pilot-table__team"><span class="pilot-dot" style="--pilot-color:' +
          esc(season.colors[i] || NEUTRAL_COLOR) + '" aria-hidden="true"></span>' + esc(name) + '</span>';
      }).join('');
      tr.innerHTML =
        '<td class="pilot-table__year">' + season.year + '</td>' +
        '<td><span class="pilot-table__teams">' + (teams || '<span class="pilot-table__dash">—</span>') + '</span></td>' +
        '<td class="tf-td-right tf-td-num">' + season.races + '</td>' +
        '<td class="tf-td-right tf-td-num">' + season.wins + '</td>' +
        '<td class="tf-td-right tf-td-num">' + season.podiums + '</td>' +
        '<td class="tf-td-right tf-td-num">' + pointsCell(season) + '</td>' +
        '<td class="tf-td-right tf-td-num">' + positionCell(season) + '</td>';
      frag.appendChild(tr);
    });
    el.resultsBody.appendChild(frag);
  }

  /* ------------------------------------------------------------- carga */
  function resolveDriverId() {
    if (driverIdParam) return Promise.resolve(driverIdParam);

    // Sin driverId: se busca por nombre en la clasificación del año y, si no,
    // recorriendo los resultados de la temporada (paginado).
    return fetchJson(API + '/' + year + '/driverStandings.json?limit=' + PAGE_LIMIT)
      .then(function (data) {
        var list = data.MRData && data.MRData.StandingsTable && data.MRData.StandingsTable.StandingsLists;
        var standings = (list && list[0] && list[0].DriverStandings) || [];
        for (var i = 0; i < standings.length; i++) {
          var driver = standings[i].Driver;
          if ((driver.givenName + ' ' + driver.familyName) === pilotoNombre) return driver.driverId;
        }
        return null;
      })
      .catch(function () { return null; })
      .then(function (found) {
        if (found) return found;
        return fetchAllRaces(API + '/' + year + '/results.json').then(function (races) {
          for (var r = 0; r < races.length; r++) {
            var results = races[r].Results || [];
            for (var k = 0; k < results.length; k++) {
              var full = results[k].Driver.givenName + ' ' + results[k].Driver.familyName;
              if (full === pilotoNombre) return results[k].Driver.driverId;
            }
          }
          return '';
        });
      });
  }

  function failAll(message) {
    el.title.textContent = pilotoNombre || 'Piloto no encontrado';
    el.sub.textContent = message;
    el.hero.removeAttribute('style');
    el.kpis.innerHTML = '';
    el.kpis.appendChild(messageBox(message, 'empty'));
    el.chart.innerHTML = '';
    el.chart.appendChild(messageBox('Sin datos de evolución.', 'empty'));
    el.stints.innerHTML = '';
    el.stints.appendChild(messageBox('Sin datos de trayectoria.', 'empty'));
    el.resultsBody.innerHTML = '<tr class="tf-table__empty"><td colspan="7">' + esc(message) + '</td></tr>';
  }

  function load() {
    state.positions = {};
    state.positionsDone = false;

    resolveDriverId()
      .then(function (id) {
        if (!id) {
          failAll('No se encontró información de este piloto para la temporada ' + year + '.');
          return null;
        }
        state.driverId = id;
        state.positions = readPositionsCache();
        el.volver.href = '/index.html/PagePilotos.php?year=' + year;
        return fetchJson(API + '/drivers/' + id + '.json');
      })
      .then(function (info) {
        if (!info) return null;
        var driver = info.MRData && info.MRData.DriverTable && info.MRData.DriverTable.Drivers[0];
        if (!driver) {
          failAll('No se encontró la ficha del piloto.');
          return null;
        }

        // La cabecera se pinta en cuanto llega la ficha (sin esperar a la
        // trayectoria) y la foto/fallecimiento se resuelven en paralelo.
        state.profile = {
          driver: driver, seasons: [], upto: [], totals: { seasons: 0, races: 0, wins: 0, podiums: 0, best: null, teams: [] },
          debut: null, lastSeason: null, selected: null
        };
        renderHeader();
        loadPhotoAndDeath(driver).then(function () { if (state.profile) renderKpis(); });

        return fetchAllRaces(API + '/drivers/' + state.driverId + '/results.json').then(function (races) {
          // Puntos de carrera por temporada (sin sprints) como respaldo informativo
          state.profile = buildProfile(driver, races);
          state.profile.upto.forEach(function (s) {
            var sum = 0;
            races.forEach(function (race) {
              if (race.season !== String(s.year)) return;
              (race.Results || []).forEach(function (r) { sum += numFloat(r.points) || 0; });
            });
            s.racePoints = Math.round(sum * 100) / 100;
          });

          renderHeader();
          renderKpis();
          renderChart();
          renderStints();
          renderTable();

          var years = state.profile.upto.map(function (s) { return s.year; });
          return loadPositions(years);
        });
      })
      .catch(function (error) {
        console.error(error);
        if (!state.profile) {
          // La API no respondió: se mantiene la identidad del enlace y se
          // ofrece reintentar en lugar de mostrar cifras vacías.
          el.title.textContent = pilotoNombre || 'Piloto';
          el.sub.textContent = 'No se pudieron cargar los datos del piloto.';
          el.season.textContent = year;
          el.initials.textContent = initials(
            (pilotoNombre.split(' ')[0] || ''), (pilotoNombre.split(' ').slice(-1)[0] || '')
          );
        }
        el.kpis.innerHTML = '';
        el.kpis.appendChild(errorBox('No se pudieron cargar los datos del piloto.', load));
        el.chart.innerHTML = '';
        el.chart.appendChild(messageBox('Sin datos de evolución.', 'error'));
        el.stints.innerHTML = '';
        el.stints.appendChild(messageBox('Sin datos de trayectoria.', 'error'));
        el.resultsBody.innerHTML = '<tr class="tf-table__empty"><td colspan="7">Error al cargar los resultados.</td></tr>';
      });
  }

  /* ------------------------------------------------------------ eventos */
  function setMetric(metric) {
    state.metric = metric;
    var isPoints = metric === 'points';
    el.metricPoints.classList.toggle('is-active', isPoints);
    el.metricPosition.classList.toggle('is-active', !isPoints);
    el.metricPoints.setAttribute('aria-pressed', isPoints ? 'true' : 'false');
    el.metricPosition.setAttribute('aria-pressed', isPoints ? 'false' : 'true');
    el.evolutionSub.textContent = isPoints
      ? 'Puntos oficiales del campeonato en cada temporada disputada.'
      : 'Posición final en el campeonato (1 es la mejor).';
    renderChart();
  }

  el.metricPoints.addEventListener('click', function () { setMetric('points'); });
  el.metricPosition.addEventListener('click', function () { setMetric('position'); });

  el.sortYear.addEventListener('click', function () {
    state.sortAsc = !state.sortAsc;
    el.thYear.setAttribute('aria-sort', state.sortAsc ? 'ascending' : 'descending');
    el.sortYear.innerHTML = 'Temporada <span aria-hidden="true">' + (state.sortAsc ? '&#8593;' : '&#8595;') + '</span>';
    renderTable();
  });

  var resizeTimer = null;
  window.addEventListener('resize', function () {
    if (resizeTimer) window.clearTimeout(resizeTimer);
    resizeTimer = window.setTimeout(function () {
      if (state.profile) renderChart();
    }, 180);
  });

  el.evolutionSub.textContent = 'Puntos oficiales del campeonato en cada temporada disputada.';
  el.resultsSub.textContent = 'Detalle temporada a temporada hasta ' + year + '.';
  el.volver.href = '/index.html/PagePilotos.php?year=' + year;
  el.season.textContent = year;
  load();
})();
