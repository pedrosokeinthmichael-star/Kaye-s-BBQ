<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kaye's Barbeque</title>
<link rel="stylesheet" href="<?=base_url('css/customer_css/styles.css')?>">
<link rel="stylesheet" href="<?=base_url('css/customer_css/customer.css')?>">
<link rel="stylesheet" href="<?=base_url('css/customer_css/history.css')?>">
<link rel="stylesheet" href="<?=base_url('css/customer_css/order_history.css')?>">
<link rel="stylesheet" href="<?=base_url('css/customer_css/order_card.css')?>">
<link rel="stylesheet" href="<?=base_url('css/customer_css/search.css')?>">
</head>
<body>

<?php $active = 'history'; include __DIR__ . '/components/header.php'; ?>

<section class="section-flush-bottom">
  <div class="container">
    <div class="eyebrow eyebrow-ember">Your Orders</div>
    <h1 class="page-title">Order History</h1>
    <p class="page-intro" id="history-intro">A look back at your past orders — see what's been delivered, and anything that got cancelled. Have an order still on the grill? Track it on the <a href="<?=site_url('/customer/orders')?>" class="text-link">Order page</a>.</p>
  </div>
</section>

<section>
  <div class="container">

    <?php
      // $order_history is passed in by Home::history() (the logged-in
      // customer's Delivered and Cancelled orders). Each row needs:
      // ORDER_ID, ORDER_DATE, ORDER_TYPE, TOTAL_AMOUNT, ORDER_STATUS,
      // ITEMS_SUMMARY. Optional keys that feed the details modal are
      // listed in components/order_card.php.
      $order_history = $order_history ?? [];
    ?>

    <div id="history-content">
      <div class="search-bar">
        <label class="sr-only" for="history-search">Search your order history</label>
        <svg class="search-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="1.7"/>
              <path d="M16 16l4.5 4.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
            </svg>
        <input type="search" id="history-search" class="search-input" placeholder="Search by order number, item or status…" autocomplete="off"
          data-search-input
          data-search-scope="#history-table-body"
          data-search-item=".order-card"
          data-search-fields=".order-card-id, .order-card-date, .order-card-items, .order-card-type, .status"
          data-search-filters="#history-filters"
          data-search-empty="#history-search-empty">
        <button type="button" class="search-clear" aria-label="Clear search" hidden>&times;</button>
      </div>

      <div class="history-toolbar">
        <div class="filters" id="history-filters">
          <span class="chip active" data-filter="all">All Orders</span>
          <span class="chip" data-filter="delivered">Delivered</span>
          <span class="chip" data-filter="cancelled">Cancelled</span>
          <span class="chip" data-filter="refund">Refunded</span>
        </div>
      </div>

      <div class="order-card-grid" id="history-table-body">
        <?php foreach ( $order_history as $order ) : ?>
          <?php include __DIR__ . '/components/order_card.php'; ?>
        <?php endforeach; ?>
      </div>

      <p id="history-search-empty" class="list-empty" hidden>No orders match your search.</p>
      <p id="history-empty" class="list-empty" style="<?= empty( $order_history ) ? '' : 'display:none;' ?>">No orders in this category yet.</p>
    </div>

  </div>
</section>

<?php include __DIR__ . '/components/order_history_modal.php'; ?>

<?php include __DIR__ . '/components/footer.php'; ?>

<?php $extra_scripts = ['js/customer/search.js']; include __DIR__ . '/components/scripts.php'; ?>

</body>
</html>