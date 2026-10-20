/* ============================================================
   TodoF1 — Loader animado reutilizable
   - Tres barras inclinadas que se iluminan de izquierda a derecha.
   - Usa --team-primary, así que cambia de color con el tema sin recargar.
   - Mejora automáticamente los indicadores .tf-loading existentes y los
     .tf-loading-inline que se inserten después (incluidas cargas dinámicas).
   ============================================================ */
(function () {
  'use strict';

  var BARS = '<i></i><i></i><i></i>';

  function esc(value) {
    return String(value == null ? '' : value).replace(/[&<>"']/g, function (ch) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch];
    });
  }

  function bars(compact) {
    return '<span class="tf-loader' + (compact ? ' tf-loader--sm' : '') + '" aria-hidden="true">' +
      BARS + '</span>';
  }

  function html(message, compact) {
    return bars(compact) + '<span class="tf-loader__text">' + esc(message) + '</span>';
  }

  function create(message) {
    var box = document.createElement('div');
    box.className = 'tf-loading';
    box.setAttribute('role', 'status');
    box.setAttribute('aria-live', 'polite');
    box.innerHTML = html(message || 'Cargando…', false);
    return box;
  }

  function enhanceBox(box) {
    if (!box || box.getAttribute('data-tf-loader') === '1') return;

    var inline = box.classList.contains('tf-loading-inline');
    if (!box.hasAttribute('role')) box.setAttribute('role', 'status');
    if (!box.hasAttribute('aria-live')) box.setAttribute('aria-live', 'polite');

    var spinner = box.querySelector('.tf-spinner');
    var barsNode = box.querySelector('.tf-loader');

    if (!barsNode) {
      barsNode = document.createElement('span');
      barsNode.className = 'tf-loader' + (inline ? ' tf-loader--sm' : '');
      barsNode.setAttribute('aria-hidden', 'true');
      barsNode.innerHTML = BARS;
      if (spinner && spinner.parentNode) spinner.parentNode.replaceChild(barsNode, spinner);
      else box.insertBefore(barsNode, box.firstChild);
    } else if (spinner && spinner.parentNode) {
      spinner.parentNode.removeChild(spinner);
    }

    box.setAttribute('data-tf-loader', '1');
  }

  function enhance(root) {
    if (!root) return;
    var boxes = [];
    if (root.nodeType === 1 && root.matches &&
        root.matches('.tf-loading, .tf-loading-inline')) {
      boxes.push(root);
    }
    if (root.querySelectorAll) {
      boxes = boxes.concat(Array.prototype.slice.call(
        root.querySelectorAll('.tf-loading, .tf-loading-inline')
      ));
    }
    boxes.forEach(enhanceBox);
  }

  function init() {
    enhance(document);

    if (!window.MutationObserver || !document.documentElement) return;
    var observer = new MutationObserver(function (mutations) {
      mutations.forEach(function (mutation) {
        Array.prototype.forEach.call(mutation.addedNodes, function (node) {
          if (node.nodeType === 1) enhance(node);
        });
      });
    });
    observer.observe(document.documentElement, { childList: true, subtree: true });
  }

  window.TodoF1Loader = {
    create: create,
    html: html,
    enhance: enhance
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
