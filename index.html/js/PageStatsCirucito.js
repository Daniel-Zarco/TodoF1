/* ============================================================
   TodoF1 — Ficha de circuito
   Trazado SVG grande y centrado, historial completo en F1
   (todas las ediciones) y clasificación de la temporada
   seleccionada. Reutiliza la API de Jolpica a través del proxy.
   ============================================================ */
(function () {
  'use strict';

  var API = 'https://api.jolpi.ca/ergast/f1';
  var params = new URLSearchParams(window.location.search);
  var circuitoNombre = params.get('circuito') || '';
  var circuitoId = params.get('id') || '';
  // La temporada por defecto es la actual (nunca un año fijo en el código).
  var year = params.get('year') || String(new Date().getFullYear());
  var horarios = window.TodoF1Horarios || null;
  var temporadaActual = !!(horarios && horarios.esTemporadaActual(year));

  var titulo = document.getElementById('titulo');
  var tbody = document.querySelector('#tabla-resultados tbody');
  var infoAdicional = document.getElementById('info-adicional');
  var vueltaRapidaDiv = document.getElementById('vuelta-rapida');
  var trazoHost = document.getElementById('circuito-trazo');
  var trazoFallback = document.getElementById('circuito-fallback');
  var trazoCargando = document.getElementById('circuito-cargando');
  var trazoNota = document.getElementById('trazo-nota');
  var trazoAviso = document.getElementById('trazo-aviso');
  var kpis = document.getElementById('kpis');
  var ganadoresBody = document.querySelector('#tabla-ganadores tbody');
  var ganadoresCuenta = document.getElementById('ganadores-cuenta');
  var trazados = window.TodoF1Trazados || null;

  titulo.textContent = circuitoNombre || 'Circuito';
  document.getElementById('year-label').textContent = year;
  document.getElementById('race-year').textContent = year;
  document.getElementById('btn-volver').href = '/index.html/PageCircuitos.php?year=' + encodeURIComponent(year);

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

  function formatFechaCorta(value) {
    var date = new Date(value);
    if (isNaN(date.getTime())) return value || '—';
    return date.toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' });
  }

  function monograma(nombre) {
    var palabras = String(nombre || '').replace(/[^\p{L}\p{N} ]/gu, ' ').split(/\s+/).filter(Boolean);
    if (!palabras.length) return '—';
    if (palabras.length === 1) return palabras[0].slice(0, 3).toUpperCase();
    return (palabras[0][0] + palabras[1][0]).toUpperCase();
  }

  function kpi(etiqueta, valor, meta) {
    return '<div class="tf-kpi">' +
      '<div class="tf-kpi__label">' + esc(etiqueta) + '</div>' +
      '<div class="tf-kpi__value">' + esc(valor) + '</div>' +
      (meta ? '<div class="tf-kpi__meta">' + esc(meta) + '</div>' : '') +
      '</div>';
  }

  /* ------------------------------ Trazado ------------------------------ */
  document.getElementById('circuito-monograma').textContent = monograma(circuitoNombre);

  function pintarTrazo() {
    if (!trazoHost) return;
    if (!circuitoId || !trazados || !trazados.tiene(circuitoId)) {
      if (trazoCargando) trazoCargando.hidden = true;
      if (trazoFallback) trazoFallback.hidden = false;
      if (trazoAviso) {
        trazoAviso.hidden = false;
        trazoAviso.textContent = 'No hay trazado verificado con licencia libre para este circuito. ' +
          'Se muestra una composición neutra: en TodoF1 no se publican trazados inventados ni de otros circuitos.';
      }
      return;
    }

    trazados.pintar(trazoHost, circuitoId, 3.2).then(function (ok) {
      if (trazoCargando) trazoCargando.hidden = true;
      if (!ok) {
        trazoHost.hidden = true;
        if (trazoFallback) trazoFallback.hidden = false;
        return;
      }
      var meta = trazados.meta(circuitoId);
      var conf = trazados.config ? trazados.config(circuitoId) : '';
      if (meta && trazoNota) {
        var autor = meta.autor ? esc(meta.autor) : 'Autoría no indicada';
        var fuente = meta.fuente
          ? '<a href="' + esc(meta.fuente) + '" target="_blank" rel="noopener noreferrer">Wikimedia Commons</a>'
          : 'Wikimedia Commons';
        trazoNota.innerHTML = 'Trazado: ' + autor + ' · ' + esc(meta.licencia || 'licencia libre') + ' · ' + fuente +
          (conf ? '. Configuración representada: ' + esc(conf) + '.' : '.');
        trazoNota.hidden = false;
      }
      if (trazoAviso) {
        trazoAviso.hidden = false;
        trazoAviso.textContent = 'Se muestra el trazado más reciente disponible con licencia libre' +
          (conf ? ' (en este caso, la configuración ' + conf + ')' : '') +
          '. Otras temporadas se disputaron con configuraciones distintas del circuito.';
      }
    });
  }

  /* ------------------------- Historial del circuito --------------------- */
  /* Vía principal: solo los ganadores de cada edición. Si el endpoint no
     está disponible, se reconstruye desde los resultados completos. */
  function paginasHistorial(id, offset, acumulado) {
    return fetch(API + '/circuits/' + encodeURIComponent(id) + '/results/1.json?limit=100&offset=' + offset)
      .then(function (res) { return res.ok ? res.json() : null; })
      .then(function (data) {
        if (!data) return acumulado === null ? null : acumulado;
        var tabla = (data.MRData && data.MRData.RaceTable) || {};
        var lista = tabla.Races || [];
        var total = parseInt((data.MRData && data.MRData.total) || '0', 10);
        acumulado = acumulado.concat(lista);
        if (acumulado.length < total && lista.length && offset < 1200) {
          return paginasHistorial(id, offset + 100, acumulado);
        }
        return acumulado;
      });
  }

  function paginasCompletas(id, offset, acumulado) {
    return fetch(API + '/circuits/' + encodeURIComponent(id) + '/results.json?limit=100&offset=' + offset)
      .then(function (res) { return res.ok ? res.json() : null; })
      .then(function (data) {
        if (!data) return acumulado;
        var tabla = (data.MRData && data.MRData.RaceTable) || {};
        var lista = tabla.Races || [];
        var total = parseInt((data.MRData && data.MRData.total) || '0', 10);
        acumulado = acumulado.concat(lista);
        if (acumulado.length < total && lista.length && offset < 1500) {
          return paginasCompletas(id, offset + 100, acumulado);
        }
        return acumulado;
      });
  }

  /* Normaliza a [{season, raceName, date, Results:[ganador]}] */
  function normalizarGanadores(carreras, soloGanadores) {
    var salida = [];
    (carreras || []).forEach(function (carrera) {
      var results = carrera.Results || [];
      if (soloGanadores) {
        var ganador = null;
        for (var i = 0; i < results.length; i++) {
          if (String(results[i].position) === '1') { ganador = results[i]; break; }
        }
        if (!ganador) return;
        salida.push({
          season: carrera.season,
          raceName: carrera.raceName,
          date: carrera.date,
          Results: [ganador]
        });
      } else {
        if (!results.length) return;
        salida.push(carrera);
      }
    });
    return salida;
  }

  function cargarHistorial(id) {
    return paginasHistorial(id, 0, [])
      .then(function (carreras) {
        if (carreras && carreras.length) return normalizarGanadores(carreras, false);
        // Respaldo: resultados completos, filtrando los vencedores
        return paginasCompletas(id, 0, []).then(function (todas) {
          return normalizarGanadores(todas, true);
        });
      });
  }

  function renderHistorial(carreras) {
    if (!carreras.length) {
      kpis.innerHTML = '<div class="tf-empty" style="grid-column:1/-1;">La API no devuelve historial para este circuito.</div>';
      ganadoresBody.innerHTML = '<tr class="tf-table__empty"><td colspan="5">Sin ediciones registradas.</td></tr>';
      ganadoresCuenta.textContent = '0 ediciones';
      return;
    }

    var temporadas = [];
    var porPiloto = {};
    var nombrePiloto = {};
    var porEscuderia = {};
    var nombreEscuderia = {};

    carreras.forEach(function (carrera) {
      var anio = parseInt(carrera.season, 10);
      if (!isNaN(anio)) temporadas.push(anio);
      var res = (carrera.Results || [])[0];
      if (!res) return;
      var d = res.Driver || {};
      var c = res.Constructor || {};
      var dId = d.driverId || (d.givenName + ' ' + d.familyName);
      porPiloto[dId] = (porPiloto[dId] || 0) + 1;
      nombrePiloto[dId] = d.familyName || (d.givenName + ' ' + d.familyName) || dId;
      var cId = c.constructorId || c.name;
      if (cId) {
        porEscuderia[cId] = (porEscuderia[cId] || 0) + 1;
        nombreEscuderia[cId] = c.name || cId;
      }
    });

    function mejor(mapa, nombres) {
      var clave = null;
      Object.keys(mapa).forEach(function (k) {
        if (clave === null || mapa[k] > mapa[clave]) clave = k;
      });
      return clave === null ? null : { nombre: nombres[clave], n: mapa[clave] };
    }

    temporadas.sort(function (a, b) { return a - b; });
    var primero = temporadas[0];
    var ultimo = temporadas[temporadas.length - 1];
    var piloto = mejor(porPiloto, nombrePiloto);
    var escuderia = mejor(porEscuderia, nombreEscuderia);

    kpis.innerHTML =
      kpi('Grandes Premios', carreras.length, primero + ' – ' + ultimo) +
      kpi('Primera edición', primero, 'en este circuito') +
      kpi('Más victorias (piloto)', piloto ? piloto.nombre : '—', piloto ? piloto.n + ' victorias' : '') +
      kpi('Más victorias (escudería)', escuderia ? escuderia.nombre : '—', escuderia ? escuderia.n + ' victorias' : '');

    // Ediciones (más recientes primero)
    var ordenadas = carreras.slice().sort(function (a, b) {
      return (parseInt(b.season, 10) || 0) - (parseInt(a.season, 10) || 0);
    });

    var fragmento = document.createDocumentFragment();
    ordenadas.forEach(function (carrera) {
      var res = (carrera.Results || [])[0] || {};
      var d = res.Driver || {};
      var c = res.Constructor || {};
      var fila = document.createElement('tr');
      fila.innerHTML =
        '<td class="tf-td-num">' + esc(carrera.season) + '</td>' +
        '<td>' + esc(carrera.raceName) + '</td>' +
        '<td class="tf-dim">' + esc(formatFechaCorta(carrera.date)) + '</td>' +
        '<td>' + esc((d.givenName ? d.givenName + ' ' : '') + (d.familyName || '—')) + '</td>' +
        '<td class="tf-dim">' + esc(c.name || '—') + '</td>';
      fragmento.appendChild(fila);
    });
    ganadoresBody.innerHTML = '';
    ganadoresBody.appendChild(fragmento);
    ganadoresCuenta.textContent = ordenadas.length + (ordenadas.length === 1 ? ' edición' : ' ediciones');
  }

  /* ------------------------ Temporada seleccionada ---------------------- */
  function renderClasificacion(carrera) {
    if (!carrera) {
      tbody.innerHTML = '<tr class="tf-table__empty"><td colspan="5">No se disputó un Gran Premio en este circuito en ' +
        esc(year) + '.</td></tr>';
      return;
    }

    return fetch(API + '/' + year + '/' + carrera.round + '/results.json?limit=100')
      .then(function (res) { return res.json(); })
      .then(function (data) {
        var races = (data.MRData && data.MRData.RaceTable && data.MRData.RaceTable.Races) || [];
        var results = (races[0] && races[0].Results) || [];
        if (!results.length) {
          tbody.innerHTML = '<tr class="tf-table__empty"><td colspan="5">No hay resultados disponibles.</td></tr>';
          vueltaRapidaDiv.innerHTML = '';
          return;
        }

        var fragmento = document.createDocumentFragment();
        results.forEach(function (result) {
          var pos = parseInt(result.position, 10);
          var fila = document.createElement('tr');
          fila.innerHTML =
            '<td class="tf-td-num tf-rank' + (pos <= 3 ? ' tf-rank-' + pos : '') + '">' + esc(result.position) + '</td>' +
            '<td>' + esc(result.Driver.givenName + ' ' + result.Driver.familyName) + '</td>' +
            '<td class="tf-dim">' + esc(result.Constructor.name) + '</td>' +
            '<td class="tf-td-right tf-td-num">' + esc(result.laps) + '</td>' +
            '<td class="tf-td-right tf-td-num">' + esc(result.Time ? result.Time.time : 'N/A') + '</td>';
          fragmento.appendChild(fila);
        });
        tbody.innerHTML = '';
        tbody.appendChild(fragmento);

        var rapida = results.find(function (r) { return r.FastestLap && r.FastestLap.rank === '1'; });
        if (rapida) {
          vueltaRapidaDiv.innerHTML =
            '<div class="tf-fastlap">' +
            '<span class="tf-fastlap__icon">⏱</span>' +
            '<div><div class="tf-fastlap__label">Vuelta rápida</div>' +
            '<div class="tf-fastlap__value">' + esc(rapida.Driver.givenName + ' ' + rapida.Driver.familyName) +
            ' · ' + esc(rapida.FastestLap.Time.time) +
            ' <span class="tf-dim">(vuelta ' + esc(rapida.FastestLap.lap) + ')</span></div></div>' +
            '</div>';
        } else {
          vueltaRapidaDiv.innerHTML =
            '<div class="tf-fastlap"><span class="tf-fastlap__icon">⏱</span>' +
            '<div><div class="tf-fastlap__label">Vuelta rápida</div>' +
            '<div class="tf-fastlap__value tf-dim">No disponible en la API para este año.</div></div></div>';
        }
      });
  }

  /* ------------------ Programa del fin de semana (actual) ------------------ */
  var programa = { sesiones: [], nodos: {} };

  function programaDelFinDeSemana(carrera) {
    var seccion = document.getElementById('seccion-horarios');
    if (!seccion) return;
    // Solo en la temporada en curso y si la API publica sesiones.
    if (!temporadaActual || !horarios || !carrera) return;

    var sesiones = horarios.sesionesDe(carrera);
    if (!sesiones.length) return;

    var contenedor = document.getElementById('horarios');
    var dias = horarios.agruparPorDia(sesiones);
    var fragmento = document.createDocumentFragment();
    programa.sesiones = sesiones;
    programa.nodos = {};

    dias.forEach(function (dia) {
      var bloque = document.createElement('div');
      bloque.className = 'horarios__dia';

      var titulo = document.createElement('h3');
      titulo.className = 'horarios__dia-titulo';
      var etiqueta = dia.etiqueta || '';
      titulo.textContent = etiqueta ? etiqueta.charAt(0).toUpperCase() + etiqueta.slice(1) : etiqueta;
      bloque.appendChild(titulo);

      var lista = document.createElement('ul');
      lista.className = 'horarios__lista';

      dia.sesiones.forEach(function (sesion) {
        var estado = horarios.estado(sesion, new Date());
        var li = document.createElement('li');
        li.className = 'horario is-' + estado;
        li.innerHTML =
          '<span class="horario__nombre">' + esc(sesion.nombre) + '</span>' +
          '<span class="horario__hora">' + (sesion.inicio ? esc(horarios.hora(sesion.inicio)) : 'Por confirmar') + '</span>' +
          '<span class="horario__estado">' + esc(horarios.etiquetaEstado(estado)) + '</span>';
        programa.nodos[sesion.clave] = li;
        lista.appendChild(li);
      });

      bloque.appendChild(lista);
      fragmento.appendChild(bloque);
    });

    contenedor.innerHTML = '';
    contenedor.appendChild(fragmento);

    var nota = document.getElementById('horarios-nota');
    if (nota) {
      var faltan = sesiones.filter(function (s) { return s.porConfirmar; }).length;
      nota.textContent = 'Horas de inicio oficiales en hora peninsular española. ' +
        'El estado se calcula con la hora real de inicio; la API no publica la duración de cada sesión.' +
        (faltan ? ' Hay ' + faltan + (faltan === 1 ? ' sesión sin horario publicado' : ' sesiones sin horario publicado') + '.' : '');
    }

    seccion.hidden = false;
    refrescarPrograma();
  }

  function refrescarPrograma() {
    if (!programa.sesiones.length || !horarios) return;
    var ahora = new Date();

    var enCurso = null;
    var siguiente = null;

    programa.sesiones.forEach(function (sesion) {
      var estado = horarios.estado(sesion, ahora);
      if (estado === 'en-curso' && !enCurso) enCurso = sesion;
      if (estado === 'pendiente' && !siguiente) siguiente = sesion;

      var li = programa.nodos[sesion.clave];
      if (li) {
        li.className = 'horario is-' + estado;
        var etiqueta = li.querySelector('.horario__estado');
        if (etiqueta) etiqueta.textContent = horarios.etiquetaEstado(estado);
      }
    });

    Object.keys(programa.nodos).forEach(function (clave) {
      programa.nodos[clave].classList.remove('is-destacada');
    });

    var objetivo = enCurso || siguiente;
    var caja = document.getElementById('horarios-proxima');

    if (caja) {
      if (objetivo && objetivo.inicio) {
        caja.hidden = false;
        caja.className = 'horarios-proxima' + (enCurso ? ' is-live' : '');
        document.getElementById('horarios-proxima-etiqueta').textContent =
          enCurso ? 'En curso' : 'Próxima sesión';
        document.getElementById('horarios-proxima-nombre').textContent = objetivo.nombre;
        document.getElementById('horarios-proxima-cuando').textContent =
          horarios.diaLargo(objetivo.inicio) + ', ' + horarios.hora(objetivo.inicio);
        document.getElementById('horarios-proxima-cuenta').textContent =
          enCurso ? '' : horarios.cuentaAtras(objetivo.inicio.getTime() - ahora.getTime());
        if (programa.nodos[objetivo.clave]) programa.nodos[objetivo.clave].classList.add('is-destacada');
      } else {
        caja.hidden = true;
      }
    }

    var resumen = document.getElementById('horarios-resumen');
    if (resumen) {
      var pendientes = programa.sesiones.filter(function (s) {
        var e = horarios.estado(s, ahora);
        return e === 'pendiente' || e === 'en-curso';
      }).length;
      resumen.textContent = pendientes
        ? pendientes + (pendientes === 1 ? ' sesión pendiente' : ' sesiones pendientes')
        : 'Fin de semana finalizado';
    }
  }

  /* ------------------------------ Arranque ------------------------------ */
  fetch(API + '/' + year + '/races.json?limit=100')
    .then(function (res) { return res.json(); })
    .then(function (data) {
      var races = (data.MRData && data.MRData.RaceTable && data.MRData.RaceTable.Races) || [];
      var carrera = races.find(function (r) {
        if (circuitoId) return r.Circuit.circuitId === circuitoId;
        return r.Circuit.circuitName === circuitoNombre;
      }) || null;

      if (carrera) {
        circuitoId = carrera.Circuit.circuitId || circuitoId;
        circuitoNombre = carrera.Circuit.circuitName || circuitoNombre;
        titulo.textContent = circuitoNombre;
        document.getElementById('circuito-monograma').textContent = monograma(circuitoNombre);

        infoAdicional.innerHTML =
          (carrera.Circuit.Location && carrera.Circuit.Location.country
            ? '<span class="tf-badge">' + esc(carrera.Circuit.Location.country) + '</span>' : '') +
          (carrera.Circuit.Location && carrera.Circuit.Location.locality
            ? '<span class="tf-badge">' + esc(carrera.Circuit.Location.locality) + '</span>' : '') +
          '<span class="tf-badge tf-badge--red">Edición ' + esc(year) + ' · ' + esc(formatDate(carrera.date)) + '</span>' +
          (carrera.Circuit.url
            ? '<a class="tf-badge" href="' + esc(carrera.Circuit.url) + '" target="_blank" rel="noopener noreferrer">Wikipedia ↗</a>'
            : '');
      } else {
        infoAdicional.innerHTML = '<span class="tf-badge">Sin Gran Premio en ' + esc(year) + '</span>';
        if (!circuitoNombre && !circuitoId) {
          titulo.textContent = 'Circuito no indicado';
        }
      }

      pintarTrazo();

      // Programa del fin de semana (solo en la temporada en curso)
      programaDelFinDeSemana(carrera);

      // Historial completo (todas las ediciones)
      if (circuitoId) {
        cargarHistorial(circuitoId)
          .then(renderHistorial)
          .catch(function (error) {
            console.error(error);
            kpis.innerHTML = '<div class="tf-error" style="grid-column:1/-1;">Error al cargar el historial.</div>';
            ganadoresBody.innerHTML = '<tr class="tf-table__empty"><td colspan="5">Error al cargar los datos.</td></tr>';
          });
      } else {
        kpis.innerHTML = '<div class="tf-empty" style="grid-column:1/-1;">No se pudo identificar el circuito.</div>';
        ganadoresBody.innerHTML = '<tr class="tf-table__empty"><td colspan="5">No se pudo identificar el circuito.</td></tr>';
      }

      return renderClasificacion(carrera);
    })
    .catch(function (error) {
      console.error(error);
      if (trazoCargando) trazoCargando.hidden = true;
      tbody.innerHTML = '<tr class="tf-table__empty"><td colspan="5">Error al cargar los datos.</td></tr>';
      kpis.innerHTML = '<div class="tf-error" style="grid-column:1/-1;">Error al cargar los datos.</div>';
    });

  /* La cuenta atrás y el estado de cada sesión se refrescan cada segundo. */
  if (temporadaActual) {
    window.setInterval(refrescarPrograma, 1000);
  }
})();
