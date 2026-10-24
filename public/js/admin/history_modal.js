/**
 * history-modal.js
 * Extracted from modals/history_modal.php so the markup file holds no inline script.
 */
document.addEventListener('DOMContentLoaded', function () {
  var backdrop = document.getElementById('history-modal-backdrop');
  var eyebrow = document.getElementById('history-modal-eyebrow');
  var titleEl = document.getElementById('history-modal-title');
  var categoryEl = document.getElementById('history-modal-category');
  var imgEl = document.getElementById('history-modal-img');
  var imgEmptyEl = document.getElementById('history-modal-img-empty');
  var detailsEl = document.getElementById('history-modal-details');
  var lastFocused = null;

  function money(n) {
    return '\u20B1' + Number(n).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function addRow(label, value, opts) {
    opts = opts || {};
    var row = document.createElement('div');
    row.className = 'admin-modal-totals-row' + (opts.grand ? ' admin-modal-totals-grand' : '');
    var l = document.createElement('span');
    l.textContent = label;
    var v = document.createElement('span');
    v.textContent = value;
    if (opts.cls) v.className = opts.cls;
    row.appendChild(l);
    row.appendChild(v);
    detailsEl.appendChild(row);
  }

  function fill(data) {
    titleEl.textContent = data.item || '';
    categoryEl.textContent = data.category && data.category !== '\u2014' ? data.category : '';

    if (data.image) {
      imgEl.src = data.image;
      imgEl.style.display = 'block';
      imgEmptyEl.style.display = 'none';
    } else {
      imgEl.removeAttribute('src');
      imgEl.style.display = 'none';
      imgEmptyEl.style.display = 'block';
    }

    detailsEl.innerHTML = '';

    if (data.type === 'price') {
      eyebrow.textContent = 'Price History';
      var diff = data.newPrice - data.oldPrice;
      var sign = diff > 0 ? '+' : (diff < 0 ? '\u2212' : '');
      var cls = diff > 0 ? 'price-up' : (diff < 0 ? 'price-down' : '');

      addRow('Old price', money(data.oldPrice));
      addRow('New price', money(data.newPrice));
      addRow('Change', sign + money(Math.abs(diff)), { cls: cls, grand: true });
    } else {
      eyebrow.textContent = 'Inventory';
      var added = data.added;
      var s = added > 0 ? '+' : (added < 0 ? '\u2212' : '');
      var c = added > 0 ? 'price-up' : (added < 0 ? 'price-down' : '');

      addRow('Previous stock', data.stock - added);
      addRow('Change', s + Math.abs(added), { cls: c });
      addRow('Resulting stock', data.stock, { grand: true });
    }
    addRow('Date changed', data.changedAt || '\u2014');
  }

  function openModal(row) {
    var data;
    try { data = JSON.parse(row.getAttribute('data-history')); } catch (err) { return; }
    lastFocused = document.activeElement;
    fill(data);
    backdrop.style.display = 'flex';
    void backdrop.offsetWidth; // let the opacity transition run
    backdrop.classList.add('open');
    document.getElementById('history-modal-close').focus();
  }

  function closeModal() {
    backdrop.classList.remove('open');
    backdrop.style.display = 'none';
    if (lastFocused && typeof lastFocused.focus === 'function') lastFocused.focus();
  }

  ['price-history-body', 'inventory-body'].forEach(function (id) {
    var body = document.getElementById(id);
    if (!body) return;
    body.addEventListener('click', function (e) {
      var row = e.target.closest('tr[data-history]');
      if (row) openModal(row);
    });
    body.addEventListener('keydown', function (e) {
      if (e.key !== 'Enter' && e.key !== ' ') return;
      var row = e.target.closest('tr[data-history]');
      if (row) { e.preventDefault(); openModal(row); }
    });
  });

  document.getElementById('history-modal-close').addEventListener('click', closeModal);
  backdrop.addEventListener('click', function (e) {
    if (e.target === backdrop) closeModal();
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && backdrop.classList.contains('open')) closeModal();
  });
});