/**
 * placeorder.js — Kaye's Barbeque, sectioned "Place Order" modal
 * -----------------------------------------------------------------
 * Drives #place-order-modal on orders.php. The modal is split into
 * three sections: (1) Customer Details, (2) Order Details, and
 * (3) Payment.
 *
 * Cart source of truth is the SERVER. When the modal opens it loads
 * the live cart (GET /cart); every qty / remove action inside the
 * modal calls /cart/update or /cart/remove and re-renders from the
 * server's response, then pushes that same cart back into the main
 * page through window.OrderCart.render() (cart_actions.js). So what
 * the modal shows is exactly what OrderController::placeOrder() will
 * charge — it can no longer drift from the database.
 *
 * Load after cart_actions.js on orders.php.
 */
(function () {
  var modal = document.getElementById('place-order-modal');
  if (!modal) return;

  var placeOrderBtn = document.getElementById('place-order-btn');
  var closeBtn = document.getElementById('place-order-modal-close');
  var confirmBtn = document.getElementById('confirm-order-btn');

  var orderTypeToggle = document.getElementById('order-type-toggle');
  var deliveryAddressGroup = document.getElementById('delivery-address-group');
  var deliveryFeeRow = document.getElementById('place-order-delivery-fee-row');
  var deliveryFeeEl = document.getElementById('place-order-delivery-fee');

  var paymentToggle = document.getElementById('payment-method-toggle');
  var gcashGroup = document.getElementById('gcash-payment-group');
  var gcashReferenceInput = document.getElementById('gcash-reference');
  var gcashProofInput = document.getElementById('gcash-proof');
  var gcashProofPreview = document.getElementById('gcash-proof-preview');
  var gcashProofPreviewImg = document.getElementById('gcash-proof-preview-img');
  var gcashProofError = document.getElementById('gcash-proof-error');

  var itemsWrap = document.getElementById('place-order-items');
  var subtotalEl = document.getElementById('place-order-subtotal');
  var totalEl = document.getElementById('place-order-total');

  var barangaySelect = document.getElementById('order-barangay');

  var cartItems = [];   // latest cart from the server
  var busy = false;     // a cart request is in flight

  function url(path) {
    return (window.APP_BASE_URL || '/').replace(/\/$/, '') + path;
  }

  // Delivery fee of the barangay currently picked in the dropdown
  // (each <option> carries its fee in data-fee). 0 until one is chosen.
  function selectedBarangayFee() {
    if (!barangaySelect || !barangaySelect.value) return 0;
    var opt = barangaySelect.options[barangaySelect.selectedIndex];
    return parseFloat(opt ? opt.getAttribute('data-fee') : '0') || 0;
  }

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

  function cartSubtotal() {
    return cartItems.reduce(function (sum, i) { return sum + (parseFloat(i.SUB_TOTAL) || 0); }, 0);
  }

  // Minimum order (window.MIN_ORDER_AMOUNT is set inline in orders.php).
  var MIN_ORDER = parseFloat(window.MIN_ORDER_AMOUNT) || 0;
  function belowMinimum() {
    return MIN_ORDER > 0 && cartSubtotal() < MIN_ORDER;
  }

  /* ---- Server cart ---- */
  function loadCart() {
    return fetch(url('/cart'), { headers: { 'Accept': 'application/json' } })
      .then(function (res) { return res.json(); })
      .then(function (res) {
        if (!res.success) throw new Error(res.message || 'Could not load your cart.');
        cartItems = Array.isArray(res.items) ? res.items : [];
      });
  }

  function postJSON(path, body) {
    return fetch(url(path), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body)
    }).then(function (res) { return res.json(); });
  }

  // Send a cart change to the server, then re-render modal + main page
  // from the cart it returns.
  function mutateCart(path, body) {
    if (busy) return;
    busy = true;
    itemsWrap.classList.add('is-busy');

    postJSON(path, body)
      .then(function (res) {
        busy = false;
        itemsWrap.classList.remove('is-busy');
        if (!res.success) {
          alert(res.message || 'Could not update your cart.');
          return;
        }
        cartItems = Array.isArray(res.items) ? res.items : [];
        if (window.OrderCart) window.OrderCart.render(cartItems);
        renderOrderItems();
      })
      .catch(function () {
        busy = false;
        itemsWrap.classList.remove('is-busy');
        alert('Something went wrong updating your cart. Please try again.');
      });
  }

  /* ---- Section 2: order items ---- */
  function renderOrderItems() {
    if (!itemsWrap) return;

    if (!cartItems.length) {
      itemsWrap.innerHTML = '<div class="modal-empty"><p>Your cart is empty</p>' +
        '<p class="modal-empty-sub">Add items from the Menu before placing an order.</p></div>';
      updateTotals(0);
      if (confirmBtn) confirmBtn.disabled = true;
      return;
    }

    itemsWrap.innerHTML = cartItems.map(function (item) {
      var id = parseInt(item.ORDER_DETAIL_ID, 10);
      var qty = parseInt(item.QUANTITY, 10) || 0;
      return '' +
        '<div class="cart-line" data-id="' + id + '">' +
          '<div>' +
            '<div class="cart-line-name">' + escapeHtml(item.NAME) + '</div>' +
            '<div class="cart-line-price">' + peso(parseFloat(item.PRICE)) + ' each</div>' +
          '</div>' +
          '<div class="qty-stepper">' +
            '<button type="button" class="stepper-btn place-order-qty-minus" data-id="' + id + '" data-qty="' + qty + '" aria-label="Decrease quantity">&minus;</button>' +
            '<span class="order-item-qty-value">' + qty + '</span>' +
            '<button type="button" class="stepper-btn place-order-qty-plus" data-id="' + id + '" data-qty="' + qty + '" aria-label="Increase quantity">+</button>' +
          '</div>' +
          '<div class="cart-line-subtotal">' + peso(parseFloat(item.SUB_TOTAL)) + '</div>' +
          '<button type="button" class="order-item-remove place-order-remove" data-id="' + id + '" aria-label="Remove item">&times;</button>' +
        '</div>';
    }).join('');

    updateTotals(cartSubtotal());
    if (confirmBtn) confirmBtn.disabled = belowMinimum();
  }

  if (itemsWrap) {
    itemsWrap.addEventListener('click', function (e) {
      var minusBtn = e.target.closest('.place-order-qty-minus');
      var plusBtn = e.target.closest('.place-order-qty-plus');
      var removeBtn = e.target.closest('.place-order-remove');

      if (minusBtn) {
        mutateCart('/cart/update', {
          order_detail_id: parseInt(minusBtn.getAttribute('data-id'), 10),
          quantity: (parseInt(minusBtn.getAttribute('data-qty'), 10) || 0) - 1   // 0 deletes the line server-side
        });
      } else if (plusBtn) {
        mutateCart('/cart/update', {
          order_detail_id: parseInt(plusBtn.getAttribute('data-id'), 10),
          quantity: (parseInt(plusBtn.getAttribute('data-qty'), 10) || 0) + 1
        });
      } else if (removeBtn) {
        mutateCart('/cart/remove', {
          order_detail_id: parseInt(removeBtn.getAttribute('data-id'), 10)
        });
      }
    });
  }

  /* ---- Totals, driven by order type + cart subtotal ---- */
  function currentOrderType() {
    var active = orderTypeToggle ? orderTypeToggle.querySelector('.chip.active') : null;
    return active ? active.getAttribute('data-order-type') : 'delivery';
  }

  function updateTotals(subtotal) {
    var isDelivery = currentOrderType() === 'delivery';
    var fee = isDelivery ? selectedBarangayFee() : 0;
    var noBarangayYet = isDelivery && barangaySelect && !barangaySelect.value;

    if (subtotalEl) subtotalEl.textContent = peso(subtotal);
    if (deliveryFeeEl) deliveryFeeEl.textContent = noBarangayYet ? 'Select barangay' : peso(fee);
    if (deliveryFeeRow) deliveryFeeRow.style.display = isDelivery ? '' : 'none';
    if (totalEl) totalEl.textContent = peso(subtotal + fee);
  }

  /* ---- Picking a barangay updates the delivery fee + total ---- */
  if (barangaySelect) {
    barangaySelect.addEventListener('change', function () { updateTotals(cartSubtotal()); });
  }

  /* ---- Section 1: order type toggle shows/hides address fields ---- */
  if (orderTypeToggle) {
    orderTypeToggle.addEventListener('click', function (e) {
      var chip = e.target.closest('.chip');
      if (!chip || chip.getAttribute('data-disabled') === '1') return; // switched off by the admin
      orderTypeToggle.querySelectorAll('.chip').forEach(function (c) { c.classList.remove('active'); });
      chip.classList.add('active');

      var isDelivery = chip.getAttribute('data-order-type') === 'delivery';
      if (deliveryAddressGroup) deliveryAddressGroup.style.display = isDelivery ? '' : 'none';
      updateTotals(cartSubtotal());
    });
  }

  /* ---- Section 3: payment method toggle ---- */
  function currentPaymentMethod() {
    var active = paymentToggle ? paymentToggle.querySelector('.chip.active') : null;
    return active ? active.getAttribute('data-payment') : 'cod';
  }

  function setGcashVisible(visible) {
    if (!gcashGroup) return;
    gcashGroup.style.display = visible ? '' : 'none';
    if (!visible) {
      // Leaving GCash — clear whatever was entered so it doesn't
      // silently ride along on a later Cash order.
      if (gcashReferenceInput) gcashReferenceInput.value = '';
      if (gcashProofInput) gcashProofInput.value = '';
      if (gcashProofPreview) gcashProofPreview.style.display = 'none';
      if (gcashProofError) gcashProofError.style.display = 'none';
    }
  }

  if (paymentToggle) {
    paymentToggle.addEventListener('click', function (e) {
      var chip = e.target.closest('.chip');
      if (!chip || chip.getAttribute('data-disabled') === '1') return; // switched off by the admin
      paymentToggle.querySelectorAll('.chip').forEach(function (c) { c.classList.remove('active'); });
      chip.classList.add('active');
      setGcashVisible(chip.getAttribute('data-payment') === 'gcash');
    });
  }

  if (gcashProofInput) {
    gcashProofInput.addEventListener('change', function () {
      var file = gcashProofInput.files && gcashProofInput.files[0];
      if (gcashProofError) gcashProofError.style.display = 'none';

      if (!file) {
        if (gcashProofPreview) gcashProofPreview.style.display = 'none';
        return;
      }

      var reader = new FileReader();
      reader.onload = function (e) {
        if (gcashProofPreviewImg) gcashProofPreviewImg.src = e.target.result;
        if (gcashProofPreview) gcashProofPreview.style.display = '';
      };
      reader.readAsDataURL(file);
    });
  }

  /* ---- Open / close ---- */
  function openModal() {
    modal.classList.add('open');
    setGcashVisible(currentPaymentMethod() === 'gcash');

    // Show a loading state, then pull the real cart from the server.
    if (confirmBtn) confirmBtn.disabled = true;
    if (itemsWrap) itemsWrap.innerHTML = '<div class="modal-empty"><p>Loading your cart…</p></div>';

    loadCart()
      .then(function () {
        if (window.OrderCart) window.OrderCart.render(cartItems); // sync main page too
        renderOrderItems();
      })
      .catch(function (err) {
        if (itemsWrap) {
          itemsWrap.innerHTML = '<div class="modal-empty"><p>Could not load your cart</p>' +
            '<p class="modal-empty-sub">' + escapeHtml(err.message || 'Please close this and try again.') + '</p></div>';
        }
      });
  }

  function closeModal() {
    modal.classList.remove('open');
  }

  if (placeOrderBtn) placeOrderBtn.addEventListener('click', openModal);
  if (closeBtn) closeBtn.addEventListener('click', closeModal);

  modal.addEventListener('click', function (e) {
    if (e.target === modal) closeModal();
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && modal.classList.contains('open')) closeModal();
  });

  /* ---- Confirm order -> POST /orders/place ---- */
  if (confirmBtn) {
    confirmBtn.addEventListener('click', function () {
      if (busy || !cartItems.length) return;

      if (belowMinimum()) {
        alert('The minimum order is ' + peso(MIN_ORDER) + '. Please add more items to your cart.');
        return;
      }

      var isDelivery = currentOrderType() === 'delivery';
      var isGcash = currentPaymentMethod() === 'gcash';

      // Delivery needs a barangay + address (server re-checks this).
      if (isDelivery) {
        var addrEl = document.getElementById('order-address-detail');
        if (!barangaySelect || !barangaySelect.value) {
          barangaySelect && barangaySelect.focus();
          alert('Please select your barangay for delivery.');
          return;
        }
        if (addrEl && !addrEl.value.trim()) {
          addrEl.focus();
          alert('Please enter your delivery address.');
          return;
        }
      }

      // GCash requires a reference number + proof-of-payment screenshot.
      if (isGcash) {
        var hasReference = gcashReferenceInput && gcashReferenceInput.value.trim();
        var hasProof = gcashProofInput && gcashProofInput.files && gcashProofInput.files[0];

        if (!hasReference || !hasProof) {
          if (gcashProofError) gcashProofError.style.display = '';
          if (!hasReference && gcashReferenceInput) gcashReferenceInput.focus();
          else if (gcashProofInput) gcashProofInput.focus();
          return;
        }
      }

      var formData = new FormData();
      formData.append('orderType', currentOrderType());
      formData.append('email', (document.getElementById('customer-email') || {}).value || '');
      formData.append('contactNumber', (document.getElementById('contact-number') || {}).value || '');
      formData.append('barangay', isDelivery ? (barangaySelect.value || 'None') : 'None');
      formData.append('addressDetail', isDelivery ? ((document.getElementById('order-address-detail') || {}).value || 'None') : 'None');
      formData.append('notes', (document.getElementById('order-notes') || {}).value || '');
      formData.append('paymentMethod', currentPaymentMethod());

      if (isGcash) {
        formData.append('gcashReference', gcashReferenceInput.value.trim());
        formData.append('gcashProof', gcashProofInput.files[0]);
      }

      // The server reads the live cart itself (OrderController::placeOrder),
      // so no item list is sent — nothing client-side can alter what's charged.
      busy = true;
      confirmBtn.disabled = true;

      fetch(url('/orders/place'), { method: 'POST', body: formData })
        .then(function (res) { return res.json(); })
        .then(function (res) {
          if (res.success) {
            // Cart became a placed order server-side — reload so the
            // cart, badge and tracking list all reflect that.
            window.location.reload();
          } else {
            busy = false;
            confirmBtn.disabled = false;
            alert(res.message || 'Could not place your order. Please try again.');
            // A stock error means quantities may need lowering — refresh them.
            loadCart().then(function () {
              if (window.OrderCart) window.OrderCart.render(cartItems);
              renderOrderItems();
            }).catch(function () {});
          }
        })
        .catch(function () {
          busy = false;
          confirmBtn.disabled = false;
          alert('Something went wrong placing your order.');
        });
    });
  }
})();