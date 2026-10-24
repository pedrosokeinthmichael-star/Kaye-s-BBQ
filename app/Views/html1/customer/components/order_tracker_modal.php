<?php
/**
 * components/order_tracker_modal.php — Order details for the Order Tracker
 * -----------------------------------------------------------------
 * Included once from orders.php. Opens when a card in
 * #tracking-table-body is clicked (or Enter/Space is pressed on it) and
 * fills itself from the card's data-order JSON, which
 * components/order_card.php already builds (orderId, date, type, status,
 * items[], subtotal, deliveryFee, total, customerName/Phone/Email,
 * deliveryAddress, notes, paymentMethod, paymentReference, paymentProof).
 * Styles: css/customer_css/order_tracker_modal.css. The script lives in this file.
 */
?>
<div id="tm-backdrop" aria-hidden="true">
  <div class="tm-box" role="dialog" aria-modal="true" aria-labelledby="tm-title">
    <div class="tm-head">
      <div>
        <div class="tm-eyebrow">Order Details</div>
        <h3 class="tm-title" id="tm-title"></h3>
      </div>
      <button type="button" class="tm-close" id="tm-close" aria-label="Close">&times;</button>
    </div>
    <div class="tm-body">
      <div class="tm-meta">
        <span id="tm-status" class="status"></span>
        <span class="tm-date" id="tm-date"></span>
      </div>

      <div class="tm-grid">
        <div>
          <div class="tm-label">Order Type</div>
          <div class="tm-text" id="tm-type"></div>
        </div>
        <div id="tm-customer-wrap">
          <div class="tm-label">Customer</div>
          <div class="tm-text" id="tm-customer"></div>
          <div class="tm-sub" id="tm-contact"></div>
        </div>
      </div>

      <div class="tm-card" id="tm-fulfillment-wrap">
        <div class="tm-label" id="tm-fulfillment-label">Delivery Address</div>
        <div class="tm-text" id="tm-fulfillment"></div>
      </div>

      <div class="tm-card" id="tm-payment-wrap">
        <div class="tm-label">Payment Method</div>
        <div class="tm-text" id="tm-payment"></div>
      </div>

      <div class="tm-card tm-gcash" id="tm-gcash-wrap" style="display:none;">
        <div class="tm-label">GCash Reference Number</div>
        <div class="tm-text" id="tm-reference"></div>
        <a class="tm-proof" id="tm-proof-link" href="#" target="_blank" rel="noopener" style="display:none;">
          <img id="tm-proof-img" alt="Proof of payment">
        </a>
      </div>

      <div class="tm-card" id="tm-extra-wrap" style="display:none;">
        <div class="tm-label">Additional Payments</div>
        <div id="tm-extra-list"></div>
      </div>

      <div class="tm-label tm-label-items">Items</div>
      <div class="tm-items" id="tm-items"></div>

      <div class="tm-totals" id="tm-totals"></div>

      <div class="tm-card tm-notes" id="tm-notes-wrap" style="display:none;">
        <div class="tm-label">Order Notes</div>
        <div class="tm-text" id="tm-notes"></div>
      </div>

      <div class="tm-card tm-addpay tm-addpay-last" id="tm-addpay-wrap" style="display:none;">
        <div class="tm-label">Add Another Payment</div>
        <div class="tm-sub tm-sub-spaced">Sent more through GCash? Enter the reference number and attach the screenshot. We will check it against your order total.</div>
        <div class="tm-qr-wrap">
          <div class="tm-label tm-label-left">Scan to Pay</div>
          <div class="tm-qr-box">
            <?php if ( ! empty( $gcash['GCASH_QR'] ) ) : ?>
              <img class="tm-qr-img" src="<?= base_url( 'uploads/' . $gcash['GCASH_QR'] ) ?>" alt="GCash QR Code">
            <?php else : ?>
              <div class="tm-qr-empty">QR code not set up yet</div>
            <?php endif; ?>
          </div>
          <?php if ( ! empty( $gcash['GCASH_NAME'] ) || ! empty( $gcash['GCASH_NUMBER'] ) ) : ?>
            <p class="tm-gcash-name"><?= esc( $gcash['GCASH_NAME'] ?? '' ) ?></p>
            <p class="tm-gcash-number"><?= esc( $gcash['GCASH_NUMBER'] ?? '' ) ?></p>
          <?php endif; ?>
        </div>
        <input type="text" id="tm-addpay-ref" class="form-input" placeholder="GCash reference number">
        <input type="file" id="tm-addpay-proof" class="form-input tm-addpay-file" accept="image/*">
        <div class="tm-addpay-preview" id="tm-addpay-preview" style="display:none;">
          <img class="tm-addpay-preview-img" id="tm-addpay-preview-img" src="" alt="Payment screenshot preview">
        </div>
        <p class="tm-error" id="tm-addpay-error" role="alert"></p>
        <button type="button" class="btn btn-primary tm-addpay-submit" id="tm-addpay-submit">Submit Payment</button>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  var BASE = <?= json_encode( base_url() ) ?>;
  if (BASE && BASE.slice(-1) !== '/') BASE += '/';

  var backdrop = document.getElementById('tm-backdrop');
  if (!backdrop) return;
  var lastFocused = null;
  var currentOrderId = 0, addBusy = false;

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
    $('tm-title').textContent = '#KB-' + (o.orderId || '');
    var st = $('tm-status');
    st.textContent = o.status || '';
    st.className = 'status status-' + String(o.statusKey || o.status || '').toLowerCase().replace(/[^a-z]/g, '');
    $('tm-date').textContent = o.date ? 'Placed ' + o.date : '';

    var isPickup = String(o.type || '').toLowerCase() === 'pickup';
    $('tm-type').textContent = o.type || '\u2014';

    // Customer
    var hasCustomer = !blank(o.customerName) || !blank(o.customerPhone) || !blank(o.customerEmail);
    $('tm-customer-wrap').style.display = hasCustomer ? '' : 'none';
    $('tm-customer').textContent = o.customerName || '';
    var contact = [o.customerPhone, o.customerEmail].filter(function (v) { return !blank(v); }).join(' \u00B7 ');
    $('tm-contact').textContent = contact;

    // Delivery address / pickup
    if (isPickup) {
      $('tm-fulfillment-label').textContent = 'Pickup';
      $('tm-fulfillment').textContent = 'Pickup at Balongis, Balulang branch';
    } else {
      $('tm-fulfillment-label').textContent = 'Delivery Address';
      var addr = String(o.deliveryAddress || '').split(',')
        .map(function (p) { return p.trim(); })
        .filter(function (p) { return !blank(p); }).join(', ');
      $('tm-fulfillment').textContent = addr || 'No address recorded';
    }

    // Payment
    var method = o.paymentMethod || '';
    var isGcash = /gcash/i.test(method);
    var label = method ? (isGcash ? 'GCash' : (/cash/i.test(method) ? method : 'Cash')) : '\u2014';
    if (!isGcash && /^cash$/i.test(method)) label = isPickup ? 'Cash on Pickup' : 'Cash on Delivery';
    $('tm-payment').textContent = label;

    var showGcash = isGcash && (!blank(o.paymentReference) || !blank(o.paymentProof));
    $('tm-gcash-wrap').style.display = showGcash ? '' : 'none';
    if (showGcash) {
      $('tm-reference').textContent = blank(o.paymentReference) ? '\u2014' : o.paymentReference;
      var link = $('tm-proof-link');
      if (!blank(o.paymentProof)) {
        var url = BASE + 'uploads/payments/' + encodeURIComponent(o.paymentProof);
        $('tm-proof-img').src = url;
        link.href = url;
        link.style.display = 'inline-block';
      } else {
        link.style.display = 'none';
      }
    }

    // Items
    var items = Array.isArray(o.items) ? o.items : [];
    if (items.length) {
      $('tm-items').innerHTML = items.map(function (it) {
        var qty = parseInt(it.QUANTITY, 10) || 1;
        var price = parseFloat(it.PRICE) || 0;
        var line = it.SUB_TOTAL != null ? parseFloat(it.SUB_TOTAL) : qty * price;
        var thumb = it.IMAGE
          ? '<img class="tm-thumb" alt="" src="' + esc(BASE + 'uploads/' + it.IMAGE) + '">'
          : '<span class="tm-thumb" aria-hidden="true"></span>';
        return '<div class="tm-item">' +
          '<div class="tm-item-main">' + thumb +
            '<div><div class="tm-item-name">' + esc(it.NAME) + '</div>' +
            '<div class="tm-item-each">' + peso(price) + ' each</div></div>' +
          '</div>' +
          '<div class="tm-item-qty">\u00D7' + qty + '</div>' +
          '<div class="tm-item-total">' + peso(line) + '</div>' +
        '</div>';
      }).join('');
    } else {
      $('tm-items').innerHTML = '<div class="tm-empty">' +
        (blank(o.itemsSummary) ? 'No item details available.' : esc(o.itemsSummary)) + '</div>';
    }

    // Totals
    var html = '<div class="tm-row"><span>Subtotal</span><span>' + peso(o.subtotal) + '</span></div>';
    if (!isPickup && Number(o.deliveryFee) > 0) {
      html += '<div class="tm-row"><span>Delivery Fee</span><span>' + peso(o.deliveryFee) + '</span></div>';
    }
    html += '<div class="tm-row tm-grand"><span>Total</span><span>' + peso(o.total) + '</span></div>';
    $('tm-totals').innerHTML = html;

    // Additional payments already sent
    var extras = Array.isArray(o.extraPaymentList) ? o.extraPaymentList : [];
    $('tm-extra-wrap').style.display = extras.length ? '' : 'none';
    $('tm-extra-list').innerHTML = extras.map(function (p, i) {
      var proof = blank(p.proof) ? '' :
        '<br><a class="tm-proof" href="' + esc(BASE + 'uploads/payments/' + p.proof) + '" target="_blank" rel="noopener">' +
        '<img alt="Payment screenshot" src="' + esc(BASE + 'uploads/payments/' + p.proof) + '"></a>';
      return '<div class="tm-extra-item"><strong>Payment ' + (i + 2) + '</strong>' +
        (p.date ? ' \u00B7 ' + esc(p.date) : '') + ' \u00B7 Ref: ' + esc(blank(p.reference) ? '\u2014' : p.reference) + proof + '</div>';
    }).join('');

    // Add another payment: Pending GCash orders only
    currentOrderId = parseInt(o.orderId, 10) || 0;
    var canAdd = String(o.statusKey || o.status || '').toLowerCase() === 'pending' && isGcash;
    $('tm-addpay-wrap').style.display = canAdd ? '' : 'none';
    $('tm-addpay-ref').value = '';
    $('tm-addpay-proof').value = '';
    $('tm-addpay-preview').style.display = 'none';
    $('tm-addpay-error').style.display = 'none';
    $('tm-addpay-submit').disabled = false;
    addBusy = false;

    // Notes
    var hasNotes = !blank(o.notes);
    $('tm-notes-wrap').style.display = hasNotes ? '' : 'none';
    $('tm-notes').textContent = hasNotes ? o.notes : '';
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
    $('tm-close').focus();
  }

  function close() {
    backdrop.classList.remove('open');
    backdrop.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    if (lastFocused && typeof lastFocused.focus === 'function') lastFocused.focus();
  }

  document.addEventListener('click', function (e) {
    var card = e.target.closest('#tracking-table-body .order-card');
    if (!card) return;
    open(card);
    // The card's "Add Payment" button opens this modal at the payment section.
    if (e.target.closest('.order-card-addpay')) {
      setTimeout(function () {
        var w = $('tm-addpay-wrap');
        if (w.style.display !== 'none') { w.scrollIntoView({ block: 'center' }); $('tm-addpay-ref').focus({ preventScroll: true }); }
      }, 0);
    }
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && backdrop.classList.contains('open')) { close(); return; }
    if (e.key !== 'Enter' && e.key !== ' ') return;
    var card = e.target.closest && e.target.closest('#tracking-table-body .order-card');
    if (card) { e.preventDefault(); open(card); }
  });
  $('tm-close').addEventListener('click', close);

  function addError(msg) { var el = $('tm-addpay-error'); el.textContent = msg; el.style.display = msg ? 'block' : 'none'; }

  $('tm-addpay-proof').addEventListener('change', function () {
    var file = this.files && this.files[0];
    if (!file) { $('tm-addpay-preview').style.display = 'none'; return; }
    var reader = new FileReader();
    reader.onload = function (ev) {
      $('tm-addpay-preview-img').src = ev.target.result;
      $('tm-addpay-preview').style.display = '';
    };
    reader.readAsDataURL(file);
  });

  $('tm-addpay-submit').addEventListener('click', function () {
    if (!currentOrderId || addBusy) return;
    var ref = $('tm-addpay-ref').value.trim();
    var file = $('tm-addpay-proof').files && $('tm-addpay-proof').files[0];
    if (!ref || !file) { addError('Please enter the reference number and attach your screenshot.'); return; }

    addBusy = true; this.disabled = true; addError('');
    var btn = this;
    var fd = new FormData();
    fd.append('order_id', currentOrderId);
    fd.append('gcashReference', ref);
    fd.append('gcashProof', file);

    fetch(BASE + 'orders/add-payment', { method: 'POST', body: fd })
      .then(function (res) { return res.json(); })
      .then(function (res) {
        if (res.success) { window.location.reload(); return; }
        addBusy = false; btn.disabled = false;
        addError(res.message || 'Could not submit your payment. Please try again.');
      })
      .catch(function () {
        addBusy = false; btn.disabled = false;
        addError('Something went wrong. Please try again.');
      });
  });
  backdrop.addEventListener('click', function (e) { if (e.target === backdrop) close(); });
})();
</script>