/* TodoF1 — Estadísticas de la temporada */
(function () {
  'use strict';

  var API = 'https://api.jolpi.ca/ergast/f1';
  var params = new URLSearchParams(window.location.search);
  var year = parseInt(params.get('year') || '2026', 10);

  var kpis = document.getElementById('kpis');
  var tbodyDrivers = document.getElementById('standings-pilotos');
  var tbodyConstructors = document.getElementById('standings-escuderias');

  document.getElementById('year-label').textContent = year;
  document.getElementById('btn-volver').href = '/index.html/PagePrincipal.php?year=' + year;

  function esc(value) {
    return String(value == null ? '' : value).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  function kpi(label, value, meta) {
    var block = document.createElement('div');
    block.className = 'tf-kpi';
    block.innerHTML =
      '<span class="tf-kpi__label">' + esc(label) + '</span>' +
      '<div class="tf-kpi__value' + (isNaN(parseFloat(value)) ? ' is-text' : ' tf-num') + '">' + esc(value) + '</div>' +
      (meta ? '<div class="tf-kpi__meta">' + esc(meta) + '</div>' : '');
    return block;
  }

  function rankClass(position) {
    return position <= 3 ? ' tf-rank-' + position : '';
  }

  function row(cells, onClick) {
    var tr = document.createElement('tr');
    tr.tabIndex = 0;
    tr.innerHTML = cells;
    tr.addEventListener('click', onClick);
    tr.addEventListener('keydown', function (event) {
      if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        onClick();
      }
    });
    return tr;
  }

  Promise.allSettled([
    fetch(API + '/' + year + '/driverStandings.json').then(function (r) { return r.json(); }),
    fetch(API + '/' + year + '/constructorStandings.json').then(function (r) { return r.json(); })
  ]).then(function (settled) {
    var driversList = extract(settled[0], 'DriverStandings');
    var constructorsList = extract(settled[1], 'ConstructorStandings');

    // KPIs
    kpis.innerHTML = '';
    var leaderDriver = driversList[0];
    var leaderTeam = constructorsList[0];
    kpis.appendChild(kpi('Líder pilotos', leaderDriver ? leaderDriver.Driver.givenName + ' ' + leaderDriver.Driver.familyName : 'N/D',
      leaderDriver ? leaderDriver.points + ' puntos' : 'Sin datos'));
    kpis.appendChild(kpi('Pilotos', driversList.length, 'En el campeonato'));
    kpis.appendChild(kpi('Líder constructores', leaderTeam ? leaderTeam.Constructor.name : 'N/D',
      leaderTeam ? leaderTeam.points + ' puntos' : 'Sin datos'));
    kpis.appendChild(kpi('Escuderías', constructorsList.length, 'En el campeonato'));

    // Pilotos
    if (!driversList.length) {
      tbodyDrivers.innerHTML = '<tr class="tf-table__empty"><td colspan="6">Sin datos para la temporada ' + esc(year) + '.</td></tr>';
    } else {
      tbodyDrivers.innerHTML = '';
      driversList.forEach(function (entry) {
        var teams = entry.Constructors || [];
        var team = teams.length ? teams[teams.length - 1].name : '';
        var name = entry.Driver.givenName + ' ' + entry.Driver.familyName;
        var driverId = entry.Driver.driverId;
        tbodyDrivers.appendChild(row(
          '<td class="tf-td-num tf-rank' + rankClass(parseInt(entry.position, 10)) + '">' + esc(entry.position) + '</td>' +
          '<td class="tf-td-num">' + esc(name) + '</td>' +
          '<td class="tf-dim">' + esc(entry.Driver.nationality || '—') + '</td>' +
          '<td>' + esc(team || '—') + '</td>' +
          '<td class="tf-td-right tf-td-num">' + esc(entry.wins || 0) + '</td>' +
          '<td class="tf-td-right tf-td-num">' + esc(entry.points) + '</td>',
          function () {
            window.location.href = 'PageStatsPiloto.php?driverId=' + encodeURIComponent(driverId) +
              '&piloto=' + encodeURIComponent(name) + '&year=' + year;
          }
        ));
      });
    }

    // Constructores
    if (!constructorsList.length) {
      tbodyConstructors.innerHTML = '<tr class="tf-table__empty"><td colspan="5">Sin datos para la temporada ' + esc(year) + '.</td></tr>';
    } else {
      tbodyConstructors.innerHTML = '';
      constructorsList.forEach(function (entry) {
        var name = entry.Constructor.name;
        tbodyConstructors.appendChild(row(
          '<td class="tf-td-num tf-rank' + rankClass(parseInt(entry.position, 10)) + '">' + esc(entry.position) + '</td>' +
          '<td class="tf-td-num">' + esc(name) + '</td>' +
          '<td class="tf-dim">' + esc(entry.Constructor.nationality || '—') + '</td>' +
          '<td class="tf-td-right tf-td-num">' + esc(entry.wins || 0) + '</td>' +
          '<td class="tf-td-right tf-td-num">' + esc(entry.points) + '</td>',
          function () {
            window.location.href = 'PageStatsEscuderia.php?escuderia=' + encodeURIComponent(name) + '&year=' + year;
          }
        ));
      });
    }
  });

  function extract(settled, key) {
    if (settled.status !== 'fulfilled') return [];
    var list = settled.value && settled.value.MRData && settled.value.MRData.StandingsTable &&
      settled.value.MRData.StandingsTable.StandingsLists &&
      settled.value.MRData.StandingsTable.StandingsLists[0];
    return list ? (list[key] || []) : [];
  }
})();
