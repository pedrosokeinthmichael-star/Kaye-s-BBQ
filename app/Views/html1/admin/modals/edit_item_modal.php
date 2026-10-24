<?php
/**
 * Edit Item modal for Menu Management.
 * Included from menus.php. Relies on the same variable/function
 * scope as the parent view (site_url(), esc(), etc.), and on
 * #menu-manage-body existing in the page that includes it.
 */
?>
<div id="edit-item-modal-backdrop" class="admin-modal-backdrop" style="display:none;">
  <div class="admin-modal" role="dialog" aria-modal="true" aria-labelledby="edit-item-modal-title">
    <div class="admin-modal-head">
      <div>
        <div class="admin-modal-eyebrow">Menu Management</div>
        <h3 id="edit-item-modal-title">Edit Item</h3>
      </div>
      <button type="button" class="admin-modal-close" id="edit-item-modal-close" aria-label="Close">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
      </button>
    </div>
    <div class="admin-modal-body">
      <form id="edit-product-form" method="post" enctype="multipart/form-data" data-update-url="<?= esc( site_url( 'admin/menus/update' ), 'attr' ) ?>">
        <?= csrf_field() ?>

        <div class="admin-form-section admin-form-row admin-form-locked">
          <div class="field">
            <label for="e-name">Item Name <span class="admin-modal-subtext" style="margin:0;">(locked)</span></label>
            <input type="text" id="e-name" name="menuName" placeholder="e.g. Pork Belly Skewer" readonly>
          </div>
          <div class="field">
            <label for="e-category">Category <span class="admin-modal-subtext" style="margin:0;">(locked)</span></label>
            <input type="text" id="e-category"  placeholder="e.g. Skewers &amp; Isaw" readonly>
          </div>
        </div>

        <div class="admin-form-section admin-form-row">
          <div class="field">
            <label for="e-price">Price (₱)</label>
            <input type="number" id="e-price" name="menuPrice" min="0" step="0.01" placeholder="0.00" required>
          </div>
          <div class="field">
            <label for="e-stock">Stock</label>
            <input type="number" id="e-stock" name="menuStock" min="0" step="1" placeholder="Stock on hand" required>
          </div>
        </div>

        <span class="form-error" id="edit-error"></span>

        <div class="admin-form-footer">
          <label class="switch-wrap">
            <span class="switch"><input type="checkbox" id="e-available" name="menuAvailable" value="1" checked><span class="switch-track"></span></span>
            <span class="switch-caption" id="e-available-caption">Available</span>
          </label>
          <div class="form-actions" style="margin-top:0;">
            <button type="submit" class="btn btn-primary">
              <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              Save Changes
            </button>
            <button type="button" class="btn btn-outline" id="edit-item-cancel-btn">Cancel</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="<?= base_url('js/admin/edit_item.js') ?>"></script>