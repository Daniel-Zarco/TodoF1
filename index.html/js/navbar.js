/* TodoF1 — Navegación: menú móvil y selector de temporada */
(function () {
  'use strict';

  function init() {
    var toggle = document.getElementById('tf-nav-toggle');
    var menu = document.getElementById('tf-nav-menu');

    if (toggle && menu) {
      toggle.addEventListener('click', function () {
        var open = menu.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        toggle.setAttribute('aria-label', open ? 'Cerrar menú' : 'Abrir menú');
      });

      document.addEventListener('click', function (event) {
        if (!menu.classList.contains('is-open')) return;
        if (menu.contains(event.target) || toggle.contains(event.target)) return;
        menu.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
      });

      document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && menu.classList.contains('is-open')) {
          menu.classList.remove('is-open');
          toggle.setAttribute('aria-expanded', 'false');
          toggle.focus();
        }
      });
    }

    var season = document.getElementById('tf-season-select');
    if (season) {
      season.addEventListener('change', function () {
        var base = season.getAttribute('data-year-base') || '/index.html/PagePrincipal.php';
        try { sessionStorage.setItem('todof1:year', season.value); } catch (e) { /* sin almacenamiento */ }
        window.location.href = base + '?year=' + encodeURIComponent(season.value);
      });
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
