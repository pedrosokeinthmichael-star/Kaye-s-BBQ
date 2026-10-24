/**
 * update-price-modal.js
 * Extracted from modals/update_price_modal.php so the markup file holds no inline script.
 */
document.addEventListener('DOMContentLoaded', function () {
  var backdrop = document.getElementById('update-price-modal-backdrop');
  var form = document.getElementById('update-price-form');
  var errorEl = document.getElementById('update-price-error');
  var nameInput = document.getElementById('up-name');
  var categoryInput = document.getElementById('up-category');
  var priceInput = document.getElementById('up-price');
  var availableInput = document.getElementById('up-available');
  var availableCaption = document.getElementById('up-available-caption');
  var menuBody = document.getElementById('menu-manage-body');

  function openModal(row) {
    var item = JSON.parse(row.getAttribute('data-item'));

    nameInput.value = item.NAME;
    categoryInput.value = item.MENU_CATEGORY;
    priceInput.value = item.PRICE;
    availableInput.checked = item.STATUS === 'Available';
    availableCaption.textContent = availableInput.checked ? 'Available' : 'Unavailable';
    errorEl.textContent = '';

    form.action = form.dataset.updateUrl + '/' + item.MENU_ID;

    backdrop.classList.add('open');
    backdrop.style.display = 'flex';
    priceInput.focus();
  }

  function closeModal() {
    backdrop.classList.remove('open');
    backdrop.style.display = 'none';
  }

  menuBody.addEventListener('click', function (e) {
    var priceBtn = e.target.closest('[data-action="update-price"]');
    if (priceBtn) {
      var row = priceBtn.closest('tr');
      if (row) openModal(row);
    }
  });

  availableInput.addEventListener('change', function () {
    availableCaption.textContent = availableInput.checked ? 'Available' : 'Unavailable';
  });

  document.getElementById('update-price-modal-close').addEventListener('click', closeModal);
  document.getElementById('update-price-cancel-btn').addEventListener('click', closeModal);
  backdrop.addEventListener('click', function (e) {
    if (e.target === backdrop) closeModal();
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && backdrop.classList.contains('open')) closeModal();
  });

  form.addEventListener('submit', function (e) {
    errorEl.textContent = '';

    var price = parseFloat(priceInput.value);
    if (isNaN(price) || price < 0) {
      e.preventDefault();
      errorEl.textContent = 'Enter a valid price.';
      return;
    }
    if (!form.action) {
      e.preventDefault();
      return;
    }

    // Validation passed -- let the form submit for real to
    // BarbequeController::update(), which redirects back here
    // with the freshly saved data.
  });
});