<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kaye's Barbeque</title>
<link rel="stylesheet" href="<?=base_url('css/customer_css/styles.css')?>">
<link rel="stylesheet" href="<?=base_url('css/customer_css/customer_common.css')?>">
<link rel="stylesheet" href="<?=base_url('css/customer_css/menu_card.css')?>">
<link rel="stylesheet" href="<?=base_url('css/customer_css/home.css')?>">
</head>
<body>

<?php $active = 'home'; include __DIR__ . '/components/header.php'; ?>

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

<section class="hero">
  <div class="container hero-inner">
    <div>
      <div class="eyebrow">Est. Pitmaster Kaye · Charcoal-Grilled Since Day One</div>
      <h1>Sticky, smoky,<br>and <em>straight</em><br>off the grill.</h1>
      <p class="hero-sub">Charcoal, skewers, and a basting brush that never stops moving. Every stick that leaves Kaye's grill is grilled to order, Pinoy street-side style.</p>
      <div class="hero-actions">
        <a href="<?=site_url('/customer/barbeques')?>" class="btn btn-primary">See the Menu</a>
        <a href="<?=site_url('/customer/orders')?>" class="btn btn-outline">Check out now</a>
      </div>
    </div>
    <div class="hero-figure">
      <svg viewBox="0 0 200 200" width="70%" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="100" cy="100" r="70" stroke="#6E2A1C" stroke-width="2" opacity="0.5"/>
        <path d="M60 130c8-30 4-45-6-60 14 4 22 14 26 26 4-16 14-26 28-32-6 16-4 28 2 40 6-14 18-22 30-24-10 14-12 26-6 40-6 14-20 22-36 22-16 0-32-4-38-12z" fill="#C64B23"/>
        <path d="M60 130c8-30 4-45-6-60 14 4 22 14 26 26 4-16 14-26 28-32-6 16-4 28 2 40 6-14 18-22 30-24-10 14-12 26-6 40-6 14-20 22-36 22-16 0-32-4-38-12z" fill="#D9A441" opacity="0.35"/>
      </svg>
      <div class="smoke-ring smoke-ring-h1"></div>
      <div class="smoke-ring smoke-ring-h2"></div>
      <div class="smoke-ring smoke-ring-h3"></div>
    </div>
  </div>
</section>

<div class="grate-divider"></div>

<?php
  // Featured Menu: pick a random handful of Available items from
  // $barbeque_list (passed in by Home::home(), same source as the
  // full Menu page). Re-randomizes on every page load.
  $availableForFeature = array_filter(
    $barbeque_list ?? [],
    fn( $item ) => ( $item['STATUS'] ?? '' ) === 'Available'
  );
  $availableForFeature = array_values( $availableForFeature );

  $featuredCount = min( 4, count( $availableForFeature ) );
  $featuredItems = [];
  if ( $featuredCount > 0 ) {
    $randomKeys = array_rand( $availableForFeature, $featuredCount );
    // array_rand returns a single int (not array) when picking just 1 item
    $randomKeys = is_array( $randomKeys ) ? $randomKeys : [ $randomKeys ];
    foreach ( $randomKeys as $featuredKey ) {
      $featuredItems[] = $availableForFeature[$featuredKey];
    }
  }
?>

<section>
  <div class="container">
    <div class="section-head">
      <div>
        <div class="kicker">Handpicked for You</div>
        <h2>Featured Menu</h2>
      </div>
      <a href="<?=site_url('/customer/barbeques')?>" class="btn btn-outline-light">Full Menu</a>
    </div>
    <div class="menu-grid">
      <?php foreach ( $featuredItems as $item ) : ?>
        <?php include __DIR__ . '/components/menu_card.php'; ?>
      <?php endforeach; ?>
    </div>
    <?php if ( empty( $featuredItems ) ) : ?>
      <p class="empty-note">No featured items yet — check the full menu instead.</p>
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/components/footer.php'; ?>

<?php $extra_scripts = ['js/customer/item_modal.js']; include __DIR__ . '/components/scripts.php'; ?>

</body>
</html>