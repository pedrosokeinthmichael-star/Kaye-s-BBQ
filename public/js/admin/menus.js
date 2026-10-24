/**
 * --------------------------------------------------------------
 * Menus page -- "Add New Item" modal (Kaye's Barbeque Admin Panel)
 * --------------------------------------------------------------
 * The "Add Item" button in the All Menu Items panel opens a modal
 * containing the add-item form (markup lives inside
 * #add-item-modal-backdrop in menus.php). On submit, a new row is
 * appended to the menu table and the summary stats update.
 *
 * Guarded so it's a no-op on every other admin page (checks for
 * #add-product-form before doing anything). Modal styling lives in
 * css/modal.css (shared with the order/cancel modals) and
 * css/tables.css (.menu-item-cell / .menu-item-thumb) — this file
 * only wires up behavior, no CSS injection.
 */
(function () {
  'use strict';

  function init() {
    var form = document.getElementById('add-product-form');
    if (!form) return; // Not on the menus page -- nothing to do.

    var backdrop = document.getElementById('add-item-modal-backdrop');
    var openBtn = document.getElementById('open-add-item-btn');
    var closeBtn = document.getElementById('add-item-modal-close');
    var cancelBtn = document.getElementById('add-item-cancel-btn');
    var errorEl = document.getElementById('add-error');

    var nameInput = document.getElementById('p-name');
    var categoryInput = document.getElementById('p-category');
    var priceInput = document.getElementById('p-price');
    var costInput = document.getElementById('stock');
    var descriptionInput = document.getElementById('p-description');
    var availableInput = document.getElementById('p-available');
    var imageInput = document.getElementById('p-image');
    var imagePreviewWrap = document.getElementById('p-image-preview-wrap');
    var imagePreview = document.getElementById('p-image-preview');
    var imageRemoveBtn = document.getElementById('p-image-remove');
    var currentImageData = null;

    var lastFocused = null;

    function openModal() {
      if (!backdrop) return;
      lastFocused = document.activeElement;
      errorEl.textContent = '';
      backdrop.style.display = 'flex';
      backdrop.classList.add('open');
      document.body.style.overflow = 'hidden';
      setTimeout(function () { nameInput.focus(); }, 0);
    }

    function clearImage() {
      currentImageData = null;
      if (imageInput) imageInput.value = '';
      if (imagePreviewWrap) imagePreviewWrap.style.display = 'none';
      if (imagePreview) imagePreview.src = '';
    }

    function closeModal() {
      if (!backdrop) return;
      backdrop.classList.remove('open');
      backdrop.style.display = 'none';
      document.body.style.overflow = '';
      form.reset();
      availableInput.checked = true;
      clearImage();
      errorEl.textContent = '';
      if (lastFocused && typeof lastFocused.focus === 'function') lastFocused.focus();
    }

    function onKeydown(e) {
      if (e.key === 'Escape' && backdrop.classList.contains('open')) closeModal();
    }

    if (imageInput) {
      imageInput.addEventListener('change', function () {
        var file = imageInput.files && imageInput.files[0];
        if (!file) { clearImage(); return; }

        if (!/^image\//.test(file.type)) {
          errorEl.textContent = 'Please choose an image file (JPG, PNG, etc).';
          imageInput.value = '';
          return;
        }
        var MAX_BYTES = 2 * 1024 * 1024; // 2MB — data URLs get large fast
        if (file.size > MAX_BYTES) {
          errorEl.textContent = 'Image is too large — please use one under 2MB.';
          imageInput.value = '';
          return;
        }

        errorEl.textContent = '';
        var reader = new FileReader();
        reader.onload = function (e) {
          currentImageData = e.target.result;
          imagePreview.src = currentImageData;
          imagePreviewWrap.style.display = 'block';
        };
        reader.onerror = function () {
          errorEl.textContent = 'Could not read that image — please try another file.';
          clearImage();
        };
        reader.readAsDataURL(file);
      });
    }

    if (imageRemoveBtn) {
      imageRemoveBtn.addEventListener('click', clearImage);
    }

    if (openBtn) openBtn.addEventListener('click', openModal);
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
    if (backdrop) {
      backdrop.addEventListener('click', function (e) {
        if (e.target === backdrop) closeModal();
      });
    }
    document.addEventListener('keydown', onKeydown);

    form.addEventListener('submit', function (e) {
      errorEl.textContent = '';

      var name = nameInput.value.trim();
      var category = categoryInput.value.trim();
      var price = parseFloat(priceInput.value);
      var stock = costInput.value.trim() === '' ? NaN : parseInt(costInput.value, 10);
      var description = descriptionInput.value.trim();
      var hasImage = !!(imageInput && imageInput.files && imageInput.files.length);

      if (!name || !category) {
        e.preventDefault();
        errorEl.textContent = 'Item name and category are required.';
        return;
      }
      if (isNaN(price) || price < 0) {
        e.preventDefault();
        errorEl.textContent = 'Enter a valid price.';
        return;
      }
      if (isNaN(stock) || stock < 0) {
        e.preventDefault();
        errorEl.textContent = 'Enter a valid stock amount.';
        return;
      }
      if (!description) {
        e.preventDefault();
        errorEl.textContent = 'Description is required.';
        return;
      }
      if (!hasImage) {
        e.preventDefault();
        errorEl.textContent = 'Please choose a photo.';
        return;
      }

      // Validation passed — let the form submit for real to
      // BarbequeController::store(), which redirects back to
      // /admin/menus with the newly saved item included.
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
/**
 * --------------------------------------------------------------
 * Menus page -- "All Menu Items" / "Price History" tabs
 * --------------------------------------------------------------
 * Guarded so it's a no-op on every other admin page (checks for
 * .tab-btn[data-tab-target] before doing anything).
 */
(function () {
  'use strict';

  function init() {
    var tabButtons = document.querySelectorAll('.tab-btn[data-tab-target]');
    if (!tabButtons.length) return;

    var panels = document.querySelectorAll('.tab-content');

    function activate(targetId) {
      tabButtons.forEach(function (btn) {
        var isActive = btn.getAttribute('data-tab-target') === targetId;
        btn.classList.toggle('active', isActive);
        btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
      });
      panels.forEach(function (panel) {
        panel.hidden = panel.id !== targetId;
      });
    }

    tabButtons.forEach(function (btn) {
      btn.addEventListener('click', function () {
        activate(btn.getAttribute('data-tab-target'));
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();

/**
 * --------------------------------------------------------------
 * Menus page -- "View" button on each row
 * --------------------------------------------------------------
 * Opens a popup with one item's full details (photo, name, category,
 * price, stock, availability, description), read from the row's
 * data-item JSON, so no extra request is needed.
 */
(function () {
  'use strict';

  function init() {
    var modal = document.getElementById('view-item-modal');
    var buttons = document.querySelectorAll('[data-view-item]');
    if (!modal || !buttons.length) return;

    var title = document.getElementById('view-item-title');
    var closeBtn = document.getElementById('view-item-close');

    // Fill the popup from the row's data-item JSON.
    function fillDetails(btn) {
      var row = btn.closest('tr');
      var item = {};
      try { item = JSON.parse(row.getAttribute('data-item') || '{}'); } catch (err) { item = {}; }

      function el(id) { return document.getElementById(id); }

      var price = parseFloat(item.PRICE);
      var stock = parseInt(item.STOCK, 10);
      if (isNaN(stock)) stock = 0;
      var available = item.STATUS === 'Available';

      el('view-item-category').textContent = item.MENU_CATEGORY || 'Uncategorized';
      el('view-item-price').textContent = isNaN(price) ? '\u2014' : '\u20B1' + price.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      el('view-item-stock').textContent = stock;
      el('view-item-id').textContent = (item.MENU_ID === null || item.MENU_ID === undefined) ? '\u2014' : item.MENU_ID;

      var statusEl = el('view-item-status');
      statusEl.textContent = available ? 'Available' : 'Unavailable';
      statusEl.className = 'status ' + (available ? 'status-available' : 'status-unavailable');

      // Stock flag: out of stock / running low (5 or fewer).
      var flag = el('view-item-stock-flag');
      if (stock <= 0) { flag.textContent = 'Out of stock'; flag.className = 'view-stock-flag out'; flag.hidden = false; }
      else if (stock <= 5) { flag.textContent = 'Running low'; flag.className = 'view-stock-flag low'; flag.hidden = false; }
      else { flag.hidden = true; }

      var desc = el('view-item-desc');
      var text = (item.DESCRIPTION || '').trim();
      desc.textContent = text || 'No description added yet.';
      desc.classList.toggle('is-empty', !text);

      // Reuse the thumbnail's URL from the row so we don't need base_url() in JS.
      var thumb = row.querySelector('img.menu-item-thumb');
      var photo = el('view-item-photo');
      var empty = el('view-item-photo-empty');
      if (thumb && thumb.getAttribute('src')) {
        photo.src = thumb.getAttribute('src');
        photo.alt = item.NAME || '';
        photo.hidden = false;
        empty.hidden = true;
      } else {
        photo.hidden = true;
        photo.removeAttribute('src');
        empty.hidden = false;
      }
    }

    function openModal(btn) {
      title.textContent = btn.getAttribute('data-name') || 'Item';
      fillDetails(btn);
      modal.hidden = false;
      document.body.style.overflow = 'hidden';
    }

    function closeModal() {
      modal.hidden = true;
      document.body.style.overflow = '';
    }

    buttons.forEach(function (btn) {
      btn.addEventListener('click', function () { openModal(btn); });
    });
    closeBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !modal.hidden) closeModal();
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();

/**
 * --------------------------------------------------------------
 * Price History / Inventory tabs -- "View" button on each row
 * --------------------------------------------------------------
 * The row already opens its details popup when clicked, so the
 * button simply clicks its own row.
 */
(function () {
  'use strict';

  function init() {
    document.querySelectorAll('.history-view-btn').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.stopPropagation();       // don't let the button's own click reach the row twice
        btn.closest('tr').click(); // same as clicking the row
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();