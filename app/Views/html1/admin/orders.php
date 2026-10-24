<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Orders — Kaye's Barbeque Admin</title>
<link rel="stylesheet" href="<?= base_url('css/admin_css/base.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/admin_css/header.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/admin_css/footer.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/admin_css/buttons.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/admin_css/forms.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/admin_css/cards.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/admin_css/tables.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/admin_css/notices.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/admin_css/modal.css') ?>">
</head>
<body>


<header class="site-header">
  <nav class="nav">
    <a href="<?= site_url('/admin/home') ?>" class="brand">
      <img class="brand-mark brand-logo" src="<?=base_url('babeques/LOGO_KBBQpng.png')?>" alt="Kaye's BBQ logo" width="80" height="80" style="width:80px;height:80px;object-fit:contain;">
    </a>



  <ul class="nav-links">
      <li><a href="<?= site_url('/admin/home') ?>" >Home</a></li>
      <li><a href="<?= site_url('/admin/menus') ?>" >Menus</a></li>
      <li><a href="<?= site_url('/admin/orders') ?>" class="active">Orders</a></li>
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

<section style="padding-bottom:0;">
  <div class="container">
    <div class="eyebrow" style="color:var(--ember);">Admin Panel</div>
    <h1 style="font-size:38px;margin-bottom:6px;">Orders</h1>
    <p style="color:var(--ink-soft);max-width:60ch;">Orders that still need action. Move an order from Pending to Delivering, then Delivered — once an order is delivered or cancelled, it moves to the Sales page.</p>
  </div>
</section>
<section style="padding-bottom:0;padding-top:18px;">
  <div class="container">
    <div class="dp-tab-bar" role="tablist" aria-label="Orders page sections">
      <button type="button" class="dp-tab-btn active" role="tab" id="os-tab-orders" data-os-tab="tab-orders" aria-controls="tab-orders" aria-selected="true">Active Orders</button>
      <button type="button" class="dp-tab-btn" role="tab" id="os-tab-settings" data-os-tab="tab-order-settings" aria-controls="tab-order-settings" aria-selected="false">Order Settings</button>
    </div>
  </div>
</section>


<div id="tab-orders" data-os-panel role="tabpanel" aria-labelledby="os-tab-orders">

<section>
  <div class="container">
    <?php
      $activeOrders    = $active_orders ?? [];
      $pendingCount    = count( array_filter( $activeOrders, fn( $o ) => $o['ORDER_STATUS'] === 'Pending' ) );
      $deliveringCount = count( array_filter( $activeOrders, fn( $o ) => $o['ORDER_STATUS'] === 'Delivering' ) );
      $activeCount     = count( $activeOrders );
    ?>
    <div class="highlight-grid" style="grid-template-columns:repeat(3,1fr);">
      <div class="highlight-card">
        <div class="highlight-icon top">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
        </div>
        <div>
          <div class="highlight-label">Active Orders</div>
          <div class="highlight-value" id="o-total"><?= esc( $activeCount ) ?></div>
          <div class="highlight-sub">Still need action</div>
        </div>
      </div>
      <div class="highlight-card">
        <div class="highlight-icon attention">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.7"/><path d="M12 7v5l3.5 2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
        </div>
        <div>
          <div class="highlight-label">Pending</div>
          <div class="highlight-value" id="o-pending"><?= esc( $pendingCount ) ?></div>
          <div class="highlight-sub">Waiting to be sent out</div>
        </div>
      </div>
      <div class="highlight-card">
        <div class="highlight-icon margin">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 12h13M11 6l6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div>
          <div class="highlight-label">Delivering</div>
          <div class="highlight-value" id="o-delivering"><?= esc( $deliveringCount ) ?></div>
          <div class="highlight-sub">Out for delivery now</div>
        </div>
      </div>
    </div>
  </div>
</section>

<div class="grate-divider"></div>

<section style="padding-bottom:0;">
  <div class="container">
    <div id="orders-notice" class="notice notice-success" style="display:none;"></div>
  </div>
</section>

