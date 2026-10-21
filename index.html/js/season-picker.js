/* TodoF1 — Selector de temporada por décadas.
   Reutiliza los <select data-season-picker> existentes: el select nativo
   sigue siendo el valor real y el fallback si este script no se ejecuta. */
(function () {
  'use strict';

  var MOBILE_MAX = 767;
  var PANEL_WIDTH = 320;
  var FALLBACK_MIN = 1950;
  var FALLBACK_MAX = 2026;
  var instances = [];
  var openInstance = null;

  function node(tag, className, html) {
    var el = document.createElement(tag);
    if (className) el.className = className;
    if (html != null) el.innerHTML = html;
    return el;
  }

  function bounds(select) {
    var min = Infinity;
    var max = -Infinity;
    for (var i = 0; i < select.options.length; i++) {
      var year = parseInt(select.options[i].value, 10);
      if (isNaN(year)) continue;
      if (year < min) min = year;
      if (year > max) max = year;
    }
    if (!isFinite(min) || !isFinite(max)) return { min: FALLBACK_MIN, max: FALLBACK_MAX };
    return { min: min, max: max };
  }

  function decadeStart(year) {
    return Math.floor(year / 10) * 10;
  }

  function currentYear(max) {
    var now = new Date().getFullYear();
    return now <= max ? now : max;
  }

  function isMobile() {
    return window.innerWidth <= MOBILE_MAX;
  }

  var CALENDAR = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" ' +
    'stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' +
    '<rect x="3.5" y="5" width="17" height="15" rx="2.5"></rect>' +
    '<path d="M3.5 9.5h17"></path><path d="M8 3.5v3"></path><path d="M16 3.5v3"></path></svg>';
  var CHEVRON = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" ' +
    'stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' +
    '<path d="m7 9 5 5 5-5"></path></svg>';

  function Picker(select, index) {
    this.select = select;
    this.index = index;
    this.isOpen = false;
    this.min = FALLBACK_MIN;
    this.max = FALLBACK_MAX;
    this.selected = FALLBACK_MAX;
    this.decade = 2020;
    this.trigger = null;
    this.panel = null;
    this.backdrop = null;
    this.build();
  }

  Picker.prototype.build = function () {
    var self = this;
    var select = this.select;
    var label = select.closest('label');
    var host = label || select;
    var context = select.id === 'tf-season-select' ? 'nav' : 'hero';
    this.context = context;
    var panelId = 'tf-season-panel-' + this.index;

    this.trigger = node('button', 'tf-season__trigger tf-season__trigger--' + context);
    this.trigger.type = 'button';
    this.trigger.setAttribute('aria-haspopup', 'dialog');
    this.trigger.setAttribute('aria-expanded', 'false');
    this.trigger.setAttribute('aria-controls', panelId);
    this.trigger.innerHTML =
      '<span class="tf-season__cal">' + CALENDAR + '</span>' +
      '<span class="tf-season__prefix">' + (context === 'nav' ? 'TEMP.' : 'Temporada') + '</span>' +
      '<span class="tf-season__year">' + FALLBACK_MAX + '</span>' +
      '<span class="tf-season__chev">' + CHEVRON + '</span>';

    this.panel = node('div', 'tf-season__panel');
    this.panel.id = panelId;
    this.panel.setAttribute('role', 'dialog');
    this.panel.setAttribute('aria-modal', 'false');
    this.panel.setAttribute('aria-label', 'Seleccionar temporada');
    this.panel.setAttribute('aria-hidden', 'true');

    this.backdrop = node('div', 'tf-season__backdrop');
    this.backdrop.setAttribute('aria-hidden', 'true');

    var head = node('div', 'tf-season__head');
    this.prev = node('button', 'tf-season__arrow', '&#8249;');
    this.prev.type = 'button';
    this.prev.setAttribute('aria-label', 'Década anterior');
    this.decadeEl = node('span', 'tf-season__decade');
    this.decadeEl.setAttribute('aria-live', 'polite');
    this.next = node('button', 'tf-season__arrow', '&#8250;');
    this.next.type = 'button';
    this.next.setAttribute('aria-label', 'Década siguiente');
    head.appendChild(this.prev);
    head.appendChild(this.decadeEl);
    head.appendChild(this.next);
    this.panel.appendChild(head);

    this.grid = node('div', 'tf-season__grid');
    this.grid.setAttribute('role', 'group');
    this.grid.setAttribute('aria-label', 'Años de la década');
    this.panel.appendChild(this.grid);

    label ? (label.hidden = true) : (select.hidden = true);
    host.parentNode.insertBefore(this.trigger, host.nextSibling);
    document.body.appendChild(this.backdrop);
    document.body.appendChild(this.panel);

    this.trigger.addEventListener('click', function () {
      if (self.isOpen) self.close(true);
      else self.openPicker();
    });
    this.backdrop.addEventListener('click', function () { self.close(true); });
    this.prev.addEventListener('click', function () { self.moveDecade(-1); });
    this.next.addEventListener('click', function () { self.moveDecade(1); });
    select.addEventListener('change', function () { self.sync(); });

    this.sync();
  };

  Picker.prototype.sync = function () {
    var b = bounds(this.select);
    this.min = b.min;
    this.max = b.max;
    var value = parseInt(this.select.value, 10);
    if (isNaN(value) || value < this.min || value > this.max) value = this.max;
    this.selected = value;
    this.trigger.querySelector('.tf-season__year').textContent = String(value);
    this.trigger.setAttribute('aria-label', 'Seleccionar temporada, actual ' + value);
    if (this.isOpen) {
      this.decade = decadeStart(value);
      this.renderDecade(false);
    }
  };

  Picker.prototype.openPicker = function () {
    var self = this;
    if (openInstance && openInstance !== this) openInstance.close(false);
    if (isMobile() && window.TodoF1Nav && typeof window.TodoF1Nav.close === 'function' &&
        typeof window.TodoF1Nav.isOpen === 'function' && window.TodoF1Nav.isOpen()) {
      window.TodoF1Nav.close();
    }

    this.sync();
    this.decade = decadeStart(this.selected);
    this.renderDecade(true);
    this.panel.classList.add('is-open');
    this.panel.setAttribute('aria-hidden', 'false');
    this.backdrop.classList.add('is-visible');
    this.trigger.setAttribute('aria-expanded', 'true');
    this.isOpen = true;
    openInstance = this;
    this.position();
    window.requestAnimationFrame(function () {
      var active = self.grid.querySelector('.tf-season__year.is-selected') ||
        self.grid.querySelector('.tf-season__year');
      if (active) active.focus();
    });
  };

  Picker.prototype.close = function (restoreFocus) {
    if (!this.isOpen) return;
    this.isOpen = false;
    this.panel.classList.remove('is-open');
    this.panel.setAttribute('aria-hidden', 'true');
    this.backdrop.classList.remove('is-visible');
    this.trigger.setAttribute('aria-expanded', 'false');
    if (openInstance === this) openInstance = null;
    if (restoreFocus) this.trigger.focus();
  };

  Picker.prototype.moveDecade = function (delta) {
    var first = decadeStart(this.min);
    var last = decadeStart(this.max);
    var next = this.decade + delta * 10;
    if (next < first || next > last) return;
    this.decade = next;
    this.renderDecade(false);
    this.position();
  };

  Picker.prototype.renderDecade = function (focusGrid) {
    var first = decadeStart(this.min);
    var last = decadeStart(this.max);
    var end = Math.min(this.decade + 9, this.max);
    var now = currentYear(this.max);
    this.decadeEl.textContent = this.decade + '\u2013' + end;
    this.prev.disabled = this.decade <= first;
    this.next.disabled = this.decade >= last;

    this.grid.innerHTML = '';
    for (var year = end; year >= this.decade; year--) {
      if (year < this.min || year > this.max) continue;
      var btn = node('button', 'tf-season__year', String(year));
      btn.type = 'button';
      btn.setAttribute('data-year', String(year));
      var selected = year === this.selected;
      var current = year === now && !selected;
      if (selected) btn.classList.add('is-selected');
      if (current) {
        btn.classList.add('is-current');
        btn.appendChild(node('span', 'tf-season__now'));
      }
      btn.setAttribute('aria-pressed', selected ? 'true' : 'false');
      btn.setAttribute('aria-label', String(year) + (current ? ', temporada actual' : ''));
      btn.addEventListener('click', (function (self, value) {
        return function () { self.selectYear(value); };
      })(this, year));
      this.grid.appendChild(btn);
    }

    this.grid.classList.remove('is-changing');
    void this.grid.offsetWidth;
    this.grid.classList.add('is-changing');
    if (focusGrid) {
      var active = this.grid.querySelector('.tf-season__year.is-selected') ||
        this.grid.querySelector('.tf-season__year');
      if (active) active.focus();
    }
  };

  Picker.prototype.selectYear = function (year) {
    this.selected = year;
    this.close(this.context !== 'nav');
    this.select.value = String(year);
    this.select.dispatchEvent(new Event('change', { bubbles: true }));
    this.sync();
  };

  Picker.prototype.position = function () {
    if (!this.isOpen) return;
    if (isMobile()) {
      this.panel.style.removeProperty('left');
      this.panel.style.removeProperty('top');
      this.panel.style.removeProperty('width');
      return;
    }
    var rect = this.trigger.getBoundingClientRect();
    var width = Math.min(PANEL_WIDTH, window.innerWidth - 24);
    var left = rect.left;
    if (left + width > window.innerWidth - 12) left = window.innerWidth - width - 12;
    if (left < 12) left = 12;
    this.panel.style.width = width + 'px';
    this.panel.style.left = left + 'px';
    this.panel.style.top = '0px';
    var height = this.panel.offsetHeight;
    var top = rect.bottom + 8;
    if (top + height > window.innerHeight - 12) {
      top = Math.max(12, rect.top - height - 8);
    }
    this.panel.style.top = top + 'px';
  };

  Picker.prototype.trapTab = function (event) {
    var focusables = this.panel.querySelectorAll('button:not([disabled])');
    if (!focusables.length) return;
    var first = focusables[0];
    var last = focusables[focusables.length - 1];
    var active = document.activeElement;
    if (event.shiftKey && (active === first || !this.panel.contains(active))) {
      event.preventDefault();
      last.focus();
    } else if (!event.shiftKey && (active === last || !this.panel.contains(active))) {
      event.preventDefault();
      first.focus();
    }
  };

  function bindGlobal() {
    document.addEventListener('click', function (event) {
      if (!openInstance) return;
      var target = event.target;
      if (openInstance.panel.contains(target) || openInstance.trigger.contains(target) ||
          openInstance.backdrop.contains(target)) return;
      openInstance.close(false);
    });

    document.addEventListener('keydown', function (event) {
      if (!openInstance) return;
      if (event.key === 'Escape') {
        event.preventDefault();
        openInstance.close(true);
        return;
      }
      if (event.key === 'Tab') {
        openInstance.trapTab(event);
        return;
      }
      if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
        if (openInstance.panel.contains(document.activeElement)) {
          event.preventDefault();
          openInstance.moveDecade(event.key === 'ArrowLeft' ? -1 : 1);
        }
      }
    });

    window.addEventListener('resize', function () {
      if (openInstance) openInstance.position();
    });

    window.addEventListener('scroll', function () {
      if (openInstance) openInstance.position();
    }, { passive: true });
  }

  function init() {
    var selects = document.querySelectorAll('select[data-season-picker]');
    Array.prototype.forEach.call(selects, function (select, index) {
      if (select.getAttribute('data-season-enhanced') === '1') return;
      select.setAttribute('data-season-enhanced', '1');
      instances.push(new Picker(select, index));
    });
    bindGlobal();
  }

  window.TodoF1SeasonPicker = {
    sync: function () {
      instances.forEach(function (picker) { picker.sync(); });
    },
    close: function () {
      if (openInstance) openInstance.close(false);
    }
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
