/* ============================================================
   TodoF1 — Racing Intelligence
   Tema claro/oscuro con persistencia y preferencia del sistema.
   - Preferencia guardada en localStorage ('todof1:theme').
   - Si no hay preferencia, se respeta prefers-color-scheme.
   - Respeta cambios del sistema solo si el usuario no eligió.
   ============================================================ */
(function () {
  'use strict';

  var KEY = 'todof1:theme';
  var root = document.documentElement;
  var media = window.matchMedia ? window.matchMedia('(prefers-color-scheme: light)') : null;

  function stored() {
    try {
      var v = localStorage.getItem(KEY);
      return (v === 'light' || v === 'dark') ? v : null;
    } catch (e) { return null; }
  }

  function save(value) {
    try { localStorage.setItem(KEY, value); } catch (e) { /* sin almacenamiento */ }
  }

  function systemTheme() {
    return media && media.matches ? 'light' : 'dark';
  }

  function current() {
    var attr = root.getAttribute('data-theme');
    if (attr === 'light' || attr === 'dark') return attr;
    return stored() || systemTheme();
  }

  function apply(theme) {
    root.setAttribute('data-theme', theme);
    root.style.colorScheme = theme;

    var btn = document.getElementById('tf-theme-toggle');
    if (btn) {
      var isLight = theme === 'light';
      btn.setAttribute('aria-pressed', isLight ? 'true' : 'false');
      btn.setAttribute('aria-label', isLight ? 'Activar tema oscuro' : 'Activar tema claro');
      btn.setAttribute('title', isLight ? 'Tema oscuro' : 'Tema claro');
    }
  }

  function init() {
    var btn = document.getElementById('tf-theme-toggle');
    apply(current());

    if (btn) {
      btn.addEventListener('click', function () {
        var next = current() === 'light' ? 'dark' : 'light';
        save(next);
        apply(next);
      });
    }
  }

  if (media && media.addEventListener) {
    media.addEventListener('change', function () {
      if (!stored()) apply(systemTheme());
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  window.TodoF1Theme = { current: current, apply: apply };
})();
