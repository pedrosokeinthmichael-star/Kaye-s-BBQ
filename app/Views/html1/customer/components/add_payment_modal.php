<?php
/**
 * components/add_payment_modal.php — "Add Payment" modal
 * -----------------------------------------------------------------
 * Included once from orders.php (after place_order_modal.php). Opens when
 * the customer clicks "Add Payment" on a tracking card, which order_card.php
 * shows on Pending GCash orders.
 * The customer sends the amount through GCash, enters the reference number
 * and attaches a screenshot; it POSTs to /orders/add-payment.
 * Needs $gcash (GCASH_NAME, GCASH_NUMBER, GCASH_QR) in scope, same as
 * place_order_modal.php. Uses the shared .modal-overlay styles.
 */
?>
<div class="modal-overlay" id="add-payment-modal" role="dialog" aria-modal="true" aria-labelledby="add-payment-title">
  <div class="modal-box ap-box">
    <div class="modal-header">
      <h3 id="add-payment-title">Add Payment</h3>
      <button type="button" class="modal-close" id="add-payment-close" aria-label="Close">&times;</button>
    </div>
    <div class="modal-body">
      <p class="ap-order-line">Order <strong id="add-payment-order"></strong></p>
      <div class="ap-summary">
        <div class="ap-items" id="add-payment-items"></div>
        <div class="ap-total-row"><span>Order total</span><span id="add-payment-total"></span></div>
        <div class="ap-paid" id="add-payment-paid"></div>
      </div>
      <p class="ap-desc">Send your additional payment through GCash, then enter the reference number and attach the screenshot below.</p>

      <div class="ap-qr-wrap">
        <div class="ap-qr-box">
          <?php if ( ! empty( $gcash['GCASH_QR'] ) ) : ?>
            <img class="ap-qr-img" src="<?=base_url('uploads/' . $gcash['GCASH_QR'])?>" alt="GCash QR Code">
          <?php else : ?>
            <div class="ap-qr-empty">QR code not set up yet</div>
          <?php endif; ?>
        </div>
        <?php if ( ! empty( $gcash['GCASH_NAME'] ) || ! empty( $gcash['GCASH_NUMBER'] ) ) : ?>
          <p class="ap-gcash-name"><?= esc( $gcash['GCASH_NAME'] ?? '' ) ?></p>
          <p class="ap-gcash-number"><?= esc( $gcash['GCASH_NUMBER'] ?? '' ) ?></p>
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label class="form-label" for="add-payment-reference">GCash Reference Number</label>
        <input type="text" id="add-payment-reference" class="form-input" placeholder="e.g. 1234567890123">
      </div>
      <div class="form-group">
        <label class="form-label" for="add-payment-proof">Proof of Payment (Screenshot)</label>
        <input type="file" id="add-payment-proof" class="form-input" accept="image/*">
        <div class="ap-preview" id="add-payment-preview" style="display:none;">
          <img class="ap-preview-img" id="add-payment-preview-img" src="" alt="Proof of payment preview">
        </div>
      </div>
      <p class="ap-error" id="add-payment-error" role="alert" style="display:none;"></p>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-primary btn-full" id="add-payment-submit">Submit Payment</button>
    </div>
  </div>
</div>

<script>
(function () {
  var modal = document.getElementById('add-payment-modal');
  var list = document.getElementById('tracking-table-body');
  if (!modal || !list) return;

  var BASE = (window.APP_BASE_URL || '/');
  if (BASE.slice(-1) !== '/') BASE += '/';

  function $(id) { return document.getElementById(id); }
  var refEl = $('add-payment-reference'), proofEl = $('add-payment-proof');
  var errEl = $('add-payment-error'), submitBtn = $('add-payment-submit');
  var activeId = 0, busy = false;

  function showError(msg) { errEl.textContent = msg; errEl.style.display = msg ? 'block' : 'none'; }

  function openForId(id) {
    activeId = parseInt(id, 10) || 0;
    if (!activeId) return;
    $('add-payment-order').textContent = '#KB-' + activeId;
    var o = null;
    var card = document.querySelector('.order-card .order-card-addpay[data-order-id="' + activeId + '"]');
    card = card && card.closest('.order-card');
    try { o = card ? JSON.parse(card.getAttribute('data-order')) : null; } catch (e) {}
    $('add-payment-items').textContent = o && o.itemsSummary ? o.itemsSummary : '';
    $('add-payment-total').textContent = o ? '\u20B1' + Number(o.total || 0).toLocaleString('en-PH') : '';
    var extra = o ? (parseInt(o.extraPayments, 10) || 0) : 0;
    var requested = o ? (parseFloat(o.requestAmount) || 0) : 0;
    $('add-payment-paid').textContent = requested > 0
      ? 'The shop requested a payment of \u20B1' + requested.toLocaleString('en-PH', { maximumFractionDigits: 2 }) + '. Send it through GCash below.'
      : extra > 0
      ? extra + ' extra payment' + (extra > 1 ? 's' : '') + ' already sent and waiting for verification.'
      : 'Add the payment you sent for this order. It will be checked against your order total.';
    refEl.value = ''; proofEl.value = '';
    $('add-payment-preview').style.display = 'none';
    showError('');
    submitBtn.disabled = false; busy = false;
    modal.classList.add('open');
    refEl.focus();
  }
  function openFor(btn) { openForId(btn.getAttribute('data-order-id')); }
  function closeModal() { if (busy) return; modal.classList.remove('open'); activeId = 0; }

  // Capture + stopImmediatePropagation so the click doesn't also open the
  // order-details modal underneath (same approach as cancel_order.js).
  list.addEventListener('click', function (e) {
    var btn = e.target.closest('.order-card-addpay');
    if (!btn) return;
    e.stopImmediatePropagation(); e.preventDefault();
    openFor(btn);
  }, true);
  list.addEventListener('keydown', function (e) {
    if (e.key !== 'Enter' && e.key !== ' ') return;
    if (e.target.closest && e.target.closest('.order-card-addpay')) e.stopImmediatePropagation();
  }, true);

  proofEl.addEventListener('change', function () {
    var file = proofEl.files && proofEl.files[0];
    if (!file) { $('add-payment-preview').style.display = 'none'; return; }
    var reader = new FileReader();
    reader.onload = function (ev) {
      $('add-payment-preview-img').src = ev.target.result;
      $('add-payment-preview').style.display = '';
    };
    reader.readAsDataURL(file);
  });

  submitBtn.addEventListener('click', function () {
    if (!activeId || busy) return;
    var ref = refEl.value.trim();
    var file = proofEl.files && proofEl.files[0];
    if (!ref || !file) { showError('Please enter the reference number and attach your screenshot.'); return; }

    busy = true; submitBtn.disabled = true; showError('');
    var fd = new FormData();
    fd.append('order_id', activeId);
    fd.append('gcashReference', ref);
    fd.append('gcashProof', file);

    fetch(BASE + 'orders/add-payment', { method: 'POST', body: fd })
      .then(function (res) { return res.json(); })
      .then(function (res) {
        if (res.success) { window.location.reload(); return; }
        busy = false; submitBtn.disabled = false;
        showError(res.message || 'Could not submit your payment. Please try again.');
      })
      .catch(function () {
        busy = false; submitBtn.disabled = false;
        showError('Something went wrong. Please try again.');
      });
  });

  $('add-payment-close').addEventListener('click', closeModal);
  modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && modal.classList.contains('open')) closeModal();
  });
})();
</script>