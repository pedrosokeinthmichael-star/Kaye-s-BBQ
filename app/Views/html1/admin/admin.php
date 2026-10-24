<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin — Kaye's Barbeque</title>
<link rel="stylesheet" href="<?=base_url('css/admin_css/base.css')?>">
<link rel="stylesheet" href="<?=base_url('css/admin_css/header.css')?>">
<link rel="stylesheet" href="<?=base_url('css/admin_css/footer.css')?>">
<link rel="stylesheet" href="<?=base_url('css/admin_css/buttons.css')?>">
<link rel="stylesheet" href="<?=base_url('css/admin_css/forms.css')?>">
<link rel="stylesheet" href="<?=base_url('css/admin_css/cards.css')?>">
<link rel="stylesheet" href="<?=base_url('css/admin_css/tables.css')?>">
<link rel="stylesheet" href="<?=base_url('css/admin_css/notices.css')?>">
<link rel="stylesheet" href="<?=base_url('css/admin_css/modal.css')?>">
<link rel="stylesheet" href="<?=base_url('css/admin_css/dashboard.css')?>">
<style>
  a.stat-card{text-decoration:none;color:inherit;cursor:pointer;transition:transform .15s ease,box-shadow .15s ease,border-color .15s ease;}
  a.stat-card:hover,a.stat-card:focus-visible{transform:translateY(-2px);box-shadow:0 6px 16px rgba(0,0,0,.12);border-color:var(--ember);}
</style>
</head>
<body>

<header class="site-header">
  <nav class="nav">
    <a href="<?= site_url('/admin/home') ?>" class="brand">
      
       <img class="brand-mark brand-logo" src="<?=base_url('babeques/LOGO_KBBQpng.png')?>" alt="Kaye's BBQ logo" width="80" height="80" style="width:80px;height:80px;object-fit:contain;">
    </a>

  <ul class="nav-links">
      <li><a href="<?= site_url('/admin/home') ?>" class="active">Home</a></li>
      <li><a href="<?= site_url('/admin/menus') ?>">Menus</a></li>
      <li><a href="<?= site_url('/admin/orders') ?>">Orders</a></li>
      <li><a href="<?= site_url('/admin/sales') ?>">Sales</a></li>
  </ul>

    <div class="nav-actions">
      <a href="<?= site_url('/admin/profile') ?>" class="nav-user" title="View profile">
        <span class="nav-avatar" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="8" r="3.4" stroke="currentColor" stroke-width="1.7"/><path d="M5 19c1.1-3.5 3.9-5.2 7-5.2s5.9 1.7 7 5.2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </span>
        Hi, <span class="nav-user-name">Kaye</span>
      </a>
      <form action="<?= site_url('/logout') ?>" method="post" style="display:inline;margin:0;">
        <?= csrf_field() ?>
        <button type="submit" class="logout-link" style="appearance:none;-webkit-appearance:none;cursor:pointer;">Logout</button>
      </form>
    </div>
  </nav>
</header>

<?php
  // Everything on this page comes from the controller. Nothing is
  // hard-coded: missing values fall back to zero / empty states.
  //
  //   $pending_count       int
  //   $orders_this_month   int
  //   $revenue_this_month  float   (delivered orders only)
  //   $menu_count          int
  //   $best_seller         ['name' => string, 'sold'   => int]   | null
  //   $top_earner          ['name' => string, 'sales'  => float] | null
  //   $slowest_seller      ['name' => string, 'sold'   => int]   | null
  //   $sales_week          [ ['label' => 'Monday', 'total' => float], ... ]
  //   $menus_ordered       [ ['name' => string, 'count' => int], ... ]  (highest first)
  $pendingCount  = (int)   ( $pending_count ?? 0 );
  $ordersMonth   = (int)   ( $orders_this_month ?? 0 );
  $revenueMonth  = (float) ( $revenue_this_month ?? 0 );
  $menuCount     = (int)   ( $menu_count ?? 0 );
  $bestSeller    = $best_seller ?? null;
  $topEarner     = $top_earner ?? null;
  $slowest       = $slowest_seller ?? null;
  $salesWeek     = $sales_week ?? [];
  $menusOrdered  = $menus_ordered ?? [];
  $weekMax       = $salesWeek    ? max( array_map( 'floatval', array_column( $salesWeek, 'total' ) ) ) : 0;
  $menuMax       = $menusOrdered ? max( array_map( 'intval',   array_column( $menusOrdered, 'count' ) ) ) : 0;
