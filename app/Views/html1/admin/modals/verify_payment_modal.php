<?php
/**
 * Verify Payment modal for the Orders page (Pending GCash orders).
 * Included from orders.php. Opened by the "Verify Payment" button, which
 * is the row's normal mark-preparing button flagged with data-verify; this
 * file intercepts its click and only lets it reach js/admin/orders.js
 * after the payment has been verified.
 *
 * The admin checks the GCash screenshot, types the amount actually
 * received, and the modal shows what applies:
 *   - equal  -> tick the confirmation, "Verify & Mark Preparing"
 *   - short  -> "Request payment": a ready-to-send message (text, email
 *               or copy). The order stays Pending.
 *   - over   -> "Refund payment": amount + number to send it back to,
 *               a message to the customer, then "Refund & Mark Preparing"
 *               once the admin ticks that the refund was sent.
 * Three buttons sit at the bottom (left to right):
 *   - Request Payment-> opens the request-payment message on demand, even
 *                       before an amount is typed (e.g. no screenshot, or
 *                       nothing received). Enabled whenever the amount is
 *                       blank, 0 or short. The order stays Pending.
 *   - Refund         -> refund the full amount received through GCash
 *                       (panel with number + message + "I sent it" tick),
 *                       then hands off to the cancel modal
 *   - Verify Payment -> enabled once the amount matches (or the overpayment
 *                       was refunded) and the admin ticked the confirmation
 *
 * Requests are messages (text / email / copy). The customer can answer with
 * "Add Payment" on their tracking card, which adds another payment row; those
 * show here as "Additional payments". Refunds are manual GCash transfers and
 * nothing is stored about them.
 * Needs $gcash (optional) in scope for the shop's GCash name/number.
 */
