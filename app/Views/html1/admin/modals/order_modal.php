<?php
/**
 * Order Details modal for the Orders page.
 * Included from orders.php. A single shared instance -- js/admin/orders.js
 * fills it from the clicked row's `data-order` JSON and opens it.
 *
 * Every id below is read by orders.js. If #order-modal-backdrop is missing,
 * the page's row actions (Mark Preparing / Delivering / Complete / Cancel)
 * used to silently stop working.
 */
?>
<div id="order-modal-backdrop" class="admin-modal-backdrop" style="display:none;">
  <div class="admin-modal" role="dialog" aria-modal="true" aria-labelledby="order-modal-title">
    <div class="admin-modal-head">
      <div>
        <div class="admin-modal-eyebrow">Order Details</div>
        <h3 id="order-modal-title"></h3>
      </div>
      <button type="button" class="admin-modal-close" id="order-modal-close" aria-label="Close">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
      </button>
    </div>
    <div class="admin-modal-body">
      <div class="admin-modal-status-row">
        <span id="order-modal-status" class="status"></span>
        <span id="order-modal-placed" class="admin-modal-placed"></span>
      </div>

      <div class="admin-modal-section">
        <div class="admin-modal-label">Customer</div>
        <div class="om-customer-row">
          <div id="order-modal-customer" class="admin-modal-text"></div>
          <div id="order-modal-phone" class="admin-modal-subtext"></div>
        </div>
      </div>

      <div class="admin-modal-section">
        <div class="admin-modal-label" id="order-modal-fulfillment-label">Delivery Address</div>
        <div id="order-modal-fulfillment" class="admin-modal-text"></div>
      </div>

      <div class="admin-modal-section">
        <div class="admin-modal-label">Payment Method</div>
        <div id="order-modal-payment" class="admin-modal-text"></div>
      </div>

      <div class="admin-modal-section" id="order-modal-gcash-wrap" style="display:none;">
        <div class="admin-modal-label">GCash Reference Number</div>
        <div id="order-modal-gcash-reference" class="admin-modal-text"></div>
        <a id="order-modal-proof-link" href="#" target="_blank" rel="noopener">
          <img id="order-modal-proof-img" alt="Proof of payment">
        </a>
        <div id="order-modal-extra-wrap" style="display:none;">
          <div class="admin-modal-label">Additional Payments</div>
          <div id="order-modal-extra-list"></div>
        </div>
      </div>

      <div class="admin-modal-section">
        <div class="admin-modal-label">Items</div>
        <div id="order-modal-items" class="om-items"></div>
      </div>

      <div class="admin-modal-totals" id="order-modal-totals"></div>

      <div class="admin-modal-section" id="order-modal-notes-wrap" style="display:none;">
        <div class="admin-modal-label">Special Instructions</div>
        <div id="order-modal-notes" class="admin-modal-text"></div>
      </div>
    </div>
  </div>
</div>