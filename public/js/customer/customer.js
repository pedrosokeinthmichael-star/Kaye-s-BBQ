/**
 * customer.js — Kaye's Barbeque, merged customer-side scripts
 * -------------------------------------------------------------
 * Combines (in order):
 *   1. cart.js          — cart modal open/close
 *   2. order.js          — place-order modal, delivery/pickup + payment toggles
 *   3. profile.js        — profile save/cancel handlers
 *   4. (notification bell now lives in order_notifications.js)
 *
 * The item-detail modal (#item-modal, used on barbeques.php) lives
 * separately in item-modal.js — include that file only on barbeques.php.
 *
 * Each section is self-contained (own IIFE) and guards against missing
 * DOM elements, so this single file can be safely included on every
 * customer page (index.php, barbeques.php, orders.php, history.php,
 * profile.php) regardless of which elements that page actually has.
 *
 * Include once, near the end of <body>:
 *   <script src="<?=base_url('js/customer/customer.js')?>"></script>
 */

/* ===================================================================
 * 1) CART MODAL  (from cart.js)
 * =================================================================== */
/* ===================================================================
 * 1) CART MODAL + shared cart state (from cart.js)
 * -------------------------------------------------------------------
 * Owns #cart-modal-body / #cart-count. Exposes window.CustomerCart so
 * item_modal.js and cart_actions.js can push updates here the instant
 * the cart changes, instead of only updating their own page's UI.
 * =================================================================== */
