/* TodoF1 — Navegación: menú móvil a pantalla completa y selector de temporada */
(function () {
  'use strict';

  var MOBILE_MAX = 767;

  function isMobileViewport() {
    return window.innerWidth <= MOBILE_MAX;
  }

  function isVisible(el) {
    return !!(el && (el.offsetWidth || el.offsetHeight || el.getClientRects().length));
  }

  function init() {
    var root = document.documentElement;
    var nav = document.getElementById('tf-nav');
    var toggle = document.getElementById('tf-nav-toggle');
    var menu = document.getElementById('tf-nav-menu');

    if (toggle && menu) {
      function syncAria() {
        var open = menu.classList.contains('is-open');
        if (isMobileViewport()) {
          if (open) {
            menu.removeAttribute('aria-hidden');
          } else {
            menu.setAttribute('aria-hidden', 'true');
          }
        } else {
          menu.removeAttribute('aria-hidden');
        }
      }

      function openMenu() {
        menu.classList.add('is-open');
        toggle.setAttribute('aria-expanded', 'true');
        toggle.setAttribute('aria-label', 'Cerrar menú');
        root.setAttribute('data-nav-open', 'true');
        syncAria();
      }

      function closeMenu(restoreFocus) {
        if (!menu.classList.contains('is-open')) {
          syncAria();
          return;
        }
        menu.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-label', 'Abrir menú');
        root.removeAttribute('data-nav-open');
        syncAria();
        if (restoreFocus && typeof toggle.focus === 'function') {
          toggle.focus();
        }
      }

      toggle.addEventListener('click', function () {
        if (menu.classList.contains('is-open')) {
          closeMenu(true);
        } else {
          openMenu();
        }
      });

      // Cerrar el menú al seleccionar cualquiera de las secciones de navegación
      var links = menu.querySelectorAll('.tf-nav__link');
      Array.prototype.forEach.call(links, function (link) {
        link.addEventListener('click', function () {
          closeMenu(false);
        });
      });

      // Cerrar si se hace clic fuera (ignorando el panel de temas si está abierto)
      document.addEventListener('click', function (event) {
        if (!menu.classList.contains('is-open')) return;
        if (root.getAttribute('data-drawer') === 'open') return;
        var target = event.target;
        if (target && target.closest && target.closest('.tf-drawer__panel, .tf-drawer__backdrop')) return;
        if (menu.contains(target) || toggle.contains(target)) return;
        closeMenu(false);
      });

      // Gestión de teclado: Escape para cerrar y foco atrapado (Tab / Shift+Tab)
      document.addEventListener('keydown', function (event) {
        if (!menu.classList.contains('is-open')) return;
        if (event.defaultPrevented) return;
        if (root.getAttribute('data-drawer') === 'open') return;
        if (event.target && event.target.closest && event.target.closest('.tf-drawer__panel, .tf-drawer__backdrop')) return;

        if (event.key === 'Escape') {
          event.preventDefault();
          closeMenu(true);
          return;
        }

        if (event.key === 'Tab' && isMobileViewport()) {
          var scope = nav || menu;
          var candidates = scope.querySelectorAll(
            'button:not([disabled]), a[href], select:not([disabled]), input:not([disabled]), [tabindex]:not([tabindex="-1"])'
          );
          var focusables = Array.prototype.filter.call(candidates, isVisible);
          if (!focusables.length) return;

          var first = focusables[0];
          var last = focusables[focusables.length - 1];
          var active = document.activeElement;

          if (event.shiftKey) {
            if (active === first || !scope.contains(active)) {
              event.preventDefault();
              last.focus();
            }
          } else {
            if (active === last || !scope.contains(active)) {
              event.preventDefault();
              first.focus();
            }
          }
        }
      });

      // Sincronizar al cambiar entre móvil (< 768px) y escritorio (≥ 768px)
      window.addEventListener('resize', function () {
        if (!isMobileViewport() && menu.classList.contains('is-open')) {
          closeMenu(false);
        } else {
          syncAria();
        }
      });

      syncAria();
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
