/* ============================================================
   TodoF1 — Transición entre páginas (burnout)
   Intercepta enlaces internos y muestra el neumático + humo
   antes de navegar. No actúa con prefers-reduced-motion, ni en
   filtros/temporadas/temas (no son enlaces), ni en enlaces
   externos, hash, nueva pestaña o con modificadores.
   ============================================================ */
(function () {
  'use strict';

  var FLAG = 'todof1:pt';
  var DUR_OUT = 235;   // ~= --pt-dur-out
  var DUR_IN_MAX = 420; // limpieza de seguridad del estado "in"
  var root = document.documentElement;

  function reduced() {
    return !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);
  }

  function hasOverlay() {
    return !!document.getElementById('tf-transition');
  }

  function stored() {
    try { return sessionStorage.getItem(FLAG); } catch (e) { return null; }
  }

  function saveFlag() {
    try { sessionStorage.setItem(FLAG, String(Date.now())); } catch (e) { /* sin almacenamiento */ }
  }

  function clearFlag() {
    try { sessionStorage.removeItem(FLAG); } catch (e) { /* sin almacenamiento */ }
  }

  // ¿Es un enlace interno navegable al que aplicar la transición?
  function isEligible(a, event) {
    if (!a || !a.getAttribute) return false;
    if (event.defaultPrevented) return false;
    if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return false;

    var target = a.getAttribute('target');
    if (target && target !== '' && target !== '_self') return false;
    if (a.hasAttribute('download')) return false;
    if (a.hasAttribute('data-no-transition')) return false;

    var href = a.getAttribute('href');
    if (!href) return false;
    if (href.charAt(0) === '#') return false;
    if (/^\s*(mailto:|tel:|javascript:|data:)/i.test(href)) return false;

    var url;
    try { url = new URL(a.href, window.location.href); } catch (e) { return false; }

    if (url.origin !== window.location.origin) return false;

    // Misma página (solo cambia query/hash) -> sin transición.
    if (url.pathname === window.location.pathname && url.search === window.location.search) return false;

    return true;
  }

  function start(event, a) {
    if (reduced() || !hasOverlay()) return false; // navegación normal
    event.preventDefault();
    saveFlag();
    root.setAttribute('data-pt', 'out');
    var dest = a.href;
    window.setTimeout(function () {
      window.location.assign(dest);
    }, DUR_OUT);
    return true;
  }

  document.addEventListener('click', function (event) {
    var a = event.target && event.target.closest ? event.target.closest('a') : null;
    if (!isEligible(a, event)) return;
    start(event, a);
  }, false);

  // Estado entrante: el script inline (navbar) ya puso data-pt="in".
  function finishIn() {
    if (root.getAttribute('data-pt') !== 'in') return;
    var fog = document.querySelector('.tf-transition__fog');
    var done = function () { root.removeAttribute('data-pt'); };
    if (fog && fog.addEventListener) {
      fog.addEventListener('animationend', done, { once: true });
    }
    window.setTimeout(done, DUR_IN_MAX);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', finishIn);
  } else {
    finishIn();
  }

  // Atrás/adelante (bfcache): nunca dejar el overlay bloqueado.
  window.addEventListener('pageshow', function () {
    root.removeAttribute('data-pt');
  });

  window.addEventListener('pagehide', function () {
    if (root.getAttribute('data-pt') !== 'out') clearFlag();
  });

  window.TodoF1Transition = { eligible: isEligible, reduced: reduced };
})();
