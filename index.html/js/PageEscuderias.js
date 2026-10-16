/* ============================================================
   TodoF1 — Escuderías · cuadrícula de tarjetas con el monoplaza
   ------------------------------------------------------------
   PROCEDENCIA DE LOS DATOS (Ergast/Jolpica):
     · Clasificación del Mundial de Constructores (posición y puntos
       OFICIALES de la temporada): GET /{año}/constructorStandings.json
       Solo existe desde 1958: en 1950-1957 no hubo campeonato.
     · Equipos participantes, pilotos, victorias, podios y Grandes
       Premios disputados: GET /{año}/results.json (paginado a 100).
       Son resultados de CARRERA: el endpoint no incluye sprints, por
       lo que las victorias son de Gran Premio (nunca de sprint).
     · Las victorias se cuentan por Gran Premio (una sola vez por
       carrera, aunque el coche lo compartiesen dos pilotos) y los
       podios por resultado único (ronda + posición), de modo que un
       coche compartido no suma doble.
   DEFINICIONES:
     · "Puntos" y "Posición" = clasificación OFICIAL de constructores.
       Nunca se sustituyen por la suma de puntos de los pilotos y, si
       no existe clasificación, se muestra "—" (no se inventa).
     · "Pilotos" = todos los que disputaron al menos un GP con el
       equipo esa temporada (sustituciones y cambios incluidos).
   FOTOGRAFÍAS:
     · Se reutilizan las imágenes ya presentes en el proyecto
       (/Images/heroes/escuderias, mapa TEAM_IMAGES de teams.js), que
       son monoplazas de la era actual: solo se muestran a partir de
       2025. En las temporadas anteriores se usa la composición de
       respaldo (nombre + color del equipo + silueta neutra), sin
       mostrar nunca un coche actual en una temporada histórica.
     · La página avisa de que son imágenes representativas del equipo.
   TEMAS: la interfaz general (buscador, ordenación, foco) sigue el
   tema activo (--team-*). El color propio de cada tarjeta identifica
   a su escudería (teams.js por constructorId + mapa histórico ya
   existente en el proyecto por nombre; si no hay correspondencia
   fiable, un color neutro).
   ============================================================ */