(function () {
  var cartBtn = document.getElementById('cart-btn');
  var cartModal = document.getElementById('cart-modal');
  var cartClose = document.getElementById('cart-modal-close');
  var cartBody = document.getElementById('cart-modal-body');
  var cartFooter = document.getElementById('cart-modal-footer');
  var countEl = document.getElementById('cart-count');

  function url(path) {
    return (window.APP_BASE_URL || '/').replace(/\/$/, '') + path;
  }
  function peso(n) {
    return '₱' + n.toLocaleString('en-PH', { maximumFractionDigits: 0 });
  }
  function escapeHtml(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
  }
  function thumbHtml(item) {
    if (item.IMAGE) {
      return '<img src="' + url('/uploads/' + item.IMAGE) + '" alt="" ' +
        'style="width:100%;height:100%;object-fit:cover;border-radius:inherit;">';
    }
    return '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">' +
      '<path d="M4 12c0-3.5 2-6 8-6s8 2.5 8 6-3.5 6-8 6-8-2.5-8-6Z" stroke="#C64B23" stroke-width="1.6" stroke-linejoin="round"/></svg>';
  }

  function updateBadge(items) {
    if (!countEl) return;
    var total = (items || []).reduce(function (sum, i) { return sum + parseInt(i.QUANTITY, 10); }, 0);
    countEl.textContent = total;
  }

  function postJSON(path, body) {
    return fetch(url(path), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body)
    }).then(function (res) { return res.json(); });
  }

  function render(items) {
    items = items || [];
    updateBadge(items);
    if (!cartBody) return;

    if (!items.length) {
      cartBody.innerHTML =
        '<div class="modal-empty"><svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">' +
        '<path d="M3 4h2l2.4 12.2a2 2 0 0 0 2 1.8h8.2a2 2 0 0 0 2-1.6L21 8H6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>' +
        '<circle cx="10" cy="21" r="1.4" fill="currentColor"/><circle cx="17" cy="21" r="1.4" fill="currentColor"/></svg>' +
        '<p>Your cart is empty</p><p class="modal-empty-sub">Items you add on the Menu page will show up here.</p></div>';
      if (cartFooter) {
        cartFooter.innerHTML = '<a href="' + url('/customer/barbeques') + '" class="btn btn-primary" style="width:100%;text-align:center;">Browse Menu</a>';
      }
      return;
    }

    var subtotal = items.reduce(function (sum, i) { return sum + parseFloat(i.SUB_TOTAL); }, 0);

    cartBody.innerHTML = items.map(function (item) {
      return '' +
        '<div class="order-item" data-order-detail-id="' + item.ORDER_DETAIL_ID + '">' +
          '<div class="order-item-thumb">' + thumbHtml(item) + '</div>' +
          '<div>' +
            '<div class="order-item-name">' + escapeHtml(item.NAME) + '</div>' +
            '<div class="order-item-price">' + peso(parseFloat(item.PRICE)) + ' each</div>' +
          '</div>' +
          '<div class="order-item-qty">' +
            '<button class="stepper-btn order-item-qty-minus" aria-label="Decrease quantity">&minus;</button>' +
            '<span class="order-item-qty-value">' + item.QUANTITY + '</span>' +
            '<button class="stepper-btn order-item-qty-plus" aria-label="Increase quantity">+</button>' +
          '</div>' +
          '<div class="order-item-subtotal">' + peso(parseFloat(item.SUB_TOTAL)) + '</div>' +
          '<button class="order-item-remove" aria-label="Remove item">&times;</button>' +
        '</div>';
    }).join('');

    if (cartFooter) {
      cartFooter.innerHTML =
        '<div class="summary-line" style="margin-bottom:12px;"><span>Subtotal</span><span>' + peso(subtotal) + '</span></div>' +
        '<a href="' + url('/customer/orders') + '" class="btn btn-primary" style="width:100%;text-align:center;">Go to Checkout</a>';
    }
  }

  function refresh() {
    return fetch(url('/cart'))
      .then(function (res) { return res.json(); })
      .then(function (res) { if (res.success) render(res.items); return res; })
      .catch(function () { /* not logged in / network error — leave as-is */ });
  }

  if (cartBody) {
    cartBody.addEventListener('click', function (e) {
      var row = e.target.closest('.order-item');
      if (!row) return;
      var id = parseInt(row.getAttribute('data-order-detail-id'), 10);
      var qtyEl = row.querySelector('.order-item-qty-value');
      var currentQty = parseInt(qtyEl ? qtyEl.textContent : '0', 10) || 0;

      if (e.target.closest('.order-item-qty-plus')) {
        postJSON('/cart/update', { order_detail_id: id, quantity: currentQty + 1 })
          .then(function (res) { if (res.success) render(res.items); });
      } else if (e.target.closest('.order-item-qty-minus')) {
        postJSON('/cart/update', { order_detail_id: id, quantity: currentQty - 1 })
          .then(function (res) { if (res.success) render(res.items); });
      } else if (e.target.closest('.order-item-remove')) {
        postJSON('/cart/remove', { order_detail_id: id })
          .then(function (res) { if (res.success) render(res.items); });
      }
    });
  }

  // Shared handle — item_modal.js / cart_actions.js call this after
  // any cart change so the header dropdown + badge update everywhere.
  window.CustomerCart = { render: render, refresh: refresh, updateBadge: updateBadge };

  // Sync badge/dropdown with the server on every page load (fixes the
  // badge being hardcoded to "0" in the HTML regardless of real cart).
  refresh();

  if (!cartModal) return;

  function openCart() { cartModal.classList.add('open'); refresh(); }
  function closeCart() { cartModal.classList.remove('open'); }

  if (cartBtn) cartBtn.addEventListener('click', openCart);
  if (cartClose) cartClose.addEventListener('click', closeCart);
  cartModal.addEventListener('click', function (e) { if (e.target === cartModal) closeCart(); });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && cartModal.classList.contains('open')) closeCart();
  });
})();

/* ===================================================================
 * 2) PLACE ORDER MODAL  (from order.js)
 * =================================================================== */