?>

<section style="padding-bottom:0;">
  <div class="container">
    <div class="eyebrow" style="color:var(--ember);">Admin Panel</div>
    <h1 style="font-size:38px;margin-bottom:6px;">Dashboard</h1>
    <p style="color:var(--ink-soft);max-width:60ch;" id="dashboard-intro">A quick look at how the shop is doing today.</p>
  </div>
</section>

<section style="padding-bottom:0;">
  <div class="container">
    <div id="action-banner" class="notice notice-success" style="display:none;font-size:15px;"></div>
  </div>
</section>

<!--
  At a glance — the four numbers worth knowing every day. Order
  status breakdown lives on the Orders page, so it isn't repeated here.
-->
<section>
  <div class="container">
    <div class="stat-grid" style="grid-template-columns:repeat(3,1fr);">
      <a href="<?= site_url('/admin/orders') ?>" class="stat-card" title="Go to Orders">
        <div class="stat-icon">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 4h2l2.4 12.2a2 2 0 0 0 2 1.8h8.2a2 2 0 0 0 2-1.6L21 8H6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><circle cx="10" cy="21" r="1.4" fill="currentColor"/><circle cx="17" cy="21" r="1.4" fill="currentColor"/></svg>
        </div>
        <div>
          <div class="stat-label">Orders</div>
          <div class="stat-value" id="stat-orders"><?= esc( number_format( $ordersMonth ) ) ?></div>
          <div class="stat-sub" id="stat-orders-sub">This month</div>
        </div>
      </a>
      <a href="<?= site_url('/admin/sales') ?>" class="stat-card" title="Go to Sales">
        <div class="stat-icon">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="2" y="6" width="20" height="12" rx="2" stroke="currentColor" stroke-width="1.7"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.7"/></svg>
        </div>
        <div>
          <div class="stat-label">Revenue</div>
          <div class="stat-value" id="stat-revenue">₱<?= esc( number_format( $revenueMonth, 0 ) ) ?></div>
          <div class="stat-sub" id="stat-revenue-sub">From delivered orders, this month</div>
        </div>
      </a>
      <a href="<?= site_url('/admin/menus') ?>" class="stat-card" title="Go to Menus">
        <div class="stat-icon">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7 3v7a2 2 0 0 0 4 0V3M9 10v11M17 3c-1.5 0-2.5 1.5-2.5 4s1 4 2.5 4v10" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div>
          <div class="stat-label">Menu Items</div>
          <div class="stat-value" id="stat-menu-count"><?= esc( number_format( $menuCount ) ) ?></div>
          <div class="stat-sub" id="stat-menu-sub">Across all categories</div>
        </div>
      </a>
    </div>
  </div>
</section>

<!--
  Menu insights — kept to three short cards instead of also
  repeating the same items in a "Top Sellers by Profit" bar chart.
  Full profit breakdowns belong on the Sales page.
-->
<section style="padding-top:0;">
  <div class="container">
    <div class="highlight-grid">
      <div class="highlight-card">
        <div class="highlight-icon top">
          <svg viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M12 2l2.6 5.6L21 8.3l-4.6 4.2 1.2 6.5L12 15.8 6.4 19l1.2-6.5L3 8.3l6.4-.7L12 2z"/></svg>
        </div>
        <div>
          <div class="highlight-label">Best Seller</div>
          <div class="highlight-value" id="highlight-top-name"><?= esc( $bestSeller['name'] ?? '—' ) ?></div>
          <div class="highlight-sub" id="highlight-top-sub"><?= $bestSeller ? esc( number_format( (int) $bestSeller['sold'] ) ) . ' sold this month' : 'No sales yet this month' ?></div>
        </div>
      </div>
      <div class="highlight-card">
        <div class="highlight-icon margin">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><polyline points="3 17 9 11 13 15 21 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><polyline points="14 6 21 6 21 13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div>
          <div class="highlight-label" title="The item that brought in the most money this month">Top Earner</div>
          <div class="highlight-value" id="highlight-margin-name"><?= esc( $topEarner['name'] ?? '—' ) ?></div>
          <div class="highlight-sub" id="highlight-margin-sub"><?= $topEarner ? '₱' . esc( number_format( (float) $topEarner['sales'], 0 ) ) . ' in sales this month' : 'No sales yet this month' ?></div>
        </div>
      </div>
      <div class="highlight-card">
        <div class="highlight-icon attention">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 3l10 18H2L12 3z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><line x1="12" y1="9" x2="12" y2="14" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><circle cx="12" cy="17" r="1" fill="currentColor"/></svg>
        </div>
        <div>
          <div class="highlight-label" title="Your slowest-selling item — might be worth a promo or removing from the menu">Slowest Seller</div>
          <div class="highlight-value" id="highlight-slow-name"><?= esc( $slowest['name'] ?? '—' ) ?></div>
          <div class="highlight-sub" id="highlight-slow-sub"><?= $slowest ? esc( number_format( (int) $slowest['sold'] ) ) . ' sold this month' : 'No sales yet this month' ?></div>
        </div>
      </div>
    </div>
  </div>
