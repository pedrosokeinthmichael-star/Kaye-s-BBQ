<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kaye's Barbeque</title>
<link rel="stylesheet" href="<?=base_url('css/customer_css/styles.css')?>">
<link rel="stylesheet" href="<?=base_url('css/customer_css/customer.css')?>">
<link rel="stylesheet" href="<?=base_url('css/customer_css/orders.css')?>">
<link rel="stylesheet" href="<?=base_url('css/customer_css/place_order.css')?>">
<link rel="stylesheet" href="<?=base_url('css/customer_css/order_tracker.css')?>">
<link rel="stylesheet" href="<?=base_url('css/customer_css/cancel_order.css')?>">
<link rel="stylesheet" href="<?=base_url('css/customer_css/order_card.css')?>">
<link rel="stylesheet" href="<?=base_url('css/customer_css/search.css')?>">
</head>
<body>

<?php $active = 'order'; include __DIR__ . '/components/header.php'; ?>

<section class="section-flush-bottom">
  <div class="container">
    <div class="eyebrow eyebrow-ember">Checkout</div>
    <h1 class="page-title">Complete Your Order</h1>
  </div>
</section>

<section>
  <div class="container">

    <div class="checkout-layout">
      <div class="order-items-card">
        <h2>Your Order</h2>
        <div id="order-items-body">
          <?php if ( empty( $cart_items ) ) : ?>
            <div class="modal-empty" id="order-items-empty">
              <p>Your cart is empty</p>
              <p class="modal-empty-sub">Add items from the <a href="<?=site_url('/customer/barbeques')?>">Menu</a> to get started.</p>
            </div>
          <?php else : ?>
            <?php foreach ( $cart_items as $item ) :
              $unitPrice = (float) $item['PRICE'];
              $qty       = (int) $item['QUANTITY'];
              $subtotal  = (float) $item['SUB_TOTAL'];
            ?>
              <div class="order-item" data-order-detail-id="<?= (int) $item['ORDER_DETAIL_ID'] ?>">
                <div class="order-item-thumb">
                  <?php if ( ! empty( $item['IMAGE'] ) ) : ?>
                    <img class="order-item-img" src="<?=base_url('uploads/' . $item['IMAGE'])?>" alt="<?= esc( $item['NAME'] ) ?>">
                  <?php else : ?>
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M4 12c0-3.5 2-6 8-6s8 2.5 8 6-3.5 6-8 6-8-2.5-8-6Z" stroke="#C64B23" stroke-width="1.6" stroke-linejoin="round"/>
                    </svg>
                  <?php endif; ?>
                </div>
                <div>
                  <div class="order-item-name"><?= esc( $item['NAME'] ) ?></div>
                  <div class="order-item-price">₱<?= number_format( $unitPrice, 0 ) ?> each</div>
                </div>
                <div class="order-item-qty">
                  <button class="stepper-btn order-item-qty-minus" aria-label="Decrease quantity">&minus;</button>
                  <span class="order-item-qty-value"><?= $qty ?></span>
                  <button class="stepper-btn order-item-qty-plus" aria-label="Increase quantity">+</button>
                </div>
                <div class="order-item-subtotal">₱<?= number_format( $subtotal, 0 ) ?></div>
                <button class="order-item-remove" aria-label="Remove item">&times;</button>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <?php
        $cartSubtotal = array_reduce(
          $cart_items ?? [],
          fn( $sum, $i ) => $sum + (float) $i['SUB_TOTAL'],
          0
        );
      ?>
      <div class="summary-card">
        <h3>Order Summary</h3>
        <?php
          $minOrder = (float) ( $min_order ?? 0 );
          $isShort  = $minOrder > 0 && $cartSubtotal < $minOrder;
        ?>
        <div id="order-summary-footer">
          <div class="summary-line"><span>Subtotal</span><span id="order-summary-subtotal">₱<?= number_format( $cartSubtotal, 0 ) ?></span></div>
          <div class="summary-line total"><span>Total</span><span id="order-summary-total">₱<?= number_format( $cartSubtotal, 0 ) ?></span></div>
          <p class="min-order-note" id="min-order-note" style="<?= $isShort ? '' : 'display:none;' ?>">
            <?php if ( $isShort ) : ?>Minimum order is ₱<?= number_format( $minOrder, 0 ) ?>. Add ₱<?= number_format( $minOrder - $cartSubtotal, 0 ) ?> more to place your order.<?php endif; ?>
          </p>
          <button type="button" class="btn btn-primary btn-place-order" id="place-order-btn" aria-haspopup="dialog" aria-controls="place-order-modal" <?= $isShort ? 'disabled' : '' ?>>Place Order</button>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/components/place_order_modal.php'; ?>

