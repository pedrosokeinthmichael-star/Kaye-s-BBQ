<?php
/**
 * Cancel Order confirmation modal for the Orders page.
 * Included from orders.php. A single shared instance -- js/admin/orders.js
 * fills in and opens it whenever "Cancel" is clicked on an order row.
 *
 * The admin can leave an optional cancellation note (e.g. "Out of stock",
 * "Customer unreachable"). The note is sent to the server as `cancel_note`
 * along with the existing cancel request -- see the script at the bottom.
 */
?>
<div id="cancel-modal-backdrop" class="admin-modal-backdrop" style="display:none;">
  <div class="admin-modal admin-modal-sm" role="dialog" aria-modal="true" aria-labelledby="cancel-modal-title">
    <div class="admin-modal-head">
      <div>
        <div class="admin-modal-eyebrow">Cancel Order</div>
        <h3 id="cancel-modal-title"></h3>
      </div>
      <button type="button" class="admin-modal-close" id="cancel-modal-close" aria-label="Close">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
      </button>
    </div>
    <div class="admin-modal-body">
      <p class="admin-modal-text" id="cancel-modal-message"></p>
      <p class="admin-modal-note" id="cancel-modal-warning" style="display:none;"></p>

      <div class="field cancel-note-field">
        <label for="cancel-modal-note">Cancellation Note (optional)</label>
        <textarea id="cancel-modal-note" name="cancel_note" rows="3" maxlength="255" placeholder="e.g. Item sold out, customer unreachable, duplicate order"></textarea>
        <div class="cancel-note-meta">
          <span>The customer will be able to see this note.</span>
          <span id="cancel-modal-note-count">0/255</span>
        </div>
      </div>

      <div class="admin-modal-confirm-actions">
        <button type="button" class="btn btn-outline" id="cancel-modal-keep">Keep Order</button>
        <button type="button" class="btn btn-danger-solid" id="cancel-modal-confirm">Cancel Order</button>
      </div>
    </div>
  </div>
</div>

<script src="<?= base_url('js/admin/cancel_modal.js') ?>"></script>