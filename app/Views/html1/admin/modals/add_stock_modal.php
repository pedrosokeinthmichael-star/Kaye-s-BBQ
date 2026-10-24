<?php
/**
 * Add Stock modal for Menu Management.
 * Included from menus.php. Relies on the same variable/function
 * scope as the parent view (site_url(), esc(), etc.), and on
 * #menu-manage-body existing in the page that includes it.
 */
?>
<div id="add-stock-modal-backdrop" class="admin-modal-backdrop" style="display:none;">
  <div class="admin-modal" role="dialog" aria-modal="true" aria-labelledby="add-stock-modal-title">
    <div class="admin-modal-head">
      <div>
        <div class="admin-modal-eyebrow">Menu Management</div>
        <h3 id="add-stock-modal-title">Add Stock</h3>
      </div>
      <button type="button" class="admin-modal-close" id="add-stock-modal-close" aria-label="Close">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
      </button>
    </div>
    <div class="admin-modal-body">
      <form id="add-stock-form" method="post" data-update-url="<?= esc( site_url( 'admin/menus/update' ), 'attr' ) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="formAction" value="add_stock">

        <div class="admin-form-section admin-form-row admin-form-locked">
          <div class="field">
            <label for="as-name">Item Name <span class="admin-modal-subtext" style="margin:0;">(locked)</span></label>
            <input type="text" id="as-name" placeholder="e.g. Pork Belly Skewer" readonly>
          </div>
          <div class="field">
            <label for="as-current-stock">Current Stock <span class="admin-modal-subtext" style="margin:0;">(locked)</span></label>
            <input type="text" id="as-current-stock" readonly>
          </div>
        </div>

        <div class="admin-form-section admin-form-row">
          <div class="field">
            <label for="as-add">Add Stock</label>
            <input type="number" id="as-add" name="menuAddStock" min="1" step="1" placeholder="e.g. 20" required>
          </div>
          <div class="field">
            <label>New Total</label>
            <input type="text" id="as-new-total" readonly>
          </div>
        </div>

        <span class="form-error" id="add-stock-error"></span>

        <div class="admin-form-footer">
          <div class="form-actions" style="margin-top:0;">
            <button type="submit" class="btn btn-primary">
              <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><line x1="12" y1="5" x2="12" y2="19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="5" y1="12" x2="19" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
              Add Stock
            </button>
            <button type="button" class="btn btn-outline" id="add-stock-cancel-btn">Cancel</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="<?= base_url('js/admin/add_stock.js') ?>"></script>