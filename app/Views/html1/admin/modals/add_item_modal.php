<?php
/**
 * Add Item modal for Menu Management.
 * Included from menus.php. Relies on the same variable/function
 * scope as the parent view (site_url(), esc(), etc.).
 */
?>
<div id="add-item-modal-backdrop" class="admin-modal-backdrop" style="display:none;"
     data-category-url="<?= esc( site_url( 'admin/categories/store' ), 'attr' ) ?>"
     data-csrf-name="<?= esc( csrf_token(), 'attr' ) ?>"
     data-csrf-hash="<?= esc( csrf_hash(), 'attr' ) ?>">
  <div class="admin-modal" role="dialog" aria-modal="true" aria-labelledby="add-item-modal-title">
    <div class="admin-modal-head">
      <div>
        <div class="admin-modal-eyebrow">Menu Management</div>
        <h3 id="add-item-modal-title">Add New Item</h3>
      </div>
      <button type="button" class="admin-modal-close" id="add-item-modal-close" aria-label="Close">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
      </button>
    </div>
    <div class="admin-modal-body">
      <form id="add-product-form" method="post" action="<?= site_url( 'admin/menus/store' ) ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="admin-form-section">
          <div class="field">
            <label for="p-name">Item Name</label>
            <input type="text" id="p-name" name="menuName" placeholder="e.g. Isaw" required>
          </div>
        </div>

        <div class="admin-form-section">
          <div class="field">
            <label for="p-category">Category</label>
            <div class="category-picker">
              <select id="p-category" name="menuCategory" required>
                <option value="">Select a category</option>
                <?php foreach ( ( $category_list ?? [] ) as $cat ) : ?>
                  <option value="<?= (int) $cat['CATEGORY_ID'] ?>"><?= esc( $cat['CATEGORY_NAME'] ) ?></option>
                <?php endforeach; ?>
              </select>
              <button type="button" class="btn btn-outline btn-sm" id="new-category-toggle" aria-expanded="false" aria-controls="new-category-row">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><line x1="12" y1="5" x2="12" y2="19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="5" y1="12" x2="19" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                New
              </button>
            </div>

            <div id="new-category-row" class="new-category-panel">
              <div class="new-category-panel-label">New category</div>
              <div class="new-category-panel-row">
                <input type="text" id="new-category-name" maxlength="30" placeholder="e.g. Skewers &amp; Isaw" autocomplete="off">
                <button type="button" class="btn btn-primary btn-sm" id="new-category-save">Add</button>
              </div>
              <div class="new-category-panel-meta">
                <span>Saved right away and available for every item.</span>
                <span id="new-category-count">0/30</span>
              </div>
            </div>
            <span class="form-error" id="new-category-error"></span>
          </div>
        </div>

        <div class="admin-form-section admin-form-row">
          <div class="field">
            <label for="p-price">Price (₱)</label>
            <input type="number" id="p-price" name="menuPrice" min="0" step="0.01" placeholder="0.00" required>
          </div>
          <div class="field">
            <label for="stock">Stock</label>
            <input type="number" id="stock" name="menuStock" min="0" step="1" placeholder="Stock on hand" required>
          </div>
        </div>

        <div class="admin-form-section">
          <div class="field">
            <label for="p-description">Description</label>
            <input type="text" id="p-description" name="menuDescription" placeholder="Short description" required>
          </div>
        </div>

        <div class="admin-form-section">
          <label for="p-image" class="admin-modal-label" style="display:block;">Photo</label>
          <div class="admin-form-photo">
            <input type="file" id="p-image" name="menuPicture" accept="image/*" required>
            <div id="p-image-preview-wrap" class="admin-form-photo-preview" style="display:none;">
              <img id="p-image-preview" src="" alt="Preview">
              <button type="button" class="btn btn-ghost btn-sm" id="p-image-remove">Remove</button>
            </div>
          </div>
        </div>

        <span class="form-error" id="add-error"></span>

        <div class="admin-form-footer">
          <label class="switch-wrap">
            <span class="switch"><input type="checkbox" id="p-available" name="menuAvailable" value="1" checked><span class="switch-track"></span></span>
            <span class="switch-caption">Available &mdash; shows up right away</span>
          </label>
          <div class="form-actions" style="margin-top:0;">
            <button type="submit" class="btn btn-primary">
              <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><line x1="12" y1="5" x2="12" y2="19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="5" y1="12" x2="19" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
              Add Item
            </button>
            <button type="button" class="btn btn-outline" id="add-item-cancel-btn">Cancel</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="<?= base_url('js/admin/add_item.js') ?>"></script>