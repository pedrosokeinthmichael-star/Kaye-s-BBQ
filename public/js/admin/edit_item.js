/**
 * edit-item-modal.js
 * Extracted from modals/edit_item_modal.php so the markup file holds no inline script.
 */
document.addEventListener('DOMContentLoaded', function () {
  var backdrop = document.getElementById('edit-item-modal-backdrop');
  var form = document.getElementById('edit-product-form');
  var errorEl = document.getElementById('edit-error');
  var nameInput = document.getElementById('e-name');
  var categoryInput = document.getElementById('e-category');
  var priceInput = document.getElementById('e-price');
  var stockInput = document.getElementById('e-stock');
  var availableInput = document.getElementById('e-available');
  var availableCaption = document.getElementById('e-available-caption');
  var menuBody = document.getElementById('menu-manage-body');
  var activeRow = null;

  function openModal(row) {
    activeRow = row;
    var item = JSON.parse(row.getAttribute('data-item'));

    nameInput.value = item.NAME;
    categoryInput.value = item.MENU_CATEGORY;
    priceInput.value = item.PRICE;
    stockInput.value = item.STOCK != null ? item.STOCK : 0;
    availableInput.checked = item.STATUS === 'Available';
    availableCaption.textContent = availableInput.checked ? 'Available' : 'Unavailable';
    errorEl.textContent = '';

    // Point the form at this specific item's update route.
    form.action = form.dataset.updateUrl + '/' + item.MENU_ID;

    backdrop.classList.add('open');
    backdrop.style.display = 'flex';
    priceInput.focus();
  }

  function closeModal() {
    backdrop.classList.remove('open');
    backdrop.style.display = 'none';
    activeRow = null;
  }

  menuBody.addEventListener('click', function (e) {
    var editBtn = e.target.closest('[data-action="edit-item"]');
    if (editBtn) {
      var row = editBtn.closest('tr');
      if (row) openModal(row);
      return;
    }

    var deleteLink = e.target.closest('[data-action="delete-item"]');
    if (deleteLink) {
      var itemName = deleteLink.getAttribute('data-name') || 'this item';
      if (!window.confirm('Delete "' + itemName + '"? This cannot be undone.')) {
        e.preventDefault();
      }
    }
  });

  availableInput.addEventListener('change', function () {
    availableCaption.textContent = availableInput.checked ? 'Available' : 'Unavailable';
  });

  document.getElementById('edit-item-modal-close').addEventListener('click', closeModal);
  document.getElementById('edit-item-cancel-btn').addEventListener('click', closeModal);
  backdrop.addEventListener('click', function (e) {
    if (e.target === backdrop) closeModal();
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && backdrop.classList.contains('open')) closeModal();
  });

  form.addEventListener('submit', function (e) {
    errorEl.textContent = '';

    var price = parseFloat(priceInput.value);
    var stock = parseInt(stockInput.value, 10);

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
    if (!activeRow || !form.action) {
      e.preventDefault();
      return;
    }

    // Validation passed — let the form submit for real to
    // BarbequeController::update(), which redirects back here
    // with the freshly saved data.
  });
});