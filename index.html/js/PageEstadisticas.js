/* ============================================================
   TodoF1 — Dashboard de estadísticas de la temporada
   ------------------------------------------------------------
   Tres pestañas: Resumen, Evolución y Comparador.

   Datos (siempre de la API, nunca calculados a mano):
   · /{año}/driverStandings.json        clasificación final oficial
   · /{año}/constructorStandings.json   copa de constructores (desde 1958)
   · /{año}/results.json                resultados de cada prueba
   · /{año}/qualifying.json             clasificación (desde 1994)
   · /{año}/{ronda}/driverStandings.json  puntos OFICIALES tras cada prueba

   La evolución usa la clasificación oficial tras cada Gran Premio, así que
   respeta los sistemas de puntos históricos (1950: 8-6-4-3-2 + vuelta
   rápida y mejores resultados) y los puntos de Sprint sin recalcularios.
   ============================================================ */
(function () {
  'use strict';

  var API = 'https://api.jolpi.ca/ergast/f1';
  var params = new URLSearchParams(window.location.search);
  // Temporada por defecto: la actual (nunca un año fijo en el código).
  var year = parseInt(params.get('year') || String(new Date().getFullYear()), 10);
  var HOY = new Date().toISOString().slice(0, 10);

  var MAX_PILOTOS_EVOLUCION = 5;
  var TOP_GRAFICOS = 10;

  /* ------------------------------ Utilidades ------------------------------ */
  function esc(value) {
    return String(value == null ? '' : value).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  function num(value) {
    if (value === null || value === undefined || value === '') return '—';
    var n = parseFloat(value);
    if (isNaN(n)) return String(value);
    // Puntos con decimales solo si existen (p. ej. media carrera de 2021).
    return (Math.round(n * 100) / 100).toLocaleString('es-ES', { maximumFractionDigits: 2 });
  }

  function entero(value) {
    var n = parseInt(value, 10);
    return isNaN(n) ? 0 : n;
  }

  function puntosDe(v) {
    var n = parseFloat(v);
    return isNaN(n) ? 0 : n;
  }

  function nombrePiloto(driver) {
    if (!driver) return '—';
    return [driver.givenName, driver.familyName].filter(Boolean).join(' ') || driver.driverId || '—';
  }

  function rank(pos) {
    return pos >= 1 && pos <= 3 ? ' tf-rank-' + pos : '';
  }

  /* --------------------------- Color y contraste -------------------------- */
  function hexToRgb(hex) {
    var h = String(hex).replace('#', '');
    if (h.length === 3) h = h[0] + h[0] + h[1] + h[1] + h[2] + h[2];
    var n = parseInt(h, 16);
    return isNaN(n) ? { r: 225, g: 6, b: 0 } : { r: (n >> 16) & 255, g: (n >> 8) & 255, b: n & 255 };
  }

  function luminancia(hex) {
    var c = hexToRgb(hex);
    function ch(v) { v /= 255; return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4); }
    return 0.2126 * ch(c.r) + 0.7152 * ch(c.g) + 0.0722 * ch(c.b);
  }

  function contraste(a, b) {
    var la = luminancia(a), lb = luminancia(b);
    var hi = Math.max(la, lb), lo = Math.min(la, lb);
    return (hi + 0.05) / (lo + 0.05);
  }

  /* Aclara un color del tema hasta que se lea sobre el fondo oscuro. */
  function colorLegible(hex, fondo, minimo) {
    var objetivo = minimo || 3;
    var actual = String(hex || '').trim() || '#E10600';
    if (contraste(actual, fondo) >= objetivo) return actual;
    var c = hexToRgb(actual);
    for (var i = 1; i <= 10; i++) {
      var f = i / 10;
      var mezcla = {
        r: Math.round(c.r + (255 - c.r) * f),
        g: Math.round(c.g + (255 - c.g) * f),
        b: Math.round(c.b + (255 - c.b) * f)
      };
      var candidato = '#' + [mezcla.r, mezcla.g, mezcla.b].map(function (v) {
        return ('0' + v.toString(16)).slice(-2);
      }).join('').toUpperCase();
      if (contraste(candidato, fondo) >= objetivo) return candidato;
    }
    return actual;
  }

  function variablesTema() {
    var cs = getComputedStyle(document.documentElement);
    function v(nombre, porDefecto) {
      var valor = (cs.getPropertyValue(nombre) || '').trim();
      return valor || porDefecto;
    }
    return {
      fondo: v('--c-bg', '#222326'),
      superficie: v('--c-surface', '#2C2D31'),
      superficie2: v('--c-surface-2', '#34353A'),
      texto: v('--c-text', '#F5F5F5'),
      dim: v('--c-dim', '#AEB4BF'),
      mute: v('--c-mute', '#7E858F'),
      linea: v('--c-line', '#3A3B41'),
      primario: v('--team-primary', '#E10600')
    };
  }

  /* Paleta de series: el color del tema (aclarado si hace falta) + 4 tonos
     fijos que se leen sobre fondo oscuro. */
  function paletaSeries() {
    var t = variablesTema();
    return [
      colorLegible(t.primario, t.fondo, 3),
      '#47C7FC',
      '#FFD166',
      '#9B8CFF',
      '#4ADE80'
    ];
  }

  /* ------------------------------- Peticiones ------------------------------ */
  var cacheResp = {};

  function pedir(ruta) {
    if (cacheResp[ruta]) return cacheResp[ruta];
    var promesa = fetch(API + '/' + ruta).then(function (r) {
      if (!r.ok) throw new Error('http_' + r.status);
      return r.json();
    }).catch(function () {
      return null;
    });
    cacheResp[ruta] = promesa;
    return promesa;
  }

  /* Encadena páginas de 100 en 100 usando el campo total de MRData. */
  function pedirPaginas(ruta, maxPaginas) {
    var paginas = [];
    var tope = maxPaginas || 25;
    function siguiente(offset) {
      return pedir(ruta + (ruta.indexOf('?') >= 0 ? '&' : '?') + 'limit=100&offset=' + offset).then(function (d) {
        if (!d) return paginas;
        paginas.push(d);
        var total = entero(d.MRData && d.MRData.total);
        if (paginas.length * 100 < total && paginas.length < tope) {
          return siguiente(offset + 100);
        }
        return paginas;
      });
    }
    return siguiente(0);
  }

  function listaPaginas(paginas, ruta) {
    // Extrae y concatena una lista concreta de cada página.
    var salida = [];
    paginas.forEach(function (pagina) {
      if (!pagina || !pagina.MRData) return;
      salida = salida.concat(ruta(pagina.MRData) || []);
    });
    return salida;
  }

  /* -------------------------------- Estado -------------------------------- */
  var estado = {
    pilotos: [],          // [{id, nombre, equipo, nacionalidad, posicion, puntos, victorias}]
    porId: {},            // id -> piloto
    constructores: [],
    hayConstructores: false,
    resultados: null,     // { porPiloto: {...}, hayVueltasRapidas: bool }
    quali: null,          // { porPiloto: {...} } o null si no hay datos
    evolucion: null,      // { rondas: [{ronda, nombre, puntos: {id -> {p, pos}}}] }
    promesas: {}
  };

  /* ============================ 1. RESUMEN ============================= */
  function asegurarResumen() {
    if (estado.promesas.resumen) return estado.promesas.resumen;
    estado.promesas.resumen = Promise.all([
      pedir(year + '/driverStandings.json'),
      pedir(year + '/constructorStandings.json')
    ]).then(function (res) {
      estado.pilotos = normalizarPilotos(res[0]);
      estado.constructores = normalizarConstructores(res[1]);
      estado.hayConstructores = estado.constructores.length > 0;
      estado.pilotos.forEach(function (p) { estado.porId[p.id] = p; });
      pintarKpis();
      pintarTablaPilotos();
      pintarTablaConstructores();
      return estado;
    });
    return estado.promesas.resumen;
  }

  function asegurarResultados() {
    if (estado.promesas.resultados) return estado.promesas.resultados;
    estado.promesas.resultados = pedirPaginas(year + '/results.json').then(function (paginas) {
      estado.resultados = agregarResultados(paginas);
      return estado.resultados;
    });
    return estado.promesas.resultados;
  }

  function asegurarClasificacion() {
    if (estado.promesas.quali) return estado.promesas.quali;
    estado.promesas.quali = pedirPaginas(year + '/qualifying.json').then(function (paginas) {
      estado.quali = agregarClasificacion(paginas);
      return estado.quali;
    });
    return estado.promesas.quali;
  }

  function normalizarPilotos(datos) {
    var lista = datos && datos.MRData && datos.MRData.StandingsTable &&
      datos.MRData.StandingsTable.StandingsLists &&
      datos.MRData.StandingsTable.StandingsLists[0];
    var filas = (lista && lista.DriverStandings) || [];
    return filas.map(function (e, i) {
      var equipos = e.Constructors || [];
      var equipo = equipos.length ? equipos[equipos.length - 1].name : '';
      return {
        id: (e.Driver && e.Driver.driverId) || ('fila' + i),
        nombre: nombrePiloto(e.Driver),
        equipo: equipo,
        nacionalidad: (e.Driver && e.Driver.nationality) || '',
        posicion: e.position ? entero(e.position) : null,
        posicionTexto: e.positionText || (e.position ? e.position : '—'),
        puntos: puntosDe(e.points),
        victorias: entero(e.wins)
      };
    });
  }

  function normalizarConstructores(datos) {
    var lista = datos && datos.MRData && datos.MRData.StandingsTable &&
      datos.MRData.StandingsTable.StandingsLists &&
      datos.MRData.StandingsTable.StandingsLists[0];
    var filas = (lista && lista.ConstructorStandings) || [];
    return filas.map(function (e, i) {
      return {
        id: (e.Constructor && e.Constructor.constructorId) || ('equipo' + i),
        nombre: (e.Constructor && e.Constructor.name) || '—',
        nacionalidad: (e.Constructor && e.Constructor.nationality) || '',
        posicion: e.position ? entero(e.position) : null,
        posicionTexto: e.positionText || (e.position ? e.position : '—'),
        puntos: puntosDe(e.points),
        victorias: entero(e.wins)
      };
    });
  }

  function agregarResultados(paginas) {
    var races = listaPaginas(paginas, function (mr) {
      return mr.RaceTable && mr.RaceTable.Races;
    });
    var porPiloto = {};
    var hayVueltasRapidas = false;

    races.forEach(function (carrera) {
      (carrera.Results || []).forEach(function (res) {
        if (!res.Driver || !res.Driver.driverId) return;
        var id = res.Driver.driverId;
        if (!porPiloto[id]) {
          porPiloto[id] = { carreras: 0, podios: 0, victorias: 0, poles: 0, vr: 0, mejor: null };
        }
        var d = porPiloto[id];
        d.carreras++;
        var pos = entero(res.position);
        if (res.position !== undefined && res.position !== null && res.position !== '') {
          if (pos === 1) d.victorias++;
          if (pos <= 3) d.podios++;
          if (d.mejor === null || pos < d.mejor) d.mejor = pos;
        }
        if (res.FastestLap) {
          hayVueltasRapidas = true;
          if (res.FastestLap.rank === '1') d.vr++;
        }
      });
    });

    return { porPiloto: porPiloto, hayVueltasRapidas: hayVueltasRapidas, pruebas: races.length };
  }

  function agregarClasificacion(paginas) {
    var races = listaPaginas(paginas, function (mr) {
      return mr.RaceTable && mr.RaceTable.Races;
    });
    if (!races.length) return null;   // temporada sin datos de clasificación
    var porPiloto = {};
    races.forEach(function (carrera) {
      (carrera.QualifyingResults || []).forEach(function (q) {
        if (!q.Driver || !q.Driver.driverId) return;
        var id = q.Driver.driverId;
        if (!porPiloto[id]) porPiloto[id] = { poles: 0, participaciones: 0 };
        porPiloto[id].participaciones++;
        if (entero(q.position) === 1) porPiloto[id].poles++;
      });
    });
    return { porPiloto: porPiloto, pruebas: races.length };
  }

  /* ------------------------------- KPIs ----------------------------------- */
  function tarjetaKpi(etiqueta, valor, meta, esTexto) {
    var div = document.createElement('div');
    div.className = 'tf-kpi';
    div.innerHTML =
      '<span class="tf-kpi__label">' + esc(etiqueta) + '</span>' +
      '<div class="tf-kpi__value' + (esTexto ? ' is-text' : ' tf-num') + '">' + esc(valor) + '</div>' +
      (meta ? '<div class="tf-kpi__meta">' + esc(meta) + '</div>' : '');
    return div;
  }

  function pintarKpis() {
    var cont = document.getElementById('kpis');
    if (!cont) return;
    cont.innerHTML = '';

    var lider = estado.pilotos[0];
    var liderEquipo = estado.constructores[0];

    cont.appendChild(tarjetaKpi('Líder pilotos', lider ? lider.nombre : 'Sin datos',
      lider ? num(lider.puntos) + ' puntos · ' + lider.victorias + (lider.victorias === 1 ? ' victoria' : ' victorias') : 'Sin datos disponibles',
      !lider));

    cont.appendChild(tarjetaKpi('Pilotos', estado.pilotos.length || '—',
      estado.pilotos.length ? 'En el campeonato' : 'Sin datos disponibles'));

    cont.appendChild(tarjetaKpi('Líder constructores', liderEquipo ? liderEquipo.nombre : 'Sin datos',
      liderEquipo ? num(liderEquipo.puntos) + ' puntos' : 'Sin datos disponibles',
      !liderEquipo));

    cont.appendChild(tarjetaKpi('Escuderías', estado.hayConstructores ? estado.constructores.length : '—',
      estado.hayConstructores ? 'En el campeonato' : 'Copa desde 1958'));
  }

  /* ------------------------------ Tablas ---------------------------------- */
  function filaTabla(celdas, alPulsar) {
    var tr = document.createElement('tr');
    tr.tabIndex = 0;
    tr.innerHTML = celdas;
    tr.addEventListener('click', alPulsar);
    tr.addEventListener('keydown', function (event) {
      if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        alPulsar();
      }
    });
    return tr;
  }

  function pintarTablaPilotos() {
    var tbody = document.getElementById('standings-pilotos');
    var cuenta = document.getElementById('cuenta-pilotos');
    if (!tbody) return;

    if (!estado.pilotos.length) {
      tbody.innerHTML = '<tr class="tf-table__empty"><td colspan="6">Sin datos disponibles para la temporada ' + esc(year) + '.</td></tr>';
      if (cuenta) cuenta.textContent = 'Sin datos';
      return;
    }

    var maxPuntos = Math.max.apply(null, estado.pilotos.map(function (p) { return p.puntos; }).concat([1]));
    var fragmento = document.createDocumentFragment();

    estado.pilotos.forEach(function (p) {
      var pos = p.posicion || entero(p.posicionTexto);
      var fila = filaTabla(
        '<td class="tf-td-num tf-rank' + rank(pos) + '">' + esc(p.posicionTexto) + '</td>' +
        '<td class="tf-td-num"><span class="celda-nombre">' + esc(p.nombre) + '</span>' +
          '<span class="celda-barra" aria-hidden="true"><i style="width:' +
            Math.max(2, Math.round((p.puntos / maxPuntos) * 100)) + '%"></i></span></td>' +
        '<td class="tf-dim col-oculta-movil">' + esc(p.nacionalidad || '—') + '</td>' +
        '<td class="col-oculta-movil">' + esc(p.equipo || '—') + '</td>' +
        '<td class="tf-td-right tf-td-num">' + esc(p.victorias) + '</td>' +
        '<td class="tf-td-right tf-td-num tf-num--fuerte">' + esc(num(p.puntos)) + '</td>',
        function () {
          window.location.href = 'PageStatsPiloto.php?driverId=' + encodeURIComponent(p.id) +
            '&piloto=' + encodeURIComponent(p.nombre) + '&year=' + year;
        }
      );
      fragmento.appendChild(fila);
    });

    tbody.innerHTML = '';
    tbody.appendChild(fragmento);
    if (cuenta) cuenta.textContent = estado.pilotos.length + (estado.pilotos.length === 1 ? ' piloto' : ' pilotos');
  }

  function pintarTablaConstructores() {
    var tbody = document.getElementById('standings-escuderias');
    var cuenta = document.getElementById('cuenta-escuderias');
    if (!tbody) return;

    if (!estado.constructores.length) {
      tbody.innerHTML = '<tr class="tf-table__empty"><td colspan="5">Sin datos disponibles: la Copa de Constructores ' +
        'se disputa desde 1958.</td></tr>';
      if (cuenta) cuenta.textContent = 'Sin datos';
      return;
    }

    var maxPuntos = Math.max.apply(null, estado.constructores.map(function (c) { return c.puntos; }).concat([1]));
    var fragmento = document.createDocumentFragment();

    estado.constructores.forEach(function (c) {
      var pos = c.posicion || entero(c.posicionTexto);
      var fila = filaTabla(
        '<td class="tf-td-num tf-rank' + rank(pos) + '">' + esc(c.posicionTexto) + '</td>' +
        '<td class="tf-td-num"><span class="celda-nombre">' + esc(c.nombre) + '</span>' +
          '<span class="celda-barra" aria-hidden="true"><i style="width:' +
            Math.max(2, Math.round((c.puntos / maxPuntos) * 100)) + '%"></i></span></td>' +
        '<td class="tf-dim col-oculta-movil">' + esc(c.nacionalidad || '—') + '</td>' +
        '<td class="tf-td-right tf-td-num">' + esc(c.victorias) + '</td>' +
        '<td class="tf-td-right tf-td-num tf-num--fuerte">' + esc(num(c.puntos)) + '</td>',
        function () {
          window.location.href = 'PageStatsEscuderia.php?escuderia=' + encodeURIComponent(c.nombre) + '&year=' + year;
        }
      );
      fragmento.appendChild(fila);
    });

    tbody.innerHTML = '';
    tbody.appendChild(fragmento);
    if (cuenta) cuenta.textContent = estado.constructores.length + (estado.constructores.length === 1 ? ' escudería' : ' escuderías');
  }

  /* ------------------------------ Gráficos -------------------------------- */
  function hayChart() {
    return typeof window.Chart !== 'undefined';
  }

  function animacion() {
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    return reduce ? false : { duration: 500 };
  }

  function opcionesEjes(t, horizontal) {
    var ejeValor = horizontal ? 'x' : 'y';
    var ejeCat = horizontal ? 'y' : 'x';
    var opciones = { responsive: true, maintainAspectRatio: false, animation: animacion() };
    opciones.scales = {};
    opciones.scales[ejeValor] = {
      beginAtZero: true,
      grid: { color: 'rgba(255,255,255,0.07)' },
      ticks: { color: t.dim, precision: 0 }
    };
    opciones.scales[ejeCat] = {
      grid: { display: false },
      ticks: { color: t.texto, autoSkip: false }
    };
    return opciones;
  }

  function mostrarRespaldoGrafico(idCanvas, idRespaldo, filas, etiqueta) {
    var caja = document.getElementById(idRespaldo);
    var canvas = document.getElementById(idCanvas);
    if (!caja) return;
    if (canvas) canvas.style.display = 'none';
    if (!filas.length) {
      caja.hidden = false;
      caja.innerHTML = '<div class="tf-empty">Sin datos disponibles.</div>';
      return;
    }
    var max = Math.max.apply(null, filas.map(function (f) { return f.valor; }).concat([1]));
    caja.hidden = false;
    caja.innerHTML = '<p class="chart-fallback__nota">Gráfico no disponible: se muestran los datos en barras.</p>' +
      filas.map(function (f) {
        return '<div class="barra-fila"><span class="barra-fila__nombre">' + esc(f.nombre) + '</span>' +
          '<span class="barra-fila__valor">' + esc(num(f.valor)) + '</span>' +
          '<span class="barra-fila__pista"><i style="width:' + Math.max(2, Math.round((f.valor / max) * 100)) + '%"></i></span></div>';
      }).join('') +
      (etiqueta ? '<p class="chart-fallback__nota">' + esc(etiqueta) + '</p>' : '');
  }

  function pintarGraficosResumen() {
    var t = variablesTema();
    var series = paletaSeries();
    var top = estado.pilotos.slice(0, TOP_GRAFICOS);
    var metaPuntos = document.getElementById('meta-puntos');
    var metaVictorias = document.getElementById('meta-victorias');

    if (metaPuntos) {
      metaPuntos.textContent = estado.pilotos.length
        ? 'Top ' + top.length + ' de ' + estado.pilotos.length + ' pilotos'
        : '';
    }

    if (!hayChart()) {
      mostrarRespaldoGrafico('chart-puntos', 'fb-puntos',
        top.map(function (p) { return { nombre: p.nombre, valor: p.puntos }; }), 'Puntos del campeonato');
      mostrarRespaldoGrafico('chart-victorias', 'fb-victorias',
        top.map(function (p) { return { nombre: p.nombre, valor: p.victorias }; }), 'Victorias');
      if (metaVictorias) metaVictorias.textContent = '';
      return;
    }

    var nombres = top.map(function (p) { return p.nombre; });

    var canvasPuntos = document.getElementById('chart-puntos');
    if (canvasPuntos) {
      new Chart(canvasPuntos, {
        type: 'bar',
        data: {
          labels: nombres,
          datasets: [{
            label: 'Puntos',
            data: top.map(function (p) { return p.puntos; }),
            backgroundColor: series[0],
            borderRadius: 4,
            maxBarThickness: 18
          }]
        },
        options: Object.assign(opcionesEjes(t, true), {
          plugins: {
            legend: { display: false },
            tooltip: {
              callbacks: {
                label: function (ctx) {
                  var p = top[ctx.dataIndex];
                  return num(p.puntos) + ' puntos · ' + p.victorias + ' victorias · ' +
                    (p.equipo || 'sin equipo');
                }
              }
            }
          }
        })
      });
    }

    // Victorias y podios: se necesitan los resultados (podios).
    var canvasVic = document.getElementById('chart-victorias');
    asegurarResultados().then(function (res) {
      var conDatos = estado.pilotos.slice(0, TOP_GRAFICOS).map(function (p) {
        var r = (res && res.porPiloto && res.porPiloto[p.id]) || {};
        return { piloto: p, podios: r.podios || 0 };
      });
      if (metaVictorias) metaVictorias.textContent = 'Top ' + conDatos.length + ' por puntos';

      if (!hayChart()) {
        mostrarRespaldoGrafico('chart-victorias', 'fb-victorias',
          conDatos.map(function (d) { return { nombre: d.piloto.nombre, valor: d.piloto.victorias }; }),
          'Victorias');
        return;
      }
      if (!canvasVic) return;
      new Chart(canvasVic, {
        type: 'bar',
        data: {
          labels: conDatos.map(function (d) { return d.piloto.nombre; }),
          datasets: [
            {
              label: 'Victorias',
              data: conDatos.map(function (d) { return d.piloto.victorias; }),
              backgroundColor: series[0],
              borderRadius: 4,
              maxBarThickness: 12
            },
            {
              label: 'Podios',
              data: conDatos.map(function (d) { return d.podios; }),
              backgroundColor: t.dim,
              borderRadius: 4,
              maxBarThickness: 12
            }
          ]
        },
        options: Object.assign(opcionesEjes(t, false), {
          plugins: {
            legend: { display: true, labels: { color: t.dim, boxWidth: 10, boxHeight: 10, usePointStyle: true } },
            tooltip: {
              callbacks: {
                label: function (ctx) { return ctx.dataset.label + ': ' + ctx.formattedValue; }
              }
            }
          }
        })
      });
    });
  }

  /* =========================== 2. EVOLUCIÓN ============================ */
  function asegurarEvolucion(alProgreso) {
    if (estado.promesas.evolucion) return estado.promesas.evolucion;

    estado.promesas.evolucion = pedir(year + '.json').then(function (cal) {
      var races = (cal && cal.MRData && cal.MRData.RaceTable && cal.MRData.RaceTable.Races) || [];
      var hechas = races.filter(function (r) { return r.date && r.date < HOY; });
      if (!hechas.length) {
        estado.evolucion = { rondas: [] };
        return estado.evolucion;
      }

      var rondas = [];
      var hechasOk = 0;
      var siguiente = 0;

      function trabajador() {
        if (siguiente >= hechas.length) return Promise.resolve();
        var carrera = hechas[siguiente++];
        return pedir(year + '/' + carrera.round + '/driverStandings.json').then(function (d) {
          var lista = d && d.MRData && d.MRData.StandingsTable &&
            d.MRData.StandingsTable.StandingsLists && d.MRData.StandingsTable.StandingsLists[0];
          var filas = (lista && lista.DriverStandings) || [];
          if (filas.length) {
            var mapa = {};
            filas.forEach(function (e) {
              var id = e.Driver && e.Driver.driverId;
              if (!id) return;
              mapa[id] = {
                puntos: puntosDe(e.points),
                posicion: e.position ? entero(e.position) : (e.positionText || '—')
              };
            });
            rondas.push({
              ronda: entero(carrera.round),
              nombre: carrera.raceName || ('Ronda ' + carrera.round),
              fecha: carrera.date,
              circuito: (carrera.Circuit && carrera.Circuit.circuitName) || '',
              puntos: mapa
            });
            hechasOk++;
          }
          if (alProgreso) alProgreso(hechasOk, hechas.length);
          return trabajador();
        });
      }

      var trabajadores = [];
      var CONCURRENCIA = 4;
      for (var i = 0; i < CONCURRENCIA; i++) trabajadores.push(trabajador());

      return Promise.all(trabajadores).then(function () {
        rondas.sort(function (a, b) { return a.ronda - b.ronda; });
        estado.evolucion = { rondas: rondas, totalPruebas: races.length, hechas: hechas.length };
        return estado.evolucion;
      });
    });

    return estado.promesas.evolucion;
  }

  var seleccionEvolucion = [];

  function pintarChipsEvolucion(ev) {
    var cont = document.getElementById('evol-pilotos');
    var badge = document.getElementById('evol-badge');
    if (!cont) return;

    // Pilotos con presencia en alguna ronda (incluye a quien puntuó pronto).
    var vistos = {};
    ev.rondas.forEach(function (r) {
      Object.keys(r.puntos).forEach(function (id) { vistos[id] = true; });
    });
    var lista = estado.pilotos.filter(function (p) { return vistos[p.id] || true; });

    if (!lista.length) {
      cont.innerHTML = '<span class="tf-mute">Sin datos disponibles.</span>';
      return;
    }

    // Por defecto, los cinco primeros del campeonato.
    seleccionEvolucion = lista.slice(0, Math.min(MAX_PILOTOS_EVOLUCION, lista.length)).map(function (p) { return p.id; });

    var fragmento = document.createDocumentFragment();
    lista.forEach(function (p) {
      var b = document.createElement('button');
      b.type = 'button';
      b.className = 'chip';
      b.dataset.piloto = p.id;
      b.setAttribute('aria-pressed', 'true');
      b.innerHTML = '<span class="chip__punto" style="background:' + colorDePiloto(p.id) + '"></span>' +
        esc(p.nombre) + '<span class="chip__puntos">' + num(p.puntos) + '</span>';
      b.addEventListener('click', function () { alternarPilotoEvolucion(p.id, b); });
      fragmento.appendChild(b);
    });
    cont.innerHTML = '';
    cont.appendChild(fragmento);
    actualizarChipsEvolucion();

    if (badge) {
      badge.textContent = ev.rondas.length
        ? ev.rondas.length + ' de ' + (ev.totalPruebas || ev.rondas.length) + ' pruebas'
        : 'Sin datos';
    }
  }

  function alternarPilotoEvolucion(id, boton) {
    var i = seleccionEvolucion.indexOf(id);
    if (i >= 0) {
      if (seleccionEvolucion.length === 1) return;   // siempre al menos uno
      seleccionEvolucion.splice(i, 1);
    } else {
      if (seleccionEvolucion.length >= MAX_PILOTOS_EVOLUCION) return;
      seleccionEvolucion.push(id);
    }
    actualizarChipsEvolucion();
    pintarGraficoEvolucion();
  }

  function actualizarChipsEvolucion() {
    var cont = document.getElementById('evol-pilotos');
    if (!cont) return;
    Array.prototype.forEach.call(cont.querySelectorAll('.chip'), function (b) {
      var activo = seleccionEvolucion.indexOf(b.dataset.piloto) >= 0;
      b.classList.toggle('is-active', activo);
      b.setAttribute('aria-pressed', activo ? 'true' : 'false');
      b.disabled = !activo && seleccionEvolucion.length >= MAX_PILOTOS_EVOLUCION;
    });
    var etiqueta = document.getElementById('evol-etiqueta');
    if (etiqueta) {
      etiqueta.textContent = 'Elige hasta ' + MAX_PILOTOS_EVOLUCION + ' pilotos (' +
        seleccionEvolucion.length + '/' + MAX_PILOTOS_EVOLUCION + ')';
    }
  }

  function colorDePiloto(id) {
    var paleta = paletaSeries();
    var indice = seleccionEvolucion.indexOf(id);
    if (indice >= 0) return paleta[indice % paleta.length];
    // Para los chips no seleccionados, un color estable por posición en la tabla.
    var i = estado.pilotos.findIndex(function (p) { return p.id === id; });
    return paleta[(i >= 0 ? i : 0) % paleta.length];
  }

  function seriesEvolucion() {
    var ev = estado.evolucion;
    if (!ev || !ev.rondas.length) return [];
    var paleta = paletaSeries();
    return seleccionEvolucion.map(function (id, i) {
      var piloto = estado.porId[id] || { nombre: id };
      return {
        id: id,
        piloto: piloto,
        color: paleta[i % paleta.length],
        datos: ev.rondas.map(function (r) {
          var v = r.puntos[id];
          return { puntos: v ? v.puntos : 0, posicion: v ? v.posicion : '—', ronda: r };
        })
      };
    });
  }

  function pintarGraficoEvolucion() {
    var ev = estado.evolucion;
    var contenedor = document.getElementById('fb-evolucion');
    var canvas = document.getElementById('chart-evolucion');
    if (!ev || !ev.rondas.length) {
      if (canvas) canvas.style.display = 'none';
      if (contenedor) {
        contenedor.hidden = false;
        contenedor.innerHTML = '<div class="tf-empty">Sin datos disponibles para esta temporada: no hay pruebas terminadas.</div>';
      }
      return;
    }

    var series = seriesEvolucion();
    if (!series.length) {
      if (canvas) canvas.style.display = 'none';
      if (contenedor) {
        contenedor.hidden = false;
        contenedor.innerHTML = '<div class="tf-empty">Sin datos disponibles: no hay pilotos que comparar en esta temporada.</div>';
      }
      return;
    }

    if (!hayChart()) {
      if (canvas) canvas.style.display = 'none';
      if (contenedor) {
        contenedor.hidden = false;
        contenedor.innerHTML = '<p class="chart-fallback__nota">Gráfico no disponible: se muestran los datos en tabla.</p>' +
          '<div class="tf-table-wrap"><table class="tf-table"><thead><tr><th>Prueba</th>' +
          series.map(function (s) { return '<th class="tf-td-right">' + esc(s.piloto.nombre) + '</th>'; }).join('') +
          '</tr></thead><tbody>' +
          ev.rondas.map(function (r, i) {
            return '<tr><td>' + esc('R' + r.ronda + ' · ' + r.nombre) + '</td>' +
              series.map(function (s) {
                return '<td class="tf-td-right tf-td-num">' + esc(num(s.datos[i].puntos)) + '</td>';
              }).join('') + '</tr>';
          }).join('') + '</tbody></table></div>';
      }
      return;
    }

    if (contenedor) contenedor.hidden = true;
    if (canvas) canvas.style.display = '';

    var t = variablesTema();
    var etiquetas = ev.rondas.map(function (r) { return 'R' + r.ronda; });

    if (window.__evolChart) {
      window.__evolChart.destroy();
      window.__evolChart = null;
    }

    window.__evolChart = new Chart(canvas, {
      type: 'line',
      data: {
        labels: etiquetas,
        datasets: series.map(function (s) {
          return {
            label: s.piloto.nombre,
            data: s.datos.map(function (d) { return d.puntos; }),
            borderColor: s.color,
            backgroundColor: s.color,
            borderWidth: 2,
            tension: 0.25,
            pointRadius: 2,
            pointHoverRadius: 5
          };
        })
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: animacion(),
        interaction: { mode: 'index', intersect: false },
        plugins: {
          legend: { display: true, labels: { color: t.dim, usePointStyle: true, boxWidth: 8 } },
          tooltip: {
            callbacks: {
              title: function (items) {
                var r = ev.rondas[items[0].dataIndex];
                return r ? ('R' + r.ronda + ' · ' + r.nombre + ' (' + r.circuito + ')') : '';
              },
              label: function (ctx) {
                var s = series[ctx.datasetIndex];
                var d = s.datos[ctx.dataIndex];
                return s.piloto.nombre + ': ' + num(d.puntos) + ' pts · P' + d.posicion;
              }
            }
          }
        },
        scales: {
          x: { grid: { display: false }, ticks: { color: t.dim } },
          y: {
            beginAtZero: true,
            grid: { color: 'rgba(255,255,255,0.07)' },
            ticks: { color: t.dim, precision: 0 }
          }
        }
      }
    });
  }

  /* =========================== 3. COMPARADOR =========================== */
  var cmpA = null;
  var cmpB = null;

  function metricasDe(id) {
    var p = estado.porId[id] || {};
    var r = (estado.resultados && estado.resultados.porPiloto && estado.resultados.porPiloto[id]) || {};
    var q = estado.quali && estado.quali.porPiloto ? estado.quali.porPiloto[id] : null;
    var hayVr = !!(estado.resultados && estado.resultados.hayVueltasRapidas);
    return {
      puntos: p.puntos,
      victorias: p.victorias !== undefined ? p.victorias : (r.victorias || 0),
      podios: r.podios !== undefined ? r.podios : null,
      poles: q ? (q.poles || 0) : null,
      carreras: r.carreras !== undefined ? r.carreras : null,
      vueltasRapidas: hayVr ? (r.vr || 0) : null,
      mejor: r.mejor !== undefined ? r.mejor : null
    };
  }

  function prepararComparador() {
    var selA = document.getElementById('cmp-a');
    var selB = document.getElementById('cmp-b');
    var badge = document.getElementById('cmp-badge');
    if (!selA || !selB) return;

    if (!estado.pilotos.length) {
      var cuerpo = document.getElementById('cmp-cuerpo');
      if (cuerpo) cuerpo.innerHTML = '<div class="tf-empty">Sin datos disponibles para la temporada ' + esc(year) + '.</div>';
      selA.disabled = true;
      selB.disabled = true;
      if (badge) badge.textContent = 'Sin datos';
      return;
    }

    var opciones = estado.pilotos.map(function (p) {
      return { id: p.id, texto: p.nombre + (p.equipo ? ' · ' + p.equipo : '') };
    });

    [selA, selB].forEach(function (sel) {
      sel.innerHTML = '';
      opciones.forEach(function (o) {
        var opt = document.createElement('option');
        opt.value = o.id;
        opt.textContent = o.texto;
        sel.appendChild(opt);
      });
    });

    cmpA = estado.pilotos[0] ? estado.pilotos[0].id : null;
    cmpB = estado.pilotos[1] ? estado.pilotos[1].id : (cmpA);
    selA.value = cmpA;
    if (cmpB) selB.value = cmpB;
    if (badge) badge.textContent = opciones.length + ' pilotos';

    selA.onchange = function () { cmpA = selA.value; refrescarComparador(); };
    selB.onchange = function () { cmpB = selB.value; refrescarComparador(); };

    var swap = document.getElementById('cmp-swap');
    if (swap) {
      swap.onclick = function () {
        var tmp = cmpA; cmpA = cmpB; cmpB = tmp;
        selA.value = cmpA;
        if (cmpB) selB.value = cmpB;
        refrescarComparador();
      };
    }

    Promise.all([asegurarResultados(), asegurarClasificacion()]).then(refrescarComparador);
  }

  var METRICAS = [
    { clave: 'puntos', etiqueta: 'Puntos', mayor: true, formato: num },
    { clave: 'victorias', etiqueta: 'Victorias', mayor: true },
    { clave: 'podios', etiqueta: 'Podios', mayor: true },
    { clave: 'poles', etiqueta: 'Poles', mayor: true },
    { clave: 'carreras', etiqueta: 'Carreras', mayor: true },
    { clave: 'vueltasRapidas', etiqueta: 'Vueltas rápidas', mayor: true },
    { clave: 'mejor', etiqueta: 'Mejor resultado', mayor: false }
  ];

  function refrescarComparador() {
    var cuerpo = document.getElementById('cmp-cuerpo');
    if (!cuerpo || !cmpA || !cmpB) return;

    var pa = estado.porId[cmpA];
    var pb = estado.porId[cmpB];
    if (!pa || !pb) return;

    var ma = metricasDe(cmpA);
    var mb = metricasDe(cmpB);
    var paleta = paletaSeries();
    var colorA = paleta[0];
    var colorB = paleta[1];

    var ganadasA = 0;
    var ganadasB = 0;
    var filas = '';

    METRICAS.forEach(function (m) {
      var va = ma[m.clave];
      var vb = mb[m.clave];
      var disponible = va !== null && va !== undefined && vb !== null && vb !== undefined;

      if (!disponible) {
        filas +=
          '<div class="cmp-fila is-vacia">' +
            '<div class="cmp-fila__valor">—</div>' +
            '<div class="cmp-fila__centro"><div class="cmp-fila__metrica">' + esc(m.etiqueta) + '</div>' +
            '<div class="cmp-fila__nota">Sin datos disponibles</div></div>' +
            '<div class="cmp-fila__valor">—</div>' +
          '</div>';
        return;
      }

      var max = Math.max(va, vb, 1);
      var pa100 = Math.max(2, Math.round((va / max) * 100));
      var pb100 = Math.max(2, Math.round((vb / max) * 100));
      var ganaA = m.mayor ? va > vb : va < vb;
      var ganaB = m.mayor ? vb > va : vb < va;
      if (ganaA) ganadasA++;
      if (ganaB) ganadasB++;

      var formato = m.formato || entero;
      filas +=
        '<div class="cmp-fila">' +
          '<div class="cmp-fila__valor' + (ganaA ? ' is-mejor' : '') + '">' + esc(formato(va)) + '</div>' +
          '<div class="cmp-fila__centro">' +
            '<div class="cmp-fila__metrica">' + esc(m.etiqueta) + '</div>' +
            '<div class="cmp-fila__barras">' +
              '<span class="cmp-barra" style="width:' + pa100 + '%;background:' + colorA + '"></span>' +
              '<span class="cmp-barra" style="width:' + pb100 + '%;background:' + colorB + '"></span>' +
            '</div>' +
          '</div>' +
          '<div class="cmp-fila__valor' + (ganaB ? ' is-mejor' : '') + '">' + esc(formato(vb)) + '</div>' +
        '</div>';
    });

    var resumen;
    if (ganadasA === ganadasB) resumen = 'Empate en métricas';
    else resumen = (ganadasA > ganadasB ? pa.nombre : pb.nombre) + ' gana en ' + Math.max(ganadasA, ganadasB) + ' métricas';

    cuerpo.innerHTML =
      '<div class="cmp-cabecera">' +
        '<div class="cmp-cabecera__lado">' +
          '<span class="cmp-cabecera__punto" style="background:' + colorA + '"></span>' +
          '<b>' + esc(pa.nombre) + '</b><small>' + esc(pa.equipo || '—') + '</small>' +
        '</div>' +
        '<div class="cmp-cabecera__centro">' + esc(resumen) + '</div>' +
        '<div class="cmp-cabecera__lado cmp-cabecera__lado--b">' +
          '<span class="cmp-cabecera__punto" style="background:' + colorB + '"></span>' +
          '<b>' + esc(pb.nombre) + '</b><small>' + esc(pb.equipo || '—') + '</small>' +
        '</div>' +
      '</div>' +
      '<div class="cmp-tabla">' + filas + '</div>' +
      '<p class="cmp-nota">Datos oficiales de la temporada ' + esc(year) + '. ' +
        (estado.quali ? '' : 'Esta temporada no tiene datos de clasificación (desde 1994). ') +
        (estado.resultados && estado.resultados.hayVueltasRapidas ? '' : 'La API no publica vueltas rápidas de esta temporada. ') +
      '</p>';
  }

  /* ============================== PESTAÑAS ============================== */
  var cargado = {};

  function mostrarPanel(nombre) {
    ['resumen', 'evolucion', 'comparador'].forEach(function (n) {
      var panel = document.getElementById('panel-' + n);
      var tab = document.getElementById('tab-' + n);
      var activo = n === nombre;
      if (panel) panel.hidden = !activo;
      if (tab) {
        tab.classList.toggle('is-active', activo);
        tab.setAttribute('aria-selected', activo ? 'true' : 'false');
        tab.tabIndex = activo ? 0 : -1;
      }
    });
  }

  function activar(nombre) {
    mostrarPanel(nombre);

    if (nombre === 'resumen' && !cargado.resumen) {
      cargado.resumen = true;
      asegurarResumen().then(function () {
        asegurarResultados().then(pintarGraficosResumen);
      });
    }

    if (nombre === 'evolucion' && !cargado.evolucion) {
      cargado.evolucion = true;
      asegurarResumen().then(function () {
        asegurarEvolucion(function (ok, total) {
          var badge = document.getElementById('evol-badge');
          if (badge) badge.textContent = ok + '/' + total + ' pruebas';
        }).then(function (ev) {
          pintarChipsEvolucion(ev);
          pintarGraficoEvolucion();
        });
      });
    }

    if (nombre === 'comparador' && !cargado.comparador) {
      cargado.comparador = true;
      asegurarResumen().then(prepararComparador);
    }
  }

  function iniciarPestanas() {
    var tabs = Array.prototype.slice.call(document.querySelectorAll('.tabs__tab'));
    tabs.forEach(function (tab, i) {
      tab.addEventListener('click', function () {
        activar(tab.id.replace('tab-', ''));
      });
      tab.addEventListener('keydown', function (e) {
        var salto = 0;
        if (e.key === 'ArrowRight') salto = 1;
        else if (e.key === 'ArrowLeft') salto = -1;
        else if (e.key === 'Home') salto = -tabs.length;
        else if (e.key === 'End') salto = tabs.length;
        if (!salto) return;
        e.preventDefault();
        var destino = tabs[(i + salto + tabs.length) % tabs.length];
        destino.focus();
        activar(destino.id.replace('tab-', ''));
      });
    });
  }

  /* ============================== ARRANQUE ============================== */
  var yearLabel = document.getElementById('year-label');
  if (yearLabel) yearLabel.textContent = year;
  var volver = document.getElementById('btn-volver');
  if (volver) volver.href = '/?year=' + year;

  var limpiar = document.getElementById('evol-limpiar');
  if (limpiar) {
    limpiar.addEventListener('click', function () {
      var top = estado.pilotos.slice(0, Math.min(MAX_PILOTOS_EVOLUCION, estado.pilotos.length)).map(function (p) { return p.id; });
      seleccionEvolucion = top.length ? top : seleccionEvolucion.slice(0, 1);
      actualizarChipsEvolucion();
      pintarGraficoEvolucion();
    });
  }

  iniciarPestanas();
  activar('resumen');

  /* El tema de la navbar cambia sin recargar: se observan los atributos del
     <html> (data-team y las variables en style) y se repintan los gráficos,
     los chips y el comparador con los colores nuevos. */
  function recolorear() {
    var t = variablesTema();
    var paleta = paletaSeries();

    ['chart-puntos', 'chart-victorias'].forEach(function (id) {
      var c = window.Chart && window.Chart.getChart ? window.Chart.getChart(id) : null;
      if (!c) return;
      c.data.datasets.forEach(function (ds, i) {
        ds.backgroundColor = i === 0 ? paleta[0] : t.dim;
      });
      Object.keys(c.options.scales || {}).forEach(function (k) {
        if (c.options.scales[k].ticks) c.options.scales[k].ticks.color = t.dim;
        if (c.options.scales[k].grid && c.options.scales[k].grid.color) {
          c.options.scales[k].grid.color = 'rgba(255,255,255,0.07)';
        }
      });
      if (c.options.plugins && c.options.plugins.legend && c.options.plugins.legend.labels) {
        c.options.plugins.legend.labels.color = t.dim;
      }
      c.update('none');
    });

    // Chips de evolución (llevan color en línea) y gráfico de líneas.
    var cont = document.getElementById('evol-pilotos');
    if (cont) {
      Array.prototype.forEach.call(cont.querySelectorAll('.chip'), function (b) {
        var punto = b.querySelector('.chip__punto');
        if (punto) punto.style.background = colorDePiloto(b.dataset.piloto);
      });
    }
    if (estado.evolucion && document.getElementById('panel-evolucion') &&
        !document.getElementById('panel-evolucion').hidden) {
      pintarGraficoEvolucion();
    }
    if (cmpA && cmpB) refrescarComparador();
  }

  (function observarTema() {
    if (!window.MutationObserver) return;
    var pendiente = null;
    var observador = new MutationObserver(function () {
      if (pendiente) window.clearTimeout(pendiente);
      pendiente = window.setTimeout(recolorear, 120);
    });
    observador.observe(document.documentElement, { attributes: true, attributeFilter: ['style', 'data-team'] });
  })();
})();
