/**
 * cancel_order.js — Kaye's Barbeque
 * -----------------------------------------------------------------
 * Handles the "Cancel" button on tracking cards in orders.php
 * (rendered by components/order_card.php when $show_cancel is set).
 *
 * Clicking Cancel opens #cancel-order-modal
 * (components/cancel_order_modal.php). Confirming POSTs { order_id }
 * to /orders/cancel; on success the card is removed from the list.
 * The server decides whether the order can still be cancelled (it
 * should verify the order belongs to the logged-in customer and is
 * still Pending/Preparing).
 *
 * Requires window.APP_BASE_URL (set inline in orders.php).
 */
(function () {
  var list = document.getElementById('tracking-table-body');
  var modal = document.getElementById('cancel-order-modal');
  if (!list || !modal) return;

  var emptyMsg = document.getElementById('tracking-empty');
  var idEl = document.getElementById('cancel-order-id');
  var errorEl = document.getElementById('cancel-order-error');
  var confirmBtn = document.getElementById('cancel-order-confirm');
  var keepBtn = document.getElementById('cancel-order-keep');
  var closeBtn = document.getElementById('cancel-order-close');

  var activeCard = null;
  var activeId = 0;
  var busy = false;

  function url(path) {
    return (window.APP_BASE_URL || '/').replace(/\/$/, '') + path;
  }


  var BASE = (window.APP_BASE_URL || '/');
  if (BASE.slice(-1) !== '/') BASE += '/';

  function $(id) { return document.getElementById(id); }
  function esc(str) {
    var d = document.createElement('div');
    d.textContent = str == null ? '' : String(str);
    return d.innerHTML;
  }
  function peso(n) { return '\u20B1' + Number(n || 0).toLocaleString('en-PH'); }
  // Empty values and the literal text "None" count as "nothing entered".
  function blank(v) { return v == null || /^\s*(none|null|n\/a)?\s*$/i.test(String(v)); }

  // Fill the modal's detail sections from the card's data-order JSON
  // (built by components/order_card.php).
  function fillDetails(o) {
    var st = $('co-status');
    st.textContent = o.status || '';
    st.className = 'status status-' + String(o.statusKey || o.status || '').toLowerCase().replace(/[^a-z]/g, '');
    $('co-date').textContent = o.date ? 'Placed ' + o.date : '';

    var isPickup = String(o.type || '').toLowerCase() === 'pickup';
    $('co-type').textContent = o.type || '\u2014';

    var hasCustomer = !blank(o.customerName) || !blank(o.customerPhone) || !blank(o.customerEmail);
    $('co-customer-wrap').style.display = hasCustomer ? '' : 'none';
    $('co-customer').textContent = o.customerName || '';
    $('co-contact').textContent = [o.customerPhone, o.customerEmail]
      .filter(function (v) { return !blank(v); }).join(' \u00B7 ');

    if (isPickup) {
      $('co-fulfillment-label').textContent = 'Pickup';
      $('co-fulfillment').textContent = 'Pickup at Balongis, Balulang branch';
    } else {
      $('co-fulfillment-label').textContent = 'Delivery Address';
      var addr = String(o.deliveryAddress || '').split(',')
        .map(function (p) { return p.trim(); })
        .filter(function (p) { return !blank(p); }).join(', ');
      $('co-fulfillment').textContent = addr || 'No address recorded';
    }

    var method = o.paymentMethod || '';
    var isGcash = /gcash/i.test(method);
    var label = method ? (isGcash ? 'GCash' : 'Cash') : '\u2014';
    if (!isGcash && method) label = isPickup ? 'Cash on Pickup' : 'Cash on Delivery';
    $('co-payment').textContent = label;
    $('co-reference').textContent = (isGcash && !blank(o.paymentReference)) ? 'Ref: ' + o.paymentReference : '';
    $('co-reference').style.display = $('co-reference').textContent ? '' : 'none';

    var items = Array.isArray(o.items) ? o.items : [];
    if (items.length) {
      $('co-items').innerHTML = items.map(function (it) {
        var qty = parseInt(it.QUANTITY, 10) || 1;
        var price = parseFloat(it.PRICE) || 0;
        var line = it.SUB_TOTAL != null ? parseFloat(it.SUB_TOTAL) : qty * price;
        var thumb = it.IMAGE
          ? '<img class="co-thumb" alt="" src="' + esc(BASE + 'uploads/' + it.IMAGE) + '">'
          : '<span class="co-thumb" aria-hidden="true"></span>';
        return '<div class="co-item">' +
          '<div class="co-item-main">' + thumb +
            '<div><div class="co-item-name">' + esc(it.NAME) + '</div>' +
            '<div class="co-item-each">' + peso(price) + ' each</div></div>' +
          '</div>' +
          '<div class="co-item-qty">\u00D7' + qty + '</div>' +
          '<div class="co-item-total">' + peso(line) + '</div>' +
        '</div>';
      }).join('');
    } else {
      $('co-items').innerHTML = '<div class="co-sub" style="padding:6px 0;">' +
        (blank(o.itemsSummary) ? 'No item details available.' : esc(o.itemsSummary)) + '</div>';
    }

    var html = '<div class="co-row"><span>Subtotal</span><span>' + peso(o.subtotal) + '</span></div>';
    if (!isPickup && Number(o.deliveryFee) > 0) {
      html += '<div class="co-row"><span>Delivery Fee</span><span>' + peso(o.deliveryFee) + '</span></div>';
    }
    html += '<div class="co-row co-grand"><span>Total</span><span>' + peso(o.total) + '</span></div>';
    $('co-totals').innerHTML = html;

    var hasNotes = !blank(o.notes);
    $('co-notes-wrap').style.display = hasNotes ? '' : 'none';
    $('co-notes').textContent = hasNotes ? o.notes : '';
  }

  function showError(msg) {
    errorEl.textContent = msg;
    errorEl.style.display = msg ? 'block' : 'none';
  }

  function updateEmptyState() {
    if (!emptyMsg) return;
    var visible = Array.prototype.some.call(
      list.querySelectorAll('.order-card'),
      function (c) { return c.style.display !== 'none'; }
    );
    emptyMsg.style.display = visible ? 'none' : '';
  }

  function openModal(btn) {
    activeCard = btn.closest('.order-card');
    activeId = parseInt(btn.getAttribute('data-order-id'), 10) || 0;
    if (!activeId) return;

    idEl.textContent = '#KB-' + activeId;
    var order = null;
    try { order = JSON.parse(activeCard.getAttribute('data-order')); } catch (e) {}
    if (order) fillDetails(order);
    showError('');
    confirmBtn.disabled = false;
    keepBtn.disabled = false;
    busy = false;
    modal.classList.add('open');
    keepBtn.focus();
  }

  function closeModal() {
    if (busy) return;
    modal.classList.remove('open');
    activeCard = null;
    activeId = 0;
  }

  // Capture phase + stopImmediatePropagation so the click doesn't also
  // open the order-details modal on the card underneath.
  list.addEventListener('click', function (e) {
    var btn = e.target.closest('.order-card-cancel');
    if (!btn) return;
    e.stopImmediatePropagation();
    e.preventDefault();
    openModal(btn);
  }, true);

  // Enter/Space on the focused Cancel button shouldn't open the details modal.
  list.addEventListener('keydown', function (e) {
    if (e.key !== 'Enter' && e.key !== ' ') return;
    if (e.target.closest && e.target.closest('.order-card-cancel')) e.stopImmediatePropagation();
  }, true);

  confirmBtn.addEventListener('click', function () {
    if (!activeId || busy) return;
    busy = true;
    confirmBtn.disabled = true;
    keepBtn.disabled = true;
    showError('');

    fetch(url('/orders/cancel'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ order_id: activeId })
    })
      .then(function (res) { return res.json(); })
      .then(function (res) {
        busy = false;
        if (res.success) {
          if (activeCard) activeCard.remove();
          updateEmptyState();
          closeModal();
        } else {
          confirmBtn.disabled = false;
          keepBtn.disabled = false;
          showError(res.message || 'Could not cancel this order.');
        }
      })
      .catch(function () {
        busy = false;
        confirmBtn.disabled = false;
        keepBtn.disabled = false;
        showError('Something went wrong cancelling your order. Please try again.');
      });
  });

  keepBtn.addEventListener('click', closeModal);
  closeBtn.addEventListener('click', closeModal);
  modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && modal.classList.contains('open')) closeModal();
  });
})();