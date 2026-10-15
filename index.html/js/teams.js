/* ============================================================
   TodoF1 — Personalización visual por escuderías favoritas
   - Los equipos se obtienen de los datos reales (API), por temporada.
   - Colores por constructorId (identidad visual real de cada equipo).
   - Preferencias por temporada en localStorage (independientes del tema).
   - Equipo principal -> variables CSS --team-* (no recolorea la página).
   - Prepara el hero para imágenes de equipos (fallback: imagen general).
   ============================================================ */
(function () {
  'use strict';

  var root = document.documentElement;
  var YEAR_MIN = 1950;
  var YEAR_MAX = 2026;
  var API = 'https://api.jolpi.ca/ergast/f1';
  var KEY_PREFS = 'todof1:teams:';
  var KEY_MAP = 'todof1:teams-map:';
  var KEY_ACTIVE = 'todof1:teams-active';

  // Identidad visual por constructorId (colores + acentos). Fallback: tema original.
  var TEAMS = {
    ferrari: { p: '#E10600', s: '#FFF200', a: '#FFF200' },
    mercedes: { p: '#00B6A3', s: '#C8CCCE', a: '#27F4D2' },
    red_bull: { p: '#1B2A78', s: '#FFCC00', a: '#E10600' },
    mclaren: { p: '#FF8000', s: '#47C7FC', a: '#47C7FC' },
    aston_martin: { p: '#00665E', s: '#CEDC00', a: '#CEDC00' },
    alpine: { p: '#2293D1', s: '#F2466E', a: '#F2466E' },
    williams: { p: '#0091C7', s: '#64C4FF', a: '#64C4FF' },
    rb: { p: '#1B2D6B', s: '#E10600', a: '#C8102E' },
    sauber: { p: '#00953A', s: '#52E252', a: '#52E252' },
    haas: { p: '#C8102E', s: '#B6BABD', a: '#E6002B' },
    audi: { p: '#B0052A', s: '#D0D0D0', a: '#E6002B' },
    cadillac: { p: '#B08A2E', s: '#1A1A1A', a: '#C7A94C' },
    alphatauri: { p: '#2B4562', s: '#C8CCCE', a: '#E10600' },
    toro_rosso: { p: '#2B4562', s: '#C8CCCE', a: '#E10600' },
    alfa: { p: '#B12039', s: '#C8CCCE', a: '#6CD3BF' },
    racing_point: { p: '#C4579E', s: '#F5F5F5', a: '#F596C8' },
    force_india: { p: '#C4579E', s: '#F5F5F5', a: '#F596C8' },
    renault: { p: '#D6C400', s: '#2B2B2B', a: '#FFF500' },
    lotus_f1: { p: '#C79000', s: '#2B2B2B', a: '#FFB800' },
    brawn: { p: '#7FA300', s: '#2B2B2B', a: '#B8F000' },
    toyota: { p: '#C8102E', s: '#C8CCCE', a: '#E6002B' },
    bmw_sauber: { p: '#2C6FBB', s: '#C8CCCE', a: '#E10600' },
    jordan: { p: '#D6B400', s: '#2B2B2B', a: '#FFD800' }
  };

  // Imágenes por equipo (pendiente: cargar assets con permisos). Vacío => general.
  var TEAM_IMAGES = {};

  // ---------------------------------------------------------- Estado
  function currentYear() {
    try {
      var y = parseInt(new URLSearchParams(window.location.search).get('year'), 10);
      if (y >= YEAR_MIN && y <= YEAR_MAX) return y;
    } catch (e) { /* sin URL */ }
    if (window.TodoF1Year) return window.TodoF1Year;
    return YEAR_MAX;
  }

  function readJSON(key) {
    try { return JSON.parse(localStorage.getItem(key) || 'null'); } catch (e) { return null; }
  }

  function writeJSON(key, value) {
    try { localStorage.setItem(key, JSON.stringify(value)); } catch (e) { /* sin almacenamiento */ }
  }

  // Estado por temporada: { theme: idTema|null, favs: [ids] }.
  // 'theme' = tema visual activo (independiente de los favoritos).
  // Compatibilidad: las preferencias antiguas usaban { main, favs }.
  function readState(year) {
    var v = readJSON(KEY_PREFS + year);
    if (v && typeof v === 'object') {
      var favs = Array.isArray(v.favs) ? v.favs.slice() : [];
      var theme = (v.theme !== undefined) ? v.theme : (v.main || null);
      if (theme && favs.indexOf(theme) === -1) favs.push(theme);
      return { theme: theme || null, favs: favs };
    }
    return { theme: null, favs: [] };
  }

  function saveState(year, state) {
    writeJSON(KEY_PREFS + year, { theme: state.theme || null, favs: state.favs || [] });
  }

  // ---------------------------------------------------------- Color
  function hexToRgb(hex) {
    var h = String(hex).replace('#', '');
    if (h.length === 3) h = h[0] + h[0] + h[1] + h[1] + h[2] + h[2];
    var n = parseInt(h, 16);
    return { r: (n >> 16) & 255, g: (n >> 8) & 255, b: n & 255 };
  }

  function rgba(hex, a) {
    var c = hexToRgb(hex);
    return 'rgba(' + c.r + ',' + c.g + ',' + c.b + ',' + a + ')';
  }

  function luminance(hex) {
    var c = hexToRgb(hex);
    function ch(v) { v /= 255; return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4); }
    return 0.2126 * ch(c.r) + 0.7152 * ch(c.g) + 0.0722 * ch(c.b);
  }

  function onColor(hex) {
    var L = luminance(hex);
    var contrastWhite = 1.05 / (L + 0.05);
    var contrastBlack = (L + 0.05) / 0.05;
    return contrastWhite >= contrastBlack ? '#FFFFFF' : '#0B0B0D';
  }

  function resolveTeam(id) {
    var t = TEAMS[id];
    if (!t) return null;
    return { id: id, primary: t.p, secondary: t.s || t.p, accent: t.a || t.s || t.p };
  }

  function varsFor(team) {
    if (!team) return null;
    return {
      '--team-primary': team.primary,
      '--team-secondary': team.secondary,
      '--team-accent': team.accent,
      '--team-glow': rgba(team.primary, 0.4),
      '--team-primary-dim': rgba(team.primary, 0.14),
      '--team-primary-line': rgba(team.primary, 0.38),
      '--team-on-primary': onColor(team.primary)
    };
  }

  function applyTeam(team) {
    var vars = varsFor(team);
    if (!vars) {
      ['--team-primary', '--team-secondary', '--team-accent', '--team-glow',
       '--team-primary-dim', '--team-primary-line', '--team-on-primary'].forEach(function (k) {
        root.style.removeProperty(k);
      });
      root.removeAttribute('data-team');
      writeJSON(KEY_ACTIVE, null);
      return;
    }
    Object.keys(vars).forEach(function (k) { root.style.setProperty(k, vars[k]); });
    root.setAttribute('data-team', team.id);
    writeJSON(KEY_ACTIVE, { year: currentYear(), vars: vars });
  }

  function applyFromState() {
    var year = currentYear();
    var state = readState(year);
    applyTeam(state.theme ? resolveTeam(state.theme) : null);
    applyHero();
    try { document.dispatchEvent(new CustomEvent('todof1:teams-changed')); } catch (e) { /* sin CustomEvent */ }
  }

  // ---------------------------------------------------------- Hero (preparado)
  function heroCandidates(state) {
    var list = [];
    if (state.theme && TEAM_IMAGES[state.theme]) list = list.concat(TEAM_IMAGES[state.theme]);
    state.favs.forEach(function (id) {
      if (TEAM_IMAGES[id]) list = list.concat(TEAM_IMAGES[id]);
    });
    return list;
  }

  function applyHero() {
    var img = document.getElementById('hero-img');
    if (!img) return;
    var state = readState(currentYear());
    var general = img.getAttribute('data-general') || img.getAttribute('src');
    var candidates = heroCandidates(state);
    var src = candidates.length ? candidates[0] : general;
    if (src && img.getAttribute('src') !== src) img.setAttribute('src', src);
  }

  // ---------------------------------------------------------- Helpers públicos
  function namesFor(year) {
    var map = readJSON(KEY_MAP + year) || {};
    return { map: map };
  }

  function favoriteNames(year) {
    var y = year || currentYear();
    var map = (readJSON(KEY_MAP + y) || {});
    return readState(y).favs.map(function (id) { return map[id]; }).filter(Boolean);
  }

  function colorForName(name, year) {
    var y = year || currentYear();
    var map = (readJSON(KEY_MAP + y) || {});
    var st = readState(y);
    for (var i = 0; i < st.favs.length; i++) {
      if (map[st.favs[i]] && map[st.favs[i]].toLowerCase() === String(name).toLowerCase()) {
        return resolveTeam(st.favs[i]);
      }
    }
    return null;
  }

  // ---------------------------------------------------------- Modal (drawer)
  var panel = null, backdrop = null, grid = null, statusEl = null, themeGrid = null;
  var listCache = {};
  var pending = {};
  var openToken = 0;
  var lastFocused = null;

  function el(tag, cls, html) {
    var n = document.createElement(tag);
    if (cls) n.className = cls;
    if (html != null) n.innerHTML = html;
    return n;
  }

  function buildDrawer() {
    if (panel) return;

    backdrop = el('div', 'tf-drawer__backdrop');
    backdrop.addEventListener('click', closeDrawer);

    panel = el('aside', 'tf-drawer__panel');
    panel.setAttribute('role', 'dialog');
    panel.setAttribute('aria-modal', 'true');
    panel.setAttribute('aria-labelledby', 'tf-drawer-title');
    panel.setAttribute('tabindex', '-1');

    var head = el('div', 'tf-drawer__head');
    var titles = el('div');
    titles.appendChild(el('h2', 'tf-drawer__title', 'Mis equipos'));
    titles.appendChild(el('p', 'tf-drawer__sub', 'Elige tus escuder&iacute;as favoritas y el tema visual de TodoF1.'));
    head.appendChild(titles);
    var closeBtn = el('button', 'tf-drawer__close', '&times;');
    closeBtn.setAttribute('type', 'button');
    closeBtn.setAttribute('aria-label', 'Cerrar');
    closeBtn.addEventListener('click', closeDrawer);
    head.appendChild(closeBtn);
    panel.appendChild(head);

    var seasonLine = el('p', 'tf-drawer__season');
    seasonLine.innerHTML = 'Temporada <b id="tf-drawer-year">—</b> · datos reales';
    panel.appendChild(seasonLine);

    // --- Tema de la web (opción única) ---
    var themeGroup = el('section', 'tf-drawer__group');
    themeGroup.setAttribute('aria-labelledby', 'tf-theme-title');
    var themeTitle = el('h3', 'tf-drawer__group-title', 'Tema de la web');
    themeTitle.id = 'tf-theme-title';
    themeGroup.appendChild(themeTitle);
    themeGrid = el('div', 'theme-grid');
    themeGrid.setAttribute('role', 'group');
    themeGrid.setAttribute('aria-label', 'Tema visual de la web');
    themeGroup.appendChild(themeGrid);
    panel.appendChild(themeGroup);

    // --- Escuderías favoritas (selección múltiple) ---
    var favGroup = el('section', 'tf-drawer__group');
    favGroup.setAttribute('aria-labelledby', 'tf-fav-title');
    var favTitle = el('h3', 'tf-drawer__group-title', 'Escuder&iacute;as favoritas');
    favTitle.id = 'tf-fav-title';
    favGroup.appendChild(favTitle);
    statusEl = el('div', 'tf-drawer__status');
    grid = el('div', 'team-grid');
    favGroup.appendChild(statusEl);
    favGroup.appendChild(grid);
    panel.appendChild(favGroup);

    var foot = el('p', 'tf-drawer__foot', 'Marca varias favoritas. El tema visual solo cambia cuando lo seleccionas.');
    panel.appendChild(foot);

    document.body.appendChild(backdrop);
    document.body.appendChild(panel);

    panel.addEventListener('keydown', onPanelKeydown);
  }

  function onPanelKeydown(event) {
    if (event.key === 'Escape') { event.preventDefault(); closeDrawer(); return; }
    if (event.key !== 'Tab') return;
    var focusables = panel.querySelectorAll('button, [href], input, select, [tabindex]:not([tabindex="-1"])');
    if (!focusables.length) return;
    var first = focusables[0];
    var last = focusables[focusables.length - 1];
    if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
    else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
  }

  function showStatus(html) {
    statusEl.hidden = false;
    statusEl.style.display = '';
    statusEl.innerHTML = html;
  }

  function hideStatus() {
    statusEl.innerHTML = '';
    statusEl.hidden = true;
    statusEl.style.display = 'none';
  }

  function ensureTeams(year) {
    if (listCache[year]) return Promise.resolve(listCache[year]);
    if (pending[year]) return pending[year];
    var request = fetch(API + '/' + year + '/constructors.json')
      .then(function (r) {
        if (r && r.ok === false) throw new Error('HTTP ' + r.status);
        return r.json();
      })
      .then(function (data) {
        var list = (data.MRData && data.MRData.ConstructorTable && data.MRData.ConstructorTable.Constructors) || [];
        listCache[year] = list;
        delete pending[year];
        return list;
      }, function (err) {
        delete pending[year];
        throw err;
      });
    pending[year] = request;
    return request;
  }

  function openDrawer() {
    buildDrawer();
    lastFocused = document.activeElement;
    var year = currentYear();
    var yearEl = document.getElementById('tf-drawer-year');
    if (yearEl) yearEl.textContent = year;

    root.setAttribute('data-drawer', 'open');
    var token = ++openToken;
    loadYear(year, token);

    var openBtn = document.getElementById('tf-teams-btn');
    if (openBtn) openBtn.setAttribute('aria-expanded', 'true');
    panel.focus();
  }

  function loadYear(year, token) {
    if (listCache[year]) {
      renderTeams(year, listCache[year]);
      return;
    }
    grid.innerHTML = '';
    showStatus('<span class="tf-spinner" aria-hidden="true"></span><span class="tf-drawer__msg">Cargando escuder&iacute;as…</span>');
    ensureTeams(year).then(function (list) {
      if (token !== openToken) return; // panel cerrado o temporada cambiada
      renderTeams(year, list);
    }, function () {
      if (token !== openToken) return;
      showError(year, token);
    });
  }

  function showError(year, token) {
    grid.innerHTML = '';
    showStatus('<span class="tf-drawer__msg">No se pudieron cargar las escuder&iacute;as de esta temporada.</span>');
    var retry = el('button', 'tf-drawer__retry', 'Reintentar');
    retry.setAttribute('type', 'button');
    retry.addEventListener('click', function () {
      delete listCache[year];
      loadYear(year, openToken);
    });
    statusEl.appendChild(retry);
  }

  function closeDrawer() {
    openToken++;
    root.removeAttribute('data-drawer');
    var openBtn = document.getElementById('tf-teams-btn');
    if (openBtn) { openBtn.setAttribute('aria-expanded', 'false'); openBtn.focus(); }
    else if (lastFocused && lastFocused.focus) { lastFocused.focus(); }
  }

  function renderTeams(year, list) {
    if (!list || !list.length) {
      grid.innerHTML = '';
      showStatus('<span class="tf-drawer__msg">Esta temporada no tiene escuder&iacute;as con identidad visual. Se usar&aacute; el tema original.</span>');
      renderThemeOptions(year);
      renderStates();
      return;
    }
    // Guarda id -> nombre para destacar favoritos en las clasificaciones.
    var map = {};
    list.forEach(function (c) { map[c.constructorId] = c.name; });
    writeJSON(KEY_MAP + year, map);

    hideStatus();
    grid.innerHTML = '';
    list.forEach(function (c) {
      grid.appendChild(buildCard(year, c));
    });
    renderThemeOptions(year);
    renderStates();
  }

  function buildCard(year, ctor) {
    var team = resolveTeam(ctor.constructorId);
    var card = el('div', 'team-card');
    card.setAttribute('data-id', ctor.constructorId);

    var fav = el('button', 'team-card__fav');
    fav.setAttribute('type', 'button');
    fav.setAttribute('aria-pressed', 'false');
    fav.setAttribute('aria-label', 'Añadir o quitar ' + ctor.name + ' de favoritos');

    var sw = el('span', 'team-card__swatches');
    if (team) {
      sw.innerHTML = '<i style="background:' + team.primary + '"></i><i style="background:' + team.secondary + '"></i><i style="background:' + team.accent + '"></i>';
    } else {
      sw.innerHTML = '<i style="background:#6E6E77"></i><i style="background:#A1A1AA"></i><i style="background:#C9CBD1"></i>';
    }
    fav.appendChild(sw);
    fav.appendChild(el('span', 'team-card__name', escHtml(ctor.name)));
    if (!team) fav.appendChild(el('span', 'team-card__hint', 'Sin tema'));

    fav.addEventListener('click', function () { toggleFav(year, ctor.constructorId); });

    card.appendChild(fav);
    return card;
  }

  // ---------------------------------------------------------- Tema de la web
  function renderThemeOptions(year) {
    if (!themeGrid) return;
    var st = readState(year);
    var map = readJSON(KEY_MAP + year) || {};
    themeGrid.innerHTML = '';
    themeGrid.appendChild(buildOriginalOption(year));
    st.favs.forEach(function (id) {
      var team = resolveTeam(id);
      if (!team) return;
      themeGrid.appendChild(buildThemeOption(year, id, map[id] || id, team));
    });
  }

  function buildOriginalOption(year) {
    var btn = el('button', 'theme-opt');
    btn.setAttribute('type', 'button');
    btn.setAttribute('data-theme-id', '');
    btn.setAttribute('aria-pressed', 'false');
    btn.innerHTML =
      '<span class="theme-opt__swatches"><i style="background:#E10600"></i><i style="background:#0B0B0D"></i><i style="background:#F5F5F5"></i></span>' +
      '<span class="theme-opt__text"><b>TodoF1 original</b><small>Rojo y negro</small></span>' +
      '<span class="theme-opt__check" aria-hidden="true">&#10003;</span>';
    btn.addEventListener('click', function () { setTheme(year, null); });
    return btn;
  }

  function buildThemeOption(year, id, name, team) {
    var btn = el('button', 'theme-opt');
    btn.setAttribute('type', 'button');
    btn.setAttribute('data-theme-id', id);
    btn.setAttribute('aria-pressed', 'false');
    btn.innerHTML =
      '<span class="theme-opt__swatches"><i style="background:' + team.primary + '"></i><i style="background:' + team.secondary + '"></i><i style="background:' + team.accent + '"></i></span>' +
      '<span class="theme-opt__text"><b>' + escHtml(name) + '</b><small>Tema activo</small></span>' +
      '<span class="theme-opt__check" aria-hidden="true">&#10003;</span>';
    btn.addEventListener('click', function () { setTheme(year, id); });
    return btn;
  }

  function setTheme(year, id) {
    var st = readState(year);
    st.theme = id || null;
    saveState(year, st);
    applyFromState();
    renderStates();
  }

  function escHtml(v) {
    return String(v == null ? '' : v).replace(/[&<>"']/g, function (ch) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch];
    });
  }

  function toggleFav(year, id) {
    var st = readState(year);
    var i = st.favs.indexOf(id);
    if (i === -1) {
      st.favs.push(id);
    } else {
      st.favs.splice(i, 1);
      if (st.theme === id) st.theme = null; // si el tema activo deja de ser favorito -> original
    }
    saveState(year, st);
    applyFromState();
    renderThemeOptions(year);
    renderStates();
  }

  function renderStates() {
    if (!grid) return;
    var year = currentYear();
    var st = readState(year);

    if (themeGrid) {
      var opts = themeGrid.querySelectorAll('.theme-opt');
      Array.prototype.forEach.call(opts, function (opt) {
        var tid = opt.getAttribute('data-theme-id') || '';
        var active = (tid === '') ? !st.theme : (st.theme === tid);
        opt.classList.toggle('is-active', active);
        opt.setAttribute('aria-pressed', active ? 'true' : 'false');
      });
    }

    var cards = grid.querySelectorAll('.team-card');
    Array.prototype.forEach.call(cards, function (card) {
      var id = card.getAttribute('data-id');
      var isFav = st.favs.indexOf(id) !== -1;
      card.classList.toggle('is-fav', isFav);
      var fav = card.querySelector('.team-card__fav');
      if (fav) fav.setAttribute('aria-pressed', isFav ? 'true' : 'false');
    });
  }

  // ---------------------------------------------------------- Init
  function init() {
    var btn = document.getElementById('tf-teams-btn');
    if (btn) {
      btn.addEventListener('click', openDrawer);
      btn.setAttribute('aria-expanded', 'false');
      btn.setAttribute('aria-haspopup', 'dialog');
    }
    applyFromState();
  }

  window.TodoF1Teams = {
    year: currentYear,
    state: function (y) { return readState(y || currentYear()); },
    favoriteNames: favoriteNames,
    colorForName: colorForName,
    apply: applyFromState,
    open: function () { openDrawer(); },
    _internal: { hexToRgb: hexToRgb, rgba: rgba, luminance: luminance, onColor: onColor, resolveTeam: resolveTeam, varsFor: varsFor, TEAMS: TEAMS }
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
