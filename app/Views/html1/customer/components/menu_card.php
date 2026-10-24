<?php
/**
 * components/menu_card.php
 * -----------------------------------------------------------------
 * RECONSTRUCTED — the original menu_card.php wasn't provided, so
 * this is a best-guess rebuild based on the #item-modal markup and
 * .menu-grid usage visible in barbeques.php/index.php. If your real
 * menu_card.php has different classes/styling, merge the data-*
 * attributes and the click target below into it rather than
 * replacing the file outright — those are what item_modal.js reads.
 *
 * Expects $item (one row from BBQModel::findAll(), i.e. the menu
 * table) to be in scope, same as the existing include callers.
 */

$itemId    = $item['MENU_ID'] ?? '';
$itemName  = $item['NAME'] ?? '';
$itemDesc  = $item['DESCRIPTION'] ?? '';
$itemPrice = (float) ( $item['PRICE'] ?? 0 );
$itemImage = $item['IMAGE'] ?? '';
$itemStock = max( 0, (int) ( $item['STOCK'] ?? 0 ) );

if ( $itemStock === 0 ) {
    $stockLabel = 'Sold out';
    $stockClass = 'is-soldout';
} elseif ( $itemStock <= 5 ) {
    $stockLabel = 'Only ' . $itemStock . ' left';
    $stockClass = 'is-low';
} else {
    $stockLabel = $itemStock . ' in stock';
    $stockClass = 'is-ok';
}
?>
<div class="menu-card"
     data-menu-id="<?= (int) $itemId ?>"
     data-name="<?= esc( $itemName, 'attr' ) ?>"
     data-desc="<?= esc( $itemDesc, 'attr' ) ?>"
     data-price="<?= $itemPrice ?>"
     data-image="<?= esc( $itemImage, 'attr' ) ?>"
     data-stock="<?= $itemStock ?>"
     role="button" tabindex="0" aria-haspopup="dialog" aria-controls="item-modal">

  <div class="menu-card-thumb">
    <?php if ( ! empty( $itemImage ) ) : ?>
      <img src="<?=base_url('uploads/' . $itemImage)?>" alt="<?= esc( $itemName ) ?>">
    <?php else : ?>
      <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M4 12c0-3.5 2-6 8-6s8 2.5 8 6-3.5 6-8 6-8-2.5-8-6Z" stroke="#C64B23" stroke-width="1.6" stroke-linejoin="round"/>
      </svg>
    <?php endif; ?>
  </div>

  <div class="menu-card-body">
    <div class="menu-card-name"><?= esc( $itemName ) ?></div>
    <?php if ( $itemDesc ) : ?>
      <p class="menu-card-desc"><?= esc( $itemDesc ) ?></p>
    <?php endif; ?>
    <div class="menu-card-price">₱<?= number_format( $itemPrice, 0 ) ?></div>
    <div class="menu-card-stock <?= $stockClass ?>"><?= esc( $stockLabel ) ?></div>
  </div>

  <button type="button" class="btn btn-outline-light menu-card-add" aria-label="Add <?= esc( $itemName, 'attr' ) ?> to cart">
    Add
  </button>
</div>