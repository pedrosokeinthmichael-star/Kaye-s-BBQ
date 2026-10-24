<?php
/**
 * components/history_order_modal.php — Order details for Order History
 * -----------------------------------------------------------------
 * Included once from history.php. Opens when a card in
 * #history-table-body is clicked (or Enter/Space is pressed on it) and
 * fills itself from the card's data-order JSON, which
 * components/order_card.php already builds (orderId, date, type, status,
 * items[], subtotal, deliveryFee, total, customerName/Phone/Email,
 * deliveryAddress, notes, paymentMethod, paymentReference, paymentProof).
 * Styles: css/customer_css/order_history.css. The script lives in this file.
 */
?>
<div id="hm-backdrop" aria-hidden="true">
  <div class="hm-box" role="dialog" aria-modal="true" aria-labelledby="hm-title">
    <div class="hm-head">
      <div>
        <div class="hm-eyebrow">Order Details</div>
        <h3 class="hm-title" id="hm-title"></h3>
      </div>
      <button type="button" class="hm-close" id="hm-close" aria-label="Close">&times;</button>
    </div>
    <div class="hm-body">
      <div class="hm-meta">
        <span id="hm-status" class="status"></span>
        <span class="hm-date" id="hm-date"></span>
      </div>

      <div class="hm-grid">
        <div>
          <div class="hm-label">Order Type</div>
          <div class="hm-text" id="hm-type"></div>
        </div>
        <div id="hm-customer-wrap">
          <div class="hm-label">Customer</div>
          <div class="hm-text" id="hm-customer"></div>
          <div class="hm-sub" id="hm-contact"></div>
        </div>
      </div>

      <div class="hm-card" id="hm-fulfillment-wrap">
        <div class="hm-label" id="hm-fulfillment-label">Delivery Address</div>
        <div class="hm-text" id="hm-fulfillment"></div>
      </div>

      <div class="hm-card" id="hm-payment-wrap">
        <div class="hm-label">Payment Method</div>
        <div class="hm-text" id="hm-payment"></div>
      </div>

      <div class="hm-card hm-gcash" id="hm-gcash-wrap" style="display:none;">
        <div class="hm-label">GCash Reference Number</div>
        <div class="hm-text" id="hm-reference"></div>
        <a class="hm-proof" id="hm-proof-link" href="#" target="_blank" rel="noopener" style="display:none;">
          <img id="hm-proof-img" alt="Proof of payment">
        </a>
      </div>

      <div class="hm-card hm-refund" id="hm-refund-wrap" style="display:none;">
        <div class="hm-label">Refund Sent</div>
        <div class="hm-sub" id="hm-refund-date"></div>
        <div class="hm-label hm-label-spaced">Refund Reference Number</div>
        <div class="hm-text" id="hm-refund-reference"></div>
        <a class="hm-proof" id="hm-refund-proof-link" href="#" target="_blank" rel="noopener" style="display:none;">
          <img id="hm-refund-proof-img" alt="Proof of refund">
        </a>
      </div>

      <div class="hm-label hm-label-items">Items</div>
      <div class="hm-items" id="hm-items"></div>

      <div class="hm-totals" id="hm-totals"></div>

      <div class="hm-card hm-notes hm-notes-last" id="hm-notes-wrap" style="display:none;">
        <div class="hm-label">Order Notes</div>
        <div class="hm-text" id="hm-notes"></div>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  var BASE = <?= json_encode( base_url() ) ?>;
  if (BASE && BASE.slice(-1) !== '/') BASE += '/';

  var backdrop = document.getElementById('hm-backdrop');
  if (!backdrop) return;
  var lastFocused = null;

  function $(id) { return document.getElementById(id); }
  function esc(str) {
    var d = document.createElement('div');
    d.textContent = str == null ? '' : String(str);
    return d.innerHTML;
  }
  function peso(n) { return '\u20B1' + Number(n || 0).toLocaleString('en-PH'); }
  // Empty values and the literal text "None" count as "nothing entered".
  function blank(v) { return v == null || /^\s*(none|null|n\/a)?\s*$/i.test(String(v)); }

  function fill(o) {
    $('hm-title').textContent = '#KB-' + (o.orderId || '');
    var st = $('hm-status');
    st.textContent = o.status || '';
    st.className = 'status status-' + String(o.statusKey || o.status || '').toLowerCase().replace(/[^a-z]/g, '');
    $('hm-date').textContent = o.date ? 'Placed ' + o.date : '';

    var isPickup = String(o.type || '').toLowerCase() === 'pickup';
    $('hm-type').textContent = o.type || '\u2014';

    // Customer
    var hasCustomer = !blank(o.customerName) || !blank(o.customerPhone) || !blank(o.customerEmail);
    $('hm-customer-wrap').style.display = hasCustomer ? '' : 'none';
    $('hm-customer').textContent = o.customerName || '';
    var contact = [o.customerPhone, o.customerEmail].filter(function (v) { return !blank(v); }).join(' \u00B7 ');
    $('hm-contact').textContent = contact;

    // Delivery address / pickup
    if (isPickup) {
      $('hm-fulfillment-label').textContent = 'Pickup';
      $('hm-fulfillment').textContent = 'Pickup at Balongis, Balulang branch';
    } else {
      $('hm-fulfillment-label').textContent = 'Delivery Address';
      var addr = String(o.deliveryAddress || '').split(',')
        .map(function (p) { return p.trim(); })
        .filter(function (p) { return !blank(p); }).join(', ');
      $('hm-fulfillment').textContent = addr || 'No address recorded';
    }

    // Payment
    var method = o.paymentMethod || '';
    var isGcash = /gcash/i.test(method);
    var label = method ? (isGcash ? 'GCash' : (/cash/i.test(method) ? method : 'Cash')) : '\u2014';
    if (!isGcash && /^cash$/i.test(method)) label = isPickup ? 'Cash on Pickup' : 'Cash on Delivery';
    $('hm-payment').textContent = label;

    var showGcash = isGcash && (!blank(o.paymentReference) || !blank(o.paymentProof));
    $('hm-gcash-wrap').style.display = showGcash ? '' : 'none';
    if (showGcash) {
      $('hm-reference').textContent = blank(o.paymentReference) ? '\u2014' : o.paymentReference;
      var link = $('hm-proof-link');
      if (!blank(o.paymentProof)) {
        var url = BASE + 'uploads/payments/' + encodeURIComponent(o.paymentProof);
        $('hm-proof-img').src = url;
        link.href = url;
        link.style.display = 'inline-block';
      } else {
        link.style.display = 'none';
      }
    }

    // Refund (admin sent the payment back)
    var rf = o.refund || null;
    var showRefund = !!rf && (!blank(rf.reference) || !blank(rf.proof));
    $('hm-refund-wrap').style.display = showRefund ? '' : 'none';
    if (showRefund) {
      $('hm-refund-date').textContent = rf.date ? 'Sent ' + rf.date : '';
      $('hm-refund-reference').textContent = blank(rf.reference) ? '\u2014' : rf.reference;
      var rLink = $('hm-refund-proof-link');
      if (!blank(rf.proof)) {
        var rUrl = BASE + 'uploads/payments/' + encodeURIComponent(rf.proof);
        $('hm-refund-proof-img').src = rUrl;
        rLink.href = rUrl;
        rLink.style.display = 'inline-block';
      } else {
        rLink.style.display = 'none';
      }
    }

    // Items
    var items = Array.isArray(o.items) ? o.items : [];
    if (items.length) {
      $('hm-items').innerHTML = items.map(function (it) {
        var qty = parseInt(it.QUANTITY, 10) || 1;
        var price = parseFloat(it.PRICE) || 0;
        var line = it.SUB_TOTAL != null ? parseFloat(it.SUB_TOTAL) : qty * price;
        var thumb = it.IMAGE
          ? '<img class="hm-thumb" alt="" src="' + esc(BASE + 'uploads/' + it.IMAGE) + '">'
          : '<span class="hm-thumb" aria-hidden="true"></span>';
        return '<div class="hm-item">' +
          '<div class="hm-item-main">' + thumb +
            '<div><div class="hm-item-name">' + esc(it.NAME) + '</div>' +
            '<div class="hm-item-each">' + peso(price) + ' each</div></div>' +
          '</div>' +
          '<div class="hm-item-qty">\u00D7' + qty + '</div>' +
          '<div class="hm-item-total">' + peso(line) + '</div>' +
        '</div>';
      }).join('');
    } else {
      $('hm-items').innerHTML = '<div class="hm-empty">' +
        (blank(o.itemsSummary) ? 'No item details available.' : esc(o.itemsSummary)) + '</div>';
    }

    // Totals
    var html = '<div class="hm-row"><span>Subtotal</span><span>' + peso(o.subtotal) + '</span></div>';
    if (!isPickup && Number(o.deliveryFee) > 0) {
      html += '<div class="hm-row"><span>Delivery Fee</span><span>' + peso(o.deliveryFee) + '</span></div>';
    }
    html += '<div class="hm-row hm-grand"><span>Total</span><span>' + peso(o.total) + '</span></div>';
    $('hm-totals').innerHTML = html;

    // Notes
    var hasNotes = !blank(o.notes);
    $('hm-notes-wrap').style.display = hasNotes ? '' : 'none';
    $('hm-notes').textContent = hasNotes ? o.notes : '';
  }

  function open(card) {
    var o;
    try { o = JSON.parse(card.getAttribute('data-order')); } catch (e) { return; }
    if (!o) return;
    lastFocused = document.activeElement;
    fill(o);
    backdrop.classList.add('open');
    backdrop.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    $('hm-close').focus();
  }

  function close() {
    backdrop.classList.remove('open');
    backdrop.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    if (lastFocused && typeof lastFocused.focus === 'function') lastFocused.focus();
  }

  document.addEventListener('click', function (e) {
    var card = e.target.closest('#history-table-body .order-card');
    if (card) open(card);
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && backdrop.classList.contains('open')) { close(); return; }
    if (e.key !== 'Enter' && e.key !== ' ') return;
    var card = e.target.closest && e.target.closest('#history-table-body .order-card');
    if (card) { e.preventDefault(); open(card); }
  });
  $('hm-close').addEventListener('click', close);
  backdrop.addEventListener('click', function (e) { if (e.target === backdrop) close(); });
})();
</script>