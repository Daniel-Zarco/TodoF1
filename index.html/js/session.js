/* TodoF1 — Temporada activa persistente
   Guarda el año elegido en sessionStorage: se mantiene al recargar y navegar,
   pero se borra al cerrar la pestaña/navegador, momento en el que se vuelve
   a la última temporada disponible (2026). */
(function () {
  'use strict';

  var KEY = 'todof1:year';
  var MIN_YEAR = 1950;
  var LATEST_YEAR = 2026;

  function isValid(value) {
    return !isNaN(value) && value >= MIN_YEAR && value <= LATEST_YEAR;
  }

  function read(key) {
    try { return sessionStorage.getItem(key); } catch (e) { return null; }
  }

  function write(key, value) {
    try { sessionStorage.setItem(key, value); } catch (e) { /* almacenamiento no disponible */ }
  }

  var params = new URLSearchParams(window.location.search);
  var urlYear = parseInt(params.get('year'), 10);
  var storedYear = parseInt(read(KEY), 10);

  var year;
  if (isValid(urlYear)) {
    year = urlYear;          // elección explícita (enlace o URL)
  } else if (isValid(storedYear)) {
    year = storedYear;       // última temporada usada en esta sesión
  } else {
    year = LATEST_YEAR;      // sesión nueva: la última disponible
  }

  write(KEY, year);

  // Refleja el año en la URL (sin recargar) para que el JS de cada página lo lea.
  if (urlYear !== year) {
    var url = new URL(window.location.href);
    url.searchParams.set('year', year);
    window.history.replaceState({}, '', url);
  }

  // Sincroniza el selector de temporada de la navbar.
  function syncSelect() {
    var select = document.getElementById('tf-season-select');
    if (select) select.value = String(year);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', syncSelect);
  } else {
    syncSelect();
  }

  window.TodoF1Year = year;
})();