</section>

<div class="grate-divider"></div>

<!--
  Sales trend + Menus Ordered — replaces the old Recent Orders
  table. Full order-by-order detail already lives on the Orders
  page; the dashboard now shows
  the shape of the week instead of a duplicate list of rows.
-->
<section>
  <div class="container">
    <div class="panel-grid">

      <div class="panel">
        <div class="panel-head">
          <h2>
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="2" y="6" width="20" height="12" rx="2" stroke="currentColor" stroke-width="1.7"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.7"/></svg>
            Sales This Week
          </h2>
        </div>
        <p class="panel-sub">Revenue from delivered orders, day by day.</p>
        <?php if ( empty( $salesWeek ) || $weekMax <= 0 ) : ?>
          <p class="empty-note" id="sales-week-chart">No delivered orders this week yet.</p>
        <?php else : ?>
        <div class="chart-bars" id="sales-week-chart">
          <?php foreach ( $salesWeek as $day ) :
            $dayTotal = (float) $day['total'];
            $dayPct   = $weekMax > 0 ? round( $dayTotal / $weekMax * 100 ) : 0;
          ?>
            <div class="chart-bar-row">
              <div class="chart-bar-label"><?= esc( $day['label'] ) ?></div>
              <div class="chart-bar-track"><div class="chart-bar-fill" style="width:<?= (int) $dayPct ?>%"></div></div>
              <div class="chart-bar-value">₱<?= number_format( $dayTotal, 0 ) ?></div>
            </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <div style="margin-top:16px;padding-top:14px;border-top:1px solid var(--line);text-align:right;">
          <a href="<?= site_url('/admin/sales') ?>" class="btn btn-ghost btn-sm">View Full Sales Report</a>
        </div>
      </div>

      <div class="panel">
        <div class="panel-head">
          <h2>
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7 3v7a2 2 0 0 0 4 0V3M9 10v11M17 3c-1.5 0-2.5 1.5-2.5 4s1 4 2.5 4v10" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Menus Ordered
          </h2>
        </div>
        <p class="panel-sub">Times each item has been ordered this month.</p>
        <?php if ( empty( $menusOrdered ) ) : ?>
          <p class="empty-note" id="menus-ordered-chart">No orders yet this month.</p>
        <?php else : ?>
        <div class="chart-bars" id="menus-ordered-chart">
          <?php foreach ( $menusOrdered as $menu ) :
            $menuCountOrdered = (int) $menu['count'];
            $menuPct = $menuMax > 0 ? round( $menuCountOrdered / $menuMax * 100 ) : 0;
          ?>
            <div class="chart-bar-row">
              <div class="chart-bar-label"><?= esc( $menu['name'] ) ?></div>
              <div class="chart-bar-track"><div class="chart-bar-fill" style="width:<?= (int) $menuPct ?>%"></div></div>
              <div class="chart-bar-value"><?= esc( number_format( $menuCountOrdered ) ) ?></div>
            </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <div style="margin-top:16px;padding-top:14px;border-top:1px solid var(--line);text-align:right;">
          <a href="<?= site_url('/admin/menus') ?>" class="btn btn-ghost btn-sm">Manage Menus</a>
        </div>
      </div>

    </div>
  </div>
</section>



<footer class="site-footer">
  <div class="container">
    <div class="footer-bottom">
      <span>© 2026 Kaye's Barbeque. All rights reserved.</span>
      <span>Smoked daily, served fresh.</span>
    </div>
  </div>
</footer>




<script src="<?= base_url('js/admin/notifications.js') ?>"></script>

</body>
</html>