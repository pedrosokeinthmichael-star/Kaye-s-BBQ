<?php
/**
 * components/cancel_order_modal.php — "Cancel Order" confirmation modal
 * -----------------------------------------------------------------
 * Included once from orders.php. Opened by js/customer/cancel_order.js
 * when a customer clicks Cancel on a tracking card. The script fills in
 * the order number and items summary, then POSTs to /orders/cancel.
 * Uses the shared .modal-overlay / .modal-box styles from styles.css;
 * its own styles are in css/customer_css/cancel_order_modal.css.
 */
?>
<div class="modal-overlay" id="cancel-order-modal" role="dialog" aria-modal="true" aria-labelledby="cancel-order-title">
  <div class="modal-box cancel-order-box">
    <div class="modal-header">
      <h3 id="cancel-order-title">Cancel Order</h3>
      <button type="button" class="modal-close" id="cancel-order-close" aria-label="Close">&times;</button>
    </div>
    <div class="modal-body">
      <div class="cancel-order-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.7"/>
          <path d="M12 7.5v5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
          <circle cx="12" cy="16.2" r="1" fill="currentColor"/>
        </svg>
      </div>
      <p class="cancel-order-text">Cancel order <strong id="cancel-order-id"></strong>?</p>
      <p class="cancel-order-sub">Please review the order details below before cancelling.</p>

      <div class="co-meta">
        <span id="co-status" class="status"></span>
        <span class="co-date" id="co-date"></span>
      </div>

      <div class="co-grid">
        <div>
          <div class="co-label">Order Type</div>
          <div class="co-text" id="co-type"></div>
        </div>
        <div id="co-customer-wrap">
          <div class="co-label">Customer</div>
          <div class="co-text" id="co-customer"></div>
          <div class="co-sub" id="co-contact"></div>
        </div>
      </div>

      <div class="co-card">
        <div class="co-label" id="co-fulfillment-label">Delivery Address</div>
        <div class="co-text" id="co-fulfillment"></div>
      </div>

      <div class="co-card">
        <div class="co-label">Payment Method</div>
        <div class="co-text" id="co-payment"></div>
        <div class="co-sub" id="co-reference"></div>
      </div>

      <div class="co-label co-label-items">Items</div>
      <div class="co-items" id="co-items"></div>

      <div class="co-totals" id="co-totals"></div>

      <div class="co-card co-notes" id="co-notes-wrap" style="display:none;">
        <div class="co-label">Order Notes</div>
        <div class="co-text" id="co-notes"></div>
      </div>

      <p class="cancel-order-warn">This can't be undone.</p>
      <p class="cancel-order-error" id="cancel-order-error" role="alert"></p>
    </div>
    <div class="modal-footer">
      <div class="cancel-order-actions">
        <button type="button" class="btn btn-keep" id="cancel-order-keep">Keep Order</button>
        <button type="button" class="btn btn-danger" id="cancel-order-confirm">Yes, Cancel</button>
      </div>
    </div>
  </div>
</div>