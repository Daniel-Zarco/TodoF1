/* TodoF1 — Escuderías */
(function () {
  'use strict';

  var API = 'https://api.jolpi.ca/ergast/f1';
  var params = new URLSearchParams(window.location.search);
  var year = parseInt(params.get('year') || '2026', 10);

  var container = document.getElementById('escuderias-container');
  var search = document.getElementById('search');
  var badge = document.getElementById('count-badge');

  document.getElementById('year-label').textContent = year;
  document.getElementById('btn-volver').href = '/index.html/PagePrincipal.php?year=' + year;

  var colorByTeam = {
    'ferrari': '#E10600',
    'mercedes': '#00D2BE',
    'red bull': '#1E41FF',
    'mclaren': '#FF8700',
    'alpine': '#0090FF',
    'aston martin': '#006F62',
    'williams': '#1E7FE0',
    'sauber': '#52E252',
    'haas': '#B6BABD',
    'rb': '#6692FF',
    'racing bulls': '#6692FF',
    'lotus': '#FFB800',
    'renault': '#FFF500',
    'toro rosso': '#4E7C9B',
    'force india': '#F480B5',
    'brawn': '#B6FF00',
    'toyota': '#CC0000',
    'bmw': '#293C6E',
    'jordan': '#FFD700',
    'benetton': '#00A551',
    'brabham': '#FFCC00',
    'tyrrell': '#003399'
  };

  var teams = [];

  function esc(value) {
    return String(value == null ? '' : value).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  function teamColor(name) {
    var normalized = String(name || '').toLowerCase().replace(/[^a-z0-9]/g, '');
    for (var key in colorByTeam) {
      if (normalized.indexOf(key.replace(/[^a-z0-9]/g, '')) !== -1) {
        return colorByTeam[key];
      }
    }
    return '#55555E';
  }

  function render() {
    var term = (search.value || '').trim().toLowerCase();
    var filtered = teams.filter(function (t) {
      return !term || t.name.toLowerCase().indexOf(term) !== -1;
    });

    badge.textContent = filtered.length + (filtered.length === 1 ? ' escudería' : ' escuderías');

    if (!filtered.length) {
      container.innerHTML = '<div class="tf-empty" style="grid-column:1/-1;">No se encontraron escuderías con ese nombre.</div>';
      return;
    }

    var fragment = document.createDocumentFragment();
    filtered.forEach(function (t) {
      var card = document.createElement('a');
      card.className = 'tf-team-card';
      card.href = 'PageStatsEscuderia.php?escuderia=' + encodeURIComponent(t.name) + '&year=' + year;
      card.style.setProperty('--team', teamColor(t.name));
      card.innerHTML =
        '<div class="tf-team-card__top">' +
        '<span class="tf-team-card__rank">P' + esc(t.position) + '</span>' +
        '<span class="tf-team-card__swatch"></span>' +
        '</div>' +
        '<h2 class="tf-team-card__name">' + esc(t.name) + '</h2>' +
        '<div class="tf-team-card__meta">' + esc(t.nationality || 'Nacionalidad N/D') + '</div>' +
        '<div class="tf-team-card__foot">' +
        '<div class="tf-team-card__points">' + esc(t.points) + '<small>Puntos</small></div>' +
        '<span class="tf-team-card__cta">Ver detalles →</span>' +
        '</div>';
      fragment.appendChild(card);
    });

    container.innerHTML = '';
    container.appendChild(fragment);
  }

  fetch(API + '/' + year + '/constructorStandings.json')
    .then(function (response) { return response.json(); })
    .then(function (data) {
      var list = data.MRData && data.MRData.StandingsTable &&
        data.MRData.StandingsTable.StandingsLists &&
        data.MRData.StandingsTable.StandingsLists[0];
      var standings = list ? (list.ConstructorStandings || []) : [];

      if (!standings.length) {
        container.innerHTML = '<div class="tf-empty" style="grid-column:1/-1;">No hay escuderías registradas para la temporada ' + esc(year) + '.</div>';
        badge.textContent = '0 escuderías';
        return;
      }

      teams = standings.map(function (entry) {
        return {
          position: entry.position,
          name: entry.Constructor.name,
          nationality: entry.Constructor.nationality,
          points: entry.points
        };
      });

      render();
    })
    .catch(function (error) {
      console.error(error);
      container.innerHTML = '<div class="tf-error" style="grid-column:1/-1;">Error al cargar las escuderías.</div>';
      badge.textContent = 'Error';
    });

  search.addEventListener('input', render);
})();
