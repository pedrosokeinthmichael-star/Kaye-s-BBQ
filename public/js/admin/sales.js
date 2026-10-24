/**
 * Sales page — date filter (Kaye's Barbeque Admin Panel)
 * --------------------------------------------------------------
 * Powers the "Filter by Date" panel and the summary stat cards
 * on admin/sales. Guarded so it's a no-op on every other admin
 * page (checks for #daily-table-body before doing anything).
 */
(function () {
  'use strict';

  function init() {
    var tbody = document.getElementById('daily-table-body');
    if (!tbody) return; // Not on the sales page — nothing to do.

    var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr'));

    var modeButtons = Array.prototype.slice.call(document.querySelectorAll('.filter-mode-btn'));
    var fields = Array.prototype.slice.call(document.querySelectorAll('.date-filter-field'));
    var applyBtn = document.getElementById('filter-apply-btn');
    var resetBtn = document.getElementById('filter-reset-btn');
    var errorEl = document.getElementById('filter-error');
    var rangeNote = document.getElementById('filter-range-note');
    var countNote = document.getElementById('daily-count-note');
    var emptyNote = document.getElementById('daily-empty');

    var revenueEl = document.getElementById('s-revenue');
    var revenueSubEl = document.getElementById('s-revenue-sub');
    var ordersEl = document.getElementById('s-orders');
    var ordersSubEl = document.getElementById('s-orders-sub');
    var aovEl = document.getElementById('s-aov');
    var cancelRateEl = document.getElementById('s-cancel-rate');
    var cancelSubEl = document.getElementById('s-cancel-sub');

    var dayInput = document.getElementById('filter-day');
    var weekInput = document.getElementById('filter-week');
    var monthInput = document.getElementById('filter-month');
    var yearInput = document.getElementById('filter-year');
    var rangeFromInput = document.getElementById('filter-range-from');
    var rangeToInput = document.getElementById('filter-range-to');

    var currentMode = 'all';

    function pad(n) { return n < 10 ? '0' + n : '' + n; }
    function toISO(d) { return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()); }
    function formatDateLabel(iso) {
      var d = new Date(iso + 'T00:00:00');
      return d.toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' });
    }

    function setMode(mode) {
      currentMode = mode;
      modeButtons.forEach(function (btn) {
        btn.classList.toggle('active', btn.getAttribute('data-mode') === mode);
      });
      fields.forEach(function (f) {
        f.classList.toggle('is-visible', f.getAttribute('data-for') === mode);
      });
      errorEl.textContent = '';
      if (mode === 'all') applyFilter();
    }

    function getRange() {
      if (currentMode === 'all') return null;

      if (currentMode === 'day') {
        var v = dayInput.value;
        if (!v) return { error: 'Pick a date to filter by.' };
        return { start: v, end: v };
      }

      if (currentMode === 'week') {
        var wv = weekInput.value;
        if (!wv) return { error: 'Pick any date within the week you want to see.' };
        var d = new Date(wv + 'T00:00:00');
        var day = d.getDay();
        var diffToMonday = (day === 0 ? -6 : 1 - day);
        var monday = new Date(d);
        monday.setDate(d.getDate() + diffToMonday);
        var sunday = new Date(monday);
        sunday.setDate(monday.getDate() + 6);
        return { start: toISO(monday), end: toISO(sunday) };
      }

      if (currentMode === 'month') {
        var mv = monthInput.value; // "YYYY-MM"
        if (!mv) return { error: 'Pick a month to filter by.' };
        var parts = mv.split('-');
        var year = parseInt(parts[0], 10), month = parseInt(parts[1], 10);
        var lastDay = new Date(year, month, 0).getDate();
        return { start: mv + '-01', end: mv + '-' + pad(lastDay) };
      }

      if (currentMode === 'year') {
        var yv = yearInput.value;
        if (!yv) return { error: 'Enter a year to filter by.' };
        return { start: yv + '-01-01', end: yv + '-12-31' };
      }

      if (currentMode === 'range') {
        var fromV = rangeFromInput.value;
        var toV = rangeToInput.value;
        if (!fromV || !toV) return { error: 'Pick both a start and end date.' };
        if (fromV > toV) return { error: 'The start date must be before the end date.' };
        return { start: fromV, end: toV };
      }

      return null;
    }

    function applyFilter() {
      var range = getRange();
      if (range && range.error) {
        errorEl.textContent = range.error;
        return;
      }
      errorEl.textContent = '';

      var visibleRows = [];
      rows.forEach(function (row) {
        var date = row.getAttribute('data-date');
        var visible = !range || (date >= range.start && date <= range.end);
        row.style.display = visible ? '' : 'none';
        if (visible) visibleRows.push(row);
      });

      var totalCount = visibleRows.length;
      var delivered = visibleRows.filter(function (r) { return r.getAttribute('data-status') === 'delivered'; });
      var cancelled = visibleRows.filter(function (r) { return r.getAttribute('data-status') === 'cancelled'; });
      var revenue = delivered.reduce(function (sum, r) { return sum + parseFloat(r.getAttribute('data-total') || '0'); }, 0);
      var completedCount = delivered.length;
      var aov = completedCount ? revenue / completedCount : 0;
      var cancelRate = totalCount ? (cancelled.length / totalCount) * 100 : 0;

      revenueEl.textContent = '₱' + revenue.toLocaleString('en-PH');
      revenueSubEl.textContent = 'From ' + completedCount + ' delivered order' + (completedCount === 1 ? '' : 's');
      ordersEl.textContent = completedCount;
      ordersSubEl.textContent = 'Out of ' + totalCount + ' order' + (totalCount === 1 ? '' : 's') + ' placed';
      aovEl.textContent = '₱' + Math.round(aov).toLocaleString('en-PH');
      cancelRateEl.textContent = Math.round(cancelRate) + '%';
      cancelSubEl.textContent = cancelled.length + ' of ' + totalCount + ' orders cancelled';

      countNote.textContent = totalCount + ' order' + (totalCount === 1 ? '' : 's') + (range ? ' in selected range' : ' on record');

      if (range) {
        rangeNote.textContent = 'Showing ' + formatDateLabel(range.start) + ' – ' + formatDateLabel(range.end);
      } else {
        rangeNote.textContent = 'Showing all recorded sales';
      }

      emptyNote.style.display = totalCount === 0 ? 'block' : 'none';
      if (totalCount === 0) {
        emptyNote.textContent = range
          ? 'No orders found in that date range. Try a different date or range.'
          : 'No orders recorded in this browser yet. Log in as a customer and place an order to see sales data here.';
      }
    }

    modeButtons.forEach(function (btn) {
      btn.addEventListener('click', function () { setMode(btn.getAttribute('data-mode')); });
    });

    applyBtn.addEventListener('click', applyFilter);
    resetBtn.addEventListener('click', function () { setMode('all'); });

    setMode('all');

    /* ---------------- Sale details modal ---------------- */
    var modal = document.getElementById('sale-modal-backdrop');
    if (modal) {
      var uploadBase = modal.getAttribute('data-upload-base') || '';
      var lastFocused = null;
      var $ = function (id) { return document.getElementById(id); };

      var money = function (n) {
        return '\u20B1' + Number(n).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      };

      var addTotal = function (box, label, value, grand) {
        var row = document.createElement('div');
        row.className = 'admin-modal-totals-row' + (grand ? ' admin-modal-totals-grand' : '');
        var l = document.createElement('span'); l.textContent = label;
        var v = document.createElement('span'); v.textContent = value;
        row.appendChild(l); row.appendChild(v);
        box.appendChild(row);
      };

      var fillModal = function (d) {
        $('sale-modal-title').textContent = 'Order #' + d.id;
        $('sale-modal-placed').textContent = 'Placed ' + d.placed;

        var statusEl = $('sale-modal-status');
        statusEl.textContent = d.status;
        statusEl.className = 'status status-' + String(d.status).toLowerCase();

        $('sale-modal-customer').textContent = d.customer;
        $('sale-modal-contact').textContent = [d.phone, d.email].filter(Boolean).join(' \u00B7 ');

        $('sale-modal-type').textContent = d.type === 'Delivery' ? 'Delivery address' : 'Pickup';
        $('sale-modal-address').textContent = d.type === 'Delivery' ? (d.address || '\u2014') : 'Customer picks up at the shop.';

        $('sale-modal-payment').textContent = d.payment || '\u2014';
        $('sale-modal-reference').textContent = d.reference ? 'Reference no. ' + d.reference : '';

        $('sale-modal-note').textContent = d.note;
        $('sale-modal-note-section').style.display = d.note ? '' : 'none';

        var itemsEl = $('sale-modal-items');
        itemsEl.innerHTML = '';
        if (!d.items.length) {
          var none = document.createElement('div');
          none.className = 'admin-modal-subtext';
          none.textContent = 'No items recorded.';
          itemsEl.appendChild(none);
        }
        d.items.forEach(function (it) {
          var row = document.createElement('div');
          row.className = 'om-item';

          var main = document.createElement('div');
          main.className = 'om-item-main';
          if (it.image) {
            var img = document.createElement('img');
            img.className = 'om-item-thumb';
            img.alt = '';
            img.src = uploadBase + it.image;
            main.appendChild(img);
          } else {
            var ph = document.createElement('span');
            ph.className = 'om-item-thumb om-item-thumb-empty';
            main.appendChild(ph);
          }
          var txt = document.createElement('div');
          var nm = document.createElement('div');
          nm.className = 'om-item-name';
          nm.textContent = it.name;
          var each = document.createElement('div');
          each.className = 'om-item-each';
          each.textContent = money(it.price) + ' each';
          txt.appendChild(nm); txt.appendChild(each);
          main.appendChild(txt);

          var qty = document.createElement('div');
          qty.className = 'om-item-qty';
          qty.textContent = '\u00D7' + it.qty;
          var tot = document.createElement('div');
          tot.className = 'om-item-total';
          tot.textContent = money(it.subtotal);

          row.appendChild(main); row.appendChild(qty); row.appendChild(tot);
          itemsEl.appendChild(row);
        });

        var totals = $('sale-modal-totals');
        totals.innerHTML = '';
        addTotal(totals, 'Subtotal', money(d.subtotal));
        if (d.type === 'Delivery') addTotal(totals, 'Delivery fee', money(d.fee));
        addTotal(totals, 'Total', money(d.total), true);
      };

      var openModal = function (row) {
        var data;
        try { data = JSON.parse(row.getAttribute('data-order')); } catch (err) { return; }
        lastFocused = document.activeElement;
        fillModal(data);
        modal.style.display = 'flex';
        void modal.offsetWidth; // let the opacity transition run
        modal.classList.add('open');
        $('sale-modal-close').focus();
      };

      var closeModal = function () {
        modal.classList.remove('open');
        modal.style.display = 'none';
        if (lastFocused && typeof lastFocused.focus === 'function') lastFocused.focus();
      };

      tbody.addEventListener('click', function (e) {
        var btn = e.target.closest('.view-sale-btn');
        if (!btn) return;
        var row = btn.closest('tr[data-order]');
        if (row) openModal(row);
      });
      $('sale-modal-close').addEventListener('click', closeModal);
      modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });
      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.classList.contains('open')) closeModal();
      });
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();