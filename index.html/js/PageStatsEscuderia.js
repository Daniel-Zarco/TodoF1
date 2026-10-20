/* ============================================================
   TodoF1 — Ficha individual de la escudería
   ------------------------------------------------------------
   PROCEDENCIA Y DEFINICIÓN DE CADA MÉTRICA (auditado):
     · Identidad (nombre y nacionalidad): /{año}/constructors.json
       El identificador estable de la historia es el constructorId; el
       nombre visible de la URL se resuelve a él y nunca se usa como
       identificador histórico.
     · Clasificación OFICIAL de constructores (posición, puntos y
       victorias) de la temporada consultada y de cada temporada del
       historial: /{año}/constructors/{id}/constructorStandings.json
       (No existió hasta 1958: en 1950-1957 no había Mundial.)
     · Resultados de carrera del constructor: los endpoints
       /{año}/constructors/{id}/results.json (temporada) y
       /constructors/{id}/results.json (historial paginado) devuelven
       resultados de CARRERA: no incluyen sprints, por lo que victorias
       y podios son de Gran Premio.
     · Estadísticas del piloto en la temporada consultada: posición y
       puntos oficiales con
       /{año}/drivers/{driverId}/driverStandings.json
     · Fotografía del piloto: API de Wikipedia, con la misma estrategia
       y fallback que la página de Pilotos.

   AUDITORÍA (discrepancia detectada y corregida):
     La ficha anterior calculaba los puntos SUMANDO los puntos de los
     resultados de carrera (que excluyen los sprints) y contaba las
     "carreras" desde el calendario completo, incluidas las pruebas no
     disputadas. En Mercedes 2026 eso daba 513 puntos y 23 carreras.
     Comprobado contra la API: 513 son los puntos de carrera y los 65
     restantes hasta los 578 oficiales son puntos de sprint; los GP
     realmente disputados son 17. La ficha ahora usa SIEMPRE la
     clasificación oficial para puntos y posición, y los resultados de
     carrera para GP, victorias, podios y pilotos.

   PUNTOS Y POSICIÓN: nunca se sustituyen por la suma de resultados
   individuales. Al antes de 1958 se muestra "—" con explicación.
   ============================================================ */
