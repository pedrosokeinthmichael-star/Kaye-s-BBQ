<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kaye's Barbeque</title>
<link rel="stylesheet" href="<?=base_url('css/customer_css/styles.css')?>">
<link rel="stylesheet" href="<?=base_url('css/customer_css/customer_common.css')?>">
<link rel="stylesheet" href="<?=base_url('css/customer_css/menu_card.css')?>">
<link rel="stylesheet" href="<?=base_url('css/customer_css/barbeques.css')?>">
<link rel="stylesheet" href="<?=base_url('css/customer_css/search.css')?>">
</head>
<body>

<?php $active = 'menu'; include __DIR__ . '/components/header.php'; ?>

<div class="modal-overlay" id="item-modal" role="dialog" aria-modal="true" aria-labelledby="item-modal-title">
  <div class="modal-box">
    <div class="modal-header">
      <h3 id="item-modal-title">Item Name</h3>
      <button class="modal-close" id="item-modal-close" aria-label="Close">&times;</button>
    </div>
    <div class="modal-body">
      <div class="item-modal-thumb" id="item-modal-thumb"></div>
      <p class="item-modal-desc" id="item-modal-desc"></p>
      <div class="item-modal-price" id="item-modal-price"></div>
      <div class="item-modal-stock item-modal-stock-line" id="item-modal-stock"></div>
      <div class="qty-stepper">
        <button type="button" class="stepper-btn" id="item-qty-minus" aria-label="Decrease quantity">&minus;</button>
        <input type="text" class="stepper-input" id="item-qty-input" value="1" inputmode="numeric">
        <button type="button" class="stepper-btn" id="item-qty-plus" aria-label="Increase quantity">+</button>
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-primary btn-full" id="item-add-to-cart">Add to Cart</button>
    </div>
  </div>
</div>

<section>
  <div class="container">

    <div class="notice" id="menu-notice">
      <strong>Note:</strong> Tap any item to choose a quantity and add it to your cart, then check out from the cart or the Order page.
    </div>

    <div class="search-bar">
      <label class="sr-only" for="menu-search">Search the menu</label>
      <svg class="search-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="1.7"/>
            <path d="M16 16l4.5 4.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
          </svg>
      <input type="search" id="menu-search" class="search-input" placeholder="Search the menu…" autocomplete="off"
        data-search-input
        data-search-scope="#menu-results"
        data-search-item=".menu-card"
        data-search-group=".menu-category"
        data-search-empty="#menu-search-empty">
      <button type="button" class="search-clear" aria-label="Clear search" hidden>&times;</button>
    </div>

    <?php
      // Group available menu items by category. $barbeque_list is
      // passed in from the controller (all rows from BBQModel::findAll());
      // we only show items marked Available here.
      $availableItems = array_filter(
        $barbeque_list ?? [],
        fn( $item ) => ( $item['STATUS'] ?? '' ) === 'Available'
      );

      $itemsByCategory = [];
      foreach ( $availableItems as $item ) {
        $cat = $item['MENU_CATEGORY'] ?? 'Other';
        $itemsByCategory[$cat][] = $item;
      }
    ?>

    <?php if ( empty( $itemsByCategory ) ) : ?>
      <p class="empty-note" id="barbeques-empty">Nothing on the menu right now — check back soon!</p>
    <?php endif; ?>

    <p class="empty-note" id="menu-search-empty" hidden>No menu items match your search. Try a different word.</p>

    <div id="menu-results">
    <?php foreach ( $itemsByCategory as $categoryName => $items ) : ?>
      <div class="menu-category">
        <div class="category-head">
          <div class="icon-badge">
            <svg viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
              <line x1="6" y1="34" x2="34" y2="6" stroke="#C64B23" stroke-width="2.5" stroke-linecap="round"/>
              <circle cx="14" cy="26" r="5.5" fill="#C64B23"/>
              <circle cx="21.5" cy="18.5" r="5.5" fill="#D9A441"/>
              <circle cx="29" cy="11" r="5.5" fill="#E0602F"/>
            </svg>
          </div>
          <h3 class="category-title"><?= esc( $categoryName ) ?></h3>
        </div>
        <div class="menu-grid">

          <?php foreach ( $items as $item ) : ?>
            <?php include __DIR__ . '/components/menu_card.php'; ?>
          <?php endforeach; ?>

        </div>
      </div>
    <?php endforeach; ?>
    </div>

  </div>
</section>

<?php include __DIR__ . '/components/footer.php'; ?>

<?php $extra_scripts = ['js/customer/item_modal.js', 'js/customer/search.js']; include __DIR__ . '/components/scripts.php'; ?>

</body>
</html>