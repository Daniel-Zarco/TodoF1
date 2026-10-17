/* ============================================================
   TodoF1 â€” Trazados de circuitos (SVG)
   ------------------------------------------------------------
   Cada trazado vive en /Images/circuitos/{circuitId}.svg, con el
   circuitId real de Jolpica. Los SVG estÃ¡n normalizados: un Ãºnico
   color (currentColor, el del tema activo), trazo uniforme por
   variable CSS (--tf-trazo) y sin textos ni fondos.

   PROCEDENCIA: mapas de circuito de Wikimedia Commons con licencia
   libre, indicados en REGISTRO (autorÃ­a y licencia). Si un circuito
   no estÃ¡ en el registro, la pÃ¡gina muestra su composiciÃ³n de
   respaldo: nunca se inventa un trazado ni se usa el de otro
   circuito.

   AVISO: se muestra el trazado mÃ¡s reciente disponible. En
   temporadas antiguas algunos circuitos tenÃ­an otra configuraciÃ³n
   (variantes histÃ³ricas) que se indican solo cuando estÃ¡n
   verificadas; si no, se advierte en la propia ficha.
   ============================================================ */
(function () {
  'use strict';

  /* circuitId de Jolpica -> autorÃ­a y licencia del trazado. */
  var REGISTRO = {
    albert_park: { autor: "Firkin", licencia: "CC0", fuente: "https://commons.wikimedia.org/wiki/File:F1_circuits_2014-2018_-_Melbourne_Grand_Prix_Circuit_(version_2).svg" },
    americas: { autor: "Firkin", licencia: "CC0", fuente: "https://commons.wikimedia.org/wiki/File:F1_circuits_2014-2018_-_Circuit_of_the_Americas_(version_2).svg" },
    bahrain: { autor: "Firkin", licencia: "CC0", fuente: "https://commons.wikimedia.org/wiki/File:Remix_of_F1_circuits_2014-2018_-_Bahrain_International_Circuit_(version_2).svg" },
    baku: { autor: "Firkin", licencia: "CC0", fuente: "https://commons.wikimedia.org/wiki/File:F1_circuits_2014-2018_-_Baku_City_Circuit_(version_2).svg" },
    catalunya: { autor: "Firkin", licencia: "CC0", fuente: "https://commons.wikimedia.org/wiki/File:F1_circuits_2014-2018_-_Circuit_de_Barcelona-Catalunya_(version_2).svg" },
    dijon: { autor: "Pitlane02", licencia: "CC BY-SA 3.0", fuente: "https://commons.wikimedia.org/wiki/File:Circuit_de_Dijon-Prenois-1975.svg" },
    hockenheimring: { autor: "Firkin", licencia: "CC0", fuente: "https://commons.wikimedia.org/wiki/File:F1_circuits_2014-2018_-_Hockenheimring_(version_2).svg" },
    hungaroring: { autor: "Firkin", licencia: "CC0", fuente: "https://commons.wikimedia.org/wiki/File:F1_circuits_2014-2018_-_Hungaroring_(version_2).svg" },
    imola: { autor: "Sentoan", licencia: "CC BY 3.0", fuente: "https://commons.wikimedia.org/wiki/File:Imola_2009.svg" },
    interlagos: { autor: "Firkin", licencia: "CC0", fuente: "https://commons.wikimedia.org/wiki/File:F1_circuits_2014-2018_-_Aut%C3%B3dromo_Jos%C3%A9_Carlos_Pace_(version_2).svg" },
    las_vegas: { autor: "Valentin JJ.", licencia: "CC BY-SA 4.0", fuente: "https://commons.wikimedia.org/wiki/File:2023_Las_Vegas_street_circuit.svg" },
    losail: { autor: "Losail.svg : Will Pittenger derivative work: Gpmat", licencia: "CC BY-SA 3.0", fuente: "https://commons.wikimedia.org/wiki/File:Lusail_International_Circuit_2023.svg" },
    madring: { autor: "Dave F63", licencia: "CC0", fuente: "https://commons.wikimedia.org/wiki/File:Madring_Formula_1_Circuit.svg" },
    marina_bay: { autor: "Firkin", licencia: "CC0", fuente: "https://commons.wikimedia.org/wiki/File:F1_circuits_2014-2018_-_Marina_Bay_Street_Circuit_(version_2).svg" },
    miami: { autor: "GabrielStella", licencia: "CC BY-SA 3.0", fuente: "https://commons.wikimedia.org/wiki/File:Hard_Rock_Stadium_Circuit_2022.svg" },
    monaco: { autor: "Firkin", licencia: "CC0", fuente: "https://commons.wikimedia.org/wiki/File:F1_circuits_2014-2018_-_Circuit_de_Monaco_(version_2).svg" },
    monza: { autor: "Firkin", licencia: "CC0", fuente: "https://commons.wikimedia.org/wiki/File:F1_circuits_2014-2018_-_Autodromo_Nazionale_Monza_(version_2).svg" },
    paul_ricard: { autor: "Firkin", licencia: "CC0", fuente: "https://commons.wikimedia.org/wiki/File:F1_circuits_2014-2018_-_Paul_Ricard_Circuit_(version_2).svg" },
    red_bull_ring: { autor: "Firkin", licencia: "CC0", fuente: "https://commons.wikimedia.org/wiki/File:F1_circuits_2014-2018_-_Red_Bull_Ring.svg" },
    ricard: { autor: "Firkin", licencia: "CC0", fuente: "https://commons.wikimedia.org/wiki/File:F1_circuits_2014-2018_-_Paul_Ricard_Circuit_(version_2).svg" },
    rodriguez: { autor: "Firkin", licencia: "CC0", fuente: "https://commons.wikimedia.org/wiki/File:F1_circuits_2014-2018_-_Aut%C3%B3dromo_Hermanos_Rodr%C3%ADguez_(version_2).svg" },
    sepang: { autor: "Firkin", licencia: "CC0", fuente: "https://commons.wikimedia.org/wiki/File:F1_circuits_2014-2018_-_Sepang_International_Circuit_(version_2).svg" },
    shanghai: { autor: "Firkin", licencia: "CC0", fuente: "https://commons.wikimedia.org/wiki/File:F1_circuits_2014-2018_-_Shanghai_International_Circuit_(version_2).svg" },
    silverstone: { autor: "Firkin", licencia: "CC0", fuente: "https://commons.wikimedia.org/wiki/File:F1_circuits_2014-2018_-_Silverstone_(version_2).svg" },
    sochi: { autor: "Firkin", licencia: "CC0", fuente: "https://commons.wikimedia.org/wiki/File:F1_circuits_2014-2018_-_Sochi_Autodrom_(version_2).svg" },
    spa: { autor: "Firkin", licencia: "CC0", fuente: "https://commons.wikimedia.org/wiki/File:F1_circuits_2014-2018_-_Circuit_de_Spa-Francorchamps_(version_2).svg" },
    suzuka: { autor: "Firkin", licencia: "CC0", fuente: "https://commons.wikimedia.org/wiki/File:F1_circuits_2014-2018_-_Suzuka_Circuit_(version_2).svg" },
    vegas: { autor: "Valentin JJ.", licencia: "CC BY-SA 4.0", fuente: "https://commons.wikimedia.org/wiki/File:2023_Las_Vegas_street_circuit.svg" },
    villeneuve: { autor: "Firkin", licencia: "CC0", fuente: "https://commons.wikimedia.org/wiki/File:F1_circuits_2014-2018_-_Circuit_Gilles_Villeneuve_(version_2).svg" },
    yas_marina: { autor: "Firkin", licencia: "CC0", fuente: "https://commons.wikimedia.org/wiki/File:F1_circuits_2014-2018_-_Yas_Marina_Circuit_(version_2).svg" },
    zandvoort: { autor: "Pitlane02", licencia: "CC BY-SA 3.0", fuente: "https://commons.wikimedia.org/wiki/File:Circuit_Park_Zandvoort-1999.svg" }
  };

  var cache = {};

  /* Configuración concreta que representa el SVG, solo cuando NO es la
     vigente en la era moderna. Se muestra en la ficha para no presentar una
     variante histórica como si fuera la actual. */
  var CONFIGURACIONES = {
    albert_park: '2014–2019',
    catalunya: '2014–2022',
    dijon: '1974–1984',
    imola: 'desde 2009',
    hockenheimring: 'desde 2002',
    red_bull_ring: 'actual (desde 2011)',
    zandvoort: 'hasta 1999'
  };

  function config(id) {
    return Object.prototype.hasOwnProperty.call(CONFIGURACIONES, id) ? CONFIGURACIONES[id] : '';
  }

  function tiene(id) {
    return !!(id && Object.prototype.hasOwnProperty.call(REGISTRO, id));
  }

  function meta(id) {
    return tiene(id) ? REGISTRO[id] : null;
  }

  /* Descarga (una sola vez por sesiÃ³n) el trazado normalizado. */
  function cargar(id) {
    if (!id) return Promise.resolve(null);
    if (cache[id]) return Promise.resolve(cache[id]);
    return fetch('/Images/circuitos/' + encodeURIComponent(id) + '.svg')
      .then(function (r) { return r.ok ? r.text() : null; })
      .then(function (txt) {
        if (!txt || txt.indexOf('<svg') === -1) return null;
        cache[id] = txt;
        return txt;
      })
      .catch(function () { return null; });
  }

  /* Inserta el trazado en `host`. Devuelve false si no hay trazado
     fiable (el llamante usa entonces su respaldo). */
  function pintar(host, id, trazo) {
    if (!host) return Promise.resolve(false);
    return cargar(id).then(function (svg) {
      if (!svg) return false;
      if (trazo) host.style.setProperty('--tf-trazo', String(trazo));
      host.innerHTML = svg;
      var nodo = host.querySelector('svg');
      if (nodo) {
        nodo.removeAttribute('width');
        nodo.removeAttribute('height');
        nodo.setAttribute('focusable', 'false');
      }
      return true;
    });
  }

  window.TodoF1Trazados = {
    registro: REGISTRO,
    tiene: tiene,
    meta: meta,
    config: config,
    cargar: cargar,
    pintar: pintar
  };
})();
