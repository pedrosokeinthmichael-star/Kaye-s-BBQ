<?php
/**
 * components/header.php — Kaye's Barbeque (customer pages)
 * -----------------------------------------------------------------
 * Site header + nav, followed by the cart modal.
 *
 * Set before including:
 *   $active    'home' | 'menu' | 'order' | 'history' | 'profile'
 *              (which nav item is highlighted)
 *   $show_cart true/false (default true) — cart button + cart modal
 *
 * Usage (in a page that lives next to /components):
 *   <?php $active = 'menu'; include __DIR__ . '/components/header.php'; ?>
 */
$active    = $active ?? '';
$show_cart = $show_cart ?? true;

$nav_links = [
  'home'    => [ '/customer/home',       'Home' ],
  'menu'    => [ '/customer/barbeques',  'Menu' ],
  'order'   => [ '/customer/orders',     'Order' ],
  'history' => [ '/customer/history',    'Order History' ],
];
?>
<header class="site-header">
  <nav class="nav">
    <a href="<?=site_url('/customer/home')?>" class="brand">
      <img class="brand-mark brand-logo" src="<?=base_url('babeques/LOGO_KBBQpng.png')?>" alt="Kaye's BBQ logo" width="80" height="80" style="width:80px;height:80px;object-fit:contain;">
    </a>

    <ul class="nav-links">
      <?php foreach ( $nav_links as $key => $link ) : ?>
        <li><a href="<?=site_url($link[0])?>"<?= $active === $key ? ' class="active"' : '' ?>><?= esc( $link[1] ) ?></a></li>
      <?php endforeach; ?>
    </ul>

    <div class="nav-actions">
      <?php if ( $show_cart ) : ?>
      <button type="button" class="cart-btn is-visible" id="cart-btn" aria-label="Open cart" aria-haspopup="dialog" aria-controls="cart-modal">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M3 4h2l2.4 12.2a2 2 0 0 0 2 1.8h8.2a2 2 0 0 0 2-1.6L21 8H6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
          <circle cx="10" cy="21" r="1.4" fill="currentColor"/>
          <circle cx="17" cy="21" r="1.4" fill="currentColor"/>
        </svg>
        <span class="cart-count" id="cart-count">0</span>
      </button>
      <?php endif; ?>

      <?php /* The notification bell is injected by order_notifications.js */ ?>

      <a class="nav-profile-link" href="<?=site_url('/customer/profile')?>">
        <span class="profile-icon nav-profile-icon<?= $active === 'profile' ? ' nav-profile-icon--current' : '' ?>" aria-hidden="true">
          <svg viewBox="0 0 24 24" width="15" height="15" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="12" cy="8" r="3.5" stroke="var(--cream-dim, #cbb9a1)" stroke-width="1.6"/>
            <path d="M4.5 20c1.4-4 4.4-6 7.5-6s6.1 2 7.5 6" stroke="var(--cream-dim, #cbb9a1)" stroke-width="1.6" stroke-linecap="round"/>
          </svg>
        </span>
        <span class="customer-name nav-customer-name" id="customer-name">Hi, <?= esc( session()->get('first_name') ) ?></span>
      </a>

      <span id="nav-auth-slot">
        <form class="nav-logout-form" action="<?=site_url('/logout')?>" method="post">
          <?= csrf_field() ?>
          <button type="submit" class="login-link nav-logout-btn">log out</button>
        </form>
      </span>
    </div>
  </nav>
</header>

<?php if ( $show_cart ) include __DIR__ . '/cart_modal.php'; ?>