/* TodoF1 — Estadísticas del circuito */
(function () {
  'use strict';

  var API = 'https://api.jolpi.ca/ergast/f1';
  var params = new URLSearchParams(window.location.search);
  var circuito = params.get('circuito') || '';
  var year = params.get('year') || '2026';

  var titulo = document.getElementById('titulo');
  var tbody = document.querySelector('#tabla-resultados tbody');
  var infoAdicional = document.getElementById('info-adicional');
  var vueltaRapidaDiv = document.getElementById('vuelta-rapida');
  var circuitoFotoElem = document.getElementById('circuito-foto');

  titulo.textContent = circuito ? circuito : 'Estadísticas del Circuito';
  document.getElementById('year-label').textContent = year;
  document.getElementById('btn-volver').href = '/index.html/PageCircuitos.php?year=' + encodeURIComponent(year);

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

  // Foto del circuito desde Wikipedia
  var wikiTitle = circuito.replace(/ /g, '_');
  fetch('https://en.wikipedia.org/w/api.php?action=query&format=json&origin=*&prop=pageimages|pageprops&titles=' + encodeURIComponent(wikiTitle) + '&piprop=original')
    .then(function (res) { return res.json(); })
    .then(function (wikiData) {
      var pages = wikiData.query.pages;
      var pageId = Object.keys(pages)[0];
      var page = pages[pageId];
      var imageUrl = page.original ? page.original.source : '';
      var fotoBlock = document.querySelector('.foto-block');

      if (imageUrl) {
        circuitoFotoElem.src = imageUrl;
      } else if (fotoBlock) {
        fotoBlock.style.display = 'none';
      }
    })
    .catch(function (error) {
      console.error(error);
      var fotoBlock = document.querySelector('.foto-block');
      if (fotoBlock) fotoBlock.style.display = 'none';
    });

  fetch(API + '/' + year + '/races.json?limit=1000')
    .then(function (response) { return response.json(); })
    .then(function (data) {
      var races = (data.MRData && data.MRData.RaceTable && data.MRData.RaceTable.Races) || [];
      var carrera = races.find(function (race) { return race.Circuit.circuitName === circuito; });

      if (!carrera) {
        infoAdicional.innerHTML = '<span class="tf-badge">Sin información para ' + esc(year) + '</span>';
        tbody.innerHTML = '<tr class="tf-table__empty"><td colspan="5">No se encontró la carrera de ' + esc(circuito) + ' en ' + esc(year) + '.</td></tr>';
        return null;
      }

      infoAdicional.innerHTML =
        '<span class="tf-badge">🏁 ' + esc(carrera.Circuit.Location.country || '—') + '</span>' +
        (carrera.Circuit.Location.locality ? '<span class="tf-badge">📍 ' + esc(carrera.Circuit.Location.locality) + '</span>' : '') +
        '<span class="tf-badge tf-badge--red">📅 ' + esc(formatDate(carrera.date)) + '</span>';

      return fetch(API + '/' + year + '/' + carrera.round + '/results.json');
    })
    .then(function (response) { return response ? response.json() : null; })
    .then(function (data) {
      if (!data) return;

      var results = (data.MRData && data.MRData.RaceTable && data.MRData.RaceTable.Races[0] && data.MRData.RaceTable.Races[0].Results) || [];
      if (!results.length) {
        tbody.innerHTML = '<tr class="tf-table__empty"><td colspan="5">No hay resultados disponibles.</td></tr>';
        vueltaRapidaDiv.innerHTML = '';
        return;
      }

      var fragment = document.createDocumentFragment();
      results.forEach(function (result) {
        var pos = parseInt(result.position, 10);
        var row = document.createElement('tr');
        row.innerHTML =
          '<td class="tf-td-num tf-rank' + (pos <= 3 ? ' tf-rank-' + pos : '') + '">' + esc(result.position) + '</td>' +
          '<td>' + esc(result.Driver.givenName + ' ' + result.Driver.familyName) + '</td>' +
          '<td class="tf-dim">' + esc(result.Constructor.name) + '</td>' +
          '<td class="tf-td-right tf-td-num">' + esc(result.laps) + '</td>' +
          '<td class="tf-td-right tf-td-num">' + esc(result.Time ? result.Time.time : 'N/A') + '</td>';
        fragment.appendChild(row);
      });
      tbody.innerHTML = '';
      tbody.appendChild(fragment);

      var fastestResult = results.find(function (r) { return r.FastestLap && r.FastestLap.rank === '1'; });
      if (fastestResult) {
        var driver = fastestResult.Driver;
        vueltaRapidaDiv.innerHTML =
          '<div class="tf-fastlap">' +
          '<span class="tf-fastlap__icon">⏱</span>' +
          '<div><div class="tf-fastlap__label">Vuelta rápida</div>' +
          '<div class="tf-fastlap__value">' + esc(driver.givenName + ' ' + driver.familyName) +
          ' · ' + esc(fastestResult.FastestLap.Time.time) +
          ' <span class="tf-dim">(vuelta ' + esc(fastestResult.FastestLap.lap) + ')</span></div></div>' +
          '</div>';
      } else {
        vueltaRapidaDiv.innerHTML =
          '<div class="tf-fastlap"><span class="tf-fastlap__icon">⏱</span>' +
          '<div><div class="tf-fastlap__label">Vuelta rápida</div>' +
          '<div class="tf-fastlap__value tf-dim">No disponible en la API para este año.</div></div></div>';
      }
    })
    .catch(function (error) {
      console.error(error);
      tbody.innerHTML = '<tr class="tf-table__empty"><td colspan="5">Error al cargar los datos.</td></tr>';
    });
})();
