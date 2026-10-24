/**
 * cart_actions.js — Kaye's Barbeque
 * -----------------------------------------------------------------
 * Drives the qty +/- and remove buttons inside #order-items-body on
 * orders.php, calling the /cart/update and /cart/remove endpoints
 * (OrderDetailController) and keeping the order summary + header
 * cart badge in sync with the server-side cart (the customer's
 * 'Cart' order and its order_item rows).
 *
 * Exposes window.OrderCart.render(items) so other scripts (the Place
 * Order modal in placeorder.js) can push a fresh server cart back
 * into the main page after they change it.
 *
 * Load order on orders.php: customer.js, cart_actions.js, placeorder.js.
 * Requires window.APP_BASE_URL (orders.php sets it inline).
 */
(function () {
  var cartBody = document.getElementById('order-items-body');
  if (!cartBody) return;

  var subtotalEl = document.getElementById('order-summary-subtotal');
  var totalEl = document.getElementById('order-summary-total');
  var busy = false;

  function url(path) {
    return (window.APP_BASE_URL || '/').replace(/\/$/, '') + path;
  }

  var FALLBACK_THUMB_SVG =
    '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">' +
    '<path d="M4 12c0-3.5 2-6 8-6s8 2.5 8 6-3.5 6-8 6-8-2.5-8-6Z" stroke="#C64B23" stroke-width="1.6" stroke-linejoin="round"/></svg>';

  function thumbHtml(item) {
    if (item.IMAGE) {
      return '<img src="' + escapeHtml(url('/uploads/' + item.IMAGE)) + '" alt="" ' +
        'style="width:100%;height:100%;object-fit:cover;border-radius:inherit;">';
    }
    return FALLBACK_THUMB_SVG;
  }

  // Same format as placeorder.js (up to 2 decimals, none when whole).
  function peso(n) {
    return '₱' + (Number(n) || 0).toLocaleString('en-PH', { maximumFractionDigits: 2 });
  }

  function escapeHtml(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function updateCartCountBadge(items) {
    var countEl = document.getElementById('cart-count');
    if (!countEl) return;
    var total = items.reduce(function (sum, i) { return sum + (parseInt(i.QUANTITY, 10) || 0); }, 0);
    countEl.textContent = total;
  }

  // Minimum order (window.MIN_ORDER_AMOUNT is set inline in orders.php).
  var MIN_ORDER = parseFloat(window.MIN_ORDER_AMOUNT) || 0;
  var placeBtn = document.getElementById('place-order-btn');
  var minNote = document.getElementById('min-order-note');

  function renderMinimum(subtotal) {
    var short = MIN_ORDER > 0 && subtotal < MIN_ORDER;
    if (placeBtn) placeBtn.disabled = short;
    if (minNote) {
      minNote.style.display = short ? '' : 'none';
      if (short) {
        minNote.textContent = 'Minimum order is ' + peso(MIN_ORDER) +
          '. Add ' + peso(MIN_ORDER - subtotal) + ' more to place your order.';
      }
    }
  }

  function renderSummary(items) {
    var subtotal = items.reduce(function (sum, i) { return sum + (parseFloat(i.SUB_TOTAL) || 0); }, 0);
    // Delivery fee is intentionally not shown here — it only appears
    // in the Place Order modal.
    if (subtotalEl) subtotalEl.textContent = peso(subtotal);
    if (totalEl) totalEl.textContent = peso(subtotal);
    renderMinimum(subtotal);
  }

  function renderCart(items) {
    items = Array.isArray(items) ? items : [];

    if (!items.length) {
      cartBody.innerHTML = '<div class="modal-empty" id="order-items-empty">' +
        '<p>Your cart is empty</p>' +
        '<p class="modal-empty-sub">Add items from the Menu to get started.</p></div>';
    } else {
      cartBody.innerHTML = items.map(function (item) {
        return '' +
          '<div class="order-item" data-order-detail-id="' + parseInt(item.ORDER_DETAIL_ID, 10) + '">' +
            '<div class="order-item-thumb">' + thumbHtml(item) + '</div>' +
            '<div>' +
              '<div class="order-item-name">' + escapeHtml(item.NAME) + '</div>' +
              '<div class="order-item-price">' + peso(parseFloat(item.PRICE)) + ' each</div>' +
            '</div>' +
            '<div class="order-item-qty">' +
              '<button class="stepper-btn order-item-qty-minus" aria-label="Decrease quantity">&minus;</button>' +
              '<span class="order-item-qty-value">' + (parseInt(item.QUANTITY, 10) || 0) + '</span>' +
              '<button class="stepper-btn order-item-qty-plus" aria-label="Increase quantity">+</button>' +
            '</div>' +
            '<div class="order-item-subtotal">' + peso(parseFloat(item.SUB_TOTAL)) + '</div>' +
            '<button class="order-item-remove" aria-label="Remove item">&times;</button>' +
          '</div>';
      }).join('');
    }

    renderSummary(items);
    updateCartCountBadge(items);

    // Keep the header cart dropdown (customer.js) in step as well.
    if (window.CustomerCart && typeof window.CustomerCart.render === 'function') {
      window.CustomerCart.render(items);
    }
  }

  // Public hook for placeorder.js.
  window.OrderCart = { render: renderCart };

  function postJSON(path, body) {
    return fetch(url(path), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body)
    }).then(function (res) { return res.json(); });
  }

  function run(path, body) {
    if (busy) return;
    busy = true;
    postJSON(path, body)
      .then(function (res) {
        busy = false;
        if (res.success) {
          renderCart(res.items);
        } else {
          alert(res.message || 'Could not update your cart.');
        }
      })
      .catch(function () {
        busy = false;
        alert('Something went wrong updating your cart. Please try again.');
      });
  }

  cartBody.addEventListener('click', function (e) {
    var row = e.target.closest('.order-item');
    if (!row) return;

    var id = parseInt(row.getAttribute('data-order-detail-id'), 10);
    var qtyEl = row.querySelector('.order-item-qty-value');
    var currentQty = parseInt(qtyEl ? qtyEl.textContent : '0', 10) || 0;

    if (e.target.closest('.order-item-qty-plus')) {
      run('/cart/update', { order_detail_id: id, quantity: currentQty + 1 });
    } else if (e.target.closest('.order-item-qty-minus')) {
      run('/cart/update', { order_detail_id: id, quantity: currentQty - 1 });
    } else if (e.target.closest('.order-item-remove')) {
      run('/cart/remove', { order_detail_id: id });
    }
  });
})();