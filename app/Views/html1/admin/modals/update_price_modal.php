<?php
/**
 * Update Price modal for Menu Management.
 * Included from menus.php. Relies on the same variable/function
 * scope as the parent view (site_url(), esc(), etc.), and on
 * #menu-manage-body existing in the page that includes it.
 */
?>
<div id="update-price-modal-backdrop" class="admin-modal-backdrop" style="display:none;">
  <div class="admin-modal" role="dialog" aria-modal="true" aria-labelledby="update-price-modal-title">
    <div class="admin-modal-head">
      <div>
        <div class="admin-modal-eyebrow">Menu Management</div>
        <h3 id="update-price-modal-title">Update Price</h3>
      </div>
      <button type="button" class="admin-modal-close" id="update-price-modal-close" aria-label="Close">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
      </button>
    </div>
    <div class="admin-modal-body">
      <form id="update-price-form" method="post" data-update-url="<?= esc( site_url( 'admin/menus/update' ), 'attr' ) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="formAction" value="update_price">

        <div class="admin-form-section admin-form-row admin-form-locked">
          <div class="field">
            <label for="up-name">Item Name <span class="admin-modal-subtext" style="margin:0;">(locked)</span></label>
            <input type="text" id="up-name" placeholder="e.g. Pork Belly Skewer" readonly>
          </div>
          <div class="field">
            <label for="up-category">Category <span class="admin-modal-subtext" style="margin:0;">(locked)</span></label>
            <input type="text" id="up-category" placeholder="e.g. Skewers &amp; Isaw" readonly>
          </div>
        </div>

        <div class="admin-form-section admin-form-row">
          <div class="field">
            <label for="up-price">Price (₱)</label>
            <input type="number" id="up-price" name="menuPrice" min="0" step="0.01" placeholder="0.00" required>
          </div>
        </div>

        <span class="form-error" id="update-price-error"></span>

        <div class="admin-form-footer">
          <label class="switch-wrap">
            <span class="switch"><input type="checkbox" id="up-available" name="menuAvailable" value="1" checked><span class="switch-track"></span></span>
            <span class="switch-caption" id="up-available-caption">Available</span>
          </label>
          <div class="form-actions" style="margin-top:0;">
            <button type="submit" class="btn btn-primary">
              <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              Save Changes
            </button>
            <button type="button" class="btn btn-outline" id="update-price-cancel-btn">Cancel</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="<?= base_url('js/admin/update_price.js') ?>"></script>