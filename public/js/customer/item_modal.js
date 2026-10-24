/**
 * item_modal.js — Kaye's Barbeque
 * -----------------------------------------------------------------
 * Item-picker flow for #item-modal (barbeques.php and index.php):
 * clicking a .menu-card (components/menu_card.php) opens the modal
 * pre-filled with that item, the qty stepper adjusts #item-qty-input,
 * and "Add to Cart" POSTs to /cart/add (OrderDetailController::add),
 * which stores the item as a row in order_detail — the same table
 * orders.php and the Place Order modal read from.
 *
 * The stock line (#item-modal-stock) is filled from the card's
 * data-stock attribute: "Sold out", "Only N left" or "N in stock".
 * (This used to be a separate inline <script> on both pages.)
 *
 * Requires window.APP_BASE_URL (set by components/scripts.php).
 */
(function () {
  var modal = document.getElementById('item-modal');
  if (!modal) return;

  var closeBtn = document.getElementById('item-modal-close');
  var titleEl = document.getElementById('item-modal-title');
  var thumbEl = document.getElementById('item-modal-thumb');
  var descEl = document.getElementById('item-modal-desc');
  var priceEl = document.getElementById('item-modal-price');
  var stockEl = document.getElementById('item-modal-stock');
  var qtyInput = document.getElementById('item-qty-input');
  var qtyMinus = document.getElementById('item-qty-minus');
  var qtyPlus = document.getElementById('item-qty-plus');
  var addBtn = document.getElementById('item-add-to-cart');

  var activeItem = null; // { id, name, price, desc, image }

  function url(path) {
    return (window.APP_BASE_URL || '/').replace(/\/$/, '') + path;
  }

  function peso(n) {
    return '₱' + n.toLocaleString('en-PH', { maximumFractionDigits: 0 });
  }

  // Most the customer can order: the stock left (Infinity if the card
  // has no data-stock, so those items stay uncapped).
  function maxQty() {
    if (!activeItem || !isFinite(activeItem.stock)) return Infinity;
    return Math.max(1, activeItem.stock);
  }

  function currentQty() {
    var qty = Math.max(1, parseInt(qtyInput.value, 10) || 1);
    return Math.min(maxQty(), qty);
  }

  // Disable - at 1 and + at the stock limit (both off when sold out).
  function syncQtyButtons() {
    var soldOut = !!(activeItem && activeItem.soldOut);
    if (qtyMinus) qtyMinus.disabled = soldOut || currentQty() <= 1;
    if (qtyPlus) qtyPlus.disabled = soldOut || currentQty() >= maxQty();
  }

  // Stock line under the price, read from the card's data-stock.
  // Returns true when the item is sold out (stock is 0 or less).
  function showStock(card) {
    var stock = parseInt(card.getAttribute('data-stock'), 10);
    if (isNaN(stock)) {
      if (stockEl) stockEl.style.display = 'none';
      return false;
    }
    if (!stockEl) return stock <= 0;

    if (stock <= 0) {
      stockEl.textContent = 'Sold out';
      stockEl.style.color = 'var(--brick, #C64B23)';
    } else if (stock <= 5) {
      stockEl.textContent = 'Only ' + stock + ' left';
      stockEl.style.color = '#a5791f';
    } else {
      stockEl.textContent = stock + ' in stock';
      stockEl.style.color = 'var(--ink-soft, #6b5c4d)';
    }
    stockEl.style.display = '';
    return stock <= 0;
  }

  // Lock the quantity controls and the Add to Cart button for sold-out
  // items, and restore them for everything else.
  var addLabel = addBtn ? addBtn.textContent : 'Add to Cart';

  function setSoldOut(soldOut) {
    if (addBtn) {
      addBtn.disabled = soldOut;
      addBtn.textContent = soldOut ? 'Sold out' : addLabel;
    }
    if (qtyInput) qtyInput.disabled = soldOut;
    syncQtyButtons();
  }

  function openModalFor(card) {
    var cardStock = parseInt(card.getAttribute('data-stock'), 10);
    activeItem = {
      stock: isNaN(cardStock) ? Infinity : cardStock,
      id: parseInt(card.getAttribute('data-menu-id'), 10),
      name: card.getAttribute('data-name') || '',
      price: parseFloat(card.getAttribute('data-price')) || 0,
      desc: card.getAttribute('data-desc') || '',
      image: card.getAttribute('data-image') || ''
    };

    if (titleEl) titleEl.textContent = activeItem.name;
    if (descEl) descEl.textContent = activeItem.desc;
    if (priceEl) priceEl.textContent = peso(activeItem.price);
    if (thumbEl) {
      thumbEl.innerHTML = activeItem.image
        ? '<img src="' + url('/uploads/' + activeItem.image) + '" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:inherit;">'
        : '';
    }
    if (qtyInput) qtyInput.value = 1;
    activeItem.soldOut = showStock(card);
    setSoldOut(activeItem.soldOut);

    modal.classList.add('open');
  }

  function closeModal() {
    modal.classList.remove('open');
    activeItem = null;
  }

  // Event delegation: works for cards rendered now or added later.
  document.addEventListener('click', function (e) {
    var card = e.target.closest('.menu-card');
    if (card) openModalFor(card);
  });

  if (closeBtn) closeBtn.addEventListener('click', closeModal);
  modal.addEventListener('click', function (e) {
    if (e.target === modal) closeModal();
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && modal.classList.contains('open')) closeModal();
  });

  if (qtyMinus) {
    qtyMinus.addEventListener('click', function () {
      qtyInput.value = Math.max(1, currentQty() - 1);
      syncQtyButtons();
    });
  }
  if (qtyPlus) {
    qtyPlus.addEventListener('click', function () {
      if (currentQty() >= maxQty()) return; // already at the stock limit
      qtyInput.value = currentQty() + 1;
      syncQtyButtons();
    });
  }
  if (qtyInput) {
    // While typing: pull anything above the stock straight back down.
    qtyInput.addEventListener('input', function () {
      var typed = parseInt(qtyInput.value, 10);
      if (!isNaN(typed) && typed > maxQty()) qtyInput.value = maxQty();
      syncQtyButtons();
    });
    // On blur/enter: fix empty or invalid values.
    qtyInput.addEventListener('change', function () {
      qtyInput.value = currentQty();
      syncQtyButtons();
    });
  }

  // After adding an item, pop open the header cart dropdown (already
  // populated a moment ago by window.CustomerCart.render) so the item
  // that was just added is immediately visible instead of only
  // bumping the badge count silently.
  function openCartDropdown() {
    var cartModal = document.getElementById('cart-modal');
    if (cartModal) cartModal.classList.add('open');
  }

  if (addBtn) {
    addBtn.addEventListener('click', function () {
      if (!activeItem || !activeItem.id) return;
      if (activeItem.soldOut) {
        alert('Sorry, ' + (activeItem.name || 'this item') + ' is sold out.');
        return;
      }

      addBtn.disabled = true;
      fetch(url('/cart/add'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ menu_id: activeItem.id, quantity: currentQty() })
      })
        .then(function (res) { return res.json(); })
        .then(function (res) {
          addBtn.disabled = false;
          if (res.success) {
            if (window.CustomerCart) window.CustomerCart.render(res.items);
            closeModal();
            openCartDropdown();
          } else {
            alert(res.message || 'Could not add that item to your cart.');
          }
        })
        .catch(function () {
          addBtn.disabled = false;
          alert('Something went wrong adding that item to your cart.');
        });
    });
  }
})();