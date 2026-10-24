/**
 * Orders page — order details modal (Kaye's Barbeque Admin Panel)
 * --------------------------------------------------------------
 * Clicking (or pressing Enter/Space on) an order row opens a modal
 * with the full order breakdown: customer info, delivery/pickup
 * details, itemized list, and totals.
 *
 * Guarded so it's a no-op on every other admin page (checks for
 * #orders-table-body before doing anything). Reads order detail
 * from a `data-order` JSON attribute on each <tr>.
 *
 * Modal markup lives in modals/order_modal.php and modals/cancel_modal.php
 * (included from orders.php); modal styling lives in css/modal.css and
 * the clickable-row styling lives in css/tables.css — this file only
 * wires up behavior, no CSS injection.
 */
(function () {
  'use strict';

  function init() {
    var tbody = document.getElementById('orders-table-body');
    if (!tbody) return; // Not on the orders page — nothing to do.

    var backdrop = document.getElementById('order-modal-backdrop');
    if (!backdrop) {
      // Modal markup missing: still wire up the status buttons so they keep working.
      initRowActions(tbody);
      return;
    }

    var lastFocused = null;

    var closeBtn = document.getElementById('order-modal-close');
    var titleEl = document.getElementById('order-modal-title');
    var statusEl = document.getElementById('order-modal-status');
    var placedEl = document.getElementById('order-modal-placed');
    var customerEl = document.getElementById('order-modal-customer');
    var phoneEl = document.getElementById('order-modal-phone');
    var fulfillmentLabelEl = document.getElementById('order-modal-fulfillment-label');
    var fulfillmentEl = document.getElementById('order-modal-fulfillment');
    var paymentEl = document.getElementById('order-modal-payment');
    var gcashWrapEl = document.getElementById('order-modal-gcash-wrap');
    var gcashReferenceEl = document.getElementById('order-modal-gcash-reference');
    var proofLinkEl = document.getElementById('order-modal-proof-link');
    var proofImgEl = document.getElementById('order-modal-proof-img');
    var itemsEl = document.getElementById('order-modal-items');
    // Older order_modal.php markup used <table><tbody id="order-modal-items">.
    // Items are now rendered as divs, so swap the table for a plain container.
    if (itemsEl && itemsEl.tagName === 'TBODY') {
      var oldTable = itemsEl.closest('table');
      var replacement = document.createElement('div');
      replacement.id = 'order-modal-items';
      replacement.className = 'om-items';
      if (oldTable && oldTable.parentNode) oldTable.parentNode.replaceChild(replacement, oldTable);
      itemsEl = replacement;
    }
    var totalsEl = document.getElementById('order-modal-totals');
    var notesWrapEl = document.getElementById('order-modal-notes-wrap');
    var notesEl = document.getElementById('order-modal-notes');

    // Lists the payments the customer added after checkout (Add Payment),
    // each with its reference number and screenshot, in the Order Details modal.
    function renderExtraPayments(extras) {
      var wrap = document.getElementById('order-modal-extra-wrap');
      var list = document.getElementById('order-modal-extra-list');
      if (!wrap || !list) return;
      if (!extras.length) { wrap.style.display = 'none'; list.innerHTML = ''; return; }
      list.innerHTML = extras.map(function (p, i) {
        var proof = p.proof
          ? '<a class="om-extra-proof" href="' + escapeHtml(p.proof) + '" target="_blank" rel="noopener">' +
              '<img src="' + escapeHtml(p.proof) + '" alt="Additional payment screenshot"></a>'
          : '';
        return '<div class="om-extra-pay">' +
          '<div class="om-extra-head">Payment ' + (i + 2) + (p.date ? ' \u00B7 ' + escapeHtml(p.date) : '') + '</div>' +
          '<div class="admin-modal-text">Ref: ' + escapeHtml(p.reference || '\u2014') + '</div>' +
          proof + '</div>';
      }).join('');
      wrap.style.display = '';
    }

    function escapeHtml(str) {
      var div = document.createElement('div');
      div.textContent = str == null ? '' : String(str);
      return div.innerHTML;
    }

    // Treat empty values and the literal text "None" as "nothing entered".
    function isBlank(v) {
      return v == null || /^\s*(none|null|n\/a)?\s*$/i.test(String(v));
    }

    // "Barangay, None" -> "Barangay"; "None" -> ''.
    function cleanAddress(v) {
      if (v == null) return '';
      return String(v).split(',')
        .map(function (part) { return part.trim(); })
        .filter(function (part) { return !isBlank(part); })
        .join(', ');
    }

    function formatPeso(n) {
      return '₱' + Number(n || 0).toLocaleString('en-PH');
    }

    function statusClass(status) {
      return 'status status-' + String(status || '').toLowerCase().replace(/[^a-z]/g, '');
    }

    function openModal(order) {
      lastFocused = document.activeElement;

      titleEl.textContent = order.id || '';
      statusEl.textContent = order.status || '';
      statusEl.className = statusClass(order.status);
      placedEl.textContent = order.placedAt ? 'Placed ' + order.placedAt : '';

      customerEl.textContent = order.customer || '—';
      var contact = [order.phone, order.email].filter(function (v) { return !isBlank(v); }).join(' \u00B7 ');
      phoneEl.textContent = contact;
      phoneEl.style.display = contact ? '' : 'none';

      if (order.type === 'Pickup') {
        fulfillmentLabelEl.textContent = 'Pickup';
        fulfillmentEl.textContent = order.pickupNote || 'Pickup at the shop';
      } else {
        fulfillmentLabelEl.textContent = 'Delivery Address';
        fulfillmentEl.textContent = cleanAddress(order.address) || 'No address provided';
      }

      paymentEl.textContent = order.payment || '—';

      // GCash orders carry a reference number and a proof-of-payment
      // screenshot (see OrderController::placeOrder() /
      // OrderModel::getActiveOrdersForAdmin()) — cash orders don't,
      // so this section only shows up when isGcash is true and a
      // proof image actually came through.
      var extraPayments = Array.isArray(order.extraPayments) ? order.extraPayments : [];
      if (order.isGcash && (order.proofOfPayment || extraPayments.length)) {
        gcashReferenceEl.textContent = (order.gcashReference || '—') +
          (order.paymentDate ? ' \u00B7 ' + order.paymentDate : '');
        if (order.proofOfPayment) {
          proofImgEl.src = order.proofOfPayment;
          proofLinkEl.href = order.proofOfPayment;
          proofLinkEl.style.display = '';
        } else {
          proofImgEl.removeAttribute('src');
          proofLinkEl.style.display = 'none';
        }
        renderExtraPayments(extraPayments);   // every payment sent after checkout
        gcashWrapEl.style.display = '';
      } else {
        renderExtraPayments([]);
        gcashWrapEl.style.display = 'none';
      }

      var items = Array.isArray(order.items) ? order.items : [];
      itemsEl.innerHTML = items.length ? items.map(function (item) {
        var qty = item.qty || 1;
        var price = item.price || 0;
        var thumbHtml = item.image
          ? '<img src="' + escapeHtml(item.image) + '" alt="" class="om-item-thumb">'
          : '<span class="om-item-thumb om-item-thumb-empty" aria-hidden="true"></span>';
        return (
          '<div class="om-item">' +
            '<div class="om-item-main">' + thumbHtml +
              '<div><div class="om-item-name">' + escapeHtml(item.name) + '</div>' +
              '<div class="om-item-each">' + formatPeso(price) + ' each</div></div>' +
            '</div>' +
            '<div class="om-item-qty">\u00D7' + qty + '</div>' +
            '<div class="om-item-total">' + formatPeso(qty * price) + '</div>' +
          '</div>'
        );
      }).join('') : '<div class="admin-modal-subtext">No items found for this order.</div>';

      var totalsHtml = '<div class="admin-modal-totals-row"><span>Subtotal</span><span>' + formatPeso(order.subtotal) + '</span></div>';
      if (order.deliveryFee) {
        totalsHtml += '<div class="admin-modal-totals-row"><span>Delivery Fee</span><span>' + formatPeso(order.deliveryFee) + '</span></div>';
      }
      totalsHtml += '<div class="admin-modal-totals-row admin-modal-totals-grand"><span>Total</span><span>' + formatPeso(order.total) + '</span></div>';
      totalsEl.innerHTML = totalsHtml;

      if (!isBlank(order.notes)) {
        notesWrapEl.style.display = '';
        notesEl.textContent = order.notes;
      } else {
        notesWrapEl.style.display = 'none';
      }

      backdrop.style.display = 'flex';
      backdrop.classList.add('open');
      document.body.style.overflow = 'hidden';
      document.addEventListener('keydown', onKeydown, true);
      closeBtn.focus();
    }

    function closeModal() {
      backdrop.classList.remove('open');
      backdrop.style.display = 'none';
      document.body.style.overflow = '';
      document.removeEventListener('keydown', onKeydown, true);
      if (lastFocused && typeof lastFocused.focus === 'function') lastFocused.focus();
    }

    function onKeydown(e) {
      if (e.key === 'Escape') closeModal();
    }

    backdrop.addEventListener('click', function (e) {
      if (e.target === backdrop) closeModal();
    });
    closeBtn.addEventListener('click', closeModal);

    function handleRowActivate(row) {
      var raw = row.getAttribute('data-order');
      if (!raw) return;
      var order;
      try {
        order = JSON.parse(raw);
      } catch (e) {
        return;
      }
      openModal(order);
    }

    tbody.addEventListener('click', function (e) {
      var actionBtn = e.target.closest('.row-actions [data-action]');
      if (actionBtn) {
        // The "View" button opens the details modal; other action buttons work as normal.
        if (actionBtn.getAttribute('data-action') !== 'view') return;
      } else if (e.target.closest('.row-actions')) {
        return;
      }
      var row = e.target.closest('tr[data-order]');
      if (!row) return;
      handleRowActivate(row);
    });

    tbody.addEventListener('keydown', function (e) {
      if (e.key !== 'Enter' && e.key !== ' ') return;
      if (e.target.closest('.row-actions')) return; // let buttons handle their own Enter/Space
      var row = e.target.closest('tr[data-order]');
      if (!row) return;
      e.preventDefault();
      handleRowActivate(row);
    });

    initRowActions(tbody);
  }

  /* ------------------------------------------------------------ *
   * Row actions — Mark Delivering / Mark Complete / Cancel.
   * Preparing -> Delivering (row stays, buttons swap to Mark
   * Complete). Delivering -> Delivered, or Cancel from either
   * state, removes the row (it now belongs on the Sales page).
   * ------------------------------------------------------------ */
  function initRowActions(tbody) {
    var totalEl = document.getElementById('o-total');
    var pendingEl = document.getElementById('o-pending');
    var deliveringEl = document.getElementById('o-delivering');
    var countNote = document.getElementById('orders-count-note');
    var emptyNote = document.getElementById('orders-empty');
    var notice = document.getElementById('orders-notice');
    var cancelModal = createCancelModal();

    function bumpStat(el, delta) {
      if (!el) return;
      var current = parseInt(el.textContent, 10) || 0;
      el.textContent = Math.max(0, current + delta);
    }

    function showNotice(message, isError) {
      if (!notice) return;
      notice.textContent = message;
      notice.classList.toggle('notice-error', !!isError);
      notice.classList.toggle('notice-success', !isError);
      notice.style.display = 'block';
      window.scrollTo({ top: 0, behavior: 'smooth' });
      setTimeout(function () { notice.style.display = 'none'; }, 4000);
    }

    /**
     * Persists a status change to OrderController::updateStatus()
     * before touching the DOM. Every row action below waits on this
     * and only updates the row/stats once the server confirms the
     * change — so a failed request (network hiccup, stale status,
     * session timed out) leaves the row exactly as it was instead of
     * silently drifting out of sync with the database.
     */
    function postStatus(orderId, status) {
      var numericId = String(orderId).replace(/^#/, '');
      // URL + CSRF token come from data-* attributes on #orders-table-body
      // (see orders.php). The hash rotates after every request, so the
      // latest one is kept on the element and shared with the verify modal.
      var cfg = tbody.dataset;
      var base = cfg.statusBase || '/admin/orders';
      var csrfName = cfg.csrfName || '';
      var csrfHash = cfg.csrfHash || '';
      var body = 'status=' + encodeURIComponent(status);
      var headers = {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-Requested-With': 'XMLHttpRequest'
      };
      if (csrfName) {
        body += '&' + encodeURIComponent(csrfName) + '=' + encodeURIComponent(csrfHash);
        headers['X-CSRF-TOKEN'] = csrfHash;
      }
      return fetch(base + '/' + encodeURIComponent(numericId) + '/status', {
        method: 'POST',
        headers: headers,
        credentials: 'same-origin',
        body: body
      }).then(function (res) {
        return res.json().catch(function () { return {}; }).then(function (data) {
          if (data.csrf) tbody.dataset.csrfHash = data.csrf;
          if (!res.ok || !data.success) {
            throw new Error(data.message || ('Could not update that order (HTTP ' + res.status + ').'));
          }
          return data;
        });
      });
    }

    function withButtonLock(btn, promise) {
      if (btn) btn.disabled = true;
      return promise.finally(function () {
        if (btn) btn.disabled = false;
      });
    }

    function updateCountNote() {
      if (!countNote) return;
      var remaining = tbody.querySelectorAll('tr[data-order]').length;
      countNote.textContent = 'Showing ' + remaining + ' of ' + remaining + ' orders';
      if (emptyNote) emptyNote.style.display = remaining === 0 ? 'block' : 'none';
    }

    function updateRowOrderStatus(row, status) {
      var raw = row.getAttribute('data-order');
      if (!raw) return;
      try {
        var order = JSON.parse(raw);
        order.status = status;
        row.setAttribute('data-order', JSON.stringify(order));
      } catch (e) { /* leave as-is if it can't be parsed */ }
    }

    function markPreparing(row, btn) {
      var orderId = row.querySelector('.order-id').textContent;

      return withButtonLock(btn, postStatus(orderId, 'Preparing')).then(function () {
        var statusCell = row.querySelector('td:nth-last-child(2) .status');
        if (statusCell) {
          statusCell.textContent = 'Preparing';
          statusCell.className = 'status status-preparing';
        }
        updateRowOrderStatus(row, 'Preparing');

        var actionBtn = row.querySelector('[data-action="mark-preparing"]');
        if (actionBtn) {
          actionBtn.textContent = 'Mark Delivering';
          actionBtn.setAttribute('data-action', 'mark-delivering');
        }

        showNotice(orderId + ' marked as Preparing.');
      }).catch(function (err) {
        showNotice(err.message || (orderId + ' could not be updated.'), true);
      });
    }

    function markDelivering(row, btn) {
      var orderId = row.querySelector('.order-id').textContent;

      return withButtonLock(btn, postStatus(orderId, 'Delivering')).then(function () {
        var statusCell = row.querySelector('td:nth-last-child(2) .status');
        if (statusCell) {
          statusCell.textContent = 'Delivering';
          statusCell.className = 'status status-delivering';
        }
        updateRowOrderStatus(row, 'Delivering');

        var actionBtn = row.querySelector('[data-action="mark-delivering"]');
        if (actionBtn) {
          actionBtn.textContent = 'Mark Complete';
          actionBtn.setAttribute('data-action', 'mark-complete');
        }

        bumpStat(pendingEl, -1);
        bumpStat(deliveringEl, 1);
        showNotice(orderId + ' marked as Delivering.');
      }).catch(function (err) {
        showNotice(err.message || (orderId + ' could not be updated.'), true);
      });
    }

    function markComplete(row, btn) {
      var orderId = row.querySelector('.order-id').textContent;

      return withButtonLock(btn, postStatus(orderId, 'Delivered')).then(function () {
        bumpStat(deliveringEl, -1);
        bumpStat(totalEl, -1);
        row.remove();
        updateCountNote();
        showNotice(orderId + ' marked as Delivered and moved to Sales.');
      }).catch(function (err) {
        showNotice(err.message || (orderId + ' could not be updated.'), true);
      });
    }

    function cancelOrder(row, btn) {
      var orderId = row.querySelector('.order-id').textContent;
      var wasDelivering = !!row.querySelector('.status-delivering');

      return withButtonLock(btn, postStatus(orderId, 'Cancelled')).then(function () {
        bumpStat(wasDelivering ? deliveringEl : pendingEl, -1);
        bumpStat(totalEl, -1);
        row.remove();
        updateCountNote();
        showNotice(orderId + ' has been cancelled.');
      }).catch(function (err) {
        showNotice(err.message || (orderId + ' could not be cancelled.'), true);
      });
    }

    function isOnlinePayment(payment) {
      // "Cash on Delivery" / "Cash on Pickup" are not online payments;
      // anything else (GCash, cards, e-wallets, etc.) is treated as online.
      return !!payment && payment.toLowerCase().indexOf('cash') === -1;
    }

    function requestCancel(row, btn) {
      var orderId = row.querySelector('.order-id').textContent;
      var customer = row.children[1] ? row.children[1].textContent : '';
      var total = row.querySelector('.order-total') ? row.querySelector('.order-total').textContent : '';

      var status = '';
      var payment = '';
      var raw = row.getAttribute('data-order');
      if (raw) {
        try {
          var order = JSON.parse(raw);
          status = order.status || '';
          payment = order.payment || '';
        } catch (e) { /* no order data — skip the payment-based note */ }
      }

      var note = null;
      if (status === 'Delivering') {
        if (isOnlinePayment(payment)) {
          note = {
            type: 'warning',
            text: 'This order is already out for delivery and was paid via ' + payment +
              '. Cancelling now is non-refundable.'
          };
        } else {
          note = {
            type: 'info',
            text: 'This order is already out for delivery. Since it\u2019s ' + (payment || 'Cash on Delivery') +
              ', no payment has been collected yet, so there\u2019s nothing to refund.'
          };
        }
      }

      cancelModal.open({ orderId: orderId, customer: customer, total: total, note: note }, function () {
        cancelOrder(row, btn);
      });
    }

    tbody.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-action]');
      if (!btn) return;
      e.stopPropagation(); // Don't also trigger the row's detail-modal click.

      var row = btn.closest('tr[data-order]');
      if (!row) return;

      var action = btn.getAttribute('data-action');
      if (action === 'mark-preparing') markPreparing(row, btn);
      else if (action === 'mark-delivering') markDelivering(row, btn);
      else if (action === 'mark-complete') markComplete(row, btn);
      else if (action === 'cancel') requestCancel(row, btn);
    });
  }

  /* ------------------------------------------------------------ *
   * Cancel-order confirmation modal. Wires up the shared instance
   * from modals/cancel_modal.php (included on orders.php) — call
   * .open(details, onConfirm) to show it for a given row.
   * ------------------------------------------------------------ */
  function createCancelModal() {
    var backdrop = document.getElementById('cancel-modal-backdrop');
    if (!backdrop) return { open: function () {} }; // Modal markup missing from the page.

    var titleEl = document.getElementById('cancel-modal-title');
    var messageEl = document.getElementById('cancel-modal-message');
    var warningEl = document.getElementById('cancel-modal-warning');
    var closeBtn = document.getElementById('cancel-modal-close');
    var keepBtn = document.getElementById('cancel-modal-keep');
    var confirmBtn = document.getElementById('cancel-modal-confirm');

    var lastFocused = null;
    var onConfirm = null;

    function close() {
      backdrop.classList.remove('open');
      backdrop.style.display = 'none';
      document.body.style.overflow = '';
      document.removeEventListener('keydown', onKeydown, true);
      if (lastFocused && typeof lastFocused.focus === 'function') lastFocused.focus();
      onConfirm = null;
    }

    function onKeydown(e) {
      if (e.key === 'Escape') close();
    }

    function open(details, onConfirmCallback) {
      titleEl.textContent = details.orderId || '';
      messageEl.textContent = 'Are you sure you want to cancel ' + (details.orderId || 'this order') +
        (details.customer ? ' for ' + details.customer : '') +
        (details.total ? ' (' + details.total + ')' : '') +
        '? This can\u2019t be undone.';

      if (details.note && details.note.text) {
        warningEl.textContent = details.note.text;
        warningEl.className = 'admin-modal-note ' +
          (details.note.type === 'warning' ? 'admin-modal-note-warning' : 'admin-modal-note-info');
        warningEl.style.display = 'block';
      } else {
        warningEl.style.display = 'none';
      }

      onConfirm = onConfirmCallback;

      lastFocused = document.activeElement;
      backdrop.style.display = 'flex';
      backdrop.classList.add('open');
      document.body.style.overflow = 'hidden';
      document.addEventListener('keydown', onKeydown, true);
      keepBtn.focus();
    }

    backdrop.addEventListener('click', function (e) {
      if (e.target === backdrop) close();
    });
    closeBtn.addEventListener('click', close);
    keepBtn.addEventListener('click', close);
    confirmBtn.addEventListener('click', function () {
      var callback = onConfirm;
      close();
      if (callback) callback();
    });

    return { open: open };
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();

(function () {
  var filterBar = document.getElementById('tracking-filters');
  var tableBody = document.getElementById('tracking-table-body');
  var emptyMsg = document.getElementById('tracking-empty');

  if (!filterBar || !tableBody) return;

  var rows = tableBody.querySelectorAll('[data-filter]');

  function applyFilter(filter) {
    var visibleCount = 0;

    rows.forEach(function (row) {
      var matches = filter === 'all' || row.getAttribute('data-filter') === filter;
      row.style.display = matches ? '' : 'none';
      if (matches) visibleCount++;
    });

    if (emptyMsg) {
      emptyMsg.style.display = visibleCount === 0 ? '' : 'none';
    }
  }

  filterBar.addEventListener('click', function (e) {
    var chip = e.target.closest('.chip');
    if (!chip || !filterBar.contains(chip)) return;

    var chips = filterBar.querySelectorAll('.chip');
    chips.forEach(function (c) { c.classList.remove('active'); });
    chip.classList.add('active');

    applyFilter(chip.getAttribute('data-filter'));
  });
})();