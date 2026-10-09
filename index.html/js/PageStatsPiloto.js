/* TodoF1 — Estadísticas del piloto */
(function () {
  'use strict';

  var API = 'https://api.jolpi.ca/ergast/f1';
  var params = new URLSearchParams(window.location.search);
  var pilotoNombre = params.get('piloto') || '';
  var driverIdParam = params.get('driverId') || '';
  var year = params.get('year') || '2026';

  var titulo = document.getElementById('titulo');
  var edadElem = document.getElementById('edad');
  var nacionalidadElem = document.getElementById('nacionalidad');
  var perfilTemporadasElem = document.getElementById('perfil-temporadas');
  var inicioElem = document.getElementById('inicio');
  var escuderiasElem = document.getElementById('escuderias');
  var victoriasElem = document.getElementById('victorias');
  var podiosElem = document.getElementById('podios');
  var mejorResultadoElem = document.getElementById('mejor-resultado');
  var temporadasElem = document.getElementById('temporadas');
  var wikiElem = document.getElementById('wiki');
  var pilotoFotoElem = document.getElementById('piloto-foto');
  var btnVolver = document.getElementById('btn-volver');

  titulo.textContent = pilotoNombre ? 'Estadísticas de ' + pilotoNombre : 'Estadísticas del Piloto';
  btnVolver.href = '/index.html/PagePilotos.php?year=' + encodeURIComponent(year);

  var pilotoId = '';

  function esc(value) {
    return String(value == null ? '' : value).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  function fetchJson(url) {
    return fetch(url).then(function (response) { return response.json(); });
  }

  /**
   * Recorre TODAS las páginas de un endpoint de Jolpica.
   * Jolpica limita "limit" a 100 por petición, por lo que hay que iterar
   * con offset hasta cubrir el total de registros (MRData.total).
   */
  async function fetchAllRaces(baseUrl) {
    var limit = 100;
    var offset = 0;
    var total = Infinity;
    var all = [];

    while (offset < total) {
      var sep = baseUrl.indexOf('?') === -1 ? '?' : '&';
      var data = await fetchJson(baseUrl + sep + 'limit=' + limit + '&offset=' + offset);
      var meta = data.MRData || {};

      total = parseInt(meta.total, 10);
      if (isNaN(total)) total = 0;

      var races = (meta.RaceTable && meta.RaceTable.Races) || [];
      if (!races.length) break;

      all = all.concat(races);
      offset += limit;
    }

    return all;
  }

  async function fetchAllSeasons(id) {
    var data = await fetchJson(API + '/drivers/' + id + '/seasons.json?limit=100&offset=0');
    return (data.MRData.SeasonTable && data.MRData.SeasonTable.Seasons) || [];
  }

  // Resuelve el driver_id externo: parámetro -> clasificación del año -> barrido de resultados
  async function resolveDriverId() {
    if (driverIdParam) return driverIdParam;

    try {
      var data = await fetchJson(API + '/' + year + '/driverStandings.json');
      var list = data.MRData.StandingsTable.StandingsLists[0];
      if (list) {
        var standings = list.DriverStandings || [];
        for (var i = 0; i < standings.length; i++) {
          var driver = standings[i].Driver;
          if ((driver.givenName + ' ' + driver.familyName) === pilotoNombre) {
            return driver.driverId;
          }
        }
      }
    } catch (e) { /* continúa con el barrido */ }

    try {
      var races = await fetchAllRaces(API + '/' + year + '/results.json');
      for (var r = 0; r < races.length; r++) {
        var results = races[r].Results || [];
        for (var k = 0; k < results.length; k++) {
          var full = results[k].Driver.givenName + ' ' + results[k].Driver.familyName;
          if (full === pilotoNombre) return results[k].Driver.driverId;
        }
      }
    } catch (e) { /* sin resultado */ }

    return '';
  }

  function calculateAge(birthDate) {
    var today = new Date();
    var age = today.getFullYear() - birthDate.getFullYear();
    if (today.getMonth() < birthDate.getMonth() ||
      (today.getMonth() === birthDate.getMonth() && today.getDate() < birthDate.getDate())) {
      age--;
    }
    return age;
  }

  function loadWikiAndAge(driver) {
    var birthDate = new Date(driver.dateOfBirth);
    var wikiTitle = (driver.url || '').split('/').pop();

    fetch('https://en.wikipedia.org/w/api.php?action=query&format=json&origin=*&prop=pageimages|pageprops&titles=' + wikiTitle + '&piprop=original')
      .then(function (res) { return res.json(); })
      .then(function (wikiData) {
        var pages = wikiData.query.pages;
        var pageId = Object.keys(pages)[0];
        var page = pages[pageId];
        var imageUrl = page.original ? page.original.source : '../Images/SinPerfil.jpg';
        pilotoFotoElem.src = imageUrl;

        var qid = page.pageprops && page.pageprops.wikibase_item;
        if (qid) {
          fetch('https://www.wikidata.org/w/api.php?action=wbgetclaims&entity=' + qid + '&property=P570&format=json&origin=*')
            .then(function (res) { return res.json(); })
            .then(function (wikidata) {
              var claims = wikidata.claims && wikidata.claims.P570;
              if (claims && claims.length > 0) {
                var deathTime = claims[0].mainsnak.datavalue.value.time.replace('+', '');
                var deathDate = new Date(deathTime);
                var ageAtDeath = deathDate.getFullYear() - birthDate.getFullYear();
                edadElem.textContent = 'Fallecido a los ' + ageAtDeath + ' años';
              } else {
                edadElem.textContent = calculateAge(birthDate) + ' años';
              }
            });
        } else {
          edadElem.textContent = calculateAge(birthDate) + ' años';
        }
      })
      .catch(function () {
        pilotoFotoElem.src = '../Images/SinPerfil.jpg';
        edadElem.textContent = calculateAge(birthDate) + ' años';
      });
  }

  function showNotFound() {
    edadElem.textContent = 'N/D';
    nacionalidadElem.textContent = 'No encontrado';
    perfilTemporadasElem.style.display = 'none';
    ['inicio', 'temporadas', 'victorias', 'podios', 'mejor-resultado'].forEach(function (id) {
      document.getElementById(id).textContent = 'N/D';
    });
    escuderiasElem.textContent = 'No se encontró información del piloto para esta temporada.';
  }

  async function load() {
    pilotoId = await resolveDriverId();
    if (!pilotoId) {
      showNotFound();
      return;
    }

    // Ficha del piloto (nacionalidad, URL de Wikipedia, fecha de nacimiento)
    var info = await fetchJson(API + '/drivers/' + pilotoId + '.json');
    var driver = info.MRData.DriverTable.Drivers[0];
    nacionalidadElem.textContent = driver.nationality || 'N/D';
    wikiElem.href = driver.url || '#';
    loadWikiAndAge(driver);

    // Temporadas (todas)
    fetchAllSeasons(pilotoId).then(function (seasons) {
      temporadasElem.textContent = seasons.length;
      perfilTemporadasElem.textContent = seasons.length + ' temporadas';
      if (seasons.length > 0) {
        var primerAnio = Math.min.apply(null, seasons.map(function (s) { return parseInt(s.season, 10); }));
        inicioElem.textContent = primerAnio;
      } else {
        inicioElem.textContent = 'N/A';
      }
    });

    // Resultados de TODA la carrera (paginado): victorias, podios, mejor resultado y escuderías
    var races = await fetchAllRaces(API + '/drivers/' + pilotoId + '/results.json');

    var victorias = 0;
    var podios = 0;
    var mejorPos = 99;
    var escuderias = [];

    races.forEach(function (race) {
      (race.Results || []).forEach(function (result) {
        var team = result.Constructor.name;
        if (escuderias.indexOf(team) === -1) escuderias.push(team);

        var pos = parseInt(result.position, 10);
        if (pos === 1) victorias++;
        if (pos >= 1 && pos <= 3) podios++;
        if (pos >= 1 && pos < mejorPos) mejorPos = pos;
      });
    });

    escuderiasElem.innerHTML = escuderias.length
      ? escuderias.map(function (t) { return '<span class="tf-badge">' + esc(t) + '</span>'; }).join('')
      : '<span class="tf-dim">Sin datos de escuderías.</span>';
    victoriasElem.textContent = victorias;
    podiosElem.textContent = podios;
    mejorResultadoElem.textContent = mejorPos === 99 ? 'N/A' : mejorPos;
  }

  load().catch(function (error) {
    console.error(error);
    showNotFound();
  });
})();
