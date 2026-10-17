/* ============================================================
   TodoF1 — Horarios de las sesiones de un Gran Premio
   ------------------------------------------------------------
   Datos: los que devuelve Jolpica en el propio calendario
   (/{año}.json y /{año}/races.json). No se inventa ningún horario
   ni duración: si un fin de semana Sprint no trae la Clasificación
   Sprint, se muestra «Por confirmar».

   Sesiones que publica la API (comprobado con datos reales):
     · Fin de semana normal: FirstPractice, SecondPractice,
       ThirdPractice, Qualifying y la carrera (date/time).
     · Fin de semana Sprint: FirstPractice, SprintQualifying,
       Sprint, Qualifying y la carrera (date/time).

   Todas las horas se muestran en hora peninsular española
   (Europe/Madrid), calculada con Intl: el cambio de horario de
   verano se aplica solo, sin cálculos manuales.

   Sobre el estado «en curso»: la API solo da la hora de inicio, no
   la duración. Para no inventar datos, el estado se decide con la
   hora real de inicio y un límite superior orientativo (el máximo
   habitual del reglamento). Nunca se muestra esa duración al
   usuario: solo sirve para no dejar una sesión «en curso» para
   siempre.
   ============================================================ */
(function () {
  'use strict';

  var ZONA = 'Europe/Madrid';

  /* Orden real de un fin de semana (la carrera siempre la última). */
  var ORDEN = ['FirstPractice', 'SprintQualifying', 'SecondPractice', 'ThirdPractice', 'Sprint', 'Qualifying', 'Race'];

  var NOMBRES = {
    FirstPractice: 'Libres 1',
    SecondPractice: 'Libres 2',
    ThirdPractice: 'Libres 3',
    Qualifying: 'Clasificación',
    SprintQualifying: 'Clasificación Sprint',
    Sprint: 'Sprint',
    Race: 'Carrera'
  };

  var CORTOS = {
    FirstPractice: 'FP1',
    SecondPractice: 'FP2',
    ThirdPractice: 'FP3',
    Qualifying: 'Clasificación',
    SprintQualifying: 'Cl. Sprint',
    Sprint: 'Sprint',
    Race: 'Carrera'
  };

  /* Límite superior orientativo en minutos (nunca se muestra). */
  var LIMITE = {
    FirstPractice: 75,
    SecondPractice: 75,
    ThirdPractice: 75,
    Qualifying: 90,
    SprintQualifying: 60,
    Sprint: 75,
    Race: 180
  };

  var formatoDiaClave = null;
  var formatoDiaLargo = null;
  var formatoHora = null;
  var formatoFechaCorta = null;

  function formateadores() {
    if (formatoDiaClave) return;
    try {
      formatoDiaClave = new Intl.DateTimeFormat('en-CA', {
        timeZone: ZONA, year: 'numeric', month: '2-digit', day: '2-digit'
      });
      formatoDiaLargo = new Intl.DateTimeFormat('es-ES', {
        timeZone: ZONA, weekday: 'long', day: 'numeric', month: 'long'
      });
      formatoHora = new Intl.DateTimeFormat('es-ES', {
        timeZone: ZONA, hour: '2-digit', minute: '2-digit', hour12: false
      });
      formatoFechaCorta = new Intl.DateTimeFormat('es-ES', {
        timeZone: ZONA, weekday: 'short', day: 'numeric', month: 'short'
      });
    } catch (e) {
      formatoDiaClave = null;
    }
  }

  /* --------------------------- Fechas --------------------------- */
  /* Convierte date + time de Jolpica en una fecha absoluta (UTC). */
  function instante(fecha, hora) {
    if (!fecha) return null;
    var texto = String(fecha);
    if (!hora) return null;
    var h = String(hora);
    if (!/Z$|[+-]\d{2}:?\d{2}$/.test(h)) h += 'Z';
    var d = new Date(texto + 'T' + h);
    return isNaN(d.getTime()) ? null : d;
  }

  function esTemporadaActual(year) {
    return String(parseInt(year, 10)) === String(new Date().getFullYear());
  }

  function añoActual() {
    return new Date().getFullYear();
  }

  /* Clave de día natural en Madrid (YYYY-MM-DD). */
  function diaClave(fecha) {
    formateadores();
    if (!fecha) return '';
    if (!formatoDiaClave) return fecha.toISOString().slice(0, 10);
    return formatoDiaClave.format(fecha); // en-CA => YYYY-MM-DD
  }

  function diaLargo(fecha) {
    formateadores();
    if (!fecha) return '';
    if (!formatoDiaLargo) return '';
    return formatoDiaLargo.format(fecha);
  }

  function hora(fecha) {
    formateadores();
    if (!fecha) return '';
    if (!formatoHora) return '';
    return formatoHora.format(fecha).replace(/^24:/, '00:');
  }

  function fechaCorta(fecha) {
    formateadores();
    if (!fecha) return '';
    if (!formatoFechaCorta) return '';
    return formatoFechaCorta.format(fecha);
  }

  /* ------------------------ Sesiones del GP ---------------------- */
  function esSprint(carrera) {
    return !!(carrera && carrera.Sprint);
  }

  /* Programa esperado según el formato del fin de semana. */
  function esqueleto(carrera) {
    if (esSprint(carrera)) {
      return ['FirstPractice', 'SprintQualifying', 'Sprint', 'Qualifying', 'Race'];
    }
    return ['FirstPractice', 'SecondPractice', 'ThirdPractice', 'Qualifying', 'Race'];
  }

  /* Lista de sesiones con su hora real (o «por confirmar»). */
  function sesionesDe(carrera) {
    if (!carrera) return [];
    var lista = [];
    var claves = [];

    // Se respeta el formato real: el esqueleto solo completa huecos del
    // mismo formato (nunca añade sesiones que ese fin de semana no tiene).
    esqueleto(carrera).forEach(function (clave) { claves.push(clave); });

    // Sesiones extra que traiga la API y no estén en el esqueleto.
    ORDEN.forEach(function (clave) {
      if (carrera[clave] && claves.indexOf(clave) === -1) claves.push(clave);
    });

    claves.forEach(function (clave) {
      var datos = (clave === 'Race') ? { date: carrera.date, time: carrera.time } : carrera[clave];
      if (!datos || !datos.date) {
        // Sesión propia del formato sin datos en la API
        lista.push({
          clave: clave,
          nombre: NOMBRES[clave] || clave,
          corto: CORTOS[clave] || clave,
          inicio: null,
          fecha: '',
          horaConocida: false,
          porConfirmar: true
        });
        return;
      }
      var inicio = instante(datos.date, datos.time);
      lista.push({
        clave: clave,
        nombre: NOMBRES[clave] || clave,
        corto: CORTOS[clave] || clave,
        inicio: inicio,
        fecha: datos.date,
        horaConocida: !!inicio,
        porConfirmar: !inicio
      });
    });

    // Orden: por hora real; las que no la tienen van al final.
    lista.sort(function (a, b) {
      var oa = ORDEN.indexOf(a.clave);
      var ob = ORDEN.indexOf(b.clave);
      if (a.inicio && b.inicio) return a.inicio - b.inicio;
      if (a.inicio && !b.inicio) return -1;
      if (!a.inicio && b.inicio) return 1;
      return oa - ob;
    });

    return lista;
  }

  /* ----------------------------- Estado ----------------------------- */
  function estado(sesion, ahora) {
    var ref = ahora ? ahora.getTime() : Date.now();
    if (!sesion || !sesion.inicio) return 'por-confirmar';
    var inicio = sesion.inicio.getTime();
    if (ref < inicio) return 'pendiente';
    var limite = (LIMITE[sesion.clave] || 90) * 60000;
    if (ref < inicio + limite) return 'en-curso';
    return 'finalizada';
  }

  function etiquetaEstado(estadoSesion) {
    if (estadoSesion === 'en-curso') return 'En curso';
    if (estadoSesion === 'finalizada') return 'Finalizada';
    if (estadoSesion === 'por-confirmar') return 'Por confirmar';
    return 'Pendiente';
  }

  /* Sesión en curso (si la hay) o la siguiente pendiente. */
  function destacada(sesiones, ahora) {
    var ref = ahora || new Date();
    var enCurso = null;
    var siguiente = null;
    for (var i = 0; i < sesiones.length; i++) {
      var e = estado(sesiones[i], ref);
      if (e === 'en-curso' && !enCurso) enCurso = sesiones[i];
      if (e === 'pendiente' && !siguiente) siguiente = sesiones[i];
    }
    return enCurso || siguiente || null;
  }

  function hayPendientes(sesiones, ahora) {
    var ref = ahora || new Date();
    for (var i = 0; i < sesiones.length; i++) {
      var e = estado(sesiones[i], ref);
      if (e === 'pendiente' || e === 'en-curso') return true;
    }
    return false;
  }

  /* Agrupa por día natural en Madrid. */
  function agruparPorDia(sesiones) {
    var dias = [];
    var indice = {};
    sesiones.forEach(function (sesion) {
      var clave = sesion.inicio ? diaClave(sesion.inicio) : (sesion.fecha || 'sin-fecha');
      if (!indice[clave]) {
        indice[clave] = {
          clave: clave,
          etiqueta: sesion.inicio ? diaLargo(sesion.inicio) : 'Por confirmar',
          sesiones: []
        };
        dias.push(indice[clave]);
      }
      indice[clave].sesiones.push(sesion);
    });
    dias.sort(function (a, b) {
      if (a.clave === 'sin-fecha') return 1;
      if (b.clave === 'sin-fecha') return -1;
      return a.clave < b.clave ? -1 : (a.clave > b.clave ? 1 : 0);
    });
    return dias;
  }

  /* --------------------------- Cuenta atrás --------------------------- */
  function cuentaAtras(ms) {
    if (ms <= 0) return 'ahora';
    var seg = Math.floor(ms / 1000);
    var dias = Math.floor(seg / 86400);
    var horas = Math.floor((seg % 86400) / 3600);
    var min = Math.floor((seg % 3600) / 60);
    var s = seg % 60;
    if (dias > 0) return 'en ' + dias + ' d ' + horas + ' h';
    if (horas > 0) return 'en ' + horas + ' h ' + min + ' min';
    if (min > 0) return 'en ' + min + ' min ' + (s < 10 ? '0' + s : s) + ' s';
    return 'en ' + s + ' s';
  }

  window.TodoF1Horarios = {
    zona: ZONA,
    añoActual: añoActual,
    esTemporadaActual: esTemporadaActual,
    esSprint: esSprint,
    sesionesDe: sesionesDe,
    estado: estado,
    etiquetaEstado: etiquetaEstado,
    destacada: destacada,
    hayPendientes: hayPendientes,
    agruparPorDia: agruparPorDia,
    cuentaAtras: cuentaAtras,
    diaClave: diaClave,
    diaLargo: diaLargo,
    hora: hora,
    fechaCorta: fechaCorta,
    nombre: function (clave) { return NOMBRES[clave] || clave; },
    corto: function (clave) { return CORTOS[clave] || clave; }
  };
})();
