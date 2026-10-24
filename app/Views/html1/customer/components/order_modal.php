<?php
/**
 * Order Details modal for the Orders page.
 * Included from orders.php. Populated entirely by js/admin/orders.js,
 * which reads the `data-order` JSON attached to each row in
 * #orders-table-body and fills these elements in on click.
 *
 * Base modal shell/typography comes from css/admin_css/modal.css.
 * css/customer_css/order_modal.css only adds what modal.css doesn't already
 * provide (the two-column info layout, the GCash/notes card
 * treatments, item thumbnails) and is scoped under
 * #order-modal-backdrop so it can't affect add_item_modal.php,
 * edit_item_modal.php, or cancel_modal.php, which share the same
 * base .admin-modal-* classes.
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

      <div class="om-grid admin-modal-section">
        <div class="admin-modal-section">
          <div class="admin-modal-label om-label">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="8" r="3.2" stroke="currentColor" stroke-width="1.7"/><path d="M5 19c1.1-3.5 3.9-5.2 7-5.2s5.9 1.7 7 5.2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
            Customer
          </div>
          <div id="order-modal-customer" class="admin-modal-text"></div>
          <div id="order-modal-phone" class="admin-modal-subtext"></div>
        </div>

        <div class="admin-modal-section">
          <div class="admin-modal-label om-label">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 21s-7-6.1-7-11a7 7 0 0 1 14 0c0 4.9-7 11-7 11z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><circle cx="12" cy="10" r="2.4" stroke="currentColor" stroke-width="1.5"/></svg>
            <span id="order-modal-fulfillment-label">Delivery Address</span>
          </div>
          <div id="order-modal-fulfillment" class="admin-modal-text"></div>
        </div>
      </div>

      <div class="admin-modal-section om-card">
        <div class="admin-modal-label om-label">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="2" y="6" width="20" height="12" rx="2" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="12" r="2.6" stroke="currentColor" stroke-width="1.6"/></svg>
          Payment Method
        </div>
        <div id="order-modal-payment" class="admin-modal-text"></div>
      </div>

      <div class="admin-modal-section om-card om-gcash" id="order-modal-gcash-wrap" style="display:none;">
        <div class="admin-modal-label om-label">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.6"/></svg>
          GCash Reference Number
        </div>
        <div id="order-modal-gcash-reference" class="admin-modal-text"></div>

        <div class="admin-modal-label om-label om-label-spaced">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="1.6"/><circle cx="8.5" cy="8.5" r="1.6" stroke="currentColor" stroke-width="1.4"/><path d="M21 15l-5-5-9 9" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
          Proof of Payment
        </div>
        <div class="om-proof-wrap">
          <a id="order-modal-proof-link" href="#" target="_blank" rel="noopener">
            <img id="order-modal-proof-img" alt="Proof of payment screenshot">
          </a>
          <span class="om-proof-hint">Click to view full size</span>
        </div>
      </div>

      <div class="admin-modal-section">
        <div class="admin-modal-label om-label">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg>
          Items
        </div>
        <div class="om-items" id="order-modal-items"></div>
      </div>

      <div class="admin-modal-totals" id="order-modal-totals"></div>

      <div class="admin-modal-section om-card om-notes" id="order-modal-notes-wrap" style="display:none;">
        <div class="admin-modal-label om-label">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 4h16v12H8l-4 4V4z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
          Special Instructions
        </div>
        <div id="order-modal-notes" class="admin-modal-text"></div>
      </div>

    </div>
  </div>
</div>