(function () {
  'use strict';

  var API = 'https://api.jolpi.ca/ergast/f1';
  var WIKI_API = 'https://en.wikipedia.org/w/api.php';
  var PHOTO_SIZE = 480;
  var PAGE_LIMIT = 100;
  var MAX_PAGES = 40;
  var MIN_YEAR = 1950;
  var LATEST_YEAR = 2026;
  var CHAMPIONSHIP_START = 1958;   // primer Mundial de Constructores
  var NEUTRAL_COLOR = '#55555E';
  var CONCURRENCY = 2;             // peticiones simultáneas de clasificación
  var MIN_GAP = 260;               // separación mínima entre peticiones a la API
  var HISTORY_CACHE = 'todof1:escuderia-hist-v2:';
  var CACHE_TTL = 20 * 60 * 1000;  // 20 minutos
  var RETRY_DELAYS = [1500, 3500, 7000]; // reintentos ante 429 / fallos de red

  /* Colores históricos ya definidos en el proyecto (PagePilotos.js /
     PageEscuderias.js). No se inventan colores: sin correspondencia
     fiable se usa el neutro. El color identifica al equipo; la interfaz
     general usa el tema activo (--team-*). */
  var HISTORIC_COLORS = {
    'lotus': '#FFB800',
    'toro rosso': '#4E7C9B',
    'force india': '#F480B5',
    'benetton': '#00A551',
    'brabham': '#FFCC00',
    'tyrrell': '#003399'
  };

  /* Punto de extensión para logotipos verificados (constructorId → src).
     Está vacío a propósito: el proyecto no dispone de escudos con
     procedencia y licencia comprobadas, y no se incorporan imágenes de
     terceros sin verificar. Mientras esté vacío se usa el monograma. */
  var LOGO_IMAGES = {};

  var params = new URLSearchParams(window.location.search);
  var escuderia = (params.get('escuderia') || '').trim();
  var constructorIdParam = (params.get('constructorId') || '').trim();
  var year = parseInt(params.get('year') || String(LATEST_YEAR), 10);
  if (!year || year < MIN_YEAR || year > 2100) year = LATEST_YEAR;

  var el = {
    volver: document.getElementById('btn-volver'),
    hero: document.getElementById('team-hero'),
    crest: document.getElementById('team-crest'),
    mono: document.getElementById('team-mono'),
    eyebrow: document.getElementById('team-eyebrow'),
    title: document.getElementById('titulo'),
    sub: document.getElementById('team-sub'),
    tags: document.getElementById('team-tags'),
    side: document.getElementById('team-side'),
    kpiScope: document.getElementById('kpi-scope'),
    kpis: document.getElementById('team-kpis'),
    chart: document.getElementById('team-chart'),
    evolutionSub: document.getElementById('evolution-sub'),
    evolutionNote: document.getElementById('evolution-note'),
    metricPoints: document.getElementById('metric-points'),
    metricPosition: document.getElementById('metric-position'),
    drivers: document.getElementById('team-drivers'),
    driversSub: document.getElementById('drivers-sub'),
    honours: document.getElementById('team-honours'),
    honoursSub: document.getElementById('honours-sub'),
    resultsSub: document.getElementById('results-sub'),
    resultsBody: document.getElementById('team-results'),
    sortYear: document.getElementById('sort-year'),
    thYear: document.getElementById('th-year')
  };

  var state = {
    constructorId: '',
    name: escuderia,
    nationality: '',
    color: NEUTRAL_COLOR,
    season: null,          // { gp, wins, podiums, drivers, position, points, available }
    provisional: false,
    history: null,         // { results:{}, standings:{}, resultsDone, standingsDone }
    driverStats: {},       // { [driverId]: { position, points } | { unavailable:true } }
    metric: 'points',
    sortAsc: false,
    finished: {}           // { [year]: true|false } temporada finalizada (verificado)
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

  /* Monograma del escudo: iniciales de las palabras (máx. 3). */
  function monogram(name) {
    var words = String(name || '').trim().split(/\s+/).filter(Boolean);
    var letters = words.map(function (w) { return w.charAt(0); });
    if (letters.length > 1) return letters.slice(0, 3).join('').toUpperCase();
    return String(name || 'F1').slice(0, 3).toUpperCase();
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

  /* Color del equipo (identidad). El resto de la interfaz usa el tema. */
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

  function el_(tag, className, html) {
    var node = document.createElement(tag);
    if (className) node.className = className;
    if (html != null) node.innerHTML = html;
    return node;
  }

  function loading(message) {
    if (window.TodoF1Loader) return window.TodoF1Loader.create(message);
    return el_('div', 'tf-loading', '<span class="tf-spinner" aria-hidden="true"></span><span>' + esc(message) + '</span>');
  }

  function messageBox(message, type) {
    return el_('div', 'tf-' + (type || 'empty'), '<span>' + esc(message) + '</span>');
  }

  function errorBox(message, onRetry) {
    var box = el_('div', 'tf-error', '<span>' + esc(message) + '</span>');
    if (onRetry) {
      var btn = el_('button', 'tf-btn tf-btn--sm tf-mt-4', 'Reintentar');
      btn.type = 'button';
      btn.addEventListener('click', onRetry);
      box.appendChild(btn);
    }
    return box;
  }

  /* --------------------------------------------------------- red (con 429) */
  /* Puerta de espaciado: evita ráfagas contra la API (Jolpica limita el ritmo
     y responde 429 o corta la conexión). Solo se aplica a la API de datos. */
  var lastRequestAt = 0;
  function apiGate(url) {
    if (String(url).indexOf(API) !== 0) return Promise.resolve();
    var now = Date.now();
    var wait = Math.max(0, lastRequestAt + MIN_GAP - now);
    lastRequestAt = now + wait;
    if (!wait) return Promise.resolve();
    return new Promise(function (resolve) { window.setTimeout(resolve, wait); });
  }

  function fetchJson(url, attempt) {
    attempt = attempt || 0;
    return apiGate(url).then(function () { return fetch(url); }).then(function (response) {
      if (!response.ok) {
        var error = new Error('HTTP ' + response.status);
        error.status = response.status;
        var retryAfter = parseInt(response.headers.get('Retry-After') || '', 10);
        if (!isNaN(retryAfter)) error.retryAfter = retryAfter;
        throw error;
      }
      return response.json();
    }).catch(function (error) {
      // Se reintenta ante límite de peticiones, errores del servidor o fallos
      // de red (una respuesta limitada puede llegar sin cabeceras CORS).
      var retryable = !error.status || error.status === 429 || error.status >= 500;
      if (retryable && attempt < RETRY_DELAYS.length) {
        var wait = error.retryAfter ? Math.min(error.retryAfter, 8) * 1000 : RETRY_DELAYS[attempt];
        return new Promise(function (resolve) {
          window.setTimeout(function () { resolve(fetchJson(url, attempt + 1)); }, wait);
        });
      }
      throw error;
    });
  }

  /* Ejecuta tareas con concurrencia limitada (evita ráfagas que disparen el 429). */
  function runQueue(items, limit, worker) {
    var index = 0;
    function next() {
      if (index >= items.length) return Promise.resolve();
      var item = items[index++];
      return worker(item).catch(function () { /* cada tarea gestiona su error */ }).then(next);
    }
    var runners = [];
    var slots = Math.min(limit, items.length);
    for (var i = 0; i < slots; i++) runners.push(next());
    return Promise.all(runners);
  }

  /* Páginas de resultados. `stopAfterYear` corta el recorrido cuando aparece una
     temporada posterior a la consultada (la API devuelve en orden ascendente). */
  function fetchPagedRaces(baseUrl, stopAfterYear) {
    var all = [];
    var offset = 0;
    var total = Infinity;
    var pages = 0;

    function step() {
      var separator = baseUrl.indexOf('?') === -1 ? '?' : '&';
      return fetchJson(baseUrl + separator + 'limit=' + PAGE_LIMIT + '&offset=' + offset).then(function (data) {
        var meta = (data && data.MRData) || {};
        var races = (meta.RaceTable && meta.RaceTable.Races) || [];
        var count = num(meta.total);
        if (count !== null) total = count;

        var reached = false;
        races.forEach(function (race) {
          var seasonYear = parseInt(race.season, 10);
          if (stopAfterYear && seasonYear > stopAfterYear) { reached = true; return; }
          all.push(race);
        });

        offset += PAGE_LIMIT;
        pages++;
        if (reached || !races.length || offset >= total || pages >= MAX_PAGES) return all;
        return step();
      });
    }

    return step();
  }

  /* Igual que fetchPagedRaces, con reintentos completos ante un corte de la API
     (una pasada de resultados puede fallar a mitad por límite de peticiones). */
  function fetchPagedRacesRetry(baseUrl, stopAfterYear, attempt) {
    attempt = attempt || 0;
    return fetchPagedRaces(baseUrl, stopAfterYear).catch(function (error) {
      if (attempt >= 2) throw error;
      return new Promise(function (resolve) { window.setTimeout(resolve, 3000); })
        .then(function () { return fetchPagedRacesRetry(baseUrl, stopAfterYear, attempt + 1); });
    });
  }

  /* Resumen por temporada a partir de los resultados de carrera del constructor. */
  function summarizeSeasons(races) {
    var byYear = {};

    races.forEach(function (race) {
      var seasonYear = parseInt(race.season, 10);
      var season = byYear[seasonYear] ||
        (byYear[seasonYear] = { year: seasonYear, gp: 0, wins: 0, podiums: 0, drivers: {}, order: [], rounds: {}, winRounds: {}, podiumKeys: {} });

      // GP disputados: una vez por ronda, no por resultado individual.
      if (!season.rounds[race.round]) { season.rounds[race.round] = 1; season.gp++; }

      (race.Results || []).forEach(function (result) {
        var position = num(result.position);
        // Victorias por Gran Premio (una sola vez por carrera, aunque el coche
        // lo compartiesen dos pilotos) y sin sprints (no están en este endpoint).
        if (position === 1 && !season.winRounds[race.round]) { season.winRounds[race.round] = 1; season.wins++; }
        // Podios: un resultado por (ronda + posición) evita duplicar coches compartidos.
        if (position !== null && position <= 3) {
          var key = race.round + ':' + position;
          if (!season.podiumKeys[key]) { season.podiumKeys[key] = 1; season.podiums++; }
        }

        var info = result.Driver || {};
        if (!info.driverId) return;
        var driver = season.drivers[info.driverId];
        if (!driver) {
          driver = season.drivers[info.driverId] = {
            id: info.driverId,
            given: info.givenName || '',
            family: info.familyName || '',
            code: info.code || '',
            nationality: info.nationality || '',
            url: info.url || '',
            numbers: {}
          };
          season.order.push(info.driverId);
        }
        var carNumber = result.number == null ? '' : String(result.number).trim();
        if (carNumber) driver.numbers[carNumber] = (driver.numbers[carNumber] || 0) + 1;
      });
    });

    Object.keys(byYear).forEach(function (key) {
      var season = byYear[key];
      season.drivers = season.order.map(function (id) {
        var driver = season.drivers[id];
        var numbers = Object.keys(driver.numbers);
        // Dorsal de la temporada: solo si usó un único número (no se elige al azar).
        driver.number = numbers.length === 1 ? numbers[0] : null;
        delete driver.numbers;
        return driver;
      }).sort(function (a, b) {
        return (a.family + ' ' + a.given).localeCompare(b.family + ' ' + b.given, 'es');
      });
      delete season.rounds; delete season.winRounds; delete season.podiumKeys; delete season.order;
    });

    return byYear;
  }

  /* Clasificación oficial de una temporada. Devuelve:
     { position, points, wins } · { unavailable:true } · null si falló. */
  function fetchSeasonStanding(seasonYear) {
    return fetchJson(API + '/' + seasonYear + '/constructors/' + state.constructorId + '/constructorStandings.json')
      .then(function (data) {
        var lists = data && data.MRData && data.MRData.StandingsTable && data.MRData.StandingsTable.StandingsLists;
        var entry = lists && lists[0] && lists[0].ConstructorStandings && lists[0].ConstructorStandings[0];
        if (!entry) return { unavailable: true };
        return {
          position: num(entry.position),
          points: entry.points == null ? null : String(entry.points),
          wins: num(entry.wins)
        };
      })
      .catch(function () { return null; });   // fallo: no se cachea
  }

  /* Resuelve el constructorId a partir del nombre visible de la URL. */
  function resolveConstructor() {
    if (constructorIdParam) {
      return fetchJson(API + '/constructors/' + encodeURIComponent(constructorIdParam) + '.json')
        .then(function (data) {
          var list = (data && data.MRData && data.MRData.ConstructorTable && data.MRData.ConstructorTable.Constructors) || [];
          if (list.length) {
            return { id: list[0].constructorId, name: list[0].name || escuderia, nationality: list[0].nationality || '' };
          }
          return { id: constructorIdParam, name: escuderia, nationality: '' };
        })
        .catch(function () { return { id: constructorIdParam, name: escuderia, nationality: '' }; });
    }

    var wanted = norm(escuderia);
    function pick(list) {
      var exact = null, partial = null;
      (list || []).forEach(function (c) {
        var n = norm(c.name);
        if (n === wanted && !exact) exact = c;
        else if (!partial && wanted && n.indexOf(wanted) !== -1) partial = c;
      });
      return exact || partial;
    }

    // 1) Constructores de la temporada consultada (rápido y suficiente casi siempre).
    return fetchJson(API + '/' + year + '/constructors.json?limit=' + PAGE_LIMIT)
      .then(function (data) {
        var list = (data && data.MRData && data.MRData.ConstructorTable && data.MRData.ConstructorTable.Constructors) || [];
        return pick(list);
      })
      .catch(function () { return null; })
      .then(function (found) {
        if (found) return { id: found.constructorId, name: found.name || escuderia, nationality: found.nationality || '' };
        // 2) Lista completa de constructores (histórica), paginada.
        return fetchJson(API + '/constructors.json?limit=' + PAGE_LIMIT)
          .then(function (data) {
            var list = (data && data.MRData && data.MRData.ConstructorTable && data.MRData.ConstructorTable.Constructors) || [];
            var match = pick(list);
            return match ? { id: match.constructorId, name: match.name || escuderia, nationality: match.nationality || '' } : null;
          })
          .catch(function () { return null; });
      });
  }

  /* ------------------------------------------------------------- cabecera */
  function renderHeader() {
    el.eyebrow.textContent = 'Escudería · Temporada ' + year;
    el.title.textContent = state.name || escuderia || 'Escudería';
    document.title = (state.name ? state.name + ' — ' : '') + 'TodoF1';

    el.hero.style.setProperty('--team-color', state.color);
    el.hero.style.setProperty('--team-soft', rgba(state.color, 0.16));
    el.hero.style.setProperty('--team-ink', onColor(state.color));

    // Escudo: logotipo verificado si existe; si no, monograma deportivo.
    var logo = LOGO_IMAGES[state.constructorId];
    el.crest.innerHTML = logo
      ? '<img class="team-hero__logo" src="' + esc(logo) + '" alt="Logotipo de ' + esc(state.name) + '">'
      : '<span class="team-hero__mono" id="team-mono">' + esc(monogram(state.name)) + '</span>' +
        '<span class="team-hero__crest-line" aria-hidden="true"></span>';

    var season = state.season;
    var sub = '';
    if (season && season.available) {
      sub = 'Participación en la temporada ' + year + ' del Campeonato Mundial de Fórmula 1.';
    } else if (season) {
      sub = 'Sin participación registrada en la temporada ' + year + '.';
    } else {
      sub = 'Cargando información de la escudería…';
    }
    el.sub.textContent = sub;

    var tags = [];
    if (state.nationality) tags.push(esc(state.nationality));
    if (state.history && state.history.resultsDone) {
      var years = Object.keys(state.history.results).map(Number).filter(function (y) { return y <= year; }).sort(function (a, b) { return a - b; });
      if (years.length) {
        tags.push(years.length + (years.length === 1 ? ' temporada' : ' temporadas') + ' hasta ' + year);
        tags.push('Debut ' + years[0]);
      }
    }
    el.tags.innerHTML = tags.map(function (text) {
      return '<li class="team-hero__tag">' + esc(text) + '</li>';
    }).join('');

    renderSide();
  }

  function renderSide() {
    var season = state.season;
    if (!season) {
      el.side.innerHTML = '<div class="team-hero__stat"><span>Temporada</span><b>' + year + '</b></div>';
      return;
    }
    var positionText = season.position !== null ? season.position + 'º' : '—';
    var pointsText = season.points !== null ? fmtPoints(season.points) : '—';
    var provisional = state.provisional && season.position !== null;
    el.side.innerHTML =
      '<div class="team-hero__stat' + (provisional ? ' is-provisional' : '') + '">' +
        '<span>Mundial de Constructores</span>' +
        '<b>' + esc(positionText) + '</b>' +
        (provisional ? '<small>provisional</small>' : '') +
      '</div>' +
      '<div class="team-hero__stat">' +
        '<span>Puntos ' + year + '</span>' +
        '<b>' + esc(pointsText) + '</b>' +
      '</div>';
  }

  /* ---------------------------------------------------------- estadísticas */
  var ICONS = {
    gp: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 3.5v17"></path><path d="M6 4.5h12l-2.4 4L18 12.5H6"></path></svg>',
    wins: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 4h8v5a4 4 0 0 1-8 0Z"></path><path d="M8 5H5.5A2.5 2.5 0 0 0 8 9.5"></path><path d="M16 5h2.5A2.5 2.5 0 0 1 16 9.5"></path><path d="M12 13v4"></path><path d="M8.5 20h7"></path><path d="M10 17h4l.8 3h-5.6Z"></path></svg>',
    podiums: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 20h16"></path><path d="M9 20v-8"></path><path d="M15 20V8"></path><path d="M12 20v-5"></path><path d="M6 12l3-2 3 2 3-4 3 2" opacity=".55"></path></svg>',
    points: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 19.5h16"></path><path d="M7 19.5V12"></path><path d="M12 19.5V6.5"></path><path d="M17 19.5v-9"></path></svg>',
    championship: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="15" r="5.5"></circle><path d="M12 12.6l.9 1.9 2.1.3-1.5 1.5.4 2.1-1.9-1-1.9 1 .4-2.1L9 14.8l2.1-.3Z"></path><path d="M8.5 9.4 6 3.5h12L15.5 9.4"></path></svg>',
    trophy: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 4h8v5a4 4 0 0 1-8 0Z"></path><path d="M8 5H5.5A2.5 2.5 0 0 0 8 9.5"></path><path d="M16 5h2.5A2.5 2.5 0 0 1 16 9.5"></path><path d="M12 13v3"></path><path d="M9 20h6"></path><path d="M10 16h4l.7 4h-5.4Z"></path></svg>'
  };

  function kpiCard(opts) {
    var card = el_('article', 'team-kpi' + (opts.lead ? ' team-kpi--lead' : ''));
    card.innerHTML =
      '<span class="team-kpi__icon">' + ICONS[opts.icon] + '</span>' +
      '<span class="team-kpi__label">' + esc(opts.label) + '</span>' +
      '<span class="team-kpi__value' + (opts.text ? ' is-text' : '') + '">' + esc(opts.value) + '</span>' +
      '<span class="team-kpi__note">' + esc(opts.note) + '</span>';
    return card;
  }

  function renderKpis() {
    var season = state.season;
    if (!season) return;

    el.kpiScope.textContent = season.available
      ? 'Rendimiento de ' + state.name + ' en la temporada ' + year + '.'
      : 'Sin participación registrada en la temporada ' + year + '.';

    el.kpis.innerHTML = '';
    var frag = document.createDocumentFragment();

    frag.appendChild(kpiCard({
      icon: 'gp', label: 'Grandes Premios',
      value: season.available ? String(season.gp) : '—',
      lead: false,
      note: 'Grandes Premios disputados en ' + year
    }));
    frag.appendChild(kpiCard({
      icon: 'wins', label: 'Victorias',
      value: season.available ? String(season.wins) : '—',
      lead: season.available && season.wins > 0,
      note: 'Grandes Premios ganados (sin sprints)'
    }));
    frag.appendChild(kpiCard({
      icon: 'podiums', label: 'Podios',
      value: season.available ? String(season.podiums) : '—',
      lead: season.available && season.podiums > 0,
      note: 'Resultados en el podio (1º a 3º)'
    }));
    frag.appendChild(kpiCard({
      icon: 'points', label: 'Puntos oficiales',
      value: season.points !== null ? fmtPoints(season.points) : '—',
      lead: season.points !== null && numFloat(season.points) > 0,
      note: season.points !== null
        ? 'Clasificación oficial de constructores' + (state.provisional ? ' · provisional' : '')
        : 'Clasificación oficial no disponible en ' + year
    }));
    frag.appendChild(kpiCard({
      icon: 'championship', label: 'Mundial de Constructores',
      value: season.position !== null ? season.position + 'º' : '—',
      text: season.position === null,
      lead: season.position === 1,
      note: season.position !== null
        ? (state.provisional ? 'Posición provisional en el campeonato' : 'Posición final en el campeonato')
        : (year < CHAMPIONSHIP_START
          ? 'Todavía no existía el Mundial de Constructores'
          : 'Sin clasificación oficial en ' + year)
    }));

    el.kpis.appendChild(frag);
  }

  /* -------------------------------------------------------------- gráfico */
  function niceMax(value) {
    if (!value || value <= 0) return 1;
    var pow = Math.pow(10, Math.floor(Math.log10(value)));
    var candidates = [1, 2, 2.5, 5, 10];
    for (var i = 0; i < candidates.length; i++) {
      if (candidates[i] * pow >= value) return candidates[i] * pow;
    }
    return 10 * pow;
  }

  function seasonStanding(y) {
    if (!state.history) return undefined;
    return state.history.standings[y];
  }

  function isChampion(y) {
    var data = seasonStanding(y);
    if (!data || data.unavailable || data.position !== 1) return false;
    return seasonFinished(y);
  }

  function seasonFinished(y) {
    if (y < LATEST_YEAR) return true;
    return state.finished[y] === true;
  }

  function checkSeasonFinished(y) {
    if (state.finished[y] !== undefined) return Promise.resolve(state.finished[y]);
    return fetchJson(API + '/' + y + '.json').then(function (data) {
      var races = (data && data.MRData && data.MRData.RaceTable && data.MRData.RaceTable.Races) || [];
      var last = races.length ? races[races.length - 1] : null;
      state.finished[y] = !!(last && last.date && new Date(last.date + 'T23:59:59Z') < new Date());
      return state.finished[y];
    }).catch(function () {
      state.finished[y] = false;   // sin calendario fiable no se destaca
      return false;
    });
  }

  function metricValue(season, metric) {
    var data = seasonStanding(season.year);
    if (!data || data.unavailable) return null;
    if (metric === 'points') {
      if (data.points === null || data.points === undefined) return null;
      return numFloat(data.points);
    }
    return data.position;
  }

  function renderChart() {
    var history = state.history;
    el.chart.innerHTML = '';
    if (!history) {
      el.chart.appendChild(loading('Cargando evolución…'));
      return;
    }
    if (history.failed) {
      el.chart.appendChild(errorBox('No se pudo cargar la evolución histórica de la escudería.', retryHistory));
      return;
    }

    // La evolución muestra toda la historia del constructor, no solo hasta el
    // año seleccionado en la URL (las cifras de temporada siguen usando year).
    var seasons = Object.keys(history.results).map(function (key) { return history.results[key]; })
      .sort(function (a, b) { return a.year - b.year; });

    if (!seasons.length) {
      el.chart.appendChild(history.resultsDone
        ? messageBox('Sin temporadas disputadas.', 'empty')
        : loading('Cargando evolución…'));
      return;
    }

    var metric = state.metric;
    var points = seasons.map(function (season) {
      return { year: season.year, season: season, value: metricValue(season, metric) };
    });
    var valid = points.filter(function (point) { return point.value !== null; });

    var pending = !history.standingsDone && valid.length < points.length;
    if (!valid.length) {
      if (pending) { el.chart.appendChild(loading('Cargando clasificaciones oficiales…')); return; }
      el.chart.appendChild(messageBox(
        seasons[seasons.length - 1].year < CHAMPIONSHIP_START
          ? 'El Campeonato Mundial de Constructores no existió hasta 1958, así que no hay puntos ni posiciones oficiales que mostrar en su historia.'
          : 'No hay datos de ' + (metric === 'points' ? 'puntos' : 'posición') + ' disponibles.',
        'empty'));
      return;
    }

    var svgNS = 'http://www.w3.org/2000/svg';
    var svg = document.createElementNS(svgNS, 'svg');
    svg.setAttribute('role', 'img');
    svg.setAttribute('aria-label', 'Evolución de ' + (metric === 'points' ? 'puntos' : 'posición en el campeonato') +
      ' de ' + (state.name || 'la escudería') + ' entre ' + seasons[0].year + ' y ' + seasons[seasons.length - 1].year + '.');
    el.chart.appendChild(svg);

    var width = Math.max(el.chart.clientWidth - 32 || 640, 280);
    var height = width < 560 ? 240 : 310;
    var padL = 48, padR = 18, padT = 22, padB = 36;
    var innerW = Math.max(width - padL - padR, 40);
    var innerH = Math.max(height - padT - padB, 40);

    var values = valid.map(function (point) { return point.value; });
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
      var step = Math.max(1, Math.ceil((maxValue - minValue) / tickCount));
      for (var v = minValue; v <= maxValue; v += step) tickValues.push(v);
      if (tickValues[tickValues.length - 1] !== maxValue) tickValues.push(maxValue);
    }

    tickValues.forEach(function (value, index) {
      var fraction = tickValues.length > 1 ? index / (tickValues.length - 1) : 0;
      var y = padT + innerH * fraction;
      var line = document.createElementNS(svgNS, 'line');
      line.setAttribute('class', 'team-chart__grid');
      line.setAttribute('x1', padL); line.setAttribute('x2', padL + innerW);
      line.setAttribute('y1', y); line.setAttribute('y2', y);
      svg.appendChild(line);

      var label = document.createElementNS(svgNS, 'text');
      label.setAttribute('class', 'team-chart__axis');
      label.setAttribute('x', padL - 8);
      label.setAttribute('y', y + 4);
      label.setAttribute('text-anchor', 'end');
      label.textContent = String(Math.round(value));
      svg.appendChild(label);
    });

    var axisTitle = document.createElementNS(svgNS, 'text');
    axisTitle.setAttribute('class', 'team-chart__axis team-chart__axis--title');
    axisTitle.setAttribute('x', padL);
    axisTitle.setAttribute('y', 12);
    axisTitle.textContent = metric === 'points' ? 'Puntos oficiales' : 'Posición (1 = mejor)';
    svg.appendChild(axisTitle);

    // Etiquetas del eje X
    var labelStep = seasons.length > 14 ? Math.ceil(seasons.length / 8) : 1;
    var shown = [];
    for (var k = 0; k < seasons.length; k += labelStep) shown.push(k);
    var lastIndex = seasons.length - 1;
    if (shown[shown.length - 1] !== lastIndex) {
      if (lastIndex - shown[shown.length - 1] < labelStep) shown.pop();
      shown.push(lastIndex);
    }
    shown.forEach(function (i) {
      var xLabel = document.createElementNS(svgNS, 'text');
      xLabel.setAttribute('class', 'team-chart__axis');
      xLabel.setAttribute('x', xAt(i));
      xLabel.setAttribute('y', height - 12);
      xLabel.setAttribute('text-anchor', 'middle');
      xLabel.textContent = String(seasons[i].year);
      svg.appendChild(xLabel);
    });

    // Segmentos con el color del tema; discontinuos cuando hay interrupción.
    for (var i = 1; i < seasons.length; i++) {
      var a = points[i - 1];
      var b = points[i];
      if (a.value === null || b.value === null) continue;
      var segment = document.createElementNS(svgNS, 'line');
      var gap = seasons[i].year - seasons[i - 1].year > 1;
      segment.setAttribute('class', 'team-chart__segment' + (gap ? ' team-chart__segment--gap' : ''));
      segment.setAttribute('x1', xAt(i - 1)); segment.setAttribute('y1', yAt(a.value));
      segment.setAttribute('x2', xAt(i)); segment.setAttribute('y2', yAt(b.value));
      svg.appendChild(segment);
    }

    var tip = el_('div', 'team-chart__tip');
    tip.setAttribute('role', 'status');

    points.forEach(function (point, index) {
      if (point.value === null) return;
      var circle = document.createElementNS(svgNS, 'circle');
      var champion = isChampion(point.year);
      circle.setAttribute('class', 'team-chart__point' + (champion ? ' team-chart__point--champion' : ''));
      circle.setAttribute('cx', xAt(index));
      circle.setAttribute('cy', yAt(point.value));
      circle.setAttribute('r', 5);
      circle.setAttribute('tabindex', '0');
      circle.setAttribute('aria-label', point.year + ': ' +
        (metric === 'points' ? (fmtPoints(point.value) || '—') + ' puntos oficiales' : 'posición ' + point.value) +
        (champion ? ', campeón del mundo de constructores' : ''));
      var show = function () {
        var pointsLabel = seasonStanding(point.year) && seasonStanding(point.year).points !== null
          ? fmtPoints(seasonStanding(point.year).points) : '—';
        var positionLabel = seasonStanding(point.year) && seasonStanding(point.year).position !== null
          ? seasonStanding(point.year).position + 'º' : '—';
        tip.innerHTML =
          '<div class="team-chart__tip-row"><span>Temporada</span><b>' + point.year + '</b></div>' +
          '<div class="team-chart__tip-row"><span>Puntos</span><b>' + esc(pointsLabel) + '</b></div>' +
          '<div class="team-chart__tip-row"><span>Posición</span><b>' + esc(positionLabel) + '</b></div>' +
          '<div class="team-chart__tip-row"><span>Victorias</span><b>' + point.season.wins + '</b></div>' +
          (champion ? '<div class="team-chart__tip-row team-chart__tip-row--gold"><span>Campeón</span><b>Mundial de Constructores</b></div>' : '');
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

  /* ------------------------------------------------------- pilotos del año */
  function driverCard(driver) {
    var full = [driver.given, driver.family].filter(Boolean).join(' ') || driver.family || driver.given;
    var card = document.createElement('a');
    card.className = 'team-driver';
    card.href = 'PageStatsPiloto.php?driverId=' + encodeURIComponent(driver.id) +
      '&piloto=' + encodeURIComponent(full) + '&year=' + year;
    card.setAttribute('data-driver-id', driver.id);
    card.innerHTML =
      '<span class="team-driver__media">' +
        '<span class="team-driver__initials" aria-hidden="true">' +
          esc(((driver.given || '').charAt(0) + (driver.family || '').charAt(0)).toUpperCase() || '?') +
        '</span>' +
        '<img class="team-driver__photo" alt="" loading="lazy" decoding="async" hidden>' +
        '<span class="team-driver__scrim" aria-hidden="true"></span>' +
        (driver.number ? '<span class="team-driver__num" aria-hidden="true">' + esc(driver.number) + '</span>' : '') +
        (driver.code && year >= 2014 ? '<span class="team-driver__code" aria-hidden="true">' + esc(driver.code) + '</span>' : '') +
      '</span>' +
      '<span class="team-driver__body">' +
        '<span class="team-driver__name">' + esc(full) + '</span>' +
        '<span class="team-driver__meta">' +
          (driver.nationality ? '<span>' + esc(driver.nationality) + '</span>' : '') +
          '<span class="team-driver__stat" data-driver-stat="' + esc(driver.id) + '"></span>' +
        '</span>' +
        '<span class="team-driver__arrow" aria-hidden="true">&#8594;</span>' +
      '</span>';
    return card;
  }

  function renderDrivers() {
    var season = state.season;
    el.drivers.innerHTML = '';

    if (!season) {
      el.drivers.appendChild(loading('Cargando pilotos…'));
      return;
    }

    var list = season.drivers || [];
    el.driversSub.textContent = list.length
      ? 'Pilotos que disputaron al menos un Gran Premio con ' + state.name + ' en ' + year + '.'
      : 'Sin pilotos registrados en ' + year + '.';

    if (!list.length) {
      el.drivers.appendChild(messageBox('No hay pilotos registrados para esta escudería en ' + year + '.', 'empty'));
      return;
    }

    var frag = document.createDocumentFragment();
    list.forEach(function (driver) { frag.appendChild(driverCard(driver)); });
    el.drivers.appendChild(frag);

    loadDriverPhotos(list);
    list.forEach(loadDriverSeasonStats);
  }

  /* Fotografías de los pilotos (Wikipedia), con la misma estrategia y fallback
     que la página de Pilotos. Una sola consulta por bloque. */
  function loadDriverPhotos(drivers) {
    var titles = drivers.filter(function (d) { return d.url; }).map(function (d) { return wikiTitle(d.url); });
    if (!titles.length) return;

    fetch(WIKI_API + '?action=query&format=json&origin=*&prop=pageimages&piprop=thumbnail&pithumbsize=' +
      PHOTO_SIZE + '&redirects=1&titles=' + encodeURIComponent(titles.join('|')))
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
        function resolve(title) {
          var current = title;
          for (var i = 0; i < 5 && alias[current]; i++) current = alias[current];
          return current;
        }
        drivers.forEach(function (driver) {
          if (!driver.url) return;
          var title = wikiTitle(driver.url);
          var page = byTitle[resolve(title)] || byTitle[title];
          var source = page && page.thumbnail && page.thumbnail.source;
          if (!source) return;
          var node = el.drivers.querySelector('.team-driver[data-driver-id="' + driver.id + '"]');
          if (!node) return;
          var image = node.querySelector('.team-driver__photo');
          if (!image) return;
          image.addEventListener('load', function () { image.classList.add('is-loaded'); });
          image.addEventListener('error', function () { image.hidden = true; });
          image.src = source;       // validada: procede del artículo del piloto
          image.hidden = false;
        });
      })
      .catch(function () { /* sin fotografías: se conserva el fallback */ });
  }

  /* Posición y puntos del piloto en la temporada (1 consulta por piloto). */
  function loadDriverSeasonStats(driver) {
    fetchJson(API + '/' + year + '/drivers/' + encodeURIComponent(driver.id) + '/driverStandings.json')
      .then(function (data) {
        var lists = data && data.MRData && data.MRData.StandingsTable && data.MRData.StandingsTable.StandingsLists;
        var entry = lists && lists[0] && lists[0].DriverStandings && lists[0].DriverStandings[0];
        if (!entry) return;
        state.driverStats[driver.id] = { position: num(entry.position), points: entry.points == null ? null : String(entry.points) };
        renderDriverStats();
      })
      .catch(function () { /* sin estadísticas del piloto: la tarjeta sigue siendo válida */ });
  }

  /* Rellena la posición y los puntos del piloto en la temporada consultada. */
  function renderDriverStats() {
    if (!state.season) return;
    (state.season.drivers || []).forEach(function (driver) {
      var node = el.drivers.querySelector('[data-driver-stat="' + driver.id + '"]');
      if (!node) return;
      var data = state.driverStats[driver.id];
      if (!data) return;
      var position = data.position;
      var pointsLabel = fmtPoints(data.points);
      var text = (position !== null ? position + 'º' : '—') +
        (pointsLabel !== null ? ' · ' + pointsLabel + ' pts' : '');
      // La temporada en curso se marca como provisional solo cuando el
      // calendario confirma que todavía no ha terminado.
      if (position === 1) {
        if (year < LATEST_YEAR) text += ' · campeón';
        else if (state.finished[year] === false) text += ' · provisional';
      }
      node.textContent = text;
      node.title = [driver.given, driver.family].filter(Boolean).join(' ') + ' en ' + year;
    });
  }

  /* ------------------------------------------------------------- palmarés */
  function renderHonours() {
    var history = state.history;
    el.honours.innerHTML = '';

    if (!history) {
      el.honours.appendChild(loading('Cargando palmarés…'));
      return;
    }
    if (history.failed) {
      el.honours.appendChild(errorBox('No se pudo cargar el palmarés de la escudería.', retryHistory));
      return;
    }

    var seasons = Object.keys(history.results).map(function (key) { return history.results[key]; })
      .filter(function (s) { return s.year <= year; })
      .sort(function (a, b) { return a.year - b.year; });

    if (!seasons.length) {
      el.honours.appendChild(history.resultsDone
        ? messageBox('Sin participación registrada hasta ' + year + '.', 'empty')
        : loading('Cargando palmarés…'));
      return;
    }

    var titles = seasons.filter(function (s) { return isChampion(s.year); }).map(function (s) { return s.year; });
    var totalWins = seasons.reduce(function (a, s) { return a + s.wins; }, 0);
    var totalPodiums = seasons.reduce(function (a, s) { return a + s.podiums; }, 0);
    var pending = !history.standingsDone;

    el.honoursSub.textContent = 'Logros acumulados hasta la temporada ' + year + '.';

    var panel = el_('div', 'team-honours__grid');

    var titlesCard = el_('article', 'team-honours__card team-honours__card--titles');
    titlesCard.innerHTML =
      '<span class="team-honours__icon">' + ICONS.trophy + '</span>' +
      '<span class="team-honours__label">Títulos de constructores</span>' +
      '<span class="team-honours__value">' + (pending && !titles.length ? '…' : String(titles.length)) + '</span>' +
      '<span class="team-honours__note">' + (year < CHAMPIONSHIP_START
        ? 'Todavía no existía el Mundial de Constructores'
        : 'Temporadas finalizadas en 1ª posición del campeonato') + '</span>';

    if (titles.length) {
      titlesCard.innerHTML += '<ul class="team-honours__years">' +
        titles.map(function (y) {
          return '<li class="team-honours__year">' + y +
            (y === year ? '<span class="team-vh"> (temporada consultada)</span>' : '') + '</li>';
        }).join('') + '</ul>';
    } else if (!pending) {
      titlesCard.innerHTML += '<span class="team-honours__empty">Sin títulos mundiales de constructores hasta ' + year + '.</span>';
    }

    panel.appendChild(titlesCard);

    var statsCard = el_('article', 'team-honours__card');
    statsCard.innerHTML =
      '<dl class="team-honours__stats">' +
        '<div><dt>Victorias en GP</dt><dd>' + totalWins + '</dd></div>' +
        '<div><dt>Podios</dt><dd>' + totalPodiums + '</dd></div>' +
        '<div><dt>Temporadas</dt><dd>' + seasons.length + '</dd></div>' +
        '<div><dt>Debut</dt><dd>' + seasons[0].year + '</dd></div>' +
      '</dl>' +
      '<p class="team-honours__note">' +
        (year < CHAMPIONSHIP_START
          ? 'Entre 1950 y 1957 no existió el Campeonato Mundial de Constructores: se muestran victorias y podios reales de Gran Premio.'
          : 'Victorias y podios de carreras de Gran Premio (sin sprints).') +
        ' El historial sigue el identificador de constructor de la fuente de datos: no se agrupan denominaciones distintas.' +
        (pending ? ' Cargando clasificaciones oficiales…' : '') +
      '</p>';

    panel.appendChild(statsCard);
    el.honours.appendChild(panel);
  }

  /* --------------------------------------------------- tabla de resultados */
  var TROPHY_ICON = '<svg class="team-table__trophy" viewBox="0 0 24 24" fill="none" stroke="currentColor" ' +
    'stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' +
    '<path d="M8 4h8v5a4 4 0 0 1-8 0Z"></path>' +
    '<path d="M8 5H5.5A2.5 2.5 0 0 0 8 9.5"></path>' +
    '<path d="M16 5h2.5A2.5 2.5 0 0 1 16 9.5"></path>' +
    '<path d="M12 13v3"></path><path d="M9 20h6"></path><path d="M10 16h4l.7 4h-5.4Z"></path></svg>';

  function pointsCell(seasonYear) {
    var data = seasonStanding(seasonYear);
    if (data && !data.unavailable) {
      var label = fmtPoints(data.points);
      if (label === null) return '<span class="team-table__dash" title="Sin puntos oficiales registrados">—</span>';
      return '<span title="Puntos oficiales del campeonato de constructores">' + esc(label) + '</span>';
    }
    if (state.history && !state.history.standingsDone) return '<span class="team-table__dash" title="Cargando">…</span>';
    return '<span class="team-table__dash" title="' +
      (seasonYear < CHAMPIONSHIP_START ? 'No existía el Mundial de Constructores' : 'Dato no disponible') + '">—</span>';
  }

  function positionCell(seasonYear) {
    var data = seasonStanding(seasonYear);
    if (data && !data.unavailable) {
      if (data.position === null) return '<span class="team-table__dash" title="Sin clasificación en el campeonato">—</span>';
      if (isChampion(seasonYear)) {
        return '<span class="team-table__pos team-table__pos--champion" title="Campeón del mundo de constructores">' +
          TROPHY_ICON + '<span>' + data.position + 'º</span>' +
          '<span class="team-vh">Campeón del mundo de constructores</span></span>';
      }
      return '<span class="team-table__pos">' + data.position + 'º</span>';
    }
    if (state.history && !state.history.standingsDone) return '<span class="team-table__dash" title="Cargando">…</span>';
    return '<span class="team-table__dash" title="' +
      (seasonYear < CHAMPIONSHIP_START ? 'No existía el Mundial de Constructores' : 'Dato no disponible') + '">—</span>';
  }

  function driversCell(season) {
    var drivers = season.drivers || [];
    if (!drivers.length) return '<span class="team-table__dash">—</span>';
    var names = drivers.map(function (d) { return [d.given, d.family].filter(Boolean).join(' ') || d.family; });
    var shown = names.slice(0, 3);
    var titles = names.join(', ');
    return '<span class="team-table__drivers" title="' + esc(titles) + '">' +
      esc(shown.join(' · ')) +
      (names.length > shown.length ? ' <span class="team-table__more">+' + (names.length - shown.length) + '</span>' : '') +
      '</span>';
  }

  function renderTable() {
    var history = state.history;
    el.resultsBody.innerHTML = '';

    if (!history) {
      el.resultsBody.innerHTML = '<tr class="tf-table__empty"><td colspan="7">Cargando resultados…</td></tr>';
      return;
    }
    if (history.failed) {
      el.resultsBody.innerHTML = '<tr class="tf-table__empty"><td colspan="7">' +
        'No se pudieron cargar los resultados históricos de esta escudería.</td></tr>';
      return;
    }

    var seasons = Object.keys(history.results).map(function (key) { return history.results[key]; })
      .filter(function (s) { return s.year <= year; })
      .sort(function (a, b) { return state.sortAsc ? a.year - b.year : b.year - a.year; });

    if (!seasons.length) {
      el.resultsBody.innerHTML = '<tr class="tf-table__empty"><td colspan="7">' +
        (history.resultsDone ? 'Sin resultados hasta ' + year + '.' : 'Cargando resultados…') + '</td></tr>';
      return;
    }

    el.resultsSub.textContent = seasons.length + (seasons.length === 1 ? ' temporada' : ' temporadas') +
      ' disputadas hasta ' + year + '. Ordenadas de ' + (state.sortAsc ? 'más antigua a más reciente' : 'más reciente a más antigua') + '.' +
      (!history.standingsDone && history.pendingTotal
        ? ' Cargando clasificaciones oficiales (' + (history.pendingDone || 0) + '/' + history.pendingTotal + ')…'
        : '');

    var frag = document.createDocumentFragment();
    seasons.forEach(function (season) {
      var classes = [];
      if (season.year === year) classes.push('team-table__row--current');
      if (isChampion(season.year)) classes.push('team-table__row--champion');
      var row = el_('tr', classes.join(' '));
      row.innerHTML =
        '<td class="team-table__year">' + season.year + '</td>' +
        '<td>' + driversCell(season) + '</td>' +
        '<td class="tf-td-right tf-td-num">' + season.gp + '</td>' +
        '<td class="tf-td-right tf-td-num">' + season.wins + '</td>' +
        '<td class="tf-td-right tf-td-num">' + season.podiums + '</td>' +
        '<td class="tf-td-right tf-td-num">' + pointsCell(season.year) + '</td>' +
        '<td class="tf-td-right tf-td-num">' + positionCell(season.year) + '</td>';
      frag.appendChild(row);
    });
    el.resultsBody.appendChild(frag);
  }

  /* --------------------------------------------------------------- render */
  /* Cabecera, escudo, etiquetas y panel lateral de la temporada. */
  function renderSeason() {
    renderHeader();
    renderKpis();
  }

  /* Coherencia ficha ↔ página general: si la clasificación oficial de la
     temporada llega por el historial, se refleja también en KPIs y cabecera. */
  function syncSeasonFromHistory() {
    if (!state.season || !state.history) return;
    var data = state.history.standings[year];
    if (!data || data.unavailable) return;
    if (state.season.position === data.position && state.season.points === data.points) return;
    state.season.position = data.position;
    state.season.points = data.points;
    renderSeason();
  }

  /* Evolución, palmarés y tabla (se repintan a medida que llegan los datos). */
  function renderHistory() {
    renderChart();
    renderHonours();
    renderTable();
    syncSeasonFromHistory();
  }

  /* --------------------------------------------------------------- historial */
  function readHistoryCache() {
    try {
      var raw = sessionStorage.getItem(HISTORY_CACHE + state.constructorId);
      if (!raw) return null;
      var saved = JSON.parse(raw);
      if (!saved || !saved.t || (Date.now() - saved.t) > CACHE_TTL) return null;
      return saved;
    } catch (e) { return null; }
  }

  function writeHistoryCache(standings, results) {
    try {
      var keepStandings = {};
      var keepResults = {};
      Object.keys(standings).forEach(function (key) {
        var y = parseInt(key, 10);
        if (y < LATEST_YEAR && !standings[key].unavailable) keepStandings[y] = standings[key];
      });
      Object.keys(results).forEach(function (key) {
        var y = parseInt(key, 10);
        if (y < LATEST_YEAR) keepResults[y] = results[key];
      });
      sessionStorage.setItem(HISTORY_CACHE + state.constructorId, JSON.stringify({
        t: Date.now(),
        throughYear: LATEST_YEAR - 1,
        standings: keepStandings,
        results: keepResults
      }));
    } catch (e) { /* sin almacenamiento */ }
  }

  var renderTimer = null;
  function scheduleRender() {
    if (renderTimer) return;
    renderTimer = window.setTimeout(function () {
      renderTimer = null;
      renderHistory();
    }, 250);
  }

  /* Carga progresiva del historial: primero los resultados (una pasada paginada)
     y después las clasificaciones oficiales por temporada, con concurrencia
     limitada y de la más reciente a la más antigua. */
  function loadHistory() {
    state.history = { results: {}, standings: {}, resultsDone: false, standingsDone: false };

    var cached = readHistoryCache();
    if (cached && cached.throughYear >= year) {
      state.history.results = cached.results || {};
      state.history.standings = cached.standings || {};
      state.history.resultsDone = true;
      state.history.standingsDone = true;
      renderHistory();
      renderSeason();
      return Promise.resolve();
    }

    var seeded = (cached && cached.standings) || {};

    // Revisita de la temporada en curso: el historial cacheado (temporadas
    // cerradas) se reutiliza y solo se actualiza la temporada en curso.
    if (cached && cached.results && Object.keys(cached.results).length && year >= LATEST_YEAR) {
      state.history.results = cached.results;
      state.history.standings = cached.standings || {};
      Object.keys(state.history.standings).forEach(function (key) { seeded[key] = state.history.standings[key]; });
      renderHistory();
      return fetchPagedRacesRetry(API + '/' + LATEST_YEAR + '/constructors/' + state.constructorId + '/results.json', null)
        .then(function (races) {
          var live = summarizeSeasons(races);
          Object.keys(live).forEach(function (key) { state.history.results[key] = live[key]; });
          state.history.resultsDone = true;
          renderHistory();
          return fetchSeasonStanding(LATEST_YEAR).then(function (data) {
            if (data) state.history.standings[LATEST_YEAR] = data;
            state.history.standingsDone = true;
            renderHistory();
          });
        })
        .catch(function (error) {
          console.error(error);
          state.history.resultsDone = true;
          state.history.standingsDone = true;
          renderHistory();
        });
    }

    Object.keys(seeded).forEach(function (key) { state.history.standings[key] = seeded[key]; });

    return fetchPagedRacesRetry(API + '/constructors/' + state.constructorId + '/results.json', null)
      .then(function (races) {
        state.history.results = summarizeSeasons(races);
        state.history.resultsDone = true;
        renderHistory();
        renderHeader();

        var seasons = Object.keys(state.history.results).map(Number)
          .filter(function (y) { return y >= CHAMPIONSHIP_START; });
        var pending = seasons.filter(function (y) { return !(y < LATEST_YEAR && seeded[y]); });
        pending.sort(function (a, b) { return b - a; });

        state.history.pendingTotal = pending.length;
        state.history.pendingDone = 0;
        var failed = [];

        return runQueue(pending, CONCURRENCY, function (y) {
          return fetchSeasonStanding(y).then(function (data) {
            state.history.pendingDone++;
            if (!data) { failed.push(y); scheduleRender(); return; }
            state.history.standings[y] = data;
            if (y < LATEST_YEAR && !data.unavailable) seeded[y] = data;
            scheduleRender();
          });
        }).then(function () {
          // Segunda pasada, secuencial y con una pausa, para las temporadas que
          // fallaron por el límite de peticiones (sus datos no se cachean).
          if (!failed.length) return null;
          var retry = failed.slice().sort(function (a, b) { return b - a; });
          return new Promise(function (resolve) { window.setTimeout(resolve, 2500); })
            .then(function () {
              return runQueue(retry, 1, function (y) {
                return fetchSeasonStanding(y).then(function (data) {
                  if (!data) return;
                  state.history.standings[y] = data;
                  if (y < LATEST_YEAR && !data.unavailable) seeded[y] = data;
                  scheduleRender();
                });
              });
            });
        });
      })
      .then(function () {
        state.history.standingsDone = true;
        if (year >= LATEST_YEAR && state.history.standings[LATEST_YEAR] && state.history.standings[LATEST_YEAR].position === 1) {
          checkSeasonFinished(LATEST_YEAR);
        }
        writeHistoryCache(seeded, state.history.results);
        renderHistory();
      })
      .catch(function (error) {
        console.error(error);
        // La API no respondió: se avisa y se ofrece reintentar en lugar de
        // mostrar la ficha como si la escudería no tuviera historia.
        state.history.failed = true;
        state.history.resultsDone = true;
        state.history.standingsDone = true;
        renderHistory();
      });
  }

  /* Reintenta el historial desde cero (se descarta la caché de esta escudería). */
  function retryHistory() {
    try { sessionStorage.removeItem(HISTORY_CACHE + state.constructorId); } catch (e) { /* sin almacenamiento */ }
    loadHistory();
  }

  /* --------------------------------------------------------------- carga */
  function applyIdentity(found) {    state.constructorId = found.id;
    state.name = found.name || escuderia;
    state.nationality = found.nationality || '';
    state.color = teamColor(state.constructorId, state.name);
    state.finished = {};
    el.volver.href = '/index.html/PageEscuderias.php?year=' + year;
    renderHeader();
  }

  function loadSeason() {
    return fetchSeasonStanding(year).then(function (data) {
      state.season = {
        available: false, gp: 0, wins: 0, podiums: 0, drivers: [],
        position: null, points: null
      };
      if (data && !data.unavailable) {
        state.season.position = data.position;
        state.season.points = data.points;
      }
      return fetchPagedRacesRetry(API + '/' + year + '/constructors/' + state.constructorId + '/results.json', null);
    }).then(function (races) {
      var byYear = summarizeSeasons(races);
      var season = byYear[year];
      if (season) {
        state.season.available = true;
        state.season.gp = season.gp;
        state.season.wins = season.wins;
        state.season.podiums = season.podiums;
        state.season.drivers = season.drivers;
      }
      renderSeason();
      renderDrivers();
    });
  }

  function loadProvisional() {
    if (year < LATEST_YEAR) { state.provisional = false; return Promise.resolve(); }
    return checkSeasonFinished(year).then(function (finished) {
      state.provisional = finished === false && state.season && state.season.position !== null;
      renderSeason();
      renderDriverStats();
      renderHistory();
    });
  }

  function load() {
    if (!escuderia && !constructorIdParam) {
      el.title.textContent = 'Escudería no encontrada';
      el.sub.textContent = 'No se ha indicado ninguna escudería en la dirección.';
      el.kpis.appendChild(messageBox('Falta el parámetro "escuderia" en la dirección.', 'empty'));
      el.chart.appendChild(messageBox('Sin datos de evolución.', 'empty'));
      el.drivers.appendChild(messageBox('Sin pilotos.', 'empty'));
      el.honours.appendChild(messageBox('Sin palmarés.', 'empty'));
      el.resultsBody.innerHTML = '<tr class="tf-table__empty"><td colspan="7">Sin datos.</td></tr>';
      return;
    }

    resolveConstructor().then(function (found) {
      if (!found) {
        el.title.textContent = escuderia || 'Escudería';
        el.sub.textContent = 'No se encontró esta escudería en la temporada ' + year + '.';
        el.side.innerHTML = '';
        el.kpis.innerHTML = '';
        el.kpis.appendChild(messageBox('No se encontró la escudería "' + escuderia + '" en ' + year + '.', 'empty'));
        el.chart.innerHTML = '';
        el.chart.appendChild(messageBox('Sin datos de evolución.', 'empty'));
        el.drivers.innerHTML = '';
        el.drivers.appendChild(messageBox('Sin pilotos.', 'empty'));
        el.honours.innerHTML = '';
        el.honours.appendChild(messageBox('Sin palmarés.', 'empty'));
        el.resultsBody.innerHTML = '<tr class="tf-table__empty"><td colspan="7">Sin datos.</td></tr>';
        return null;
      }

      applyIdentity(found);

      // Carga progresiva: primero la temporada (cabecera, KPIs y pilotos) y, en
      // paralelo, el historial completo para evolución, palmarés y tabla.
      loadSeason().then(loadProvisional).catch(function (error) {
        console.error(error);
        renderSeason();
        renderDrivers();
      });
      loadHistory();

      return null;
    }).catch(function (error) {
      console.error(error);
      el.sub.textContent = 'No se pudieron cargar los datos de la escudería.';
      el.kpis.innerHTML = '';
      el.kpis.appendChild(errorBox('No se pudieron cargar las estadísticas.', load));
      el.chart.innerHTML = '';
      el.chart.appendChild(messageBox('Sin datos de evolución.', 'error'));
      el.drivers.innerHTML = '';
      el.drivers.appendChild(messageBox('Sin pilotos.', 'error'));
      el.honours.innerHTML = '';
      el.honours.appendChild(messageBox('Sin palmarés.', 'error'));
      el.resultsBody.innerHTML = '<tr class="tf-table__empty"><td colspan="7">Error al cargar los resultados.</td></tr>';
    });
  }

  /* --------------------------------------------------------------- eventos */
  function setMetric(metric) {
    state.metric = metric;
    var isPoints = metric === 'points';
    el.metricPoints.classList.toggle('is-active', isPoints);
    el.metricPosition.classList.toggle('is-active', !isPoints);
    el.metricPoints.setAttribute('aria-pressed', isPoints ? 'true' : 'false');
    el.metricPosition.setAttribute('aria-pressed', isPoints ? 'false' : 'true');
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

  load();
})();