(function () {
  'use strict';

  var API = 'https://api.jolpi.ca/ergast/f1';
  var PAGE_LIMIT = 100;          // máximo de resultados por página en Jolpica
  var MAX_PAGES = 40;            // tope de seguridad
  var MIN_YEAR = 1950;
  var LATEST_YEAR = 2026;
  var NEUTRAL_COLOR = '#55555E';
  var CAR_IMAGE_MIN_YEAR = 2025; // las fotos del proyecto son de la era actual
  var CHAMPIONSHIP_START = 1958; // primer año del Mundial de Constructores
  var CACHE_KEY = 'todof1:escuderias:v1:';
  var CACHE_TTL = 20 * 60 * 1000; // 20 minutos
  var DRIVERS_VISIBLE = 4;        // pilotos mostrados antes de "Ver todos"

  /* Colores históricos ya definidos en el proyecto (PagePilotos.js).
     No se inventan colores: las escuderías sin correspondencia fiable
     usan el neutro. */
  var HISTORIC_COLORS = {
    'lotus': '#FFB800',
    'toro rosso': '#4E7C9B',
    'force india': '#F480B5',
    'benetton': '#00A551',
    'brabham': '#FFCC00',
    'tyrrell': '#003399'
  };

  /* Silueta neutra de monoplaza para el respaldo: es un elemento gráfico
     genérico, no representa a ningún equipo ni temporada concretos. */
  var CAR_SVG =
    '<svg class="team-card__car" viewBox="0 0 280 96" aria-hidden="true" focusable="false">' +
    '<g fill="currentColor">' +
    '<rect x="6" y="80" width="268" height="4" rx="2" opacity=".16"></rect>' +
    '<rect x="14" y="72" width="60" height="5" rx="2.5" opacity=".35"></rect>' +
    '<text x="140" y="34" text-anchor="middle" font-family="Sora, Inter, sans-serif" ' +
    'font-size="30" font-weight="800" letter-spacing="2" opacity=".22">%MONO%</text>' +
    '<path d="M232 22h42v9h-42z"></path>' +
    '<rect x="248" y="31" width="9" height="30" rx="4"></rect>' +
    '<path d="M18 64l30-9 42-5 30-14c8-4 17-6 27-6h22c9 0 16 3 20 9l7 11 30 4c9 1 15 4 18 10 2 5-1 8-7 8H24c-8 0-10-4-6-8z"></path>' +
    '<path d="M136 40c10-9 26-13 42-12" fill="none" stroke="currentColor" stroke-width="5" stroke-linecap="round"></path>' +
    '<circle cx="70" cy="68" r="22"></circle>' +
    '<circle cx="70" cy="68" r="9" fill="#0B0B0D" opacity=".55"></circle>' +
    '<circle cx="214" cy="66" r="25"></circle>' +
    '<circle cx="214" cy="66" r="10" fill="#0B0B0D" opacity=".55"></circle>' +
    '</g>' +
    '</svg>';

  var params = new URLSearchParams(window.location.search);
  var year = parseInt(params.get('year') || String(LATEST_YEAR), 10);
  if (!year || year < MIN_YEAR || year > 2100) year = LATEST_YEAR;

  var grid = document.getElementById('teams-grid');
  var search = document.getElementById('search');
  var clearBtn = document.getElementById('search-clear');
  var sortSelect = document.getElementById('sort-by');
  var counter = document.getElementById('count-badge');
  var seasonSub = document.getElementById('season-sub');
  var seasonNote = document.getElementById('season-note');
  var photoNote = document.getElementById('photo-note');

  document.getElementById('year-label').textContent = year;
  document.getElementById('btn-volver').href = '/?year=' + year;
  seasonSub.textContent = 'Constructores, pilotos y estadísticas de la temporada ' + year + '.';

  var state = { term: '', sort: 'championship' };
  var teams = [];          // equipos ya fusionados (clasificación + resultados)
  var hasStandings = false;// existe clasificación oficial de constructores
  var standingsFailed = false; // la petición de clasificación falló (no es lo mismo que no existir)
  var resultsOk = true;    // se pudieron cargar los resultados de carrera
  var provisional = false; // la temporada está en curso
  var photosShown = false; // alguna tarjeta muestra fotografía

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

  /* Monograma para el respaldo: iniciales de las palabras (máx. 3). */
  function monogram(name) {
    var words = String(name || '').trim().split(/\s+/).filter(Boolean);
    var letters = words.map(function (w) { return w.charAt(0); });
    return (letters.length > 1 ? letters.slice(0, 3) : [String(name || 'F1').slice(0, 3)])
      .join('').toUpperCase();
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

  /* Texto legible sobre el color del equipo (mismo cálculo del sistema de temas). */
  function onColor(hex) {
    var helper = window.TodoF1Teams && window.TodoF1Teams._internal && window.TodoF1Teams._internal.onColor;
    if (typeof helper === 'function') return helper(hex);
    var c = hexToRgb(hex);
    function channel(v) { v /= 255; return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4); }
    var l = 0.2126 * channel(c.r) + 0.7152 * channel(c.g) + 0.0722 * channel(c.b);
    return (1.05 / (l + 0.05)) >= ((l + 0.05) / 0.05) ? '#FFFFFF' : '#0B0B0D';
  }

  /* Color del equipo: identidad del sistema de temas (constructorId) y, si no
     existe, el mapa histórico del proyecto; si tampoco, un color neutro. */
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

  /* Fotografía del equipo (solo si el proyecto la tiene y la temporada es de la
     era actual). Devuelve '' si no hay imagen fiable: se usará el respaldo. */
  function teamImage(constructorId) {
    if (year < CAR_IMAGE_MIN_YEAR) return '';
    var internal = window.TodoF1Teams && window.TodoF1Teams._internal;
    var map = internal && internal.TEAM_IMAGES;
    var entry = map && constructorId ? map[constructorId] : null;
    return (entry && entry.src) ? entry.src : '';
  }

  function versionedSrc(src) {
    var helper = window.TodoF1Teams && window.TodoF1Teams._internal && window.TodoF1Teams._internal.versionedSrc;
    return typeof helper === 'function' ? helper(src) : src;
  }

  /* ---------------------------------------------------------------- datos */
  function fetchStandings() {
    return fetch(API + '/' + year + '/constructorStandings.json?limit=' + PAGE_LIMIT + '&offset=0')
      .then(function (response) {
        if (!response.ok) throw new Error('HTTP ' + response.status);
        return response.json();
      })
      .then(function (data) {
        var meta = (data && data.MRData) || {};
        var list = meta.StandingsTable && meta.StandingsTable.StandingsLists && meta.StandingsTable.StandingsLists[0];
        var rows = (list && list.ConstructorStandings) || [];
        return rows.map(function (row) {
          var c = row.Constructor || {};
          return {
            id: c.constructorId || '',
            name: c.name || '',
            nationality: c.nationality || '',
            position: num(row.position),
            points: row.points == null ? null : String(row.points),
            wins: num(row.wins)
          };
        });
      });
  }

  /* Recorre todas las páginas de resultados. Jolpica pagina por RESULTADOS,
     así que una carrera puede quedar partida entre dos páginas: se agrupan los
     resultados por ronda en lugar de concatenar carreras. */
  function fetchAllResults() {
    var byRound = {};
    var offset = 0;
    var total = Infinity;
    var pages = 0;

    function step() {
      return fetch(API + '/' + year + '/results.json?limit=' + PAGE_LIMIT + '&offset=' + offset)
        .then(function (response) {
          if (!response.ok) throw new Error('HTTP ' + response.status);
          return response.json();
        })
        .then(function (data) {
          var meta = (data && data.MRData) || {};
          var races = (meta.RaceTable && meta.RaceTable.Races) || [];
          var count = num(meta.total);
          if (count !== null) total = count;

          races.forEach(function (race) {
            var key = String(race.round);
            if (!byRound[key]) byRound[key] = { round: race.round, raceName: race.raceName || '', Results: [] };
            if (race.Results && race.Results.length) {
              byRound[key].Results = byRound[key].Results.concat(race.Results);
            }
          });

          offset += PAGE_LIMIT;
          pages++;
          if (!races.length || offset >= total || pages >= MAX_PAGES) return byRound;
          return step();
        });
    }

    return step();
  }

  /* ¿La temporada está en curso? Solo puede serlo la última disponible; se
     comprueba con la fecha de la última prueba del calendario. */
  function checkProvisional() {
    if (year < LATEST_YEAR) return Promise.resolve(false);
    return fetch(API + '/' + year + '.json')
      .then(function (response) { return response.ok ? response.json() : null; })
      .then(function (data) {
        var races = (data && data.MRData && data.MRData.RaceTable && data.MRData.RaceTable.Races) || [];
        if (!races.length) return true;
        var last = races[races.length - 1];
        var when = last.date ? new Date(last.date + 'T23:59:59Z') : null;
        if (!when || isNaN(when.getTime())) return true;
        return when.getTime() > Date.now();
      })
      .catch(function () { return true; }); // ante la duda, provisional (no afirmar final)
  }

  function emptyTeam(id, name, nationality) {
    return {
      id: id,
      name: name || '',
      nationality: nationality || '',
      position: null,
      points: null,
      wins: null,
      podiums: null,
      races: null,
      drivers: [],
      _seenDrivers: {},
      _rounds: {},
      _winRounds: {},
      _podiumKeys: {}
    };
  }

  /* Fusiona clasificación oficial y resultados en la lista final de equipos.
     `hasResults` indica que los resultados de carrera se cargaron: en ese caso
     un cero es un dato real (0 victorias, 0 podios) y no se confunde con "—". */
  function buildTeams(standings, byRound, hasResults) {
    var byId = {};
    var order = [];

    function ensure(id, name, nationality) {
      var key = id || norm(name);
      if (!key) key = 'desconocido';
      if (!byId[key]) {
        byId[key] = emptyTeam(id || key, name, nationality);
        order.push(key);
      }
      var team = byId[key];
      if (!team.name && name) team.name = name;
      if (!team.nationality && nationality) team.nationality = nationality;
      return team;
    }

    if (byRound) {
      Object.keys(byRound).forEach(function (round) {
        var race = byRound[round];
        (race.Results || []).forEach(function (result) {
          var c = result.Constructor || {};
          var d = result.Driver || {};
          var team = ensure(c.constructorId || '', c.name || '', c.nationality || '');

          // Grandes Premios disputados: una vez por ronda.
          if (!team._rounds[round]) { team._rounds[round] = 1; team.races = (team.races || 0) + 1; }

          var pos = num(result.position);
          if (pos === 1 && !team._winRounds[round]) {
            team._winRounds[round] = 1;
            team.wins = (team.wins || 0) + 1;
          }
          if (pos !== null && pos <= 3) {
            var pk = round + ':' + pos; // evita el doble cómputo de coches compartidos
            if (!team._podiumKeys[pk]) { team._podiumKeys[pk] = 1; team.podiums = (team.podiums || 0) + 1; }
          }

          if (d.driverId && !team._seenDrivers[d.driverId]) {
            team._seenDrivers[d.driverId] = 1;
            team.drivers.push({
              id: d.driverId,
              given: d.givenName || '',
              family: d.familyName || '',
              code: d.code || '',
              number: num(d.permanentNumber),
              nationality: d.nationality || ''
            });
          }
        });
      });
    }

    if (standings) {
      standings.forEach(function (row) {
        var team = ensure(row.id, row.name, row.nationality);
        team.position = row.position;
        team.points = row.points;
        // La clasificación también aporta victorias: solo se usan si no hay resultados.
        if (team.wins === null && row.wins !== null) team.wins = row.wins;
      });
    }

    return order.map(function (key) {
      var team = byId[key];
      // Con resultados cargados, "sin victorias/podios" es 0, no ausencia de dato.
      if (hasResults) {
        if (team.wins === null) team.wins = 0;
        if (team.podiums === null) team.podiums = 0;
        if (team.races === null) team.races = 0;
      }
      team.color = teamColor(team.id, team.name);
      team.drivers.sort(function (a, b) {
        return (a.family + ' ' + a.given).localeCompare(b.family + ' ' + b.given, 'es');
      });
      team.driverCount = team.drivers.length;
      delete team._seenDrivers; delete team._rounds; delete team._winRounds; delete team._podiumKeys;
      return team;
    }).filter(function (team) { return team.name; });
  }

  /* ----------------------------------------------------------------- caché */
  function readCache() {
    try {
      var raw = sessionStorage.getItem(CACHE_KEY + year);
      if (!raw) return null;
      var saved = JSON.parse(raw);
      if (!saved || !saved.t || !saved.teams || (Date.now() - saved.t) > CACHE_TTL) return null;
      return saved;
    } catch (e) { return null; }
  }

  function writeCache(ok) {
    if (!ok) return; // nunca se cachean cargas incompletas o con error
    try {
      sessionStorage.setItem(CACHE_KEY + year, JSON.stringify({
        t: Date.now(),
        teams: teams,
        hasStandings: hasStandings,
        standingsFailed: standingsFailed,
        resultsOk: resultsOk,
        provisional: provisional
      }));
    } catch (e) { /* almacenamiento no disponible */ }
  }

  /* ------------------------------------------------------- filtro y orden */
  function matches(team) {
    if (!state.term) return true;
    var haystack = norm(team.name);
    var needles = state.term.split(' ').filter(Boolean);
    for (var i = 0; i < needles.length; i++) {
      if (haystack.indexOf(needles[i]) === -1) return false;
    }
    return true;
  }

  function byName(a, b) {
    return a.name.localeCompare(b.name, 'es');
  }

  function compare(a, b) {
    switch (state.sort) {
      case 'name-asc':
        return byName(a, b);
      case 'name-desc':
        return byName(b, a);

      case 'wins-desc': {
        var aw = a.wins, bw = b.wins;
        // Sin dato: siempre al final, con criterio secundario estable (alfabético).
        if (aw === null && bw === null) return byName(a, b);
        if (aw === null) return 1;
        if (bw === null) return -1;
        if (aw !== bw) return bw - aw;
        return byName(a, b);
      }

      case 'points-desc': {
        var ap = a.points === null ? null : parseFloat(a.points);
        var bp = b.points === null ? null : parseFloat(b.points);
        if (ap === null && bp === null) return byName(a, b);
        if (ap === null) return 1;
        if (bp === null) return -1;
        if (ap !== bp) return bp - ap;
        return byName(a, b);
      }

      default: {
        // Clasificación del campeonato. Sin campeonato oficial (1950-1957)
        // se usa un orden alternativo explícito y estable: alfabético.
        if (!hasStandings) return byName(a, b);
        var an = a.position, bn = b.position;
        if (an === null && bn === null) return byName(a, b);
        if (an === null) return 1;
        if (bn === null) return -1;
        if (an !== bn) return an - bn;
        return byName(a, b);
      }
    }
  }

  function visibleTeams() {
    return teams.filter(matches).slice().sort(compare);
  }

  /* ---------------------------------------------------------------- tarjetas */
  function statCell(label, value, title) {
    var text = (value === null || value === undefined || value === '') ? '&mdash;' : esc(value);
    return '<div class="team-card__stat"' + (title ? ' title="' + esc(title) + '"' : '') + '>' +
      '<span class="team-card__stat-value">' + text + '</span>' +
      '<span class="team-card__stat-label">' + esc(label) + '</span>' +
      '</div>';
  }

  function driverChip(driver) {
    var full = [driver.given, driver.family].filter(Boolean).join(' ') || driver.family || driver.given;
    var href = 'PageStatsPiloto.php?driverId=' + encodeURIComponent(driver.id) +
      '&piloto=' + encodeURIComponent(full) + '&year=' + year;
    return '<a class="team-card__driver" href="' + href + '" title="' + esc(full) + '">' +
      '<span class="team-card__driver-dot" aria-hidden="true"></span>' +
      '<span class="team-card__driver-name">' + esc(full) + '</span>' +
      (driver.number !== null ? '<span class="team-card__driver-num">#' + driver.number + '</span>' : '') +
      '</a>';
  }

  function card(team) {
    var article = document.createElement('article');
    article.className = 'team-card';
    article.setAttribute('data-team-id', team.id);
    article.style.setProperty('--team-color', team.color);
    article.style.setProperty('--team-soft', rgba(team.color, 0.18));
    article.style.setProperty('--team-line', rgba(team.color, 0.45));
    article.style.setProperty('--team-ink', onColor(team.color));

    var href = 'PageStatsEscuderia.php?escuderia=' + encodeURIComponent(team.name) + '&year=' + year;

    // Posición en el Mundial (solo si hay clasificación oficial).
    var posBadge;
    if (team.position !== null) {
      posBadge = '<span class="team-card__pos">P' + team.position + '</span>';
    } else {
      var posTitle = hasStandings
        ? 'Sin clasificación en el Mundial de Constructores de ' + year
        : (standingsFailed
          ? 'Clasificación oficial no disponible'
          : 'No existía el Campeonato de Constructores en ' + year);
      posBadge = '<span class="team-card__pos team-card__pos--none" title="' + esc(posTitle) + '">&mdash;</span>';
    }

    // Fotografía (o composición de respaldo) del monoplaza.
    var src = teamImage(team.id);
    var photo = src
      ? '<img class="team-card__photo" src="' + esc(versionedSrc(src)) + '" alt="Monoplaza del equipo ' + esc(team.name) +
        '" title="Imagen representativa del equipo; puede corresponder a otra temporada" loading="lazy" decoding="async">'
      : '';
    if (src) photosShown = true;

    var fallback = '<span class="team-card__fallback" aria-hidden="true">' +
      CAR_SVG.replace('%MONO%', esc(monogram(team.name))) + '</span>';

    // Estadísticas de la temporada (los "—" indican dato no disponible).
    var pointsTitle = hasStandings
      ? 'Puntos oficiales del Mundial de Constructores'
      : (standingsFailed
        ? 'Clasificación oficial no disponible'
        : 'No existía el Mundial de Constructores en ' + year);
    var stats =
      statCell('Puntos', team.points, pointsTitle) +
      statCell('Victorias', team.wins, 'Grandes Premios ganados (sin contar carreras al sprint)') +
      statCell('Podios', team.podiums, 'Podios (top 3) en Grandes Premios') +
      statCell('Grandes Premios', team.races, 'Grandes Premios en los que participó el equipo');

    // Pilotos: chips + "Ver todos" cuando superan el máximo visible.
    var driverItems = team.drivers.map(function (driver) {
      return '<li class="team-card__driver-item">' + driverChip(driver) + '</li>';
    });
    if (team.drivers.length > DRIVERS_VISIBLE) {
      driverItems = team.drivers.map(function (driver, index) {
        var extra = index >= DRIVERS_VISIBLE ? ' is-extra' : '';
        return '<li class="team-card__driver-item' + extra + '">' + driverChip(driver) + '</li>';
      });
    }
    var driversBlock = team.drivers.length
      ? '<span class="team-card__crews-label">Pilotos <span class="team-card__crews-count">' + team.drivers.length + '</span></span>' +
        '<ul class="team-card__drivers">' + driverItems.join('') + '</ul>' +
        (team.drivers.length > DRIVERS_VISIBLE
          ? '<button type="button" class="team-card__more" aria-expanded="false">Ver los ' + team.drivers.length + ' pilotos</button>'
          : '')
      : '<span class="team-card__crews-label">Pilotos</span>' +
        '<span class="team-card__no-drivers">Sin pilotos registrados.</span>';

    article.innerHTML =
      '<a class="team-card__link" href="' + href + '" aria-label="Ver las estadísticas de ' +
        esc(team.name) + ' en ' + year + '"></a>' +
      '<div class="team-card__media">' +
        fallback + photo +
        '<span class="team-card__scrim" aria-hidden="true"></span>' +
        posBadge +
        '<span class="team-card__arrow" aria-hidden="true">&#8599;</span>' +
        '<div class="team-card__head">' +
          '<h2 class="team-card__name">' + esc(team.name) + '</h2>' +
          (team.nationality ? '<span class="team-card__nat">' + esc(team.nationality) + '</span>' : '') +
        '</div>' +
      '</div>' +
      '<div class="team-card__body">' +
        '<div class="team-card__stats">' + stats + '</div>' +
        '<div class="team-card__crews">' + driversBlock + '</div>' +
      '</div>';

    var img = article.querySelector('.team-card__photo');
    if (img) {
      if (img.complete && img.naturalWidth) img.classList.add('is-loaded');
      img.addEventListener('load', function () { img.classList.add('is-loaded'); });
      img.addEventListener('error', function () { img.style.display = 'none'; });
    }

    var more = article.querySelector('.team-card__more');
    if (more) {
      more.addEventListener('click', function () {
        var list = article.querySelector('.team-card__drivers');
        var expanded = list.classList.toggle('is-expanded');
        more.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        more.textContent = expanded ? 'Ver menos' : ('Ver los ' + team.drivers.length + ' pilotos');
      });
    }

    return article;
  }

  function emptyState(message) {
    var box = document.createElement('div');
    box.className = 'tf-empty';
    box.innerHTML = '<span>' + esc(message) + '</span>';
    return box;
  }

  function loadingState(message) {
    var box = document.createElement('div');
    box.className = 'tf-loading';
    box.innerHTML = '<span class="tf-spinner" aria-hidden="true"></span><span>' + esc(message) + '</span>';
    return box;
  }

  function errorState(message) {
    var box = document.createElement('div');
    box.className = 'tf-error';
    box.innerHTML = '<span>' + esc(message) + '</span>' +
      '<button type="button" class="tf-btn tf-btn--sm tf-mt-4" id="teams-retry">Reintentar</button>';
    return box;
  }

  /* -------------------------------------------------------------- notas */
  function renderNotes() {
    if (!seasonNote) return;
    var parts = [];
    if (!hasStandings && standingsFailed) {
      parts.push('No se pudo cargar la clasificación oficial de constructores de ' + year +
        '; se muestran los equipos participantes, sus pilotos y sus resultados de carrera.');
    } else if (!hasStandings && year < CHAMPIONSHIP_START) {
      parts.push('En ' + year + ' todavía no existía el Campeonato Mundial de Constructores (comenzó en 1958): ' +
        'se muestran los equipos participantes, sus pilotos y las victorias de Gran Premio.');
    }
    if (!resultsOk) {
      parts.push('No se pudieron cargar los pilotos ni las victorias de esta temporada; se muestran solo los datos oficiales de la clasificación.');
    }
    seasonNote.textContent = parts.join(' ');
    seasonNote.hidden = !parts.length;
  }

  function updateSortAvailability() {
    var anyPoints = teams.some(function (team) { return team.points !== null; });
    Array.prototype.forEach.call(sortSelect.options, function (option) {
      if (option.value !== 'points-desc') return;
      option.disabled = !anyPoints;
      option.title = anyPoints ? '' : 'Esta temporada no tiene puntos oficiales de constructores';
    });
    if (!anyPoints && state.sort === 'points-desc') {
      state.sort = 'championship';
      sortSelect.value = 'championship';
    }
    // Sin campeonato oficial (1950-1957) el orden por defecto es explícitamente
    // alfabético, y así se indica en el selector.
    var champOption = sortSelect.options[0];
    if (champOption && champOption.value === 'championship') {
      champOption.textContent = hasStandings
        ? 'Clasificación del campeonato'
        : (standingsFailed ? 'Orden alfabético (clasificación no disponible)' : 'Orden alfabético (sin campeonato)');
    }
  }

  /* --------------------------------------------------------------- render */
  function render() {
    if (!teams.length) {
      grid.innerHTML = '';
      grid.appendChild(emptyState('No hay escuderías registradas para la temporada ' + year + '.'));
      counter.textContent = '0 escuderías';
      if (photoNote) photoNote.hidden = true;
      return;
    }

    var list = visibleTeams();
    counter.textContent = 'Mostrando ' + list.length + ' de ' + teams.length +
      (teams.length === 1 ? ' escudería' : ' escuderías') +
      (provisional ? ' · clasificación provisional' : '');

    grid.innerHTML = '';
    if (!list.length) {
      grid.appendChild(emptyState('No se encontraron escuderías con ese nombre.'));
      if (photoNote) photoNote.hidden = true;
      return;
    }

    photosShown = false;
    var fragment = document.createDocumentFragment();
    list.forEach(function (team) { fragment.appendChild(card(team)); });
    grid.appendChild(fragment);

    if (photoNote) {
      photoNote.textContent = 'Las fotografías son imágenes representativas del equipo y pueden corresponder a otra temporada.';
      photoNote.hidden = !photosShown;
    }
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

  sortSelect.addEventListener('change', function () {
    state.sort = sortSelect.value;
    render();
  });

  /* ----------------------------------------------------------------- carga */
  function applyData(data) {
    teams = data.teams || [];
    hasStandings = !!data.hasStandings;
    standingsFailed = !!data.standingsFailed;
    resultsOk = data.resultsOk !== false;
    provisional = !!data.provisional;
    updateSortAvailability();
    renderNotes();
    render();
  }

  function load() {
    var cached = readCache();
    if (cached) {
      applyData(cached);
      return;
    }

    grid.innerHTML = '';
    grid.appendChild(loadingState('Cargando escuderías\u2026'));
    counter.textContent = 'Cargando\u2026';

    Promise.all([
      fetchStandings().then(function (rows) { return { ok: true, rows: rows }; },
        function () { return { ok: false, rows: [] }; }),
      fetchAllResults().then(function (rounds) { return { ok: true, rounds: rounds }; },
        function () { return { ok: false, rounds: null }; }),
      checkProvisional()
    ]).then(function (results) {
      var standingsRes = results[0];
      var racesRes = results[1];
      var standings = standingsRes.rows || [];
      var rounds = racesRes.rounds;

      hasStandings = standingsRes.ok && standings.length > 0;
      standingsFailed = !standingsRes.ok && year >= CHAMPIONSHIP_START;
      resultsOk = racesRes.ok;
      provisional = !!results[2];

      if (!standingsRes.ok && !racesRes.ok) throw new Error('Sin datos');

      var hasRoundData = !!(rounds && Object.keys(rounds).length);
      teams = buildTeams(hasStandings ? standings : [], rounds ? rounds : {}, resultsOk && hasRoundData);

      if (!teams.length) {
        applyData({
          teams: [],
          hasStandings: hasStandings,
          standingsFailed: standingsFailed,
          resultsOk: resultsOk,
          provisional: provisional
        });
        writeCache(standingsRes.ok && racesRes.ok);
        return;
      }

      updateSortAvailability();
      renderNotes();
      render();
      // Solo se guarda en caché la carga completa: un fallo parcial se reintenta.
      writeCache(standingsRes.ok && racesRes.ok);
    }).catch(function (error) {
      console.error(error);
      grid.innerHTML = '';
      var box = errorState('No se pudieron cargar las escuderías de esta temporada.');
      grid.appendChild(box);
      counter.textContent = 'Error';
      if (seasonNote) seasonNote.hidden = true;
      if (photoNote) photoNote.hidden = true;
      var retry = box.querySelector('#teams-retry');
      if (retry) retry.addEventListener('click', load);
    });
  }

  syncClearButton();
  load();
})();
