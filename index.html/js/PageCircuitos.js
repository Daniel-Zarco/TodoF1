/* TodoF1 — Circuitos */
(function () {
  'use strict';

  var API = 'https://api.jolpi.ca/ergast/f1';
  var params = new URLSearchParams(window.location.search);
  var year = parseInt(params.get('year') || '2026', 10);

  var container = document.getElementById('circuitos-container');
  var search = document.getElementById('search');
  var badge = document.getElementById('count-badge');

  document.getElementById('year-label').textContent = year;
  document.getElementById('btn-volver').href = '/index.html/PagePrincipal.php?year=' + year;

  var races = [];

  function esc(value) {
    return String(value == null ? '' : value).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  function formatDate(value) {
    var date = new Date(value);
    if (isNaN(date.getTime())) return value;
    return date.toLocaleDateString('es-ES', { day: 'numeric', month: 'long', year: 'numeric' });
  }

  function render() {
    var term = (search.value || '').trim().toLowerCase();
    var today = new Date();

    var filtered = races.filter(function (race) {
      if (!term) return true;
      return (race.circuitName + ' ' + race.country + ' ' + race.locality).toLowerCase().indexOf(term) !== -1;
    });

    badge.textContent = filtered.length + (filtered.length === 1 ? ' circuito' : ' circuitos');

    if (!filtered.length) {
      container.innerHTML = '<div class="tf-empty" style="grid-column:1/-1;">No se encontraron circuitos con esa búsqueda.</div>';
      return;
    }

    var fragment = document.createDocumentFragment();
    filtered.forEach(function (race) {
      var upcoming = race.dateObj > today;
      var card = document.createElement('a');
      card.className = 'tf-circuit-card' + (upcoming ? ' is-upcoming' : '');
      card.href = 'PageStatsCircuito.php?circuito=' + encodeURIComponent(race.circuitName) + '&year=' + year;

      card.innerHTML =
        '<div class="tf-circuit-card__top">' +
        '<span class="tf-circuit-card__round">Ronda ' + esc(race.round) + '</span>' +
        (upcoming ? '<span class="tf-badge tf-badge--red">Próximamente</span>' : '<span class="tf-badge">Disputado</span>') +
        '</div>' +
        '<h2 class="tf-circuit-card__name">' + esc(race.circuitName) + '</h2>' +
        '<div class="tf-circuit-card__meta">' +
        '<span>🏁 ' + esc(race.country || '—') + '</span>' +
        (race.locality ? '<span>📍 ' + esc(race.locality) + '</span>' : '') +
        '</div>' +
        '<div class="tf-circuit-card__foot">' +
        '<span class="tf-circuit-card__date">' + esc(formatDate(race.date)) + '</span>' +
        '<span class="tf-circuit-card__cta">Detalles →</span>' +
        '</div>';

      fragment.appendChild(card);
    });

    container.innerHTML = '';
    container.appendChild(fragment);
  }

  fetch(API + '/' + year + '.json')
    .then(function (response) { return response.json(); })
    .then(function (data) {
      var list = (data.MRData && data.MRData.RaceTable && data.MRData.RaceTable.Races) || [];

      if (!list.length) {
        container.innerHTML = '<div class="tf-empty" style="grid-column:1/-1;">No hay carreras registradas para la temporada ' + esc(year) + '.</div>';
        badge.textContent = '0 circuitos';
        return;
      }

      races = list.map(function (race) {
        return {
          round: race.round,
          circuitName: race.Circuit.circuitName,
          country: race.Circuit.Location && race.Circuit.Location.country,
          locality: race.Circuit.Location && race.Circuit.Location.locality,
          date: race.date,
          dateObj: new Date(race.date)
        };
      });

      render();
    })
    .catch(function (error) {
      console.error(error);
      container.innerHTML = '<div class="tf-error" style="grid-column:1/-1;">Error al cargar los circuitos.</div>';
      badge.textContent = 'Error';
    });

  search.addEventListener('input', render);
})();