$g = ( isset( $gcash ) && is_array( $gcash ) ) ? $gcash : [];
$shopGcash = [
  'name'   => (string) ( $g['GCASH_NAME'] ?? '' ),
  'number' => (string) ( $g['GCASH_NUMBER'] ?? '' ),
];
?>
<div id="verify-modal-backdrop" class="admin-modal-backdrop" style="display:none;"
     data-shop-name="<?= esc( $shopGcash['name'], 'attr' ) ?>"
     data-shop-number="<?= esc( $shopGcash['number'], 'attr' ) ?>"
     data-request-base="<?= esc( site_url( 'admin/orders' ), 'attr' ) ?>"
     data-csrf-name="<?= esc( csrf_token(), 'attr' ) ?>"
     data-csrf-hash="<?= esc( csrf_hash(), 'attr' ) ?>">
  <div class="admin-modal" role="dialog" aria-modal="true" aria-labelledby="verify-modal-title">
    <div class="admin-modal-head">
      <div>
        <div class="admin-modal-eyebrow">Verify Payment &middot; GCash</div>
        <h3 id="verify-modal-title"></h3>
      </div>
      <button type="button" class="admin-modal-close" id="verify-modal-close" aria-label="Close">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
      </button>
    </div>
    <div class="admin-modal-body">

      <div class="admin-modal-totals" style="margin-top:0;border-top:0;padding-top:0;">
        <div class="admin-modal-totals-row"><span>Customer</span><span id="vp-customer"></span></div>
        <div class="admin-modal-totals-row"><span>Contact</span><span id="vp-contact"></span></div>
        <div class="admin-modal-totals-row"><span>GCash reference</span><span id="vp-reference"></span></div>
        <div class="admin-modal-totals-row admin-modal-totals-grand"><span>Order total</span><span id="vp-total"></span></div>
      </div>

      <div class="admin-modal-label" style="margin-top:14px;">Proof of payment</div>
      <a class="vp-proof" id="vp-proof-link" href="#" target="_blank" rel="noopener" style="display:none;">
        <img id="vp-proof-img" alt="Proof of payment screenshot">
      </a>
      <div class="vp-hint" id="vp-proof-hint" style="display:none;">Click the screenshot to open it full size.</div>
      <p class="admin-modal-note admin-modal-note-warning" id="vp-no-proof" style="display:none;">No proof of payment screenshot is on file for this order.</p>

      <div class="admin-modal-note admin-modal-note-info" id="vp-extra" style="display:none;margin-top:12px;">
        <strong>Additional payments sent by the customer</strong>
        <div id="vp-extra-list"></div>
      </div>

      <div class="field vp-field">
        <label for="vp-received">Amount received (&#8369;)</label>
        <input type="number" id="vp-received" min="0" step="0.01" placeholder="0.00">
        <div class="vp-hint">Enter the amount shown in the screenshot.</div>
      </div>

      <p class="admin-modal-note vp-result" id="vp-result" style="display:none;"></p>

      <!-- Amount matches -->
      <label class="vp-check" id="vp-panel-match" style="display:none;">
        <input type="checkbox" id="vp-confirm">
        <span>I checked the GCash screenshot and reference number, and the amount received matches the order total.</span>
      </label>

      <!-- Amount is short: request payment -->
      <div class="vp-panel" id="vp-panel-short" style="display:none;">
        <div class="admin-modal-label">Request payment</div>
        <div class="field"><textarea id="vp-msg-short" rows="4"></textarea></div>
        <div class="vp-msg-actions">
          <a class="btn btn-outline btn-sm" id="vp-sms-short" href="#">Text customer</a>
          <a class="btn btn-outline btn-sm" id="vp-mail-short" href="#">Email customer</a>
          <button type="button" class="btn btn-outline btn-sm" id="vp-copy-short">Copy message</button>
        </div>
        <div class="vp-hint">The order stays Pending. The customer can use <strong>Add Payment</strong> on their order to send the balance. It will show up above, then verify again.</div>
        <div class="vp-hint vp-status" id="vp-request-status" style="display:none;"></div>
      </div>

      <!-- Amount is over: refund payment -->
      <div class="vp-panel" id="vp-panel-over" style="display:none;">
        <div class="admin-modal-label">Refund payment</div>
        <div class="vp-row"><span>Send back through GCash</span><strong id="vp-refund-amount"></strong></div>
        <div class="vp-row"><span>Customer</span><strong id="vp-refund-name"></strong></div>
        <div class="vp-row">
          <span>Customer's GCash number</span>
          <span class="vp-copyable"><strong id="vp-refund-to"></strong> <button type="button" class="btn btn-outline btn-sm" id="vp-copy-number">Copy</button></span>
        </div>
        <div class="vp-refund-details">
          <div class="field">
            <label for="vp-refund-ref">Your GCash reference no.</label>
            <input type="text" id="vp-refund-ref" maxlength="30" autocomplete="off" placeholder="Reference no. on your GCash receipt">
          </div>
          <div class="field">
            <label for="vp-refund-proof">Proof of refund (screenshot)</label>
            <div class="admin-form-photo">
              <input type="file" id="vp-refund-proof" accept="image/*">
              <div class="admin-form-photo-preview" id="vp-refund-preview" style="display:none;"><img alt="Refund proof preview"></div>
            </div>
          </div>
          <div class="vp-hint">Both are required before you can continue.</div>
        </div>
        <div class="field" style="margin-top:12px;"><textarea id="vp-msg-over" rows="3"></textarea></div>
        <div class="vp-msg-actions">
          <a class="btn btn-outline btn-sm" id="vp-sms-over" href="#">Text customer</a>
          <a class="btn btn-outline btn-sm" id="vp-mail-over" href="#">Email customer</a>
          <button type="button" class="btn btn-outline btn-sm" id="vp-copy-over">Copy message</button>
        </div>
        <label class="vp-check">
          <input type="checkbox" id="vp-refund-sent">
          <span>I sent the refund through GCash.</span>
        </label>
      </div>

      <!-- Refund the full amount received (then cancel the order) -->
      <div class="vp-panel" id="vp-panel-full" style="display:none;">
        <div class="admin-modal-label">Refund &amp; cancel</div>
        <div class="vp-row"><span>Send back through GCash</span><strong id="vp-full-amount"></strong></div>
        <div class="vp-row"><span>Customer</span><strong id="vp-full-name"></strong></div>
        <div class="vp-row">
          <span>Customer's GCash number</span>
          <span class="vp-copyable"><strong id="vp-full-to"></strong> <button type="button" class="btn btn-outline btn-sm" id="vp-copy-full-number">Copy</button></span>
        </div>
        <div class="vp-refund-details">
          <div class="field">
            <label for="vp-full-ref">Your GCash reference no.</label>
            <input type="text" id="vp-full-ref" maxlength="30" autocomplete="off" placeholder="Reference no. on your GCash receipt">
          </div>
          <div class="field">
            <label for="vp-full-proof">Proof of refund (screenshot)</label>
            <div class="admin-form-photo">
              <input type="file" id="vp-full-proof" accept="image/*">
              <div class="admin-form-photo-preview" id="vp-full-preview" style="display:none;"><img alt="Refund proof preview"></div>
            </div>
          </div>
          <div class="vp-hint">Both are required before you can continue.</div>
        </div>
        <div class="field" style="margin-top:12px;"><textarea id="vp-msg-full" rows="3"></textarea></div>
        <div class="vp-msg-actions">
          <a class="btn btn-outline btn-sm" id="vp-sms-full" href="#">Text customer</a>
          <a class="btn btn-outline btn-sm" id="vp-mail-full" href="#">Email customer</a>
          <button type="button" class="btn btn-outline btn-sm" id="vp-copy-full">Copy message</button>
          <button type="button" class="btn btn-outline btn-sm" id="vp-full-back">Never mind</button>
        </div>
        <label class="vp-check">
          <input type="checkbox" id="vp-full-sent">
          <span>I sent the full refund through GCash. Then press Refund to cancel the order.</span>
        </label>
      </div>

      <div class="vp-hint" id="vp-cancel-hint" style="margin-top:14px;"></div>
      <div class="vp-hint vp-status is-error" id="vp-save-error" style="display:none;"></div>

      <div class="vp-actions">
        <button type="button" class="btn btn-outline" id="vp-request-btn">Request Payment</button>
        <button type="button" class="btn btn-outline" id="vp-refund-btn">Refund</button>
        <button type="button" class="btn btn-primary" id="vp-submit" disabled>Verify Payment</button>
      </div>

    </div>
  </div>
</div>

<!-- Confirmation shown when the admin clicks "Request Payment" (driven by verify_payment.js) -->
<div id="request-confirm-backdrop" class="admin-modal-backdrop" style="display:none;">
  <div class="admin-modal admin-modal-sm" role="alertdialog" aria-modal="true" aria-labelledby="rc-title" aria-describedby="rc-message">
    <div class="admin-modal-head">
      <div>
        <div class="admin-modal-eyebrow">Confirmation</div>
        <h3 id="rc-title">Request payment?</h3>
      </div>
      <button type="button" class="admin-modal-close" id="rc-close" aria-label="Close">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
      </button>
    </div>
    <div class="admin-modal-body">
      <div class="rc-icon" id="rc-icon" style="display:none;" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </div>
      <p class="admin-modal-text" id="rc-message"></p>
      <div class="admin-modal-confirm-actions">
        <button type="button" class="btn btn-outline" id="rc-cancel">Cancel</button>
        <button type="button" class="btn btn-primary" id="rc-confirm">Send Request</button>
      </div>
    </div>
  </div>
</div>

<script src="<?= base_url('js/admin/verify_payment.js') ?>"></script>