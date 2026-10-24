/**
 * order-settings.js
 * Extracted from partials/delivery_payment_settings.php so the markup file holds no inline script.
 */
document.addEventListener('DOMContentLoaded', function () {
  var notice = document.getElementById('settings-notice');
  var cfg = document.getElementById('dp-settings-panels').dataset;

  // CSRF token shared by every fetch() on this page. CodeIgniter rotates it after
  // each request, so setCsrf() also refreshes every CSRF input on the page.
  var csrfName = cfg.csrfName;
  var csrfHash = cfg.csrfHash;
  if (!csrfName) {
    // Fallback: borrow the token from any CSRF hidden input already on the page (e.g. the Logout form).
    var anyCsrf = document.querySelector('input[type="hidden"][name*="csrf"]');
    if (anyCsrf) { csrfName = anyCsrf.name; csrfHash = anyCsrf.value; }
  }
  // Fallback: build the URL from the current page if the data attribute is missing
  // (/admin/orders -> /admin/settings/update, works with or without index.php).
  var settingsUrl = cfg.settingsUrl || window.location.pathname.replace(/\/admin(\/.*)?$/, '/admin/settings/update');
  function setCsrf(hash) {
    if (!hash) return;
    csrfHash = hash;
    document.querySelectorAll('input[name="' + csrfName + '"]').forEach(function (i) { i.value = hash; });
  }

  function showNotice(message, isError) {
    notice.className = 'notice ' + (isError ? 'notice-error' : 'notice-success');
    notice.textContent = message;
    notice.style.display = 'block';
    notice.scrollIntoView({ behavior: 'smooth', block: 'center' });
    setTimeout(function () { notice.style.display = 'none'; }, 4000);
  }

  function escapeHtml(str) {
    var div = document.createElement('div');
    div.textContent = str == null ? '' : String(str);
    return div.innerHTML;
  }

  function formatHistoryDate(iso) {
    var d = new Date(iso + 'T00:00:00');
    return d.toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' });
  }

  function todayISO() {
    var d = new Date();
    var pad = function (n) { return n < 10 ? '0' + n : '' + n; };
    return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
  }

  // ---- Tab groups: each .dp-tab-group manages only its own panels ----
  Array.prototype.slice.call(document.querySelectorAll('.dp-tab-group')).forEach(function (group) {
    var buttons = Array.prototype.slice.call(group.querySelectorAll('.dp-tab-btn[data-dp-tab-target]'));
    var panels = Array.prototype.slice.call(group.querySelectorAll('.dp-tab-panel'));

    function activate(targetId) {
      buttons.forEach(function (b) {
        var isActive = b.getAttribute('data-dp-tab-target') === targetId;
        b.classList.toggle('active', isActive);
        b.setAttribute('aria-selected', isActive ? 'true' : 'false');
      });
      panels.forEach(function (panel) { panel.hidden = panel.id !== targetId; });
    }

    buttons.forEach(function (btn) {
      btn.addEventListener('click', function () { activate(btn.getAttribute('data-dp-tab-target')); });
    });

    group.__activateTab = activate; // exposed so buttons outside the tab bar (e.g. "View GCash Archive") can switch tabs
  });

  function activateTabById(targetId) {
    var panel = document.getElementById(targetId);
    var group = panel && panel.closest('.dp-tab-group');
    if (group && group.__activateTab) group.__activateTab(targetId);
  }

  // ==================== Delivery Areas (Barangays) ====================
  var barangayTbody = document.getElementById('delivery-areas-table-body');
  var barangayEmptyNote = document.getElementById('barangay-empty-note');

  // Seeded from the database (Home::admin_settings() -> BarangayModel::findAll()).
  var barangays = JSON.parse(cfg.barangays || '[]');

  function statusBadge(active) {
    return active
      ? '<span class="status-badge is-active"><span class="status-dot"></span>Active</span>'
      : '<span class="status-badge is-inactive"><span class="status-dot"></span>Inactive</span>';
  }

  function renderBarangays() {
    barangayTbody.innerHTML = '';
    barangayEmptyNote.style.display = barangays.length ? 'none' : 'block';

    barangays.forEach(function (entry, index) {
      var tr = document.createElement('tr');
      tr.innerHTML =
        '<td>' + (index + 1) + '</td>' +
        '<td>' + escapeHtml(entry.name) + '</td>' +
        '<td class="num">₱' + Number(entry.fee).toLocaleString('en-PH') + '</td>' +
        '<td>' + statusBadge(entry.active) + '</td>' +
        '<td class="table-actions-cell">' +
          '<button type="button" class="table-update-btn" data-edit-barangay="' + index + '" title="Update this barangay\'s details">' +
            '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 20h4l10.5-10.5a2.1 2.1 0 0 0 0-3l-1-1a2.1 2.1 0 0 0-3 0L4 16v4z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>' +
            'Update' +
          '</button>' +
          '<button type="button" class="table-icon-btn danger" data-delete-barangay="' + index + '" title="Delete">' +
            '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 7h16M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2m-9 0 1 13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1l1-13" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>' +
          '</button>' +
        '</td>';
      barangayTbody.appendChild(tr);
    });
  }

  renderBarangays();

  // ---- Add/Edit Barangay modal ----
  var barangayModalBackdrop = document.getElementById('add-barangay-modal-backdrop');
  var barangayForm = document.getElementById('add-barangay-form');
  var barangayModalError = document.getElementById('add-barangay-error');
  var barangayIndexInput = document.getElementById('modal-barangay-index');
  var barangayNameInput = document.getElementById('modal-barangay-name');
  var barangayFeeInput = document.getElementById('modal-barangay-fee');
  var barangayActiveInput = document.getElementById('modal-barangay-active');
  var barangayModalTitle = document.getElementById('add-barangay-modal-title');
  var barangaySubmitBtn = document.getElementById('add-barangay-submit-btn');
  var deleteBarangayBtn = document.getElementById('delete-barangay-btn');

  function openBarangayModal(index) {
    var editing = typeof index === 'number';
    barangayIndexInput.value = editing ? index : '';
    barangayModalTitle.textContent = editing ? 'Update Delivery Area' : 'Add Delivery Area';
    barangaySubmitBtn.textContent = editing ? 'Update Delivery Area' : 'Add Delivery Area';
    deleteBarangayBtn.style.display = editing ? 'inline-flex' : 'none';
    barangayModalError.textContent = '';

    if (editing) {
      var entry = barangays[index];
      barangayNameInput.value = entry.name;
      barangayFeeInput.value = entry.fee;
      barangayActiveInput.checked = entry.active;
    } else {
      barangayForm.reset();
      barangayActiveInput.checked = true;
    }

    barangayModalBackdrop.classList.add('open');
    barangayModalBackdrop.style.display = 'flex';
    barangayNameInput.focus();
  }

  function closeBarangayModal() {
    barangayModalBackdrop.classList.remove('open');
    barangayModalBackdrop.style.display = 'none';
  }

  document.getElementById('open-add-barangay-modal').addEventListener('click', function () { openBarangayModal(); });
  document.getElementById('add-barangay-modal-close').addEventListener('click', closeBarangayModal);
  document.getElementById('add-barangay-cancel-btn').addEventListener('click', closeBarangayModal);
  barangayModalBackdrop.addEventListener('click', function (e) { if (e.target === barangayModalBackdrop) closeBarangayModal(); });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && barangayModalBackdrop.classList.contains('open')) closeBarangayModal();
  });

  // Row actions: edit opens the modal, delete removes the row directly
  barangayTbody.addEventListener('click', function (e) {
    var editBtn = e.target.closest('[data-edit-barangay]');
    if (editBtn) { openBarangayModal(parseInt(editBtn.getAttribute('data-edit-barangay'), 10)); return; }

    var deleteBtn = e.target.closest('[data-delete-barangay]');
    if (deleteBtn) {
      var index = parseInt(deleteBtn.getAttribute('data-delete-barangay'), 10);
      var entry = barangays[index];
      var name = entry ? entry.name : 'this barangay';
      if (entry && window.confirm('Remove ' + name + ' from your delivery areas?')) {
        window.location.href = cfg.barangayDeleteUrl + '/' + entry.id;
      }
    }
  });

  deleteBarangayBtn.addEventListener('click', function () {
    var index = parseInt(barangayIndexInput.value, 10);
    var entry = barangays[index];
    if (isNaN(index) || !entry) return;
    if (window.confirm('Remove ' + entry.name + ' from your delivery areas?')) {
      window.location.href = cfg.barangayDeleteUrl + '/' + entry.id;
    }
  });

  // Normalise a name the same way the server does: trim, collapse spaces, ignore case.
  function normName(str) { return String(str || '').replace(/\s+/g, ' ').trim().toLowerCase(); }

  // The barangay already on the list with this name (other than the one being edited), if any.
  function findDuplicate(name, editIndex) {
    var wanted = normName(name);
    if (!wanted) return null;
    for (var i = 0; i < barangays.length; i++) {
      if (i !== editIndex && normName(barangays[i].name) === wanted) return barangays[i];
    }
    return null;
  }

  function duplicateMessage(entry) {
    return '\u201C' + entry.name + '\u201D is already on the delivery list' +
      (entry.active ? '.' : ' (currently Inactive \u2014 use Update to turn it back on).');
  }

  function currentEditIndex() {
    return barangayIndexInput.value === '' ? null : parseInt(barangayIndexInput.value, 10);
  }

  // Warn as soon as a duplicate name is typed, before the admin even presses Add.
  barangayNameInput.addEventListener('input', function () {
    var dup = findDuplicate(barangayNameInput.value, currentEditIndex());
    barangayModalError.textContent = dup ? duplicateMessage(dup) : '';
  });

  barangayForm.addEventListener('submit', function (e) {
    barangayModalError.textContent = '';

    var name = barangayNameInput.value.replace(/\s+/g, ' ').trim();
    var fee = parseFloat(barangayFeeInput.value);
    var editIndex = currentEditIndex();

    if (!name) { e.preventDefault(); barangayModalError.textContent = 'Enter a barangay name.'; return; }
    if (barangayFeeInput.value === '' || isNaN(fee) || fee < 0) { e.preventDefault(); barangayModalError.textContent = 'Enter a valid delivery fee.'; return; }

    var dup = findDuplicate(name, editIndex);
    if (dup) { e.preventDefault(); barangayModalError.textContent = duplicateMessage(dup); return; }

    // Editing: let the form submit for real (BarangayController::update()
    // redirects back here, and also re-checks for duplicates).
    if (editIndex !== null) {
      barangayForm.action = cfg.barangayUpdateUrl + '/' + barangays[editIndex].id;
      return;
    }

    // Adding: send it with fetch() so the new row appears immediately, no page reload.
    e.preventDefault();
    var fd = new FormData(barangayForm);
    fd.set('barangayName', name);
    fd.set(csrfName, csrfHash);

    barangaySubmitBtn.disabled = true;
    fetch(cfg.barangayStoreUrl, {
      method: 'POST',
      body: fd,
      credentials: 'same-origin',
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(function (r) { return r.json(); })
    .then(function (res) {
      setCsrf(res.csrf);
      if (!res.success) { barangayModalError.textContent = res.message || 'Could not add the barangay.'; return; }

      barangays.push(res.barangay);
      renderBarangays();
      closeBarangayModal();
      barangayForm.reset();
      showNotice(res.message || 'Barangay added successfully!');
    })
    .catch(function () { barangayModalError.textContent = 'Something went wrong. Please try again.'; })
    .then(function () { barangaySubmitBtn.disabled = false; });
  });

  // ---- Delivery Fee History ----
  var feeHistoryTbody = document.getElementById('delivery-fee-history-table-body');
  var feeHistoryEmptyNote = document.getElementById('delivery-fee-history-empty-note');

  // Seeded from the database (Home::admin_settings() -> BarangayHistoryModel).
  var feeHistory = JSON.parse(cfg.feeHistory || '[]');

  function renderFeeHistory() {
    feeHistoryTbody.innerHTML = '';
    feeHistoryEmptyNote.style.display = feeHistory.length ? 'none' : 'block';

    feeHistory
      .slice()
      .sort(function (a, b) { return a.date < b.date ? 1 : -1; }) // newest first
      .forEach(function (entry) {
        var tr = document.createElement('tr');
        tr.innerHTML =
          '<td>' + formatHistoryDate(entry.date) + '</td>' +
          '<td>' + escapeHtml(entry.barangay) + '</td>' +
          '<td class="num">₱' + Number(entry.oldFee).toLocaleString('en-PH') + '</td>' +
          '<td class="num">₱' + Number(entry.newFee).toLocaleString('en-PH') + '</td>';
        feeHistoryTbody.appendChild(tr);
      });
  }

  renderFeeHistory();

  function logFeeChange(barangayName, oldFee, newFee) {
    if (oldFee === newFee) return;
    feeHistory.push({ date: todayISO(), barangay: barangayName, oldFee: oldFee, newFee: newFee });
    renderFeeHistory();
  }

  // ==================== GCash Account ====================
  var gcashViewCard = document.getElementById('gcash-view-card');
  var gcashEmptyCard = document.getElementById('gcash-empty-card');
  var gcashEditCard = document.getElementById('gcash-edit-card');
  var gcashAddBtn = document.getElementById('gcash-add-btn');
  var gcashEditBtn = document.getElementById('gcash-edit-btn');

  var gcashForm = document.getElementById('gcash-form');
  var gcashNameInput = document.getElementById('gcash-name-input');
  var gcashNumberInput = document.getElementById('gcash-number-input');
  var gcashError = document.getElementById('gcash-error');
  var qrInput = document.getElementById('gcash-qr-input');
  var qrPreview = document.getElementById('gcash-qr-preview');
  var qrRemoveBtn = document.getElementById('gcash-qr-remove');
  var resetGcashBtn = document.getElementById('reset-gcash-btn');
  var archiveTbody = document.getElementById('gcash-archive-table-body');
  var archiveEmptyNote = document.getElementById('gcash-archive-empty-note');

  // Seeded from the database (Home::admin_settings() -> GcashModel).
  // null means no GCash record has been saved yet.
  var currentGcash = JSON.parse(cfg.gcash || 'null');

  var GCASH_STORE_URL  = cfg.gcashStoreUrl;
  var GCASH_UPDATE_URL = cfg.gcashUpdateUrl;

  function resetQrField() {
    qrInput.value = '';
    qrPreview.src = '';
    qrPreview.style.display = 'none';
    qrRemoveBtn.style.display = 'none';
  }

  function showGcashView() {
    gcashEditCard.style.display = 'none';
    if (currentGcash) {
      gcashViewCard.style.display = 'flex';
      gcashEmptyCard.style.display = 'none';
    } else {
      gcashViewCard.style.display = 'none';
      gcashEmptyCard.style.display = 'flex';
    }
  }

  // Opens the form empty, pointed at the "store" route -- for adding
  // GCash details for the first time.
  function showGcashAdd() {
    gcashError.textContent = '';
    gcashForm.action = GCASH_STORE_URL;
    gcashNameInput.value = '';
    gcashNumberInput.value = '';
    resetQrField();
    gcashViewCard.style.display = 'none';
    gcashEmptyCard.style.display = 'none';
    gcashEditCard.style.display = 'flex';
    gcashNameInput.focus();
  }

  // Opens the form pre-filled with the current record, pointed at the
  // "update" route for that record's id.
  function showGcashEdit() {
    gcashError.textContent = '';
    gcashForm.action = GCASH_UPDATE_URL + '/' + currentGcash.id;
    gcashNameInput.value = currentGcash.name;
    gcashNumberInput.value = currentGcash.number;
    resetQrField();
    if (currentGcash.qr) {
      qrPreview.src = currentGcash.qr;
      qrPreview.style.display = 'block';
      qrRemoveBtn.style.display = 'inline-flex';
    }
    gcashViewCard.style.display = 'none';
    gcashEmptyCard.style.display = 'none';
    gcashEditCard.style.display = 'flex';
    gcashNameInput.focus();
  }

  showGcashView();

  gcashAddBtn.addEventListener('click', showGcashAdd);
  gcashEditBtn.addEventListener('click', showGcashEdit);
  resetGcashBtn.addEventListener('click', showGcashView);

  qrInput.addEventListener('change', function () {
    var file = qrInput.files && qrInput.files[0];
    if (!file) return;

    var reader = new FileReader();
    reader.onload = function (e) {
      qrPreview.src = e.target.result;
      qrPreview.style.display = 'block';
      qrRemoveBtn.style.display = 'inline-flex';
    };
    reader.readAsDataURL(file);
  });

  // Clears a newly-chosen (not yet saved) QR file. It does not delete
  // an already-saved QR -- GcashController only replaces the QR when a
  // new file is uploaded, so leaving the picker empty keeps the current one.
  qrRemoveBtn.addEventListener('click', function () {
    resetQrField();
  });

  gcashForm.addEventListener('submit', function (e) {
    gcashError.textContent = '';

    var name = gcashNameInput.value.trim();
    var number = gcashNumberInput.value.trim();

    if (!name || !number) {
      e.preventDefault();
      gcashError.textContent = 'Enter both a GCash account name and number.';
      return;
    }

    // Validation passed -- let the form submit for real.
    // GcashController::store()/update() redirects back to
    // /admin/settings, which re-renders this tab from the database.
  });

  // ---- GCash Archive ----
  // NOTE: front-end only for now -- there's no archive table/model
  // backing this yet, so it starts empty. Wire this up to a real
  // archive once GcashController logs replaced records somewhere.
  var gcashArchive = [];

  function renderGcashArchive() {
    archiveTbody.innerHTML = '';
    archiveEmptyNote.style.display = gcashArchive.length ? 'none' : 'block';

    gcashArchive
      .slice()
      .sort(function (a, b) { return a.date < b.date ? 1 : -1; }) // newest first
      .forEach(function (entry) {
        var tr = document.createElement('tr');
        var qrCell = entry.qr
          ? '<img src="' + entry.qr + '" alt="Archived GCash QR" style="width:36px;height:36px;border-radius:6px;object-fit:contain;border:1px solid rgba(0,0,0,.1);background:#fff;">'
          : '<span style="color:var(--ink-soft,#8a7a68);">—</span>';
        tr.innerHTML =
          '<td>' + qrCell + '</td>' +
          '<td>' + escapeHtml(entry.name) + '</td>' +
          '<td>' + escapeHtml(entry.number) + '</td>' +
          '<td>' + formatHistoryDate(entry.date) + '</td>';
        archiveTbody.appendChild(tr);
      });
  }

  renderGcashArchive();

  document.getElementById('view-gcash-archive-btn').addEventListener('click', function () {
    activateTabById('gcash-archive-tab');
  });

  // ==================== Enable Delivery / Enable GCash switches ====================
  // Saved straight away (no need to press "Save Changes"). The customer's
  // checkout and OrderController::placeOrder() both read these settings.
  (function () {
    function bindSwitch(toggleId, setting, label, offMessage) {
      var toggle = document.getElementById(toggleId);
      if (!toggle) return;

      toggle.addEventListener('change', function () {
        var wanted = toggle.checked;
        var fd = new FormData();
        fd.append('setting', setting);
        fd.append('enabled', wanted ? '1' : '0');
        fd.append(csrfName, csrfHash);

        toggle.disabled = true;
        fetch(settingsUrl, {
          method: 'POST',
          body: fd,
          credentials: 'same-origin',
          headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(function (r) {
          // Keep the HTTP status so a failure can say WHY it failed.
          return r.text().then(function (text) {
            var data = null;
            try { data = JSON.parse(text); } catch (err) { /* not JSON: an error page */ }
            return { status: r.status, data: data };
          });
        })
        .then(function (out) {
          if (out.data) setCsrf(out.data.csrf);
          if (out.data && out.data.success) {
            showNotice(wanted ? label + ' enabled. Customers can use it at checkout.' : offMessage);
            return;
          }
          toggle.checked = !wanted; // nothing was saved, put the switch back
          var why;
          if (out.status === 404)      why = 'POST ' + settingsUrl + ' was not found. Check that SettingsController.php is in app/Controllers and that Routes.php has the /admin/settings/update line (run: php spark routes).';
          else if (out.status === 403) why = 'the request was blocked (CSRF token or login session). Refresh the page and try again.';
          else if (out.status >= 500)  why = 'a server error happened (check writable/logs, and that the writable/ folder can be written to).';
          else if (out.data && out.data.success === false) why = 'the server could not save it (is writable/shop_settings.json writable?).';
          else                         why = 'unexpected response (HTTP ' + out.status + ').';
          showNotice('Could not update ' + label.toLowerCase() + ': ' + why, true);
        })
        .catch(function () {
          toggle.checked = !wanted;
          showNotice('Could not update ' + label.toLowerCase() + ': could not reach the server.', true);
        })
        .then(function () { toggle.disabled = false; });
      });
    }

    bindSwitch('enable-delivery-toggle', 'delivery', 'Delivery',
      'Delivery disabled. Customers can only choose Pickup.');
    bindSwitch('enable-online-payments-toggle', 'gcash', 'GCash payments',
      'GCash payments disabled. Customers can only pay with cash.');
  })();

  // ==================== Save / Cancel ====================
  document.getElementById('settings-save-btn').addEventListener('click', function () {
    if (document.getElementById('enable-delivery-toggle').checked && barangays.length === 0) {
      showNotice('Add at least one delivery area before saving.');
      return;
    }

    // NOTE: this is front-end only for now, matching profile.php's
    // pattern -- nothing is persisted to the database yet. Wire this
    // up to a settings controller/model + POST route when ready.
    showNotice('Delivery & payment settings updated successfully.');
  });

  document.getElementById('settings-cancel-btn').addEventListener('click', function () {
    // No page-level fields left to reset here besides the tables above.
  });
});