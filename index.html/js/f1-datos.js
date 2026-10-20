/* ============================================================
   TodoF1 — Capa de disponibilidad de datos (respaldo ante caídas)
   ------------------------------------------------------------
   Qué hace:
     · Redirige las peticiones a la API de datos (Jolpica) hacia el
       proxy propio /api/f1.php, que mantiene una copia persistente
       en el servidor y sirve esa copia cuando Jolpica no responde.
     · Si el proxy no está disponible (no desplegado, red o HTML),
       usa la URL original directamente: el comportamiento es el de
       siempre.
     · Añade un tiempo máximo a TODAS las peticiones de datos para
       que ninguna página se quede cargando indefinidamente.
     · Muestra un aviso discreto y accesible solo cuando los datos
       servidos son de respaldo (no cuando están vigentes).

   No modifica los datos ni los cálculos: solo el transporte.
   Las respuestas del proxy son el JSON original de Jolpica.
   ============================================================ */
(function () {
  'use strict';

  var ORIGIN = 'https://api.jolpi.ca/ergast/f1';
  var PROXY = '/api/f1.php';
  var TIMEOUT = 20000;          // tiempo máximo por petición (ms); cubre el single-flight del proxy
  var originalFetch = window.fetch ? window.fetch.bind(window) : null;

  if (!originalFetch) {
    return;                     // sin fetch no hay nada que hacer
  }

  var proxyBroken = false;      // el proxy no existe o no responde
  var dismissed = false;        // el usuario cerró el aviso
  var lastSeen = '';            // último modo informado (diagnóstico)
  var sawRespaldo = false;      // alguna consulta se sirvió de la copia guardada
  var sawSinCopia = false;      // alguna consulta no tenía copia disponible
  var lastSaved = '';           // fecha de guardado del respaldo (ISO)
  var notice = null;
  var inflight = Object.create(null);   // peticiones idénticas simultáneas

  /* -------------------------------------------------------- utilidades */
  function isApiUrl(url) {
    return typeof url === 'string' && url.indexOf(ORIGIN) === 0;
  }

  /* https://api.jolpi.ca/ergast/f1/2026/results.json?limit=100&offset=0
     ->  /api/f1.php?ruta=2026/results.json&limit=100&offset=0          */
  function toProxy(url) {
    var rest = url.slice(ORIGIN.length);
    var query = '';
    var cut = rest.indexOf('?');
    if (cut !== -1) {
      query = rest.slice(cut + 1);
      rest = rest.slice(0, cut);
    }
    var params = new URLSearchParams(query);
    var out = PROXY + '?ruta=' + encodeURIComponent(rest.replace(/^\/+/, ''));
    ['limit', 'offset'].forEach(function (name) {
      if (params.has(name)) {
        out += '&' + name + '=' + encodeURIComponent(params.get(name));
      }
    });
    return out;
  }

  function withTimeout(input, init) {
    var controller = typeof AbortController === 'function' ? new AbortController() : null;
    if (!controller) {
      return originalFetch(input, init);
    }
    var options = {};
    if (init) {
      for (var key in init) {
        if (Object.prototype.hasOwnProperty.call(init, key)) {
          options[key] = init[key];
        }
      }
    }
    // Si quien llama ya usaba AbortController, su cancelación se respeta:
    // se propaga a nuestro controlador en lugar de sustituirla.
    if (init && init.signal) {
      var externa = init.signal;
      if (externa.aborted) {
        controller.abort();
      } else if (typeof externa.addEventListener === 'function') {
        externa.addEventListener('abort', function () { controller.abort(); });
      }
    }
    options.signal = controller.signal;
    var timer = window.setTimeout(function () { controller.abort(); }, TIMEOUT);
    return originalFetch(input, options).then(function (response) {
      window.clearTimeout(timer);
      return response;
    }, function (error) {
      window.clearTimeout(timer);
      throw error;
    });
  }

  /* ------------------------------------------------------------ aviso */
  function formatDate(iso) {
    var date = new Date(iso);
    if (isNaN(date.getTime())) return '';
    try {
      return date.toLocaleString('es-ES', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
      });
    } catch (e) {
      return date.toISOString();
    }
  }

  function ensureNotice() {
    if (notice && notice.isConnected) return notice;

    var host = document.querySelector('.tf-main > .tf-container') ||
      document.querySelector('.tf-main') ||
      document.querySelector('main') ||
      document.body;

    notice = document.createElement('div');
    notice.id = 'tf-datos-aviso';
    notice.setAttribute('role', 'status');
    notice.setAttribute('aria-live', 'polite');
    notice.style.cssText = [
      'display:flex', 'align-items:flex-start', 'gap:10px',
      'margin:0 0 var(--sp-4, 16px)',
      'padding:10px 12px',
      'border:1px solid var(--c-line, #3A3B41)',
      'border-left:3px solid var(--team-primary, #E10600)',
      'border-radius:var(--r-md, 12px)',
      'background:var(--c-surface, #2C2D31)',
      'color:var(--c-dim, #AEB4BF)',
      'font-size:0.82rem', 'line-height:1.5'
    ].join(';');

    var text = document.createElement('span');
    text.setAttribute('data-aviso-texto', '');
    text.style.cssText = 'flex:1 1 auto;min-width:0';
    notice.appendChild(text);

    var retry = document.createElement('button');
    retry.type = 'button';
    retry.textContent = 'Reintentar';
    retry.style.cssText = [
      'flex:0 0 auto', 'cursor:pointer',
      'padding:3px 10px', 'border-radius:999px',
      'border:1px solid var(--team-primary-line, rgba(225,6,0,.38))',
      'background:transparent', 'color:var(--c-text, #F5F5F5)',
      'font:inherit', 'font-size:0.78rem', 'font-weight:600'
    ].join(';');
    retry.addEventListener('click', function () { window.location.reload(); });
    notice.appendChild(retry);

    var close = document.createElement('button');
    close.type = 'button';
    close.setAttribute('aria-label', 'Cerrar aviso');
    close.textContent = '\u00d7';
    close.style.cssText = [
      'flex:0 0 auto', 'cursor:pointer', 'border:0', 'background:transparent',
      'color:var(--c-mute, #7E858F)', 'font-size:1.05rem', 'line-height:1',
      'padding:0 2px'
    ].join(';');
    close.addEventListener('click', function () {
      dismissed = true;
      if (notice && notice.parentNode) notice.parentNode.removeChild(notice);
      notice = null;
    });
    notice.appendChild(close);

    host.insertBefore(notice, host.firstChild);
    return notice;
  }

  function updateNotice() {
    if (!sawRespaldo && !sawSinCopia) {
      if (notice && notice.parentNode) {
        notice.parentNode.removeChild(notice);
        notice = null;
      }
      return;
    }
    if (dismissed) return;

    var el = ensureNotice();
    var text = el.querySelector('[data-aviso-texto]');
    if (!text) return;

    var when = lastSaved ? formatDate(lastSaved) : '';

    // Puede coincidir una parte servida desde copia y otra sin copia: se
    // informa de ambas para no dar a entender que todo está respaldado.
    if (sawRespaldo && sawSinCopia) {
      text.textContent = 'Jolpica no está disponible temporalmente. Mostrando los últimos datos guardados' +
        (when ? ' (actualizados el ' + when + ')' : '') +
        ' para algunas consultas; otras todavía no tienen copia guardada.';
      return;
    }
    if (sawRespaldo) {
      text.textContent = 'Jolpica no está disponible temporalmente. Mostrando los últimos datos guardados' +
        (when ? ' (actualizados el ' + when + ')' : '') + '.';
      return;
    }

    text.textContent = 'Jolpica no está disponible temporalmente y todavía no hay datos guardados de esta consulta.';
  }

  /* Modo que informa el proxy ('' si no hay cabecera). */
  function headerMode(response) {
    try {
      return response.headers ? (response.headers.get('X-TodoF1-Datos') || '') : '';
    } catch (e) {
      return '';
    }
  }

  /* Registro del estado que informa el proxy. El aviso solo depende de los
     modos que indican que los datos NO son los últimos disponibles: 'live'
     (al día) lo retira; 'respaldo' y 'sin-copia' lo muestran. */
  function record(response, mode) {
    if (!mode) {
      return;
    }
    lastSeen = mode;

    if (mode === 'live') {
      sawRespaldo = false;
      sawSinCopia = false;
      lastSaved = '';
      updateNotice();
      return;
    }
    if (mode === 'respaldo') {
      sawRespaldo = true;
      try {
        lastSaved = response.headers.get('X-TodoF1-Guardado') || '';
      } catch (e) {
        lastSaved = '';
      }
      updateNotice();
      return;
    }
    if (mode === 'sin-copia') {
      sawSinCopia = true;
      updateNotice();
    }
    // cache-vigente / cache-cerrada / cache-historial / definitiva: datos válidos, sin aviso.
  }

  /* ¿La respuesta la ha producido nuestro proxy? (lleva sus cabeceras) */
  function esDelProxy(response) {
    try {
      return !!(response.headers.get('X-TodoF1-Datos') || response.headers.get('X-TodoF1-Ruta'));
    } catch (e) {
      return false;
    }
  }

  /* ----------------------------------------------------------- transporte */
  function requestViaProxy(url, input, init) {
    return withTimeout(toProxy(url), init).then(function (response) {
      // El proxy no está desplegado: 404/405 SIN sus cabeceras propias. (Un 404
      // legítimo del proveedor sí las lleva y se traslada sin más.)
      if ((response.status === 404 || response.status === 405) && !esDelProxy(response)) {
        proxyBroken = true;
        return withTimeout(input, init);
      }

      // Un 5xx sin las cabeceras del proxy procede del hosting (Alwaysdata),
      // no de Jolpica: se intenta la API original mientras el proxy se recupera.
      if (response.status >= 500 && !esDelProxy(response)) {
        proxyBroken = true;
        return withTimeout(input, init);
      }

      var mode = headerMode(response);
      record(response, mode);

      // Sin copia guardada y con el origen caído: se responde con un 4xx para
      // que las páginas NO reintenten en bucle y muestren su error controlado
      // con opción de reintentar.
      if (mode === 'sin-copia') {
        return response.text().then(function (body) {
          return new Response(body, {
            status: 424,
            statusText: 'Sin datos disponibles',
            headers: { 'Content-Type': 'application/json; charset=utf-8' }
          });
        });
      }

      return response;
    }, function (error) {
      // Cancelación pedida por quien llama (AbortController): no significa que
      // el proxy esté roto; se propaga la cancelación sin tocar el estado.
      var cancelada = false;
      try {
        cancelada = !!(init && init.signal && init.signal.aborted);
      } catch (e) {
        cancelada = false;
      }
      if (cancelada) {
        throw error;
      }
      // El proxy no responde (red caída en el propio hosting o tiempo máximo
      // agotado): se intenta la API original, que aplicará su propio error.
      proxyBroken = true;
      return withTimeout(input, init);
    });
  }

  window.fetch = function (input, init) {
    var url = typeof input === 'string' ? input : (input && input.url);

    if (!isApiUrl(url)) {
      return originalFetch(input, init);      // otros dominios: intactos
    }

    // Solo se redirigen lecturas: cualquier otro método se deja pasar tal cual.
    var metodo = (init && init.method) || (input && input.method) || 'GET';
    if (String(metodo).toUpperCase() !== 'GET') {
      return originalFetch(input, init);
    }

    // El proxy no está disponible: se usa la API original con tiempo máximo.
    if (proxyBroken) {
      return withTimeout(input, init);
    }

    // Varias partes de una página pueden pedir el mismo endpoint a la vez
    // (por ejemplo, la ficha y el historial). Se comparte una sola petición y
    // cada consumidor recibe su propia copia de la respuesta.
    var dedupe = typeof input === 'string' && !(init && init.signal);
    var key = dedupe ? toProxy(url) : '';
    if (key && inflight[key]) {
      return inflight[key].then(function (response) { return response.clone(); });
    }

    var request = requestViaProxy(url, input, init);
    if (key) {
      inflight[key] = request;
      var clear = function () {
        if (inflight[key] === request) delete inflight[key];
      };
      request.then(clear, clear);
      return request.then(function (response) { return response.clone(); });
    }
    return request;
  };

  /* Estado consultable (diagnóstico y pruebas). */
  window.TodoF1Datos = {
    origen: ORIGIN,
    proxy: PROXY,
    modo: function () {
      if (sawRespaldo && sawSinCopia) return 'mixto';
      if (sawRespaldo) return 'respaldo';
      if (sawSinCopia) return 'sin-copia';
      return lastSeen === 'live' ? 'live' : '';
    },
    visto: function () { return lastSeen; },
    guardado: function () { return lastSaved; },
    proxyDisponible: function () { return !proxyBroken; },
    enVuelo: function () { return Object.keys(inflight).length; }
  };
})();