<?php include __DIR__ . '/components/order_tracker_modal.php'; ?>

<?php include __DIR__ . '/components/cancel_order_modal.php'; ?>

<section id="tracking-section">
  <div class="container">
    <div class="eyebrow eyebrow-ember">In Progress</div>
    <h2 class="tracking-title">Track Your Order</h2>
    <p class="tracking-intro">Here's what's still on its way. Once it arrives, it'll move to your <a class="text-link" href="<?=site_url('/customer/history')?>">Order History</a> as delivered.</p>

    <div class="search-bar">
      <label class="sr-only" for="tracking-search">Search your orders</label>
      <svg class="search-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="1.7"/>
            <path d="M16 16l4.5 4.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
          </svg>
      <input type="search" id="tracking-search" class="search-input" placeholder="Search by order number, item or status…" autocomplete="off"
        data-search-input
        data-search-scope="#tracking-table-body"
        data-search-item=".order-card"
        data-search-fields=".order-card-id, .order-card-date, .order-card-items, .order-card-type, .status"
        data-search-filters="#tracking-filters"
        data-search-empty="#tracking-search-empty">
      <button type="button" class="search-clear" aria-label="Clear search" hidden>&times;</button>
    </div>

    <div class="history-toolbar">
      <div class="filters" id="tracking-filters">
        <span class="chip active" data-filter="all">All</span>
        <span class="chip" data-filter="pending">Pending</span>
        <span class="chip" data-filter="preparing">Preparing</span>
        <span class="chip" data-filter="delivering">Delivering</span>
      </div>
    </div>

    <div class="order-card-grid" id="tracking-table-body">
      <?php foreach ( $tracking_orders ?? [] as $order ) : ?>
        <?php
          // Expected keys per row (from OrderModel::getOrdersForUser(),
          // filtered to ORDER_STATUS in ['Preparing','Delivering'],
          // joined with order_detail/menu for an item summary):
          //   ORDER_ID, ORDER_DATE, ORDER_TYPE, TOTAL_AMOUNT,
          //   ORDER_STATUS, ITEMS_SUMMARY
          // Same shape components/order_card.php already expects.
          $show_cancel = true; // show the Cancel button on tracking cards
        ?>
        <?php include __DIR__ . '/components/order_card.php'; ?>
      <?php endforeach; ?>
    </div>

    <p id="tracking-search-empty" class="list-empty" hidden>No orders match your search.</p>
    <p id="tracking-empty" class="list-empty" style="<?= empty( $tracking_orders ) ? '' : 'display:none;' ?>">No orders in this category right now.</p>
  </div>
</section>

<?php include __DIR__ . '/components/footer.php'; ?>

<script>window.MIN_ORDER_AMOUNT = <?= (float) ( $min_order ?? 0 ) ?>;</script>
<?php
  $extra_scripts = [
    'js/customer/cart_actions.js',
    'js/customer/placeorder.js',
    'js/customer/cancel_order.js',
    'js/customer/search.js',
  ];
  include __DIR__ . '/components/scripts.php';
?>

</body>
</html>