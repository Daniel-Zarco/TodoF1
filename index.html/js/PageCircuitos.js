/* ============================================================
   TodoF1 — Circuitos (listado)
   Muestra el calendario de la temporada con el trazado como
   protagonista de cada tarjeta, buscador, filtro por país,
   filtro por temporada y ordenación.
   ============================================================ */
(function () {
  'use strict';

  var API = 'https://api.jolpi.ca/ergast/f1';
  var params = new URLSearchParams(window.location.search);
  // La temporada por defecto es la actual (nunca un año fijo en el código).
  var year = parseInt(params.get('year') || String(new Date().getFullYear()), 10);
  var horarios = window.TodoF1Horarios || null;
  var temporadaActual = !!(horarios && horarios.esTemporadaActual(year));

  var grid = document.getElementById('circuitos-grid');
  var search = document.getElementById('search');
  var searchClear = document.getElementById('search-clear');
  var countrySel = document.getElementById('filter-country');
  var yearSel = document.getElementById('filter-year');
  var sortSel = document.getElementById('sort-by');
  var badge = document.getElementById('count-badge');
  var seasonNote = document.getElementById('season-note');
  var trazadosNote = document.getElementById('trazados-note');

  document.getElementById('year-label').textContent = year;
  document.getElementById('btn-volver').href = '/?year=' + year;

  var entries = [];   // { race, el, statsEl, circuitoId }
  var stats = {};     // circuitoId -> { gp, primera, ultima, piloto, escuderia }
  var pedidos = {};   // circuitoId -> true (evita peticiones duplicadas)
  var trazados = window.TodoF1Trazados || null;

  /* ---------------------------- Utilidades ---------------------------- */
  function esc(value) {
    return String(value == null ? '' : value).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  function formatDate(value) {
    var date = new Date(value);
    if (isNaN(date.getTime())) return value || '—';
    return date.toLocaleDateString('es-ES', { day: 'numeric', month: 'long', year: 'numeric' });
  }

  function iniciales(nombre) {
    var palabras = String(nombre || '').replace(/[^\p{L}\p{N} ]/gu, ' ').split(/\s+/).filter(Boolean);
    if (!palabras.length) return '—';
    if (palabras.length === 1) return palabras[0].slice(0, 3).toUpperCase();
    return (palabras[0][0] + palabras[1][0]).toUpperCase();
  }

  /* ------------------------ Selector de temporada ---------------------- */
  (function poblarTemporadas() {
    if (!yearSel) return;
    var fragmento = document.createDocumentFragment();
    var ultima = new Date().getFullYear();
    for (var y = ultima; y >= 1950; y--) {
      var opcion = document.createElement('option');
      opcion.value = String(y);
      opcion.textContent = String(y);
      if (y === year) opcion.selected = true;
      fragmento.appendChild(opcion);
    }
    yearSel.appendChild(fragmento);
    yearSel.addEventListener('change', function () {
      window.location.href = 'PageCircuitos.php?year=' + encodeURIComponent(yearSel.value);
    });
  })();

  /* ------------------------------ Tarjeta ------------------------------ */
  function construirTarjeta(race) {
    var hoy = new Date();
    var proximo = race.dateObj > hoy;
    var tieneTrazo = trazados && trazados.tiene(race.circuitoId);

    var card = document.createElement('a');
    card.className = 'circuito-card';
    card.href = 'PageStatsCircuito.php?circuito=' + encodeURIComponent(race.circuitName) +
      '&year=' + encodeURIComponent(year) +
      (race.circuitoId ? '&id=' + encodeURIComponent(race.circuitoId) : '');

    card.innerHTML =
      '<span class="circuito-card__trazo">' +
        '<span class="circuito-card__ronda">Ronda ' + esc(race.round) + '</span>' +
        '<span class="circuito-card__estado' + (proximo ? ' circuito-card__estado--proximo' : '') + '">' +
          (proximo ? 'Próximamente' : 'Disputado') +
        '</span>' +
        (tieneTrazo
          ? '<span class="tf-trazo" data-trazo="' + esc(race.circuitoId) + '"></span>' +
            '<span class="circuito-card__fallback" hidden><strong>' + esc(iniciales(race.circuitName)) +
            '</strong><small>Trazado no disponible</small></span>'
          : '<span class="circuito-card__fallback"><strong>' + esc(iniciales(race.circuitName)) +
            '</strong><small>Trazado no disponible</small></span>') +
        '</span>' +
      '<span class="circuito-card__info">' +
        '<h2 class="circuito-card__nombre">' + esc(race.circuitName) + '</h2>' +
        '<span class="circuito-card__lugar">' + esc(race.country || '—') +
          (race.locality ? ' · ' + esc(race.locality) : '') + '</span>' +
        '<span class="circuito-card__stats" data-stats="' + esc(race.circuitoId) + '">' +
          '<span class="is-loading tf-loading-inline">Consultando historial…</span>' +
        '</span>' +
        (temporadaActual
          ? '<span class="circuito-card__sesion" data-sesion="' + esc(race.round) + '" hidden></span>'
          : '') +
      '</span>' +
      '<span class="circuito-card__pie">' +
        '<span>' + esc(formatDate(race.date)) + '</span>' +
        '<span class="circuito-card__cta">Ver ficha →</span>' +
      '</span>';

    return card;
  }

  function esStatsVacio(s) {
    return !s;
  }

  function pintarStats(circuitoId) {
    var datos = stats[circuitoId];
    var objetivo = grid.querySelector('[data-stats="' + circuitoId + '"]');
    // Puede haber varias tarjetas del mismo circuito en una temporada (doble GP)
    var objetivos = grid.querySelectorAll('[data-stats="' + circuitoId + '"]');
    var html;
    if (esStatsVacio(datos) || !datos.gp) {
      html = '<span class="is-loading">Historial no disponible en la API</span>';
    } else {
      html =
        '<span><b>' + esc(datos.gp) + '</b> GP disputados</span>' +
        '<span>' + esc(datos.primera) + '–' + esc(datos.ultima) + '</span>' +
        (datos.piloto ? '<span>Más victorias: <b>' + esc(datos.piloto) + '</b> (' + esc(datos.victorias) + ')</span>' : '');
    }
    for (var i = 0; i < objetivos.length; i++) {
      objetivos[i].innerHTML = html;
    }
    if (objetivo && sortSel.value === 'gp-desc') ordenar();
  }

  /* --------------------- Historial del circuito (API) ------------------- */
  function cargarHistorial(circuitoId) {
    if (!circuitoId || pedidos[circuitoId]) return;
    pedidos[circuitoId] = true;

    function paginas(ruta, offset, acumulado) {
      return fetch(API + '/circuits/' + encodeURIComponent(circuitoId) + '/' + ruta + '.json?limit=100&offset=' + offset)
        .then(function (res) { return res.ok ? res.json() : null; })
        .then(function (data) {
          if (!data) return acumulado;
          var tabla = (data.MRData && data.MRData.RaceTable) || {};
          var lista = tabla.Races || [];
          var total = parseInt((data.MRData && data.MRData.total) || '0', 10);
          acumulado = acumulado.concat(lista);
          if (acumulado.length < total && lista.length && offset < 1500) {
            return paginas(ruta, offset + 100, acumulado);
          }
          return acumulado;
        })
        .catch(function () { return acumulado; });
    }

    /* Solo vencedores; si el endpoint no responde, se usan los resultados
       completos y se filtran las victorias. */
    paginas('results/1', 0, []).then(function (carreras) {
      if (carreras.length) return carreras;
      return paginas('results', 0, []).then(function (todas) {
        var salida = [];
        todas.forEach(function (carrera) {
          var results = carrera.Results || [];
          for (var i = 0; i < results.length; i++) {
            if (String(results[i].position) === '1') {
              salida.push({
                season: carrera.season,
                raceName: carrera.raceName,
                date: carrera.date,
                Results: [results[i]]
              });
              break;
            }
          }
        });
        return salida;
      });
    }).then(function (carreras) {
      if (!carreras.length) { pintarStats(circuitoId); return; }

      var temporadas = [];
      var porPiloto = {};
      var porEscuderia = {};
      var nombrePiloto = {};

      carreras.forEach(function (carrera) {
        if (carrera.season) temporadas.push(parseInt(carrera.season, 10));
        var res = (carrera.Results || [])[0];
        if (!res) return;
        var dId = res.Driver && res.Driver.driverId;
        if (dId) {
          porPiloto[dId] = (porPiloto[dId] || 0) + 1;
          nombrePiloto[dId] = res.Driver.familyName || (res.Driver.givenName + ' ' + res.Driver.familyName);
        }
        var cId = res.Constructor && (res.Constructor.constructorId || res.Constructor.name);
        if (cId) porEscuderia[cId] = (porEscuderia[cId] || 0) + 1;
      });

      var mejorPiloto = null;
      Object.keys(porPiloto).forEach(function (k) {
        if (!mejorPiloto || porPiloto[k] > porPiloto[mejorPiloto.id]) {
          mejorPiloto = { id: k, n: porPiloto[k] };
        }
      });

      temporadas.sort(function (a, b) { return a - b; });

      stats[circuitoId] = {
        gp: carreras.length,
        primera: temporadas.length ? temporadas[0] : '—',
        ultima: temporadas.length ? temporadas[temporadas.length - 1] : '—',
        piloto: mejorPiloto ? nombrePiloto[mejorPiloto.id] : '',
        victorias: mejorPiloto ? mejorPiloto.n : 0
      };
      pintarStats(circuitoId);
    });
  }

  /* --------------------------- Filtro y orden --------------------------- */
  function ordenar() {
    var modo = sortSel.value;
    var lista = entries.slice();

    lista.sort(function (a, b) {
      var sa = stats[a.circuitoId] || {};
      var sb = stats[b.circuitoId] || {};
      switch (modo) {
        case 'name-asc':
          return a.race.circuitName.localeCompare(b.race.circuitName, 'es');
        case 'name-desc':
          return b.race.circuitName.localeCompare(a.race.circuitName, 'es');
        case 'country':
          return (a.race.country || '').localeCompare(b.race.country || '', 'es') ||
            a.race.circuitName.localeCompare(b.race.circuitName, 'es');
        case 'gp-desc':
          return ((sb.gp || 0) - (sa.gp || 0)) ||
            a.race.circuitName.localeCompare(b.race.circuitName, 'es');
        default:
          return (parseInt(a.race.round, 10) || 0) - (parseInt(b.race.round, 10) || 0);
      }
    });

    var fragmento = document.createDocumentFragment();
    lista.forEach(function (entrada) { fragmento.appendChild(entrada.el); });
    grid.appendChild(fragmento);
  }

  function aplicarFiltros() {
    var termino = (search.value || '').trim().toLowerCase();
    var pais = countrySel.value;
    var visibles = 0;

    entries.forEach(function (entrada) {
      var r = entrada.race;
      var coincideTexto = !termino ||
        (r.circuitName + ' ' + (r.country || '') + ' ' + (r.locality || '')).toLowerCase().indexOf(termino) !== -1;
      var coincidePais = !pais || (r.country || '') === pais;
      var visible = coincideTexto && coincidePais;
      entrada.el.hidden = !visible;
      if (visible) visibles++;
    });

    ordenar();

    badge.textContent = visibles + (visibles === 1 ? ' circuito' : ' circuitos') +
      (visibles !== entries.length ? ' de ' + entries.length : '');

    if (!visibles) {
      var vacio = grid.querySelector('.tf-empty');
      if (!vacio) {
        vacio = document.createElement('div');
        vacio.className = 'tf-empty';
        vacio.style.gridColumn = '1 / -1';
        grid.appendChild(vacio);
      }
      vacio.textContent = 'No se encontraron circuitos con esos filtros.';
      vacio.hidden = false;
    } else {
      var vacio2 = grid.querySelector('.tf-empty');
      if (vacio2) vacio2.hidden = true;
    }

    searchClear.hidden = !search.value;
  }

  /* --------------------- Próxima sesión (temporada actual) ------------------ */
  /* Una sola línea por tarjeta: en curso o la siguiente, con cuenta atrás
     compacta. Nunca se muestran horarios que la API no publique. */
  function pintarSesiones() {
    if (!temporadaActual || !horarios) return;
    var ahora = new Date();

    entries.forEach(function (entrada) {
      var nodo = entrada.el.querySelector('[data-sesion]');
      if (!nodo) return;

      if (!entrada.sesiones) entrada.sesiones = horarios.sesionesDe(entrada.race.origen || entrada.race);
      var enCurso = null;
      var siguiente = null;
      for (var i = 0; i < entrada.sesiones.length; i++) {
        var e = horarios.estado(entrada.sesiones[i], ahora);
        if (e === 'en-curso' && !enCurso) enCurso = entrada.sesiones[i];
        if (e === 'pendiente' && !siguiente) siguiente = entrada.sesiones[i];
      }

      var objetivo = enCurso || siguiente;
      if (!objetivo || !objetivo.inicio) {
        nodo.hidden = true;
        nodo.textContent = '';
        return;
      }

      nodo.hidden = false;
      nodo.className = 'circuito-card__sesion' + (enCurso ? ' is-live' : '');
      nodo.innerHTML =
        '<b>' + (enCurso ? 'En curso' : 'Próxima') + '</b>' +
        '<span class="circuito-card__sesion-nombre">' + esc(horarios.corto(objetivo.clave)) + '</span>' +
        '<span class="circuito-card__sesion-cuando">' + esc(horarios.fechaCompacta(objetivo.inicio)) + ' · ' +
          esc(horarios.hora(objetivo.inicio)) + '</span>' +
        (enCurso ? '' : '<span class="circuito-card__sesion-cuenta">' +
          esc(horarios.cuentaAtras(objetivo.inicio.getTime() - ahora.getTime())) + '</span>');
    });
  }

  /* ------------------------------ Arranque ------------------------------ */
  function montar(races) {
    grid.innerHTML = '';
    entries = [];

    races.forEach(function (race) {
      var card = construirTarjeta(race);
      var entrada = {
        race: race,
        circuitoId: race.circuitoId,
        el: card,
        statsEl: card.querySelector('.circuito-card__stats')
      };
      entries.push(entrada);
      grid.appendChild(card);

      if (trazados && trazados.tiene(race.circuitoId)) {
        (function (nodo, id) {
          trazados.pintar(nodo, id, 2.2).then(function (ok) {
            var respaldo = nodo.parentNode.querySelector('.circuito-card__fallback');
            if (!ok) {
              nodo.remove();
              if (respaldo) respaldo.hidden = false;
            } else if (respaldo) {
              respaldo.hidden = true;
            }
          });
        })(card.querySelector('[data-trazo]'), race.circuitoId);
      }
    });

    // Países disponibles en la temporada
    var paises = [];
    races.forEach(function (r) {
      if (r.country && paises.indexOf(r.country) === -1) paises.push(r.country);
    });
    paises.sort(function (a, b) { return a.localeCompare(b, 'es'); });
    paises.forEach(function (p) {
      var opcion = document.createElement('option');
      opcion.value = p;
      opcion.textContent = p;
      countrySel.appendChild(opcion);
    });

    aplicarFiltros();

    // Próxima sesión de cada GP (solo en la temporada en curso)
    pintarSesiones();

    // Historial (progresivo, sin bloquear la vista)
    var ids = [];
    races.forEach(function (r) {
      if (r.circuitoId && ids.indexOf(r.circuitoId) === -1) ids.push(r.circuitoId);
    });
    ids.forEach(function (id, i) {
      window.setTimeout(function () { cargarHistorial(id); }, 250 * i);
    });

    // Aviso de procedencia de los trazados
    var conTrazo = 0;
    races.forEach(function (r) { if (trazados && trazados.tiene(r.circuitoId)) conTrazo++; });
    if (trazadosNote) {
      trazadosNote.hidden = false;
      trazadosNote.innerHTML = 'Trazados: ' + conTrazo + ' de ' + races.length +
        ' circuitos de la temporada ' + esc(year) +
        ' tienen trazado verificado (Wikimedia Commons, licencias libres). ' +
        'Se muestra el trazado más reciente disponible; en temporadas antiguas algunos circuitos tenían otra configuración. ' +
        'Los trazados sin fuente verificada usan una composición neutra: nunca se inventa un trazado.';
    }
  }

  search.addEventListener('input', aplicarFiltros);
  search.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') { search.value = ''; aplicarFiltros(); }
  });
  searchClear.addEventListener('click', function () {
    search.value = '';
    search.focus();
    aplicarFiltros();
  });
  countrySel.addEventListener('change', aplicarFiltros);
  sortSel.addEventListener('change', aplicarFiltros);

  // La próxima sesión y su cuenta atrás se actualizan sin recargar
  if (temporadaActual) {
    window.setInterval(pintarSesiones, 30000);
  }

  fetch(API + '/' + year + '.json?limit=100')
    .then(function (res) { return res.json(); })
    .then(function (data) {
      var lista = (data.MRData && data.MRData.RaceTable && data.MRData.RaceTable.Races) || [];
      if (!lista.length) {
        grid.innerHTML = '<div class="tf-empty" style="grid-column:1/-1;">No hay Grandes Premios registrados en ' + esc(year) + '.</div>';
        badge.textContent = 'Sin datos';
        if (seasonNote) {
          seasonNote.hidden = false;
          seasonNote.textContent = 'La API no devuelve calendario para la temporada ' + year + '.';
        }
        return;
      }

      var races = lista.map(function (race) {
        return {
          round: race.round,
          raceName: race.raceName,
          circuitName: race.Circuit.circuitName,
          circuitoId: race.Circuit.circuitId,
          country: race.Circuit.Location && race.Circuit.Location.country,
          locality: race.Circuit.Location && race.Circuit.Location.locality,
          date: race.date,
          dateObj: new Date(race.date),
          origen: race
        };
      });

      montar(races);
    })
    .catch(function (error) {
      console.error(error);
      grid.innerHTML = '<div class="tf-error" style="grid-column:1/-1;">Error al cargar los circuitos.</div>';
      badge.textContent = 'Error';
    });
})();
