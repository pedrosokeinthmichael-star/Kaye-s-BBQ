<?php
/**
 * components/place_order_modal.php — "Confirm Your Order" modal
 * -----------------------------------------------------------------
 * Included from orders.php:
 *   include __DIR__ . '/components/place_order_modal.php';
 *
 * Expects $user (associative array with EMAIL, PHONE_NO, etc.),
 * $barangay_list (BarangayModel::findAll() — BARANGAY_ID,
 * BARANGAY_NAME, DELIVERY_FEE), and $gcash (GcashModel's current
 * record — GCASH_NAME, GCASH_NUMBER, GCASH_QR, or null if the admin
 * hasn't set one up yet) to be in scope, same as the rest of
 * orders.php. The item list inside #place-order-items is populated
 * client-side by placeorder.js.
 */

// Admin switches (Orders > Order Settings), passed in by Home::orders().
// Default to ON so the page still works if a flag isn't passed.
$deliveryOn = $delivery_enabled ?? true;
$gcashOn    = $gcash_enabled ?? true;
?>
<style>
  .chip.is-disabled { opacity: .45; cursor: not-allowed; text-decoration: line-through; pointer-events: auto; }
  /* Hide the scrollbar in the Place Order modal (it still scrolls with wheel/touch/keys). */
  #place-order-modal, #place-order-modal .modal-box, #place-order-modal .modal-body { scrollbar-width: none; -ms-overflow-style: none; }
  #place-order-modal::-webkit-scrollbar, #place-order-modal .modal-box::-webkit-scrollbar, #place-order-modal .modal-body::-webkit-scrollbar { display: none; width: 0; height: 0; }
  .po-unavailable-note { margin: 6px 0 0; font-size: 13px; color: var(--brick, #C64B23); }
</style>
<div class="modal-overlay" id="place-order-modal" role="dialog" aria-modal="true" aria-labelledby="place-order-modal-title">
  <div class="modal-box place-order-box">
    <div class="modal-header">
      <h3 id="place-order-modal-title">Confirm Your Order</h3>
      <button class="modal-close" id="place-order-modal-close" aria-label="Close">&times;</button>
    </div>
    <div class="modal-body" id="place-order-modal-body">

      <!-- ============ 1. Customer Details ============ -->
      <div class="order-section" id="section-customer-details">
        <div class="order-section-title"><span class="order-section-num">1</span>Customer Details</div>

        <div class="form-group">
          <label class="form-label">Order Type</label>
          <div class="filters" id="order-type-toggle">
            <span class="chip<?= $deliveryOn ? ' active' : ' is-disabled' ?>" data-order-type="delivery" <?= $deliveryOn ? '' : 'data-disabled="1" aria-disabled="true" title="Delivery is currently unavailable"' ?>>Delivery</span>
            <span class="chip<?= $deliveryOn ? '' : ' active' ?>" data-order-type="pickup">Pickup</span>
          </div>
          <?php if ( ! $deliveryOn ) : ?>
            <p class="po-unavailable-note">Delivery is currently unavailable. Pickup only.</p>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label class="form-label" for="customer-email">Email Address</label>
          <input type="email" id="customer-email" class="form-input" placeholder="you@email.com" value="<?= esc( $user['EMAIL'] ?? '' ) ?>">
        </div>

        <div class="form-group">
          <label class="form-label" for="contact-number">Contact Number</label>
          <input type="tel" id="contact-number" class="form-input" placeholder="09XX XXX XXXX" value="<?= esc( $user['PHONE_NO'] ?? '' ) ?>">
        </div>

        <div id="delivery-address-group">
          <div class="form-group">
            <label class="form-label" for="order-barangay">Barangay <span class="po-required">*</span></label>
            <select id="order-barangay" class="form-input" required aria-required="true">
              <option value="">Select Barangay</option>
              <?php foreach ( $barangay_list ?? [] as $brgy ) : ?>
                <option value="<?= esc( $brgy['BARANGAY_ID'] ) ?>" data-fee="<?= esc( $brgy['DELIVERY_FEE'] ) ?>">
                  <?= esc( $brgy['BARANGAY_NAME'] ) ?> — ₱<?= number_format( (float) $brgy['DELIVERY_FEE'], 0 ) ?> delivery
                </option>
              <?php endforeach; ?>
            </select>
            <p class="delivery-field-error po-field-error" id="order-barangay-error" style="display:none;">Please select your barangay.</p>
          </div>

          <div class="form-group">
            <label class="form-label" for="order-address-detail">Complete Address <span class="po-required">*</span></label>
            <textarea id="order-address-detail" class="form-input" rows="2" required aria-required="true" minlength="10" maxlength="255" placeholder="House/Unit No., Street, Landmark, City"></textarea>
            <p class="delivery-field-error po-field-error" id="order-address-detail-error" style="display:none;">Please enter your complete delivery address (house/unit no., street, landmark).</p>
          </div>
        </div>
      </div>

      <!-- ============ 2. Order Details ============ -->
      <div class="order-section" id="section-order-details">
        <div class="order-section-title"><span class="order-section-num">2</span>Order Details</div>

        <div class="cart-lines" id="place-order-items">
          <!-- populated from the cart on open, incl. qty steppers + remove -->
        </div>

        <div class="form-group po-notes-group">
          <label class="form-label" for="order-notes">Order Notes (optional)</label>
          <textarea id="order-notes" class="form-input" rows="2" placeholder="e.g. less spicy, ring the doorbell twice"></textarea>
        </div>
      </div>

      <!-- ============ 3. Payment ============ -->
      <div class="order-section po-section-last" id="section-payment">
        <div class="order-section-title"><span class="order-section-num">3</span>Payment</div>

        <div class="form-group">
          <label class="form-label">Payment Method</label>
          <div class="filters" id="payment-method-toggle">
            <span class="chip active" data-payment="cod">Cash</span>
            <span class="chip<?= $gcashOn ? '' : ' is-disabled' ?>" data-payment="gcash" <?= $gcashOn ? '' : 'data-disabled="1" aria-disabled="true" title="GCash is currently unavailable"' ?>>GCash</span>
          </div>
          <?php if ( ! $gcashOn ) : ?>
            <p class="po-unavailable-note">GCash payments are currently unavailable. Cash only.</p>
          <?php endif; ?>
        </div>

        <div id="gcash-payment-group" style="display:none;">
          <div class="form-group po-qr-group">
            <label class="form-label po-label-left">Scan to Pay</label>
            <div class="po-qr-box">
              <?php if ( ! empty( $gcash['GCASH_QR'] ) ) : ?>
                <img class="po-qr-img" src="<?=base_url('uploads/' . $gcash['GCASH_QR'])?>" alt="GCash QR Code">
              <?php else : ?>
                <div class="po-qr-empty">QR code not set up yet</div>
              <?php endif; ?>
            </div>
            <?php if ( ! empty( $gcash['GCASH_NAME'] ) || ! empty( $gcash['GCASH_NUMBER'] ) ) : ?>
              <p class="po-gcash-name"><?= esc( $gcash['GCASH_NAME'] ?? '' ) ?></p>
              <p class="po-gcash-number"><?= esc( $gcash['GCASH_NUMBER'] ?? '' ) ?></p>
            <?php endif; ?>
            <p class="po-gcash-hint">Open your GCash app, scan the code above, then fill in the details below.</p>
          </div>

          <div class="form-group">
            <label class="form-label" for="gcash-reference">GCash Reference Number</label>
            <input type="text" id="gcash-reference" class="form-input" placeholder="e.g. 1234567890123">
          </div>

          <div class="form-group">
            <label class="form-label" for="gcash-proof">Proof of Payment (Screenshot)</label>
            <input type="file" id="gcash-proof" class="form-input" accept="image/*">
            <div class="po-proof-preview" id="gcash-proof-preview" style="display:none;">
              <img class="po-proof-img" id="gcash-proof-preview-img" src="" alt="Proof of payment preview">
            </div>
            <p class="po-proof-error" id="gcash-proof-error" style="display:none;">Please attach a screenshot of your payment.</p>
          </div>
        </div>

        <div class="summary-line"><span>Subtotal</span><span id="place-order-subtotal">₱0</span></div>
        <div class="summary-line" id="place-order-delivery-fee-row"><span>Delivery Fee</span><span id="place-order-delivery-fee">₱50</span></div>
        <div class="summary-line total po-total-line">
          <span>Total</span>
          <span id="place-order-total">₱0</span>
        </div>
      </div>

    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-primary btn-full" id="confirm-order-btn">Confirm Order</button>
    </div>
  </div>
</div>

<script>
/**
 * Delivery details are required for Delivery orders. Runs in the capture
 * phase on the document so it fires before placeorder.js's own click
 * handler on #confirm-order-btn, and blocks it when details are missing.
 * Pickup orders are not affected.
 */
(function () {
  var MIN_ADDRESS_LENGTH = 10;

  function el(id) { return document.getElementById(id); }

  function isDelivery() {
    var active = document.querySelector('#order-type-toggle .chip.active');
    return !active || active.getAttribute('data-order-type') === 'delivery';
  }

  function setError(inputId, errorId, message) {
    var input = el(inputId), err = el(errorId);
    if (!input || !err) return;
    if (message) {
      err.textContent = message;
      err.style.display = 'block';
      input.style.borderColor = 'var(--brick, #C64B23)';
    } else {
      err.style.display = 'none';
      input.style.borderColor = '';
    }
  }

  function validateDelivery() {
    var barangay = el('order-barangay');
    var detail = el('order-address-detail');
    var firstBad = null;

    var barangayOk = !!(barangay && barangay.value);
    setError('order-barangay', 'order-barangay-error', barangayOk ? '' : 'Please select your barangay.');
    if (!barangayOk) firstBad = barangay;

    var text = detail ? detail.value.trim() : '';
    var detailOk = text.length >= MIN_ADDRESS_LENGTH;
    setError('order-address-detail', 'order-address-detail-error', detailOk ? '' :
      (text.length === 0
        ? 'Please enter your complete delivery address (house/unit no., street, landmark).'
        : 'That address looks too short. Please include house/unit no., street and landmark.'));
    if (!detailOk && !firstBad) firstBad = detail;

    if (firstBad) {
      firstBad.scrollIntoView({ behavior: 'smooth', block: 'center' });
      firstBad.focus({ preventScroll: true });
      return false;
    }
    return true;
  }

  document.addEventListener('click', function (e) {
    if (!e.target.closest('#confirm-order-btn')) return;
    if (!isDelivery()) return;
    if (!validateDelivery()) {
      e.preventDefault();
      e.stopImmediatePropagation();
    }
  }, true);

  // Clear errors as the customer fixes them.
  document.addEventListener('input', function (e) {
    if (e.target.id === 'order-address-detail') setError('order-address-detail', 'order-address-detail-error', '');
  });
  document.addEventListener('change', function (e) {
    if (e.target.id === 'order-barangay') setError('order-barangay', 'order-barangay-error', '');
  });
})();
</script>

<script>
/**
 * Disabled options (Delivery / GCash switched off by the admin) can't be chosen.
 * The capture-phase listener runs before placeorder.js's own chip handlers, so
 * a click on a disabled chip does nothing. On load (and whenever the modal is
 * opened) we also click the allowed option (Pickup / Cash) if the current
 * selection is a disabled one, so placeorder.js hides the address fields,
 * drops the delivery fee, and hides the GCash section.
 */
(function () {
  document.addEventListener('click', function (e) {
    var chip = e.target.closest && e.target.closest('.chip[data-disabled="1"]');
    if (!chip) return;
    e.preventDefault();
    e.stopImmediatePropagation();
  }, true);

  function fixGroup(groupId, fallbackSelector) {
    var group = document.getElementById(groupId);
    if (!group) return;
    var active = group.querySelector('.chip.active');
    if (active && active.getAttribute('data-disabled') !== '1') return;
    var fallback = group.querySelector(fallbackSelector);
    if (fallback) fallback.click();
  }

  function sync() {
    fixGroup('order-type-toggle', '[data-order-type="pickup"]');
    fixGroup('payment-method-toggle', '[data-payment="cod"]');
  }

  function init() {
    sync();
    var modal = document.getElementById('place-order-modal');
    if (modal && window.MutationObserver) {
      new MutationObserver(sync).observe(modal, { attributes: true, attributeFilter: ['class', 'style'] });
    }
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
</script>