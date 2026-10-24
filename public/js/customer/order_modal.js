/**
 * order_modal.js — Kaye's Barbeque
 * -----------------------------------------------------------------
 * Opens components/order_modal.php's #order-modal when an
 * .order-card (components/order_card.php) is clicked or activated
 * with Enter/Space, and fills it in from that card's data-order
 * JSON payload — order id, date, type, status, customer info,
 * itemized (or summary) items, payment method, and totals.
 *
 * Sections with no data for a given order (e.g. no address on a
 * Pickup order, no payment reference on a COD order) are hidden
 * rather than shown empty.
 *
 * Include on any page that uses order cards — currently orders.php
 * and history.php — after order_card.css and the modal markup are
 * in the DOM. Guarded (bails out if #order-modal isn't on the page).
 */
(function () {
  var modal = document.getElementById('order-modal');
  if (!modal) return;

  var titleEl = document.getElementById('order-modal-title');
  var dateEl = document.getElementById('order-modal-date');
  var typeEl = document.getElementById('order-modal-type');
  var statusEl = document.getElementById('order-modal-status');

  var customerSection = document.getElementById('order-modal-customer-section');
  var customerList = document.getElementById('order-modal-customer-list');

  var notesSection = document.getElementById('order-modal-notes-section');
  var notesEl = document.getElementById('order-modal-notes');

  var itemsEl = document.getElementById('order-modal-items');

  var paymentSection = document.getElementById('order-modal-payment-section');
  var paymentList = document.getElementById('order-modal-payment-list');

  var subtotalEl = document.getElementById('order-modal-subtotal');
  var deliveryFeeRow = document.getElementById('order-modal-delivery-fee-row');
  var deliveryFeeEl = document.getElementById('order-modal-delivery-fee');
  var totalEl = document.getElementById('order-modal-total');

  var closeBtn = document.getElementById('order-modal-close');
  var closeFooterBtn = document.getElementById('order-modal-close-btn');

  var proofWrap = document.getElementById('order-modal-proof');
  var proofImg = document.getElementById('order-modal-proof-img');

  var FALLBACK_THUMB_SVG =
    '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">' +
    '<path d="M4 12c0-3.5 2-6 8-6s8 2.5 8 6-3.5 6-8 6-8-2.5-8-6Z" stroke="#C64B23" stroke-width="1.6" stroke-linejoin="round"/></svg>';

  function url(path) {
    return (window.APP_BASE_URL || '/').replace(/\/$/, '') + path;
  }

  function peso(n) {
    return '₱' + (parseFloat(n) || 0).toLocaleString('en-PH', { maximumFractionDigits: 0 });
  }

  function escapeHtml(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;');
  }

  // Cash/pickup orders carry the literal string 'None' for fields
  // that only apply to delivery/GCash orders — treat that the same
  // as missing data so the matching row/section stays hidden.
  function isEmptyValue(v) {
    return v === undefined || v === null || v === '' || v === 'None';
  }

  function itemThumbHtml(item) {
    if (item && item.IMAGE) {
      return '<img src="' + url('/uploads/' + item.IMAGE) + '" alt="">';
    }
    return FALLBACK_THUMB_SVG;
  }

  function detailRow(label, value) {
    return '' +
      '<div class="order-modal-detail-row">' +
        '<span class="order-modal-detail-label">' + escapeHtml(label) + '</span>' +
        '<span class="order-modal-detail-value">' + escapeHtml(value) + '</span>' +
      '</div>';
  }

  function parseOrder(raw) {
    if (!raw) return null;
    try {
      return JSON.parse(raw);
    } catch (e) {
      return null;
    }
  }

  function renderCustomer(order) {
    var rows = '';
    if (!isEmptyValue(order.customerName)) rows += detailRow('Name', order.customerName);
    if (!isEmptyValue(order.customerEmail)) rows += detailRow('Email', order.customerEmail);
    if (!isEmptyValue(order.customerPhone)) rows += detailRow('Phone', order.customerPhone);
    if (!isEmptyValue(order.deliveryAddress)) rows += detailRow('Address', order.deliveryAddress);

    if (rows) {
      customerList.innerHTML = rows;
      customerSection.style.display = '';
    } else {
      customerSection.style.display = 'none';
    }
  }

  function renderNotes(order) {
    if (!isEmptyValue(order.notes)) {
      notesEl.textContent = order.notes;
      notesSection.style.display = '';
    } else {
      notesSection.style.display = 'none';
    }
  }

  function renderItems(order) {
    var items = Array.isArray(order.items) ? order.items : [];

    if (items.length) {
      itemsEl.innerHTML = items.map(function (item) {
        var qty = parseInt(item.QUANTITY, 10) || 0;
        var price = parseFloat(item.PRICE) || 0;
        var sub = item.SUB_TOTAL != null ? parseFloat(item.SUB_TOTAL) : qty * price;
        return '' +
          '<div class="order-modal-line">' +
            '<div class="order-modal-line-thumb">' + itemThumbHtml(item) + '</div>' +
            '<div>' +
              '<div class="order-modal-line-name">' + escapeHtml(item.NAME || '') + '</div>' +
              '<div class="order-modal-line-price">' + qty + ' &times; ' + peso(price) + '</div>' +
            '</div>' +
            '<div class="order-modal-line-subtotal">' + peso(sub) + '</div>' +
          '</div>';
      }).join('');
    } else if (order.itemsSummary) {
      // No itemized breakdown available — fall back to the same
      // summary text shown on the card itself.
      itemsEl.innerHTML = '<p style="color:var(--ink-soft);font-size:13.5px;margin:0;">' +
        escapeHtml(order.itemsSummary) + '</p>';
    } else {
      itemsEl.innerHTML = '<div class="modal-empty"><p>No item details available</p></div>';
    }
  }

  function renderPayment(order) {
    var rows = '';
    if (!isEmptyValue(order.paymentMethod)) rows += detailRow('Method', order.paymentMethod);
    if (!isEmptyValue(order.paymentReference)) rows += detailRow('Reference', order.paymentReference);

    if (rows) {
      paymentList.innerHTML = rows;
    } else {
      paymentList.innerHTML = '';
    }

    // Only GCash orders carry a proof-of-payment screenshot — Cash
    // orders store 'None' (normalized to '' server-side).
    if (proofWrap && proofImg) {
      if (!isEmptyValue(order.paymentProof)) {
        proofImg.src = url('/uploads/payments/' + order.paymentProof);
        proofWrap.style.display = '';
      } else {
        proofImg.src = '';
        proofWrap.style.display = 'none';
      }
    }

    var hasProof = proofWrap && proofWrap.style.display !== 'none';
    if (rows || hasProof) {
      paymentSection.style.display = '';
    } else {
      paymentSection.style.display = 'none';
    }
  }

  function openFromCard(card) {
    var order = parseOrder(card.getAttribute('data-order'));
    if (!order) return;

    if (titleEl) titleEl.textContent = 'Order #KB-' + order.orderId;
    if (dateEl) dateEl.textContent = order.date || '—';
    if (typeEl) typeEl.textContent = order.type || '—';

    if (statusEl) {
      statusEl.textContent = order.status || '—';
      statusEl.className = 'status status-' + (order.statusKey || '');
    }

    renderCustomer(order);
    renderNotes(order);
    renderItems(order);
    renderPayment(order);

    var deliveryFee = parseFloat(order.deliveryFee) || 0;
    if (subtotalEl) subtotalEl.textContent = peso(order.subtotal);
    if (totalEl) totalEl.textContent = peso(order.total);
    if (deliveryFeeEl) deliveryFeeEl.textContent = peso(deliveryFee);
    if (deliveryFeeRow) deliveryFeeRow.style.display = deliveryFee > 0 ? '' : 'none';

    modal.classList.add('open');
  }

  function closeModal() {
    modal.classList.remove('open');
  }

  // Event delegation covers cards rendered now or added later (e.g.
  // after a filter chip re-renders the list).
  document.addEventListener('click', function (e) {
    var card = e.target.closest('.order-card');
    if (card) openFromCard(card);
  });

  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Enter' && e.key !== ' ') return;
    var card = e.target.closest && e.target.closest('.order-card');
    if (!card) return;
    e.preventDefault();
    openFromCard(card);
  });

  if (closeBtn) closeBtn.addEventListener('click', closeModal);
  if (closeFooterBtn) closeFooterBtn.addEventListener('click', closeModal);

  modal.addEventListener('click', function (e) {
    if (e.target === modal) closeModal();
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && modal.classList.contains('open')) closeModal();
  });
})();