<section style="padding-top:14px;", >
  <div class="container">
    <div class="panel">
      <div class="panel-head">
        <h2>
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 4h2l2.4 12.2a2 2 0 0 0 2 1.8h8.2a2 2 0 0 0 2-1.6L21 8H6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
          Active Orders
        </h2>
        <span class="panel-note" id="orders-count-note">Showing <?= esc( $activeCount ) ?> of <?= esc( $activeCount ) ?> orders</span>
      </div>
      <p class="panel-sub">Newest orders first. Click an order to view its full details. Pending orders can be moved to Delivering, then Delivered — or cancelled at any point before they're delivered. Delivered and cancelled orders move to the Sales page.</p>
      
    <div class="history-toolbar">
      <div class="filters" id="tracking-filters">
        <span class="chip active" data-filter="all">All</span>
        <span class="chip" data-filter="pending">Pending</span>
        <span class="chip" data-filter="preparing">Preparing</span>
        <span class="chip" data-filter="delivering">Delivering</span>
      </div>
    </div>

      <br>

      <div class="table-wrap admin-table-wrap">
        <table>
          <thead>
            <tr>
              <th>Order #</th>
              <th>Customer</th>
              <th>Date</th>
              <th>Items</th>
              <th>Order Type</th>
              <th class="num">Total</th>
              <th>Order Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="orders-table-body"
                 data-status-base="<?= esc( site_url( 'admin/orders' ), 'attr' ) ?>"
                 data-csrf-name="<?= esc( csrf_token(), 'attr' ) ?>"
                 data-csrf-hash="<?= esc( csrf_hash(), 'attr' ) ?>">
            <?php foreach ( $activeOrders as $order ) :
              $isDelivery  = $order['ORDER_TYPE'] === 'delivery';
              $statusRaw   = $order['ORDER_STATUS'] ?? '';
              $statusKey   = strtolower( $statusRaw );
              // OrderModel::getActiveOrdersForAdmin() returns a single
              // concatenated CUSTOMER_NAME (not FIRST_NAME/LAST_NAME),
              // a phone column aliased CUSTOMER_PHONE (not PHONE_NO),
              // and a barangay column aliased BARANGAY (not
              // BARANGAY_NAME) — read those instead.
              $customer    = trim( $order['CUSTOMER_NAME'] ?? '' );

              // Each item comes back from the model shaped
              // NAME/QUANTITY/PRICE/SUB_TOTAL/IMAGE (uppercase) —
              // normalize to name/qty/price/image here since that's
              // what this view's summary/subtotal math and
              // orders.js's modal renderer both expect. Menu images
              // are served from uploads/ (see menus.php's thumbnail
              // markup), so build the full URL here rather than in JS.
              $itemsList = array_map( fn( $i ) => [
                  'name'  => $i['NAME'],
                  'qty'   => (int) $i['QUANTITY'],
                  'price' => (float) $i['PRICE'],
                  'image' => ! empty( $i['IMAGE'] ) ? base_url( 'uploads/' . $i['IMAGE'] ) : null,
              ], $order['ITEMS'] ?? [] );

              $itemsSummary = implode( ', ', array_map(
                  fn( $i ) => ( $i['qty'] > 1 ? $i['qty'] . 'x ' : '' ) . $i['name'],
                  $itemsList
              ) );
              $subtotal = array_reduce( $itemsList, fn( $sum, $i ) => $sum + ( $i['qty'] * $i['price'] ), 0 );
              $deliveryFee = $isDelivery ? (float) $order['TOTAL_AMOUNT'] - $subtotal : 0;

              // GCash proof screenshots are moved to uploads/payments/
              // by OrderController::placeOrder(); the model already
              // normalizes PAYMENT_REFERENCE / PROOF_OF_PAYMENT to ''
              // for non-GCash orders, so build the full URL only when
              // one is actually present.
              $isGcash = ( $order['PAYMENT_METHOD'] ?? '' ) === 'GCash';
              $proofFile = $order['PROOF_OF_PAYMENT'] ?? '';

              // Every payment the customer sent for this order. The checkout
              // payment is carried by gcashReference / proofOfPayment below;
              // EXTRA_PAYMENTS (OrderModel::withExtraPayments()) are the ones
              // added later with "Add Payment". Both the Order Details modal
              // and the Verify Payment modal read `extraPayments`.
              $extraPayments = array_map( function ( $p ) {
                  $ref   = trim( (string) ( $p['REFERENCE_NUMBER'] ?? '' ) );
                  $proof = trim( (string) ( $p['PROOF_OF_PAYMENT'] ?? '' ) );
                  return [
                      'date'      => ! empty( $p['PAYMENT_DATE'] ) ? date( 'M j, Y \\· g:i A', strtotime( $p['PAYMENT_DATE'] ) ) : '',
                      'reference' => strcasecmp( $ref, 'None' ) === 0 ? '' : $ref,
                      'proof'     => ( $proof !== '' && strcasecmp( $proof, 'None' ) !== 0 )
                          ? base_url( 'uploads/payments/' . $proof ) : '',
                  ];
              }, $order['EXTRA_PAYMENTS'] ?? [] );

              $orderData = [
                  'id'              => '#' . $order['ORDER_ID'],
                  'customer'        => $customer !== '' ? $customer : 'Unknown Customer',
                  'phone'           => $order['CUSTOMER_PHONE'] ?? '',
                  'email'           => $order['CUSTOMER_EMAIL'] ?? '',
                  'placedAt'        => date( 'M j, Y \\· g:i A', strtotime( $order['ORDER_DATE'] ) ),
                  'type'            => $isDelivery ? 'Delivery' : 'Pickup',
                  'address'         => $isDelivery
                      ? trim( ( $order['BARANGAY_NAME'] ?? $order['BARANGAY'] ?? '' ) . ', ' . ( $order['ADDRESS_DETAIL'] ?? '' ), ', ' )
                      : null,
                  'pickupNote'      => ! $isDelivery ? 'Pickup at Balongis, Balulang branch' : null,
                  'payment'         => $isGcash
                      ? 'GCash'
                      : ( $isDelivery ? 'Cash on Delivery' : 'Cash on Pickup' ),
                  'isGcash'         => $isGcash,
                  'gcashReference'  => $isGcash ? ( $order['PAYMENT_REFERENCE'] ?? '' ) : '',
                  'proofOfPayment'  => ( $isGcash && $proofFile !== '' ) ? base_url( 'uploads/payments/' . $proofFile ) : '',
                  'paymentDate'     => ! empty( $order['PAYMENT_DATE'] ) ? date( 'M j, Y \\· g:i A', strtotime( $order['PAYMENT_DATE'] ) ) : '',
                  'extraPayments'   => $extraPayments,
                  'status'          => $statusRaw,
                  'notes'           => $order['NOTE'] ?? '',
                  'items'           => $itemsList,
                  'deliveryFee'     => $deliveryFee,
                  'subtotal'        => $subtotal,
                  'total'           => (float) $order['TOTAL_AMOUNT'],
              ];
            ?>
              <tr tabindex="0" role="button" aria-haspopup="dialog" data-order='<?= esc( json_encode( $orderData ), 'attr' ) ?>'>
                <td class="order-id">#<?= (int) $order['ORDER_ID'] ?></td>
                <td><?= esc( $customer !== '' ? $customer : 'Unknown Customer' ) ?></td>
                <td><?= esc( date( 'M j, Y', strtotime( $order['ORDER_DATE'] ) ) ) ?></td>
                <td><?= esc( $itemsSummary ) ?></td>
                <td><?= $isDelivery ? 'Delivery' : 'Pickup' ?></td>
                <td class="order-total num">₱<?= number_format( (float) $order['TOTAL_AMOUNT'], 0 ) ?></td>
                <td>
                  <span class="status status-<?= esc( $statusKey ) ?>"><?= esc( $statusRaw ) ?></span>
                  <?php if ( ! empty( $extraPayments ) ) : ?>
                    <span class="tag" title="The customer sent more than one payment"><?= count( $extraPayments ) + 1 ?> payments</span>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="row-actions">
                    <button type="button" class="btn btn-outline btn-sm" data-action="view">View</button>
                    <?php if ( $statusKey === 'pending' && $isGcash ) : ?>
                      <?php /* GCash: verify_payment_modal.php intercepts this click and only lets it
                               through to orders.js once the payment has been verified. */ ?>
                      <button class="btn btn-ghost btn-sm" data-action="mark-preparing" data-verify="1">Verify Payment</button>
                    <?php elseif ( $statusKey === 'pending' ) : ?>
                      <button class="btn btn-ghost btn-sm" data-action="mark-preparing">Mark Preparing</button>
                    <?php elseif ( $statusKey === 'preparing' ) : ?>
                      <button class="btn btn-ghost btn-sm" data-action="mark-delivering">Mark Delivering</button>
                    <?php elseif ( $statusKey === 'delivering' ) : ?>
                      <button class="btn btn-ghost btn-sm" data-action="mark-complete">Mark Complete</button>
                    <?php endif; ?>
                   <!--<button class="btn btn-danger btn-sm" data-action="cancel"  >Cancel</button button>-->
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <p id="orders-empty" class="empty-note" style="<?= empty( $activeOrders ) ? '' : 'display:none;' ?>">No orders recorded in this browser yet. Log in as a customer and place an order to see it show up here.</p>
    </div>
  </div>
</section>

</div>

<div id="tab-order-settings" data-os-panel role="tabpanel" aria-labelledby="os-tab-settings" hidden>
<?php include __DIR__ . '/modals/delivery_payment_settings.php'; ?>
</div>

<script src="<?= base_url('js/admin/order_tab.js') ?>"></script>

<?php include __DIR__ . '/modals/order_modal.php'; ?>
<?php include __DIR__ . '/modals/cancel_modal.php'; ?>
<?php include __DIR__ . '/modals/verify_payment_modal.php'; ?>

<footer class="site-footer">
  <div class="container">
    <div class="footer-bottom">
      <span>© 2026 Kaye's Barbeque. All rights reserved.</span>
      <span>Smoked daily, served fresh.</span>
    </div>
  </div>
</footer>



<script src="<?= base_url('js/admin/notifications.js') ?>"></script>
<script src="<?= base_url('js/admin/orders.js') ?>"></script>

</body>
</html>