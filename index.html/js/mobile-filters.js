/* Panel plegable de búsqueda y filtros en móvil.
   Reutiliza los controles existentes; no recrea inputs ni listeners de filtrado. */
(function () {
  'use strict';

  var controls = document.querySelector('.circuitos-controls, .teams-controls, .pilots-controls');
  if (!controls) return;

  var toggle = controls.querySelector('.tf-filters-toggle');
  var body = controls.querySelector('.tf-filters-body');
  if (!toggle || !body) return;

  var dot = toggle.querySelector('.tf-filters-toggle__dot');
  var media = window.matchMedia('(max-width: 767px)');
  var defaults = [];

  function captureDefaults() {
    defaults = [];
    Array.prototype.forEach.call(body.querySelectorAll('input, select'), function (field) {
      defaults.push({ field: field, value: field.value });
    });
  }

  function hasActiveFilters() {
    for (var i = 0; i < defaults.length; i++) {
      if (defaults[i].field.value !== defaults[i].value) return true;
    }
    return false;
  }

  function updateIndicator() {
    var active = hasActiveFilters();
    if (dot) dot.hidden = !active;
    toggle.setAttribute('aria-label', 'Buscar y filtrar' + (active ? ', filtros activos' : ''));
  }

  function setCollapsed(collapsed) {
    body.classList.toggle('is-collapsed', collapsed);
    toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
    if (!collapsed) updateIndicator();
  }

  function syncViewport() {
    if (media.matches) {
      toggle.hidden = false;
      setCollapsed(true);
    } else {
      toggle.hidden = true;
      setCollapsed(false);
    }
  }

  toggle.addEventListener('click', function () {
    setCollapsed(!body.classList.contains('is-collapsed'));
  });

  body.addEventListener('input', updateIndicator);
  body.addEventListener('change', updateIndicator);
  body.addEventListener('click', updateIndicator);
  body.addEventListener('focusin', updateIndicator);
  body.addEventListener('keyup', updateIndicator);

  if (typeof media.addEventListener === 'function') {
    media.addEventListener('change', syncViewport);
  } else if (typeof media.addListener === 'function') {
    media.addListener(syncViewport);
  }

  captureDefaults();
  syncViewport();
  updateIndicator();
})();
