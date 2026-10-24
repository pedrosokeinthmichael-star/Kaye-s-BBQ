<?php
/**
 * History Details modal for Menu Management.
 * Included from menus.php. A single shared instance used by both the
 * Price History and Inventory tabs -- clicking a row in either
 * #price-history-body or #inventory-body opens it, filled from the
 * row's `data-history` JSON (type = 'price' | 'inventory').
 *
 * All styling for this modal lives in css/admin_css/modal.css.
 */
?>
<div id="history-modal-backdrop" class="admin-modal-backdrop" style="display:none;">
  <div class="admin-modal admin-modal-sm" role="dialog" aria-modal="true" aria-labelledby="history-modal-title">
    <div class="admin-modal-head">
      <div>
        <div class="admin-modal-eyebrow" id="history-modal-eyebrow">History Details</div>
        <h3 id="history-modal-title"></h3>
      </div>
      <button type="button" class="admin-modal-close" id="history-modal-close" aria-label="Close">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
      </button>
    </div>
    <div class="admin-modal-body">
      <div class="hm-item">
        <img id="history-modal-img" class="hm-thumb" alt="" style="display:none;">
        <span id="history-modal-img-empty" class="hm-thumb hm-thumb-empty" aria-hidden="true"></span>
        <div class="admin-modal-subtext" id="history-modal-category" style="margin:0;"></div>
      </div>
      <div class="admin-modal-totals" id="history-modal-details" style="margin-top:0;"></div>
    </div>
  </div>
</div>

<script src="<?= base_url('js/admin/history_modal.js') ?>"></script>