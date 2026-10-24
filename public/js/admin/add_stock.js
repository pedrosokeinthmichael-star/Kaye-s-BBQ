/**
 * add-stock-modal.js
 * Extracted from modals/add_stock_modal.php so the markup file holds no inline script.
 */
document.addEventListener('DOMContentLoaded', function () {
  var backdrop = document.getElementById('add-stock-modal-backdrop');
  var form = document.getElementById('add-stock-form');
  var errorEl = document.getElementById('add-stock-error');
  var nameInput = document.getElementById('as-name');
  var currentStockInput = document.getElementById('as-current-stock');
  var addInput = document.getElementById('as-add');
  var newTotalInput = document.getElementById('as-new-total');
  var menuBody = document.getElementById('menu-manage-body');
  var currentStock = 0;

  function refreshTotal() {
    var added = parseInt(addInput.value, 10);
    if (isNaN(added)) added = 0;
    newTotalInput.value = currentStock + added;
  }

  function openModal(row) {
    var item = JSON.parse(row.getAttribute('data-item'));

    currentStock = item.STOCK != null ? parseInt(item.STOCK, 10) : 0;
    if (isNaN(currentStock)) currentStock = 0;

    nameInput.value = item.NAME;
    currentStockInput.value = currentStock;
    addInput.value = '';
    newTotalInput.value = currentStock;
    errorEl.textContent = '';

    form.action = form.dataset.updateUrl + '/' + item.MENU_ID;

    backdrop.classList.add('open');
    backdrop.style.display = 'flex';
    addInput.focus();
  }

  function closeModal() {
    backdrop.classList.remove('open');
    backdrop.style.display = 'none';
  }

  menuBody.addEventListener('click', function (e) {
    var addBtn = e.target.closest('[data-action="add-stock"]');
    if (addBtn) {
      var row = addBtn.closest('tr');
      if (row) openModal(row);
    }
  });

  addInput.addEventListener('input', refreshTotal);

  document.getElementById('add-stock-modal-close').addEventListener('click', closeModal);
  document.getElementById('add-stock-cancel-btn').addEventListener('click', closeModal);
  backdrop.addEventListener('click', function (e) {
    if (e.target === backdrop) closeModal();
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && backdrop.classList.contains('open')) closeModal();
  });

  form.addEventListener('submit', function (e) {
    errorEl.textContent = '';

    var added = parseInt(addInput.value, 10);
    if (isNaN(added) || added < 1) {
      e.preventDefault();
      errorEl.textContent = 'Enter how much stock to add.';
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