/* ============================================================
   TodoF1 — Selector de tema visual
   - Un único tema activo a la vez.
   - Escuderías obtenidas de los datos reales, por temporada.
   - Colores por constructorId (identidad visual de cada equipo).
   - Preferencia guardada en localStorage por temporada
     (independiente del modo claro/oscuro).
   - Aplica variables CSS globales --team-* (no recolorea la página).
   ============================================================ */
(function () {
  'use strict';

  var root = document.documentElement;
  var YEAR_MIN = 1950;
  var YEAR_MAX = 2026;
  var API = 'https://api.jolpi.ca/ergast/f1';
  var KEY_THEME = 'todof1:teams:';     // preferencia (compat) por temporada
  var KEY_ACTIVE = 'todof1:teams-active';
  var TEAM_VARS = ['--team-primary', '--team-secondary', '--team-accent', '--team-glow',
    '--team-primary-dim', '--team-primary-line', '--team-on-primary', '--team-stripe'];

  // Identidad visual por constructorId. Sin entrada => tema original.
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

  // Imágenes por equipo (pendiente de assets con permisos). Vacío => general.
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

  // Estado por temporada: { theme: idTema|null }. Compatibilidad: { main } previo.
  function readState(year) {
    var v = readJSON(KEY_THEME + year);
    if (v && typeof v === 'object') {
      var theme = (v.theme !== undefined) ? v.theme : (v.main || null);
      return { theme: theme || null };
    }
    return { theme: null };
  }

  function saveState(year, state) {
    writeJSON(KEY_THEME + year, { theme: state.theme || null });
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
    var cw = 1.05 / (L + 0.05);
    var cb = (L + 0.05) / 0.05;
    return cw >= cb ? '#FFFFFF' : '#0B0B0D';
  }

  function resolveTeam(id) {
    var t = TEAMS[id];
    if (!t) return null;
    return { id: id, primary: t.p, secondary: t.s || t.p, accent: t.a || t.s || t.p };
  }

  // Colores únicos del tema, manteniendo el orden original (sin repetir).
  function uniqueColors(team) {
    var order = [team.primary, team.secondary, team.accent];
    var out = [], seen = {};
    order.forEach(function (c) {
      if (!c) return;
      var k = String(c).toLowerCase();
      if (!seen[k]) { seen[k] = 1; out.push(c); }
    });
    return out;
  }

  // Degradado con transiciones suaves: 1, 2 o 3 colores (cada uno ~ su parte).
  function stripeGradient(team) {
    if (!team) return null;
    var c = uniqueColors(team);
    if (c.length <= 1) return 'linear-gradient(90deg, ' + c[0] + ', ' + c[0] + ')';
    if (c.length === 2) {
      return 'linear-gradient(90deg, ' + c[0] + ' 0%, ' + c[0] + ' 44%, ' + c[1] + ' 56%, ' + c[1] + ' 100%)';
    }
    return 'linear-gradient(90deg, '
      + c[0] + ' 0%, ' + c[0] + ' 28%, '
      + c[1] + ' 39%, ' + c[1] + ' 61%, '
      + c[2] + ' 72%, ' + c[2] + ' 100%)';
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
      '--team-on-primary': onColor(team.primary),
      '--team-stripe': stripeGradient(team)
    };
  }

  function applyTeam(team) {
    var vars = varsFor(team);
    if (!vars) {
      TEAM_VARS.forEach(function (k) { root.style.removeProperty(k); });
      root.removeAttribute('data-team');
      writeJSON(KEY_ACTIVE, null);
      return;
    }
    Object.keys(vars).forEach(function (k) { root.style.setProperty(k, vars[k]); });
    root.setAttribute('data-team', team.id);
    writeJSON(KEY_ACTIVE, { year: currentYear(), vars: vars });
  }

  function applyFromState() {
    var state = readState(currentYear());
    applyTeam(state.theme ? resolveTeam(state.theme) : null);
    applyHero();
  }

  // ---------------------------------------------------------- Hero (preparado)
  function applyHero() {
    var img = document.getElementById('hero-img');
    if (!img) return;
    var state = readState(currentYear());
    var general = img.getAttribute('data-general') || img.getAttribute('src');
    var candidates = (state.theme && TEAM_IMAGES[state.theme]) ? TEAM_IMAGES[state.theme] : [];
    var src = candidates.length ? candidates[0] : general;
    if (src && img.getAttribute('src') !== src) img.setAttribute('src', src);
  }

  // ---------------------------------------------------------- Panel (drawer)
  var panel = null, backdrop = null, themeGrid = null, statusEl = null;
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

  function escHtml(v) {
    return String(v == null ? '' : v).replace(/[&<>"']/g, function (ch) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch];
    });
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
    titles.appendChild(el('h2', 'tf-drawer__title', 'Elegir tema'));
    titles.appendChild(el('p', 'tf-drawer__sub', 'Aplica los colores de una escuder&iacute;a a toda la web.'));
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

    var group = el('section', 'tf-drawer__group');
    group.setAttribute('aria-labelledby', 'tf-theme-title');
    var title = el('h3', 'tf-drawer__group-title', 'Tema de la web');
    title.id = 'tf-theme-title';
    group.appendChild(title);
    statusEl = el('div', 'tf-drawer__status');
    group.appendChild(statusEl);
    themeGrid = el('div', 'theme-grid');
    themeGrid.setAttribute('role', 'radiogroup');
    themeGrid.setAttribute('aria-label', 'Tema visual de la web');
    group.appendChild(themeGrid);
    panel.appendChild(group);

    panel.appendChild(el('p', 'tf-drawer__foot', 'Solo puede haber un tema activo. Se guarda por temporada.'));

    document.body.appendChild(backdrop);
    document.body.appendChild(panel);

    panel.addEventListener('keydown', onPanelKeydown);
  }

  function onPanelKeydown(event) {
    if (event.key === 'Escape') { event.preventDefault(); closeDrawer(); return; }
    if (event.key !== 'Tab') return;
    var focusables = panel.querySelectorAll('button:not([disabled]), [href], [tabindex]:not([tabindex="-1"])');
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

    var openBtn = document.getElementById('tf-theme-picker');
    if (openBtn) openBtn.setAttribute('aria-expanded', 'true');
    panel.focus();
  }

  function loadYear(year, token) {
    if (listCache[year]) {
      renderOptions(year, listCache[year]);
      return;
    }
    themeGrid.innerHTML = '';
    showStatus('<span class="tf-spinner" aria-hidden="true"></span><span class="tf-drawer__msg">Cargando escuder&iacute;as…</span>');
    ensureTeams(year).then(function (list) {
      if (token !== openToken) return;
      renderOptions(year, list);
    }, function () {
      if (token !== openToken) return;
      showError(year, token);
    });
  }

  function showError(year, token) {
    themeGrid.innerHTML = '';
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
    var openBtn = document.getElementById('tf-theme-picker');
    if (openBtn) { openBtn.setAttribute('aria-expanded', 'false'); openBtn.focus(); }
    else if (lastFocused && lastFocused.focus) { lastFocused.focus(); }
  }

  function renderOptions(year, list) {
    if (!themeGrid) return;
    themeGrid.innerHTML = '';
    themeGrid.appendChild(buildOriginalOption(year));
    if (!list || !list.length) {
      showStatus('<span class="tf-drawer__msg">Esta temporada no tiene escuder&iacute;as con identidad visual. Se usa el tema original.</span>');
      renderStates();
      return;
    }
    hideStatus();
    list.forEach(function (c) {
      themeGrid.appendChild(buildTeamOption(year, c.constructorId, c.name, resolveTeam(c.constructorId)));
    });
    renderStates();
  }

  function buildOriginalOption(year) {
    var btn = el('button', 'theme-opt');
    btn.setAttribute('type', 'button');
    btn.setAttribute('role', 'radio');
    btn.setAttribute('data-theme-id', '');
    btn.setAttribute('aria-checked', 'false');
    btn.innerHTML =
      '<span class="theme-opt__swatches"><i style="background:#E10600"></i><i style="background:#0B0B0D"></i><i style="background:#F5F5F5"></i></span>' +
      '<span class="theme-opt__text"><b>TodoF1 original</b><small>Rojo y negro</small></span>' +
      '<span class="theme-opt__check" aria-hidden="true">&#10003;</span>';
    btn.addEventListener('click', function () { setTheme(year, null); });
    return btn;
  }

  function buildTeamOption(year, id, name, team) {
    var btn = el('button', 'theme-opt');
    btn.setAttribute('type', 'button');
    btn.setAttribute('role', 'radio');
    btn.setAttribute('data-theme-id', id);
    btn.setAttribute('aria-checked', 'false');
    var sw = team
      ? '<i style="background:' + team.primary + '"></i><i style="background:' + team.secondary + '"></i><i style="background:' + team.accent + '"></i>'
      : '<i style="background:#6E6E77"></i><i style="background:#A1A1AA"></i><i style="background:#C9CBD1"></i>';
    btn.innerHTML =
      '<span class="theme-opt__swatches">' + sw + '</span>' +
      '<span class="theme-opt__text"><b>' + escHtml(name) + '</b><small>' + (team ? 'Escuder&iacute;a' : 'Sin tema disponible') + '</small></span>' +
      '<span class="theme-opt__check" aria-hidden="true">&#10003;</span>';
    if (team) {
      btn.addEventListener('click', function () { setTheme(year, id); });
    } else {
      btn.disabled = true;
      btn.setAttribute('aria-disabled', 'true');
    }
    return btn;
  }

  function setTheme(year, id) {
    var st = readState(year);
    st.theme = id || null;
    saveState(year, st);
    applyFromState();
    renderStates();
  }

  function renderStates() {
    if (!themeGrid) return;
    var st = readState(currentYear());
    var opts = themeGrid.querySelectorAll('.theme-opt');
    Array.prototype.forEach.call(opts, function (opt) {
      var tid = opt.getAttribute('data-theme-id') || '';
      var active = (tid === '') ? !st.theme : (st.theme === tid);
      opt.classList.toggle('is-active', active);
      opt.setAttribute('aria-checked', active ? 'true' : 'false');
    });
  }

  // ---------------------------------------------------------- Init
  function init() {
    var btn = document.getElementById('tf-theme-picker');
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
    apply: applyFromState,
    open: function () { openDrawer(); },
    _internal: { hexToRgb: hexToRgb, rgba: rgba, luminance: luminance, onColor: onColor, resolveTeam: resolveTeam, varsFor: varsFor, uniqueColors: uniqueColors, stripeGradient: stripeGradient, TEAMS: TEAMS }
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
