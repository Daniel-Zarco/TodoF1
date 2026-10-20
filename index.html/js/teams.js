/* ============================================================
   TodoF1 — Selector de tema visual
   - Un único tema activo a la vez.
   - Paleta fija de los 11 equipos de 2026, disponible en toda temporada.
   - Colores por constructorId (identidad visual de cada equipo).
   - Preferencia única y global en localStorage (independiente de la temporada).
   - Aplica variables CSS globales --team-* (no recolorea la página).
   ============================================================ */
(function () {
  'use strict';

  var root = document.documentElement;
  var YEAR_MIN = 1950;
  var YEAR_MAX = 2026;
  try { localStorage.removeItem('todof1:teams-active'); } catch (e) { /* sin almacenamiento */ }
  var KEY_THEME = 'todof1:theme';       // preferencia global única
  var LEGACY_PREFIX = 'todof1:teams:'; // claves antiguas, solo para migrar
  var TEAM_VARS = ['--team-primary', '--team-secondary', '--team-accent', '--team-glow',
    '--team-primary-dim', '--team-primary-line', '--team-on-primary', '--team-stripe'];

  // Identidad visual por constructorId. Sin entrada => tema original.
  var TEAMS = {
    ferrari: { p: '#E10600', s: '#FFF200', a: '#FFF200' },
    mercedes: { p: '#00B6A3', s: '#C8CCCE', a: '#27F4D2' },
    red_bull: { p: '#203A70', s: '#FFCC00', a: '#E10600' },
    mclaren: { p: '#FF8000', s: '#47C7FC', a: '#47C7FC' },
    aston_martin: { p: '#00665E', s: '#CEDC00', a: '#CEDC00' },
    alpine: { p: '#2293D1', s: '#F2466E', a: '#F2466E' },
    williams: { p: '#0091C7', s: '#64C4FF', a: '#64C4FF' },
    rb: { p: '#F2F5FA', s: '#1B2D6B', a: '#1B2D6B' },
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

  // Paleta fija del selector: las 11 escuderías de 2026 para cualquier temporada.
  var THEME_TEAMS_2026 = [
    { id: 'alpine', name: 'Alpine F1 Team' },
    { id: 'aston_martin', name: 'Aston Martin' },
    { id: 'audi', name: 'Audi' },
    { id: 'cadillac', name: 'Cadillac F1 Team' },
    { id: 'ferrari', name: 'Ferrari' },
    { id: 'haas', name: 'Haas F1 Team' },
    { id: 'mclaren', name: 'McLaren' },
    { id: 'mercedes', name: 'Mercedes' },
    { id: 'rb', name: 'RB F1 Team' },
    { id: 'red_bull', name: 'Red Bull' },
    { id: 'williams', name: 'Williams' }
  ];

  // Imágenes del hero por escudería, usando los constructorId reales y las
  // rutas/formatos REALES de /Images/heroes/escuderias (formatos mixtos).
  // Si el archivo falla, se usa una imagen válida de /Images/heroes/default.
  // `credit` es opcional: { label, author, license, licenseUrl, source }.
  var TEAM_IMAGES = {
    ferrari: { src: '/Images/heroes/escuderias/ferrari.png' },
    mclaren: { src: '/Images/heroes/escuderias/mclaren.jpg' },
    mercedes: { src: '/Images/heroes/escuderias/mercedes.avif' },
    red_bull: { src: '/Images/heroes/escuderias/red_bull.avif' },
    rb: { src: '/Images/heroes/escuderias/rb.webp' },
    aston_martin: { src: '/Images/heroes/escuderias/aston_martin.webp' },
    alpine: { src: '/Images/heroes/escuderias/alpine.webp' },
    williams: { src: '/Images/heroes/escuderias/williams.avif' },
    haas: { src: '/Images/heroes/escuderias/haas.avif' },
    audi: { src: '/Images/heroes/escuderias/audi.jpg' },
    cadillac: { src: '/Images/heroes/escuderias/cadillac.jpg' }
  };

  // Imágenes del carrusel del tema «TodoF1 original», en orden por nombre.
  // Se resuelven en init() para que la lista del servidor (definida más abajo
  // en el <head>) esté disponible aunque este script se cargue antes.
  var DEFAULT_FALLBACK = [
    '/Images/heroes/default/a.jpg',
    '/Images/heroes/default/b.jpg',
    '/Images/heroes/default/c.jpg',
    '/Images/heroes/default/d.jpg',
    '/Images/heroes/default/e.jpg'
  ];
  var DEFAULT_IMAGES = DEFAULT_FALLBACK.slice();

  // Imagen de respaldo si alguna falla (una imagen válida de default).
  var FALLBACK_IMAGE = DEFAULT_IMAGES[0];

  // Créditos de las imágenes de default (sin datos de autoría => pendiente).
  var DEFAULT_CREDITS = [];

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

  // Preferencia global: { theme: idTema|null }. Compatibilidad: { main } previo.
  var legacyChecked = false;

  function legacyTheme(year) {
    var keys = [];
    try {
      for (var i = 0; i < localStorage.length; i++) {
        var key = localStorage.key(i);
        if (key && key.indexOf(LEGACY_PREFIX) === 0) keys.push(key);
      }
    } catch (e) { return { found: false, theme: null }; }

    var preferred = LEGACY_PREFIX + (year || currentYear());
    keys.sort(function (a, b) {
      if (a === preferred) return -1;
      if (b === preferred) return 1;
      return a < b ? 1 : -1;
    });
    for (var k = 0; k < keys.length; k++) {
      var value = readJSON(keys[k]);
      if (value && typeof value === 'object') {
        var theme = (value.theme !== undefined) ? value.theme : (value.main || null);
        return { found: true, theme: theme || null };
      }
    }
    return { found: false, theme: null };
  }

  function removeLegacyKeys() {
    try {
      var keys = [];
      for (var i = 0; i < localStorage.length; i++) {
        var key = localStorage.key(i);
        if (key && key.indexOf(LEGACY_PREFIX) === 0) keys.push(key);
      }
      keys.forEach(function (key) { localStorage.removeItem(key); });
      localStorage.removeItem('todof1:teams-active');
    } catch (e) { /* sin almacenamiento */ }
  }

  function readState(year) {
    var v = readJSON(KEY_THEME);
    if (v !== null) {
      if (typeof v === 'string') return { theme: v || null };
      if (typeof v === 'object') {
        var theme = (v.theme !== undefined) ? v.theme : (v.main || null);
        return { theme: theme || null };
      }
      return { theme: null };
    }

    if (legacyChecked) return { theme: null };
    legacyChecked = true;
    var legacy = legacyTheme(year);
    if (legacy.found) {
      saveState({ theme: legacy.theme });
      removeLegacyKeys();
      return { theme: legacy.theme };
    }
    return { theme: null };
  }

  function saveState(state) {
    legacyChecked = true;
    writeJSON(KEY_THEME, { theme: state.theme || null });
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

  // Degradado cíclico (empieza y termina con el primer color) para que el
  // "Gradient Flow" del navbar se repita sin saltos. 1, 2 o 3 colores.
  function stripeGradient(team) {
    if (!team) return null;
    var c = uniqueColors(team);
    var n = c.length;
    if (n <= 1) return 'linear-gradient(90deg, ' + c[0] + ', ' + c[0] + ')';
    var stops = [c[0] + ' 0%'];
    for (var i = 1; i < n; i++) {
      stops.push(c[i] + ' ' + ((i * 100 / n).toFixed(3)) + '%');
    }
    stops.push(c[0] + ' 100%');
    return 'linear-gradient(90deg, ' + stops.join(', ') + ')';
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

  function stripeMode(team) {
    return (team && uniqueColors(team).length > 1) ? 'flow' : 'static';
  }

  function applyTeam(team) {
    var vars = varsFor(team);
    if (!vars) {
      TEAM_VARS.forEach(function (k) { root.style.removeProperty(k); });
      root.removeAttribute('data-team');
      root.removeAttribute('data-team-stripe');
      root.removeAttribute('data-team-colors');
      return;
    }
    Object.keys(vars).forEach(function (k) { root.style.setProperty(k, vars[k]); });
    root.setAttribute('data-team', team.id);
    root.setAttribute('data-team-stripe', stripeMode(team));
    // Nº de colores reales del tema (2 o 3): decide el ciclo de la línea inferior.
    root.setAttribute('data-team-colors', String(uniqueColors(team).length));
  }

  function applyThemeOnly() {
    var state = readState();
    applyTeam(state.theme ? resolveTeam(state.theme) : null);
  }

  function applyFromState(animate) {
    applyThemeOnly();
    applyHero(animate);
  }

  // ---------------------------------------------------------- Hero (imagen por tema)
  var HERO_FADE_MS = 700;       // fundido cruzado
  var CAROUSEL_MS = 8000;       // carrusel del tema original
  var heroShown = null;         // src actualmente visible (capa base, ya versionado)
  var heroToken = 0;            // invalida operaciones async al cambiar de tema
  var carouselTimer = null;     // temporizador del carrusel
  var carouselIndex = 0;        // índice actual dentro de DEFAULT_IMAGES
  var carouselActive = false;   // carrusel (tema original) activo

  function teamHeroSrc(id) {
    var entry = id ? TEAM_IMAGES[id] : null;
    return (entry && entry.src) ? entry.src : null;
  }

  function prefersReducedMotion() {
    try {
      return !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);
    } catch (e) { return false; }
  }

  // Cache busting de imágenes: añade ?v=<fecha de modificación> (mapa del servidor).
  function versionedSrc(src) {
    if (!src) return src;
    var versions = window.TodoF1HeroVersions;
    if (!versions) return src;
    var v = versions[src];
    if (v == null || v === '') return src;
    return src + (src.indexOf('?') >= 0 ? '&' : '?') + 'v=' + v;
  }

  function preloadImage(src) {
    return new Promise(function (resolve) {
      var im = new Image();
      im.onload = function () { resolve(true); };
      im.onerror = function () { resolve(false); };
      im.src = versionedSrc(src);
    });
  }

  function stopCarousel() {
    if (carouselTimer) {
      clearInterval(carouselTimer);
      carouselTimer = null;
    }
  }

  // Precarga las imágenes adyacentes del carrusel para evitar parpadeos.
  function preloadAround() {
    if (DEFAULT_IMAGES.length < 2 || prefersReducedMotion()) return;
    var len = DEFAULT_IMAGES.length;
    preloadImage(DEFAULT_IMAGES[(carouselIndex + 1) % len]);
    preloadImage(DEFAULT_IMAGES[(carouselIndex - 1 + len) % len]);
  }

  // Muestra `src` en el hero (con fundido si `animate`). Cae a FALLBACK_IMAGE si falla.
  function setHeroImage(src, animate, token) {
    var img = document.getElementById('hero-img');
    if (!img) return;
    var full = versionedSrc(src);
    if (full === heroShown) return;
    var next = document.getElementById('hero-img-next');
    var fade = animate && !prefersReducedMotion() && next;

    preloadImage(src).then(function (ok) {
      if (token !== heroToken) return;
      var finalSrc = ok ? full : versionedSrc(FALLBACK_IMAGE);
      if (finalSrc === heroShown) return;

      if (!fade) {
        img.setAttribute('src', finalSrc);
        heroShown = finalSrc;
        return;
      }

      var settled = false;
      function commit() {
        if (settled || token !== heroToken) return;
        settled = true;
        next.removeEventListener('transitionend', commit);
        img.setAttribute('src', finalSrc);
        heroShown = finalSrc;
        next.classList.remove('is-visible');
      }
      next.setAttribute('src', finalSrc);
      requestAnimationFrame(function () { if (token === heroToken) next.classList.add('is-visible'); });
      next.addEventListener('transitionend', commit);
      setTimeout(commit, HERO_FADE_MS + 400);
    });
  }

  function startCarousel() {
    stopCarousel();
    if (!carouselActive || DEFAULT_IMAGES.length < 2 || prefersReducedMotion()) return;
    preloadAround();
    carouselTimer = setInterval(function () {
      carouselIndex = (carouselIndex + 1) % DEFAULT_IMAGES.length;
      setHeroImage(DEFAULT_IMAGES[carouselIndex], true, heroToken);
      preloadAround();
    }, CAROUSEL_MS);
  }

  // Reinicia el contador de 8 s tras una interacción manual.
  function restartCarousel() {
    if (carouselActive) startCarousel();
  }

  // Navegación manual del carrusel (solo tema original). `delta` = +1 / -1.
  function goToImage(delta) {
    if (!carouselActive || DEFAULT_IMAGES.length < 2) return;
    var len = DEFAULT_IMAGES.length;
    carouselIndex = ((carouselIndex + delta) % len + len) % len;
    var token = ++heroToken;   // cancela fundidos en curso (clics rápidos)
    setHeroImage(DEFAULT_IMAGES[carouselIndex], true, token);
    preloadAround();
    restartCarousel();
  }

  function nextImage() { goToImage(1); }
  function prevImage() { goToImage(-1); }

  function setCarouselActive(on) {
    carouselActive = !!on;
    var hero = document.getElementById('hero');
    if (!hero) return;
    if (carouselActive) hero.setAttribute('data-hero-carousel', 'on');
    else hero.removeAttribute('data-hero-carousel');
  }

  function initHeroNav() {
    var hero = document.getElementById('hero');
    if (!hero) return;

    var prev = hero.querySelector('.hero__nav--prev');
    var next = hero.querySelector('.hero__nav--next');
    if (prev) prev.addEventListener('click', function () { prevImage(); });
    if (next) next.addEventListener('click', function () { nextImage(); });

    // Deslizar en móvil (usa touchend; no intercepta el scroll vertical).
    var startX = null, startY = null;
    hero.addEventListener('touchstart', function (e) {
      if (!carouselActive || e.touches.length !== 1) { startX = null; return; }
      startX = e.touches[0].clientX;
      startY = e.touches[0].clientY;
    }, { passive: true });
    hero.addEventListener('touchend', function (e) {
      if (startX === null || !carouselActive) return;
      var t = e.changedTouches && e.changedTouches[0];
      if (!t) return;
      var dx = t.clientX - startX;
      var dy = t.clientY - startY;
      startX = null; startY = null;
      if (Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy) * 1.2) {
        if (dx < 0) nextImage(); else prevImage();
      }
    }, { passive: true });
  }

  // Aplica la imagen del hero del tema activo. `animate` activa el fundido cruzado.
  function applyHero(animate) {
    var img = document.getElementById('hero-img');
    if (!img) return;
    var token = ++heroToken;
    stopCarousel();
    if (heroShown === null) heroShown = img.getAttribute('src') || versionedSrc(FALLBACK_IMAGE);

    var state = readState();
    if (state.theme) {
      setCarouselActive(false);
      setHeroImage(teamHeroSrc(state.theme) || FALLBACK_IMAGE, animate, token);
      return;
    }

    // Tema original: carrusel, empezando por la primera imagen.
    setCarouselActive(true);
    carouselIndex = 0;
    setHeroImage(DEFAULT_IMAGES[0] || FALLBACK_IMAGE, animate, token);
    startCarousel();
  }

  // ---------------------------------------------------------- Panel (drawer)
  var panel = null, backdrop = null, themeGrid = null, statusEl = null;
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
    seasonLine.innerHTML = 'Temporada <b id="tf-drawer-year">—</b> · los 11 temas están disponibles en todas las temporadas';
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

    panel.appendChild(el('p', 'tf-drawer__foot', 'Solo puede haber un tema activo. Se guarda para todas las temporadas.'));

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

  function openDrawer() {
    buildDrawer();
    lastFocused = document.activeElement;
    var year = currentYear();
    var yearEl = document.getElementById('tf-drawer-year');
    if (yearEl) yearEl.textContent = year;

    root.setAttribute('data-drawer', 'open');
    openToken++;
    loadYear(year);

    var openBtn = document.getElementById('tf-theme-picker');
    if (openBtn) openBtn.setAttribute('aria-expanded', 'true');
    panel.focus();
  }

  function loadYear(year) {
    renderOptions(year);
  }

  function closeDrawer() {
    openToken++;
    root.removeAttribute('data-drawer');
    var openBtn = document.getElementById('tf-theme-picker');
    if (openBtn) { openBtn.setAttribute('aria-expanded', 'false'); openBtn.focus(); }
    else if (lastFocused && lastFocused.focus) { lastFocused.focus(); }
  }

  function renderOptions(year) {
    if (!themeGrid) return;
    themeGrid.innerHTML = '';
    themeGrid.appendChild(buildOriginalOption(year));
    hideStatus();
    THEME_TEAMS_2026.forEach(function (t) {
      themeGrid.appendChild(buildTeamOption(year, t.id, t.name, resolveTeam(t.id)));
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
    btn.addEventListener('click', function () { setTheme(null); });
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
      btn.addEventListener('click', function () { setTheme(id); });
    } else {
      btn.disabled = true;
      btn.setAttribute('aria-disabled', 'true');
    }
    return btn;
  }

  function setTheme(id) {
    var st = readState();
    st.theme = id || null;
    saveState(st);
    applyFromState(true);
    renderStates();
    // El selector se cierra solo al elegir (o repetir) un tema: sin recarga,
    // sin perder la posición de scroll y conservando la preferencia guardada.
    closeDrawer();
    // En móvil, cerramos también el menú: es quien libera el scroll y los
    // bloqueos de interacción (reutiliza el cierre de navbar.js).
    var nav = window.TodoF1Nav;
    if (nav && typeof nav.isOpen === 'function' && typeof nav.close === 'function' && nav.isOpen()) {
      nav.close();
    }
  }

  function renderStates() {
    if (!themeGrid) return;
    var st = readState();
    var opts = themeGrid.querySelectorAll('.theme-opt');
    Array.prototype.forEach.call(opts, function (opt) {
      var tid = opt.getAttribute('data-theme-id') || '';
      var active = (tid === '') ? !st.theme : (st.theme === tid);
      opt.classList.toggle('is-active', active);
      opt.setAttribute('aria-checked', active ? 'true' : 'false');
    });
  }

  // ---------------------------------------------------------- Créditos fotográficos
  function renderCredits() {
    var list = document.getElementById('photo-credits');
    var pending = document.getElementById('photo-credits-pending');
    if (!list && !pending) return;

    var items = [];
    DEFAULT_CREDITS.forEach(function (credit) {
      if (credit && credit.author) items.push(credit);
    });
    Object.keys(TEAM_IMAGES).forEach(function (id) {
      var entry = TEAM_IMAGES[id];
      if (entry && entry.credit && entry.credit.author) items.push(entry.credit);
    });

    var html = items.map(function (credit) {
      var part = credit.label ? escHtml(credit.label) + ' \u2014 ' : '';
      part += escHtml(credit.author);
      if (credit.license) {
        part += credit.licenseUrl
          ? ' \u00b7 <a href="' + escHtml(credit.licenseUrl) + '" target="_blank" rel="noopener license">' + escHtml(credit.license) + '</a>'
          : ' \u00b7 ' + escHtml(credit.license);
      }
      if (credit.source) {
        part += ' \u00b7 <a href="' + escHtml(credit.source) + '" target="_blank" rel="noopener">Fuente</a>';
      }
      return '<li>' + part + '</li>';
    }).join('');

    if (list) {
      list.innerHTML = html;
      list.hidden = !html;
    }
    if (pending) pending.hidden = !!html;
  }

  // ---------------------------------------------------------- Init
  function init() {
    if (window.TodoF1DefaultImages && window.TodoF1DefaultImages.length) {
      DEFAULT_IMAGES = window.TodoF1DefaultImages.slice();
      FALLBACK_IMAGE = DEFAULT_IMAGES[0] || FALLBACK_IMAGE;
    }

    var btn = document.getElementById('tf-theme-picker');
    if (btn) {
      btn.addEventListener('click', openDrawer);
      btn.setAttribute('aria-expanded', 'false');
      btn.setAttribute('aria-haspopup', 'dialog');
    }
    applyFromState();
    renderCredits();
    initHeroNav();
  }

  window.TodoF1Teams = {
    year: currentYear,
    state: function () { return readState(); },
    apply: applyFromState,
    boot: applyThemeOnly,
    open: function () { openDrawer(); },
    _internal: { hexToRgb: hexToRgb, rgba: rgba, luminance: luminance, onColor: onColor, resolveTeam: resolveTeam, varsFor: varsFor, uniqueColors: uniqueColors, stripeGradient: stripeGradient, teamHeroSrc: teamHeroSrc, versionedSrc: versionedSrc, TEAMS: TEAMS, TEAM_IMAGES: TEAM_IMAGES, DEFAULT_IMAGES: DEFAULT_IMAGES, FALLBACK_IMAGE: FALLBACK_IMAGE }
  };

  // Aplicación temprana: el script se carga en <head>, antes de pintar el
  // cuerpo. Así se evita el parpadeo al recuperar la preferencia global.
  applyThemeOnly();

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
