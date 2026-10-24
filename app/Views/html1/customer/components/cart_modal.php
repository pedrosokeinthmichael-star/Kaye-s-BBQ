<?php
/**
 * components/cart_modal.php — header cart dropdown (#cart-modal).
 * Included by components/header.php. Filled in by customer.js.
 */
?>
<div class="modal-overlay" id="cart-modal" role="dialog" aria-modal="true" aria-labelledby="cart-modal-title">
  <div class="modal-box">
    <div class="modal-header">
      <h3 id="cart-modal-title">Your Cart</h3>
      <button class="modal-close" id="cart-modal-close" aria-label="Close cart">&times;</button>
    </div>
    <div class="modal-body" id="cart-modal-body">
      <div class="modal-empty">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M3 4h2l2.4 12.2a2 2 0 0 0 2 1.8h8.2a2 2 0 0 0 2-1.6L21 8H6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
          <circle cx="10" cy="21" r="1.4" fill="currentColor"/>
          <circle cx="17" cy="21" r="1.4" fill="currentColor"/>
        </svg>
        <p>Your cart is empty</p>
        <p class="modal-empty-sub">Items you add on the Menu page will show up here.</p>
      </div>
    </div>
    <div class="modal-footer" id="cart-modal-footer">
      <a href="<?=site_url('/customer/barbeques')?>" class="btn btn-primary btn-full-center">Browse Menu</a>
    </div>
  </div>
</div>