(function () {
  var placeOrderBtn = document.getElementById('place-order-btn');
  var placeOrderModal = document.getElementById('place-order-modal');
  var placeOrderClose = document.getElementById('place-order-modal-close');
  var confirmOrderBtn = document.getElementById('confirm-order-btn');

  if (!placeOrderModal) return;

  function openModal() {
    placeOrderModal.classList.add('open');
  }

  function closeModal() {
    placeOrderModal.classList.remove('open');
  }

  if (placeOrderBtn) {
    placeOrderBtn.addEventListener('click', openModal);
  }

  if (placeOrderClose) {
    placeOrderClose.addEventListener('click', closeModal);
  }

  // Close when clicking the dark overlay outside the modal box
  placeOrderModal.addEventListener('click', function (e) {
    if (e.target === placeOrderModal) closeModal();
  });

  // Close on Escape key
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && placeOrderModal.classList.contains('open')) {
      closeModal();
    }
  });

  // Order type toggle (Delivery / Pickup)
  var orderTypeToggle = document.getElementById('order-type-toggle');
  var deliveryAddressGroup = document.getElementById('delivery-address-group');

  if (orderTypeToggle) {
    orderTypeToggle.addEventListener('click', function (e) {
      var chip = e.target.closest('.chip');
      if (!chip) return;

      var chips = orderTypeToggle.querySelectorAll('.chip');
      chips.forEach(function (c) { c.classList.remove('active'); });
      chip.classList.add('active');

      if (deliveryAddressGroup) {
        var isPickup = chip.getAttribute('data-order-type') === 'pickup';
        deliveryAddressGroup.style.display = isPickup ? 'none' : '';
      }
    });
  }

  // Payment method toggle (Cash / GCash)
  var paymentMethodToggle = document.getElementById('payment-method-toggle');

  if (paymentMethodToggle) {
    paymentMethodToggle.addEventListener('click', function (e) {
      var chip = e.target.closest('.chip');
      if (!chip) return;

      var chips = paymentMethodToggle.querySelectorAll('.chip');
      chips.forEach(function (c) { c.classList.remove('active'); });
      chip.classList.add('active');
    });
  }

  // Placeholder confirm handler — no backend call yet.
  if (confirmOrderBtn) {
    confirmOrderBtn.addEventListener('click', function () {
      // Order submission logic goes here once the backend endpoint exists.
      closeModal();
    });
  }
})();

/* ===================================================================
 * 3) PROFILE FORM  (from profile.js)
 * =================================================================== */
(function () {
  var saveBtn = document.getElementById('profile-save-btn');
  var cancelBtn = document.getElementById('profile-cancel-btn');
  var form = document.querySelector('.form-card');

  if (saveBtn) {
    saveBtn.addEventListener('click', function () {
      // Account update logic goes here once the backend endpoint exists.
    });
  }

  if (cancelBtn && form) {
    cancelBtn.addEventListener('click', function () {
      var inputs = form.querySelectorAll('input, textarea');
      inputs.forEach(function (input) {
        input.value = input.defaultValue;
      });
    });
  }
})();

/* ===================================================================
 * 4) ORDER HISTORY FILTER TABS  (history.php)
 * ------------------------------------------------------------------
 * Wires up the "All Orders / Delivered / Cancelled" chips above the
 * history table. Clicking a chip shows only the rows whose
 * data-filter attribute matches (or all rows, for "all"), and shows
 * a friendly empty-state message if a category has no rows.
 * =================================================================== */
(function () {
  var filterBar = document.getElementById('history-filters');
  var tableBody = document.getElementById('history-table-body');
  var emptyMsg = document.getElementById('history-empty');

  if (!filterBar || !tableBody) return;

  // '[data-filter]' rather than 'tr' so this works whether
  // #history-table-body holds <tr> rows (old table layout) or
  // .order-card elements (new card grid layout).
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

/* ===================================================================
 * 4b) ORDER TRACKING FILTER TABS  (orders.php)
 * ------------------------------------------------------------------
 * Same "All / Pending / Preparing / Delivering" chip pattern as the
 * history filters above, wired to #tracking-filters / #tracking-
 * table-body instead. Kept as its own small block (rather than
 * generalizing the block above) so each page's filter bar can still
 * be edited independently later without touching the other.
 * =================================================================== */
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