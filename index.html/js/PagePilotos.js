/* TodoF1 — Pilotos */
(function () {
  'use strict';

  var API = 'https://api.jolpi.ca/ergast/f1';
  var params = new URLSearchParams(window.location.search);
  var year = parseInt(params.get('year') || '2026', 10);

  var tbody = document.getElementById('pilotos-tbody');
  var search = document.getElementById('search');
  var teamFilter = document.getElementById('filter-team');
  var badge = document.getElementById('count-badge');

  document.getElementById('year-label').textContent = year;
  document.getElementById('btn-volver').href = '/index.html/PagePrincipal.php?year=' + year;

  var drivers = [];

  function esc(value) {
    return String(value == null ? '' : value).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  function rankClass(position) {
    return position <= 3 ? ' tf-rank-' + position : '';
  }

  function render() {
    var term = (search.value || '').trim().toLowerCase();
    var team = teamFilter.value;

    var filtered = drivers.filter(function (d) {
      var matchesTerm = !term || d.name.toLowerCase().indexOf(term) !== -1;
      var matchesTeam = !team || d.team === team;
      return matchesTerm && matchesTeam;
    });

    badge.textContent = filtered.length + (filtered.length === 1 ? ' piloto' : ' pilotos');

    if (!filtered.length) {
      tbody.innerHTML = '<tr class="tf-table__empty"><td colspan="5">No se encontraron pilotos con esos filtros.</td></tr>';
      return;
    }

    var fragment = document.createDocumentFragment();
    filtered.forEach(function (d) {
      var tr = document.createElement('tr');
      tr.className = 'is-clickable';
      tr.tabIndex = 0;
      tr.innerHTML =
        '<td class="tf-td-num tf-rank' + rankClass(d.position) + '">' + esc(d.position) + '</td>' +
        '<td><span class="tf-pilot-name">' + esc(d.name) + '</span></td>' +
        '<td class="tf-dim">' + esc(d.nationality || '—') + '</td>' +
        '<td><span class="tf-team-cell">' + esc(d.team || '—') + '</span></td>' +
        '<td class="tf-td-right tf-td-num">' + esc(d.points) + '</td>';

      var go = function () {
        window.location.href = 'PageStatsPiloto.php?driverId=' + encodeURIComponent(d.driverId) +
          '&piloto=' + encodeURIComponent(d.name) + '&year=' + year;
      };
      tr.addEventListener('click', go);
      tr.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' || event.key === ' ') {
          event.preventDefault();
          go();
        }
      });
      fragment.appendChild(tr);
    });

    tbody.innerHTML = '';
    tbody.appendChild(fragment);
  }

  function populateTeams() {
    var teams = [];
    drivers.forEach(function (d) {
      if (d.team && teams.indexOf(d.team) === -1) teams.push(d.team);
    });
    teams.sort(function (a, b) { return a.localeCompare(b, 'es'); });
    teams.forEach(function (name) {
      var opt = document.createElement('option');
      opt.value = name;
      opt.textContent = name;
      teamFilter.appendChild(opt);
    });
  }

  fetch(API + '/' + year + '/driverStandings.json')
    .then(function (response) { return response.json(); })
    .then(function (data) {
      var list = data.MRData && data.MRData.StandingsTable &&
        data.MRData.StandingsTable.StandingsLists &&
        data.MRData.StandingsTable.StandingsLists[0];
      var standings = list ? (list.DriverStandings || []) : [];

      if (!standings.length) {
        tbody.innerHTML = '<tr class="tf-table__empty"><td colspan="5">No hay pilotos registrados para la temporada ' + esc(year) + '.</td></tr>';
        badge.textContent = '0 pilotos';
        return;
      }

      drivers = standings.map(function (entry) {
        var teams = entry.Constructors || [];
        return {
          position: entry.position,
          driverId: entry.Driver.driverId,
          name: entry.Driver.givenName + ' ' + entry.Driver.familyName,
          nationality: entry.Driver.nationality,
          team: teams.length ? teams[teams.length - 1].name : '',
          points: entry.points
        };
      });

      populateTeams();
      render();
    })
    .catch(function (error) {
      console.error(error);
      tbody.innerHTML = '<tr class="tf-table__empty"><td colspan="5">Error al cargar los pilotos.</td></tr>';
      badge.textContent = 'Error';
    });

  search.addEventListener('input', render);
  teamFilter.addEventListener('change', render);
})();
