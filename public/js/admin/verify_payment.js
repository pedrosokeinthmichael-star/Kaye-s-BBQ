/**
 * verify-payment-modal.js
 * Extracted from modals/verify_payment_modal.php so the markup file holds no inline script.
 */
(function () {
  var backdrop = document.getElementById('verify-modal-backdrop');
  if (!backdrop || !document.getElementById('orders-table-body')) return;

  var cfg = backdrop.dataset;
  var SHOP = { name: cfg.shopName || '', number: cfg.shopNumber || '' };
  var REQUEST_BASE = cfg.requestBase;
  var csrfName = cfg.csrfName;
  var csrfHash = cfg.csrfHash;
  var ordersBody = document.getElementById('orders-table-body');
  // orders.js keeps the newest CSRF hash on #orders-table-body; use whichever is latest.
  function currentHash() { return (ordersBody && ordersBody.dataset.csrfHash) || csrfHash; }
  function saveHash(h) { csrfHash = h; if (ordersBody) ordersBody.dataset.csrfHash = h; }

  function $(id) { return document.getElementById(id); }
  var receivedEl = $('vp-received'), resultEl = $('vp-result'), submitBtn = $('vp-submit');
  var confirmBox = $('vp-confirm'), refundSent = $('vp-refund-sent');
  var refundBtn = $('vp-refund-btn'), requestBtn = $('vp-request-btn'), fullSent = $('vp-full-sent'), refundOpen = false;

  var data = null, row = null, rowBtn = null, mode = '', lastFocused = null;

  // The admin's own refund record (GCash reference + screenshot). It is posted to
  // REQUEST_BASE/{orderId}/refund before the order moves on. Set to false to skip
  // saving (the fields are still required) until that route exists.
  var SAVE_REFUND_RECORD = true;
  var REFUND = {
    over: { ref: $('vp-refund-ref'), file: $('vp-refund-proof'), prev: $('vp-refund-preview') },
    full: { ref: $('vp-full-ref'),   file: $('vp-full-proof'),   prev: $('vp-full-preview') }
  };
  function refundReady(k) {
    return REFUND[k].ref.value.trim() !== '' && !!(REFUND[k].file.files && REFUND[k].file.files.length);
  }
  function showSaveError(msg) { var el = $('vp-save-error'); el.textContent = msg; el.style.display = ''; }
  function hideSaveError() { $('vp-save-error').style.display = 'none'; }
  function clearRefundFields() {
    ['over', 'full'].forEach(function (k) {
      var r = REFUND[k];
      r.ref.value = ''; r.file.value = '';
      if (r.prev._url) { URL.revokeObjectURL(r.prev._url); r.prev._url = null; }
      r.prev.style.display = 'none';
    });
    hideSaveError();
  }

  function round2(n) { return Math.round(n * 100) / 100; }
  function money(n) {
    return '\u20B1' + Number(n || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }
  function firstName() { return String((data && data.customer) || '').trim().split(/\s+/)[0] || 'there'; }
  function shopGcash() { return [SHOP.name, SHOP.number].filter(Boolean).join(' \u00B7 '); }

  function shortMessage(received, short) {
    if (received === 0) {
      return 'Hi ' + firstName() + ', this is Kaye\u2019s Barbeque. We have not received your GCash payment for order ' + data.id +
        ' yet. Please send ' + money(data.total) + (shopGcash() ? ' to ' + shopGcash() : '') +
        ' and reply with the screenshot so we can start preparing your order. Thank you!';
    }
    return 'Hi ' + firstName() + ', this is Kaye\u2019s Barbeque. For order ' + data.id + ' we received ' + money(received) +
      ' of ' + money(data.total) + ', so your GCash payment is ' + money(short) + ' short. ' +
      'Please send the remaining ' + money(short) + (shopGcash() ? ' to ' + shopGcash() : '') +
      ' and reply with the screenshot so we can start preparing your order. Thank you!';
  }
  function refPart(ref) { return ref ? ' (GCash ref no. ' + ref + ')' : ''; }
  function fullMessage(received, ref) {
    return 'Hi ' + firstName() + ', this is Kaye\u2019s Barbeque. We are cancelling order ' + data.id +
      ' and sending your ' + money(received) + ' payment back to your GCash' + refPart(ref) + '. Sorry for the trouble, and thank you!';
  }
  function overMessage(received, extra, ref) {
    return 'Hi ' + firstName() + ', this is Kaye\u2019s Barbeque. You paid ' + money(received) + ' for order ' + data.id +
      ', but the total is ' + money(data.total) + '. We are sending the extra ' + money(extra) +
      ' back to your GCash' + refPart(ref) + '. Thank you!';
  }

  // Text / email links follow whatever is currently in the message box.
  function wireMessage(suffix) {
    var box = $('vp-msg-' + suffix), sms = $('vp-sms-' + suffix), mail = $('vp-mail-' + suffix);
    function refresh() {
      var body = encodeURIComponent(box.value);
      var phone = String((data && data.phone) || '').replace(/[^0-9+]/g, '');
      var email = String((data && data.email) || '').trim();
      sms.style.display = phone ? '' : 'none';
      sms.href = phone ? 'sms:' + phone + '?body=' + body : '#';
      mail.style.display = email ? '' : 'none';
      mail.href = email ? 'mailto:' + email + '?subject=' + encodeURIComponent('Kaye\u2019s Barbeque order ' + data.id) + '&body=' + body : '#';
    }
    box.addEventListener('input', refresh);
    return refresh;
  }
  var refreshShort = wireMessage('short'), refreshOver = wireMessage('over'), refreshFull = wireMessage('full');

  function copyText(text, btn) {
    function done() {
      var old = btn.textContent;
      btn.textContent = 'Copied';
      setTimeout(function () { btn.textContent = old; }, 1200);
    }
    function fallback() {
      var ta = document.createElement('textarea');
      ta.value = text; ta.style.position = 'fixed'; ta.style.opacity = '0';
      document.body.appendChild(ta); ta.select();
      try { document.execCommand('copy'); done(); } catch (e) {}
      document.body.removeChild(ta);
    }
    if (navigator.clipboard && navigator.clipboard.writeText) navigator.clipboard.writeText(text).then(done, fallback);
    else fallback();
  }
  $('vp-copy-short').addEventListener('click', function () { copyText($('vp-msg-short').value, this); });
  $('vp-copy-over').addEventListener('click', function () { copyText($('vp-msg-over').value, this); });
  $('vp-copy-number').addEventListener('click', function () { copyText(String((data && data.phone) || ''), this); });
  $('vp-copy-full').addEventListener('click', function () { copyText($('vp-msg-full').value, this); });
  $('vp-copy-full-number').addEventListener('click', function () { copyText(String((data && data.phone) || ''), this); });

  function recalc() {
    ['match', 'short', 'over', 'full'].forEach(function (m) { $('vp-panel-' + m).style.display = 'none'; });
    submitBtn.disabled = true;
    refundBtn.disabled = true;
    refundBtn.textContent = 'Refund';
    requestBtn.disabled = true;
    $('vp-cancel-hint').textContent = '';
    $('vp-request-status').style.display = 'none';

    var received = parseFloat(receivedEl.value);
    if (isNaN(received) || received < 0) { resultEl.style.display = 'none'; mode = ''; refundOpen = false; requestBtn.disabled = false; return; }
    if (received === 0) refundOpen = false;
    refundBtn.disabled = !(received > 0);

    var diff = round2(received - data.total);
    resultEl.style.display = '';
    resultEl.className = 'admin-modal-note vp-result';

    if (Math.abs(diff) < 0.005) {
      mode = 'match';
      resultEl.classList.add('is-ok');
      resultEl.textContent = 'The amount matches the order total.';
      $('vp-panel-match').style.display = '';
      submitBtn.disabled = !confirmBox.checked;

    } else if (diff < 0) {
      mode = 'short';
      resultEl.classList.add('admin-modal-note-warning');
      requestBtn.disabled = false;
      resultEl.textContent = received === 0
        ? 'No payment received. Ask the customer to send the full ' + money(data.total) + '. The order stays Pending until it is paid.'
        : 'Short by ' + money(-diff) + '. Ask the customer to pay the balance. The order stays Pending until it is paid.';
      $('vp-panel-short').style.display = '';
      $('vp-msg-short').value = shortMessage(received, -diff);
      refreshShort();

    } else {
      mode = 'over';
      resultEl.classList.add('admin-modal-note-warning');
      resultEl.textContent = 'Overpaid by ' + money(diff) + '. Send the extra back through GCash, then the order can move to Preparing.';
      $('vp-panel-over').style.display = '';
      $('vp-refund-amount').textContent = money(diff);
      $('vp-refund-name').textContent = data.customer || '\u2014';
      $('vp-refund-to').textContent = data.phone || '\u2014';
      $('vp-copy-number').style.display = data.phone ? '' : 'none';
      $('vp-msg-over').value = overMessage(received, diff, REFUND.over.ref.value.trim());
      refreshOver();
      submitBtn.disabled = !(refundSent.checked && refundReady('over'));
    }

    // Full refund (cancel) takes over the panel area while it is open.
    if (refundOpen && received > 0) {
      ['match', 'short', 'over'].forEach(function (m) { $('vp-panel-' + m).style.display = 'none'; });
      $('vp-panel-full').style.display = '';
      $('vp-full-amount').textContent = money(received);
      $('vp-full-name').textContent = data.customer || '\u2014';
      $('vp-full-to').textContent = data.phone || '\u2014';
      $('vp-copy-full-number').style.display = data.phone ? '' : 'none';
      $('vp-msg-full').value = fullMessage(received, REFUND.full.ref.value.trim());
      refreshFull();
      submitBtn.disabled = true;
      requestBtn.disabled = true;
      refundBtn.textContent = 'Refund';
      refundBtn.disabled = !(fullSent.checked && refundReady('full'));
    }

    if (received > 0) {
      $('vp-cancel-hint').textContent = 'Cancelling instead? Remember to refund the ' + money(received) +
        ' already received to ' + (data.phone || 'the customer') + ' through GCash.';
    }
  }

  function cancelThroughModal() {
    var tr = row;
    closeModal();
    var cancelBtn = tr && tr.querySelector('[data-action="cancel"]');
    if (cancelBtn) cancelBtn.click();
  }

  function openFor(btn) {
    var tr = btn.closest('tr');
    if (!tr) return;
    try { data = JSON.parse(tr.getAttribute('data-order')); } catch (e) { return; }
    if (!data) return;
    row = tr; rowBtn = btn;
    lastFocused = document.activeElement;

    $('verify-modal-title').textContent = data.id || '';
    $('vp-customer').textContent = data.customer || '\u2014';
    $('vp-contact').textContent = [data.phone, data.email].filter(Boolean).join(' \u00B7 ') || '\u2014';
    $('vp-reference').textContent = data.gcashReference || '\u2014';
    $('vp-total').textContent = money(data.total);

    var hasProof = !!data.proofOfPayment;
    $('vp-proof-link').style.display = hasProof ? 'inline-block' : 'none';
    $('vp-proof-hint').style.display = hasProof ? '' : 'none';
    $('vp-no-proof').style.display = hasProof ? 'none' : '';
    if (hasProof) { $('vp-proof-img').src = data.proofOfPayment; $('vp-proof-link').href = data.proofOfPayment; }

    var extras = Array.isArray(data.extraPayments) ? data.extraPayments : [];
    $('vp-extra').style.display = extras.length ? '' : 'none';
    var list = $('vp-extra-list');
    list.innerHTML = '';
    extras.forEach(function (p, i) {
      var box = document.createElement('div');
      box.style.marginTop = '8px';
      var line = document.createElement('div');
      line.textContent = 'Payment ' + (i + 2) + (p.date ? ' \u00B7 ' + p.date : '') + ' \u00B7 Ref: ' + (p.reference || '\u2014');
      box.appendChild(line);
      if (p.proof) {
        var a = document.createElement('a');
        a.className = 'vp-proof'; a.href = p.proof; a.target = '_blank'; a.rel = 'noopener';
        var img = document.createElement('img');
        img.src = p.proof; img.alt = 'Additional payment screenshot';
        a.appendChild(img); box.appendChild(a);
      }
      list.appendChild(box);
    });

    receivedEl.value = data.total;
    confirmBox.checked = false;
    refundSent.checked = false;
    fullSent.checked = false;
    refundOpen = false;
    clearRefundFields();
    recalc();

    backdrop.style.display = 'flex';
    void backdrop.offsetWidth;
    backdrop.classList.add('open');
    receivedEl.focus();
  }

  function closeModal() {
    backdrop.classList.remove('open');
    backdrop.style.display = 'none';
    if (lastFocused && typeof lastFocused.focus === 'function') lastFocused.focus();
  }

  // Capture phase, so orders.js only sees this click once it is verified.
  document.addEventListener('click', function (e) {
    var btn = e.target.closest && e.target.closest('#orders-table-body [data-action="mark-preparing"][data-verify]');
    if (!btn) return;
    if (btn.dataset.verified === '1') { delete btn.dataset.verified; return; } // verified: let orders.js handle it
    e.preventDefault();
    e.stopPropagation();
    e.stopImmediatePropagation();
    openFor(btn);
  }, true);

  receivedEl.addEventListener('input', recalc);
  confirmBox.addEventListener('change', function () { if (mode === 'match') submitBtn.disabled = !confirmBox.checked; });
  function syncRefundGate() {
    if (refundOpen) refundBtn.disabled = !(fullSent.checked && refundReady('full'));
    else if (mode === 'over') submitBtn.disabled = !(refundSent.checked && refundReady('over'));
  }
  // Keep the customer message in step with the reference number being typed.
  function updateRefundMessages() {
    var received = parseFloat(receivedEl.value);
    if (isNaN(received) || !data) return;
    var diff = round2(received - data.total);
    if (mode === 'over' && !refundOpen && diff > 0) { $('vp-msg-over').value = overMessage(received, diff, REFUND.over.ref.value.trim()); refreshOver(); }
    if (refundOpen && received > 0) { $('vp-msg-full').value = fullMessage(received, REFUND.full.ref.value.trim()); refreshFull(); }
  }
  refundSent.addEventListener('change', syncRefundGate);
  fullSent.addEventListener('change', syncRefundGate);
  ['over', 'full'].forEach(function (k) {
    var r = REFUND[k];
    r.ref.addEventListener('input', function () { hideSaveError(); updateRefundMessages(); syncRefundGate(); });
    r.file.addEventListener('change', function () {
      hideSaveError();
      var f = r.file.files && r.file.files[0];
      if (f && (!/^image\//.test(f.type) || f.size > 5 * 1024 * 1024)) {
        r.file.value = ''; f = null;
        showSaveError('Please choose an image (JPG or PNG) under 5 MB.');
      }
      if (r.prev._url) { URL.revokeObjectURL(r.prev._url); r.prev._url = null; }
      if (f) { r.prev._url = URL.createObjectURL(f); r.prev.querySelector('img').src = r.prev._url; r.prev.style.display = ''; }
      else r.prev.style.display = 'none';
      syncRefundGate();
    });
  });

  // Save the admin's refund reference + screenshot on the order.
  function saveRefund(kind) {
    if (!SAVE_REFUND_RECORD) return Promise.resolve();
    var k = kind === 'full' ? 'full' : 'over';
    var received = parseFloat(receivedEl.value) || 0;
    var orderId = String(data.id || '').replace(/[^0-9]/g, '');
    var fd = new FormData();
    fd.append('type', kind === 'full' ? 'full' : 'overpayment');
    fd.append('amount', kind === 'full' ? received : round2(received - data.total));
    fd.append('reference', REFUND[k].ref.value.trim());
    fd.append('proof', REFUND[k].file.files[0]);
    fd.append(csrfName, currentHash());
    return fetch(REQUEST_BASE + '/' + orderId + '/refund', {
      method: 'POST', body: fd, credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' }
    }).then(function (r) {
      return r.json().catch(function () { return {}; }).then(function (res) {
        if (res.csrf) saveHash(res.csrf);
        if (!r.ok || !res.success) throw new Error(res.message || 'Could not save the refund details. Please try again.');
        return res;
      });
    });
  }

  // Refund: first press opens the refund panel, second press (after ticking "sent") cancels.
  refundBtn.addEventListener('click', function () {
    if (refundBtn.disabled) return;
    if (!refundOpen) {
      refundOpen = true;
      fullSent.checked = false;
      recalc();
      $('vp-panel-full').scrollIntoView({ block: 'nearest', behavior: 'smooth' });
      return;
    }
    if (!fullSent.checked || !refundReady('full')) return;
    // Confirm first: this cancels the order and tells the customer, and can't be undone.
    var refundAmount = parseFloat(receivedEl.value) || 0;
    if (!window.confirm('Refund ' + money(refundAmount) + ' and cancel order ' + (data.id || '') + '?\n\n' +
        'The order will be cancelled, its stock returned, and ' + firstName() + ' will be notified. This cannot be undone.')) return;
    refundBtn.disabled = true; refundBtn.textContent = 'Saving\u2026'; hideSaveError();
    saveRefund('full').then(function () { closeModal(); window.location.reload(); }).catch(function (err) {
      refundBtn.textContent = 'Refund';
      syncRefundGate();
      showSaveError(err.message || 'Could not save the refund details. Please try again.');
    });
  });
  // Request Payment: if the amount is blank, treat it as nothing received so
  // the request message for the full total is shown.
  // Request Payment: asks for confirmation first, then saves the request on the
  // order so "Add Payment" shows on the customer's order card. (Nothing is copied
  // to the clipboard here; use the "Copy message" button for that.)
  var rc = {
    backdrop: $('request-confirm-backdrop'), icon: $('rc-icon'), title: $('rc-title'), msg: $('rc-message'),
    cancel: $('rc-cancel'), confirm: $('rc-confirm'), close: $('rc-close')
  };
  var rcState = 'confirm', rcBalance = 0;

  function rcShow(state, text) {
    rcState = state;
    rc.icon.style.display = state === 'success' ? 'flex' : 'none';
    rc.cancel.style.display = state === 'success' ? 'none' : '';
    rc.cancel.disabled = state === 'sending';
    rc.confirm.disabled = state === 'sending';
    if (state === 'confirm') {
      rc.title.textContent = 'Request payment?';
      rc.msg.textContent = text || ('Ask ' + firstName() + ' to send ' + money(rcBalance) + '? The order stays Pending and \u201CAdd Payment\u201D will appear on their order.');
      rc.cancel.textContent = 'Cancel'; rc.confirm.textContent = 'Send Request';
    } else if (state === 'sending') {
      rc.confirm.textContent = 'Sending\u2026';
    } else if (state === 'success') {
      rc.title.textContent = 'Payment requested';
      rc.msg.textContent = text;
      rc.confirm.textContent = 'Done';
    } else {
      rc.title.textContent = 'Request not sent';
      rc.msg.textContent = text;
      rc.cancel.textContent = 'Close'; rc.confirm.textContent = 'Try again';
    }
  }
  function rcOpen() {
    rc.backdrop.style.display = 'flex';
    void rc.backdrop.offsetWidth;
    rc.backdrop.classList.add('open');
    rc.confirm.focus();
  }
  function rcClose() {
    rc.backdrop.classList.remove('open');
    rc.backdrop.style.display = 'none';
    requestBtn.focus();
  }

  function sendRequest(balance) {
    var statusEl = $('vp-request-status');
    statusEl.className = 'vp-hint vp-status';
    statusEl.style.display = '';
    statusEl.textContent = 'Sending request\u2026';
    if (rc.backdrop) rcShow('sending');

    var orderId = String(data.id || '').replace(/[^0-9]/g, '');
    var fd = new FormData();
    fd.append('amount', balance);
    fd.append(csrfName, currentHash());

    return fetch(REQUEST_BASE + '/' + orderId + '/request-payment', {
      method: 'POST',
      body: fd,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (res.csrf) saveHash(res.csrf);
        var ok = !!res.success;
        var text = ok
          ? 'Payment of ' + money(res.amount) + ' requested. \u201CAdd Payment\u201D now shows on the customer\u2019s order. You can also text or email the message above.'
          : (res.message || 'Could not save the request.');
        statusEl.classList.add(ok ? 'is-ok' : 'is-error');
        statusEl.textContent = text;
        if (rc.backdrop) rcShow(ok ? 'success' : 'error', text);
      })
      .catch(function () {
        var text = 'The request could not be saved. Please try again.';
        statusEl.classList.add('is-error');
        statusEl.textContent = text;
        if (rc.backdrop) rcShow('error', text);
      });
  }

  requestBtn.addEventListener('click', function () {
    if (requestBtn.disabled) return;
    if (isNaN(parseFloat(receivedEl.value))) { receivedEl.value = 0; recalc(); }
    $('vp-panel-short').scrollIntoView({ block: 'nearest', behavior: 'smooth' });

    var received = parseFloat(receivedEl.value) || 0;
    var balance = round2(data.total - received);
    if (balance <= 0) {
      var statusEl = $('vp-request-status');
      statusEl.className = 'vp-hint vp-status';
      statusEl.style.display = '';
      statusEl.textContent = 'Nothing to request: the full amount has been received.';
      return;
    }
    if (!rc.backdrop) { sendRequest(balance); return; }   // older markup: send straight away
    rcBalance = balance;
    rcShow('confirm');
    rcOpen();
  });

  if (rc.backdrop) {
    rc.confirm.addEventListener('click', function () {
      if (rc.confirm.disabled) return;
      if (rcState === 'success') { rcClose(); return; }
      sendRequest(rcBalance);
    });
    rc.cancel.addEventListener('click', function () { if (!rc.cancel.disabled) rcClose(); });
    rc.close.addEventListener('click', function () { if (rcState !== 'sending') rcClose(); });
    rc.backdrop.addEventListener('click', function (e) { if (e.target === rc.backdrop && rcState !== 'sending') rcClose(); });
    // Escape closes only this dialog, not the Verify Payment modal underneath.
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && rc.backdrop.classList.contains('open')) {
        e.stopImmediatePropagation();
        if (rcState !== 'sending') rcClose();
      }
    }, true);
  }
  $('vp-full-back').addEventListener('click', function () { refundOpen = false; recalc(); });

  // Verified (or refund sent): replay the click so orders.js moves it to Preparing.
  submitBtn.addEventListener('click', function () {
    if (submitBtn.disabled || !rowBtn) return;
    var btn = rowBtn;
    function proceed() { closeModal(); btn.dataset.verified = '1'; btn.click(); }
    if (mode !== 'over') { proceed(); return; }
    submitBtn.disabled = true; submitBtn.textContent = 'Saving\u2026'; hideSaveError();
    saveRefund('over').then(function () { submitBtn.textContent = 'Verify Payment'; proceed(); }).catch(function (err) {
      submitBtn.textContent = 'Verify Payment';
      syncRefundGate();
      showSaveError(err.message || 'Could not save the refund details. Please try again.');
    });
  });

  $('verify-modal-close').addEventListener('click', closeModal);
  backdrop.addEventListener('click', function (e) { if (e.target === backdrop) closeModal(); });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && backdrop.classList.contains('open')) closeModal();
  });
})();