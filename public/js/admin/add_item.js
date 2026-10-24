/**
 * add-item-modal.js
 * Extracted from modals/add_item_modal.php so the markup file holds no inline script.
 */
document.addEventListener('DOMContentLoaded', function () {
  var select  = document.getElementById('p-category');
  var toggle  = document.getElementById('new-category-toggle');
  var row     = document.getElementById('new-category-row');
  var input   = document.getElementById('new-category-name');
  var saveBtn = document.getElementById('new-category-save');
  var errorEl = document.getElementById('new-category-error');
  if (!select || !toggle) return;
  var cfg = document.getElementById('add-item-modal-backdrop').dataset;
  var csrfName = cfg.csrfName;
  var csrfHash = cfg.csrfHash;

  var count = document.getElementById('new-category-count');

  function setPanel(open) {
    row.classList.toggle('is-open', open);
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    errorEl.textContent = '';
    if (open) input.focus();
  }

  toggle.addEventListener('click', function () {
    setPanel(!row.classList.contains('is-open'));
  });

  input.addEventListener('input', function () {
    count.textContent = input.value.length + '/' + input.maxLength;
  });

  // menus.js calls form.reset() when the modal closes: collapse the panel too.
  if (select.form) {
    select.form.addEventListener('reset', function () {
      setPanel(false);
      input.value = '';
      count.textContent = '0/' + input.maxLength;
    });
  }

  function save() {
    errorEl.textContent = '';
    var name = input.value.trim();
    if (!name) { errorEl.textContent = 'Enter a category name.'; return; }

    var fd = new FormData();
    fd.append('categoryName', name);
    fd.append(csrfName, csrfHash);

    saveBtn.disabled = true;
    fetch(cfg.categoryUrl, {
      method: 'POST',
      body: fd,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function (r) { return r.json(); })
    .then(function (res) {
      if (res.csrf) {
        csrfHash = res.csrf;
        // CodeIgniter rotates the token after each request, so refresh every
        // CSRF input on the page or the next form submit gets a 403.
        document.querySelectorAll('input[name="' + csrfName + '"]').forEach(function (i) { i.value = res.csrf; });
      }
      if (!res.success) { errorEl.textContent = res.message || 'Could not add category.'; return; }

      var opt = select.querySelector('option[value="' + res.id + '"]');
      if (!opt) {
        opt = document.createElement('option');
        opt.value = res.id;
        opt.textContent = res.name;
        select.appendChild(opt);
      }
      select.value = res.id;
      input.value = '';
      count.textContent = '0/' + input.maxLength;
      setPanel(false);
    })
    .catch(function () { errorEl.textContent = 'Something went wrong. Please try again.'; })
    .then(function () { saveBtn.disabled = false; });
  }

  saveBtn.addEventListener('click', save);
  input.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') { e.preventDefault(); save(); }
  });
});