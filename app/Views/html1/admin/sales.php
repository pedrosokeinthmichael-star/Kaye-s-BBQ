<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sales — Kaye's Barbeque Admin</title>
<link rel="stylesheet" href="<?= base_url('css/admin_css/base.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/admin_css/header.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/admin_css/footer.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/admin_css/buttons.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/admin_css/forms.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/admin_css/cards.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/admin_css/tables.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/admin_css/notices.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/admin_css/modal.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/admin_css/sales_filter.css') ?>">
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
      <li><a href="<?= site_url('/admin/orders') ?>">Orders</a></li>
      <li><a href="<?= site_url('/admin/sales') ?>"  class="active">Sales</a></li>
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
    <h1 style="font-size:38px;margin-bottom:6px;">Sales</h1>
    <p style="color:var(--ink-soft);max-width:60ch;" id="sales-intro">How the shop is performing, based on delivered, cancelled and refunded orders.</p>
  </div>
</section>

<?php
  $completedOrders = $completed_orders ?? [];
  $deliveredCount  = $delivered_count ?? 0;
  $cancelledCount  = $cancelled_count ?? 0;
  $refundedCount   = $refunded_count ?? 0;
  $totalCount      = $total_count ?? 0;
  $netRevenue      = $net_revenue ?? 0;
  $avgOrder        = $avg_order ?? 0;
  $cancelRate      = $cancel_rate ?? 0;

  // 'None' / empty placeholders -> '', so the view can skip printing them.
  $cleanVal = function ( $v ) {
      $v = trim( (string) $v );
      return strcasecmp( $v, 'None' ) === 0 ? '' : $v;
  };
  $fmtDate = fn( $v ) => empty( $v ) ? '' : date( 'F j, Y \a\t g:i A', strtotime( $v ) );
?>
<section>
  <div class="container">
    <div class="stat-grid">
      <div class="stat-card">
        <div class="stat-icon">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="2" y="6" width="20" height="12" rx="2" stroke="currentColor" stroke-width="1.7"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.7"/></svg>
        </div>
        <div>
          <div class="stat-label">Net Revenue</div>
          <div class="stat-value" id="s-revenue">₱<?= esc( number_format( $netRevenue, 0 ) ) ?></div>
          <div class="stat-sub" id="s-revenue-sub">From <?= esc( $deliveredCount ) ?> delivered order<?= $deliveredCount === 1 ? '' : 's' ?></div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 4h2l2.4 12.2a2 2 0 0 0 2 1.8h8.2a2 2 0 0 0 2-1.6L21 8H6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><circle cx="10" cy="21" r="1.4" fill="currentColor"/><circle cx="17" cy="21" r="1.4" fill="currentColor"/></svg>
        </div>
        <div>
          <div class="stat-label">Completed Orders</div>
          <div class="stat-value" id="s-orders"><?= esc( $deliveredCount ) ?></div>
          <div class="stat-sub" id="s-orders-sub">Out of <?= esc( $totalCount ) ?> order<?= $totalCount === 1 ? '' : 's' ?> placed</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><line x1="9" y1="8" x2="15" y2="8" stroke="currentColor" stroke-width="1.4"/><line x1="9" y1="12" x2="15" y2="12" stroke="currentColor" stroke-width="1.4"/></svg>
        </div>
        <div>
          <div class="stat-label">Average Order</div>
          <div class="stat-value" id="s-aov">₱<?= esc( number_format( $avgOrder, 0 ) ) ?></div>
          <div class="stat-sub">Per completed order</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
        </div>
        <div>
          <div class="stat-label">Cancellation Rate</div>
          <div class="stat-value" id="s-cancel-rate"><?= esc( $cancelRate ) ?>%</div>
          <div class="stat-sub" id="s-cancel-sub"><?= esc( $cancelledCount ) ?> of <?= esc( $totalCount ) ?> orders cancelled<?= $refundedCount > 0 ? ' · ' . esc( $refundedCount ) . ' refunded' : '' ?></div>
        </div>
      </div>
    </div>
  </div>
</section>



<div class="grate-divider"></div>

<section>
  <div class="container">
    <div class="panel">
      <div class="panel-head">
        <h2>
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="4" width="18" height="18" rx="2" stroke="currentColor" stroke-width="1.7"/><line x1="3" y1="9" x2="21" y2="9" stroke="currentColor" stroke-width="1.7"/><line x1="8" y1="2" x2="8" y2="6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><line x1="16" y1="2" x2="16" y2="6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
          Filter by Date
        </h2>
        <span class="panel-note" id="filter-range-note">Showing all recorded sales</span>
      </div>
      <p class="panel-sub">Look at sales for a single day, a week, a month, a year, or any custom date range — for example, Feb 20 to Apr 10.</p>

      <div class="date-filter-modes" role="tablist" aria-label="Date filter mode">
        <button type="button" class="filter-mode-btn active" data-mode="all">All Time</button>
        <button type="button" class="filter-mode-btn" data-mode="day">Day</button>
        <button type="button" class="filter-mode-btn" data-mode="week">Week</button>
        <button type="button" class="filter-mode-btn" data-mode="month">Month</button>
        <button type="button" class="filter-mode-btn" data-mode="year">Year</button>
        <button type="button" class="filter-mode-btn" data-mode="range">Custom Range</button>
      </div>

      <div class="date-filter-inputs">
        <div class="field date-filter-field" data-for="day">
          <label for="filter-day">Date</label>
          <input type="date" id="filter-day">
        </div>
        <div class="field date-filter-field" data-for="week">
          <label for="filter-week">Any day in the week</label>
          <input type="date" id="filter-week">
        </div>
        <div class="field date-filter-field" data-for="month">
          <label for="filter-month">Month</label>
          <input type="month" id="filter-month">
        </div>
        <div class="field date-filter-field" data-for="year">
          <label for="filter-year">Year</label>
          <input type="number" id="filter-year" min="2000" max="2100" placeholder="e.g. 2026">
        </div>
        <div class="field date-filter-field" data-for="range">
          <label for="filter-range-from">From</label>
          <input type="date" id="filter-range-from">
        </div>
        <div class="field date-filter-field" data-for="range">
          <label for="filter-range-to">To</label>
          <input type="date" id="filter-range-to">
        </div>
        <div class="date-filter-actions">
          <button type="button" class="btn btn-primary" id="filter-apply-btn">Apply</button>
          <button type="button" class="btn btn-outline" id="filter-reset-btn">View All</button>
        </div>
      </div>
      <span class="form-error" id="filter-error"></span>
    </div>
  </div>
</section>


<section>
  <div class="container">
    <div class="panel">
      <div class="panel-head">
        <h2>
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
          TOTAL SALES
        </h2>
        <span class="panel-note" id="daily-count-note"><?= esc( $totalCount ) ?> order<?= $totalCount === 1 ? '' : 's' ?> on record</span>
      </div>
      <p class="panel-sub">Every day with at least one order, most recent first.</p>
      <div class="table-wrap admin-table-wrap">
        <table>
          <thead>
            <tr>
              <th>Order #</th>
              <th>Date</th>
              <th>Customer</th>
              <th>Items</th>
              <th>Type</th>
              <th>Address</th>
              <th>Payment</th>
              <th>Total</th>
              <th>Status</th>
              <th>Details</th>
            </tr>
          </thead>
          <tbody id="daily-table-body">
            <?php foreach ( $completedOrders as $order ) :
              $statusRaw  = $order['ORDER_STATUS'] ?? '';
              $statusKey  = strtolower( $statusRaw );
              $isDelivery = strtolower( $order['ORDER_TYPE'] ?? '' ) === 'delivery';
              $timestamp  = strtotime( $order['ORDER_DATE'] );
              $customer   = trim( $order['CUSTOMER_NAME'] ?? '' );

              // GCash orders show the payment method plus their
              // reference number; cash orders just show the method.
              $isGcash   = ( $order['PAYMENT_METHOD'] ?? '' ) === 'GCash';
              $reference = $order['PAYMENT_REFERENCE'] ?? '';
              $paymentLabel = $order['PAYMENT_METHOD'] ?? '';
              if ( $isGcash && $reference !== '' ) {
                  $paymentLabel .= ' (' . $reference . ')';
              }

              // Refund orders: the payment the admin sent back to the customer.
              $isRefund    = $statusKey === 'refund';
              $refundRow   = $order['REFUND_PAYMENT'] ?? null;
              $refundRef   = $refundRow ? $cleanVal( $refundRow['REFERENCE_NUMBER'] ?? '' ) : '';

              // Everything the "View" modal needs, passed along as JSON on the row.
              $detail = [
                  'id'        => (int) $order['ORDER_ID'],
                  'placed'    => date( 'F j, Y \a\t g:i A', $timestamp ),
                  'status'    => $statusRaw,
                  'type'      => $isDelivery ? 'Delivery' : 'Pickup',
                  'customer'  => $customer !== '' ? $customer : 'Unknown Customer',
                  'phone'     => $order['CUSTOMER_PHONE'] ?? '',
                  'email'     => $order['CUSTOMER_EMAIL'] ?? '',
                  'address'   => $isDelivery ? ( $order['DELIVERY_ADDRESS'] ?? '' ) : '',
                  'payment'   => $order['PAYMENT_METHOD'] ?? '',
                  'reference' => $reference,
                  // Payment details for the modal: the customer's own payment (+ any they
                  // added later) and, for Refund orders, the refund the admin sent back.
                  'paymentDate'   => $fmtDate( $order['PAYMENT_DATE'] ?? '' ),
                  'customerProof' => $cleanVal( $order['PROOF_OF_PAYMENT'] ?? '' ),
                  'extraPayments' => array_map( fn( $p ) => [
                      'reference' => $cleanVal( $p['REFERENCE_NUMBER'] ?? '' ),
                      'proof'     => $cleanVal( $p['PROOF_OF_PAYMENT'] ?? '' ),
                      'date'      => $fmtDate( $p['PAYMENT_DATE'] ?? '' ),
                  ], $order['EXTRA_PAYMENTS'] ?? [] ),
                  'refund'        => ( $isRefund && $refundRow ) ? [
                      'reference' => $refundRef,
                      'proof'     => $cleanVal( $refundRow['PROOF_OF_PAYMENT'] ?? '' ),
                      'date'      => $fmtDate( $refundRow['PAYMENT_DATE'] ?? '' ),
                  ] : null,
                  'note'      => trim( (string) ( $order['NOTE'] ?? '' ) ),
                  'items'     => array_map( fn( $i ) => [
                      'name'     => $i['NAME'],
                      'image'    => $i['IMAGE'] ?? '',
                      'qty'      => (int) $i['QUANTITY'],
                      'price'    => (float) $i['PRICE'],
                      'subtotal' => (float) $i['SUB_TOTAL'],
                  ], $order['ITEMS'] ?? [] ),
                  'subtotal'  => (float) ( $order['SUBTOTAL'] ?? 0 ),
                  'fee'       => (float) ( $order['DELIVERY_FEE'] ?? 0 ),
                  'total'     => (float) $order['TOTAL_AMOUNT'],
              ];
            ?>
              <tr data-order="<?= esc( json_encode( $detail ), 'attr' ) ?>" data-date="<?= esc( date( 'Y-m-d', $timestamp ) ) ?>" data-total="<?= esc( (float) $order['TOTAL_AMOUNT'] ) ?>" data-status="<?= esc( $statusKey ) ?>">
                <td class="order-id">#<?= (int) $order['ORDER_ID'] ?></td>
                <td><?= esc( date( 'F j, Y', $timestamp ) ) ?></td>
                <td><?= esc( $customer !== '' ? $customer : 'Unknown Customer' ) ?></td>
                <td><?= esc( $order['ITEMS_SUMMARY'] ?? '' ) ?></td>
                <td><?= $isDelivery ? 'Delivery' : 'Pickup' ?></td>
                <td><?= $isDelivery ? esc( $order['DELIVERY_ADDRESS'] ?? '' ) : '—' ?></td>
                <td><?= esc( $paymentLabel !== '' ? $paymentLabel : '—' ) ?><?php if ( $isRefund ) : ?><br><small style="color:var(--ink-soft);">Refunded<?= $refundRef !== '' ? ' (' . esc( $refundRef ) . ')' : '' ?></small><?php endif; ?></td>
                <td class="order-total">₱<?= number_format( (float) $order['TOTAL_AMOUNT'], 0 ) ?></td>
                <td><span class="status status-<?= esc( $statusKey ) ?>"><?= esc( $statusRaw ) ?></span></td>
                <td><button type="button" class="btn btn-ghost btn-sm view-sale-btn">View</button></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <p id="daily-empty" class="empty-note" style="<?= empty( $completedOrders ) ? '' : 'display:none;' ?>">No delivered, cancelled or refunded orders yet. Once an order is marked Delivered, Cancelled or Refunded on the Orders page, it will show up here.</p>
    </div>
  </div>
</section>

<!-- Sale details modal (filled by js/admin/sales.js from each row's data-order JSON) -->
<div id="sale-modal-backdrop" class="admin-modal-backdrop" data-upload-base="<?= esc( base_url( 'uploads/' ) ) ?>" style="display:none;">
  <div class="admin-modal" role="dialog" aria-modal="true" aria-labelledby="sale-modal-title">
    <div class="admin-modal-head">
      <div>
        <div class="admin-modal-eyebrow">Sale Details</div>
        <h3 id="sale-modal-title"></h3>
      </div>
      <button type="button" class="admin-modal-close" id="sale-modal-close" aria-label="Close">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
      </button>
    </div>
    <div class="admin-modal-body">
      <div class="admin-modal-status-row">
        <span id="sale-modal-status" class="status"></span>
        <span class="admin-modal-placed" id="sale-modal-placed"></span>
      </div>

      <div class="admin-modal-section">
        <div class="admin-modal-label">Customer</div>
        <div class="admin-modal-text" id="sale-modal-customer"></div>
        <div class="admin-modal-subtext" id="sale-modal-contact"></div>
      </div>

      <div class="admin-modal-section">
        <div class="admin-modal-label" id="sale-modal-type"></div>
        <div class="admin-modal-text" id="sale-modal-address"></div>
      </div>

      <div class="admin-modal-section" id="sale-modal-payment-section">
        <div class="admin-modal-label">Payment</div>
        <div class="admin-modal-text" id="sale-modal-payment"></div>
        <div class="admin-modal-subtext" id="sale-modal-reference"></div>
      </div>

      <!-- Refund orders only: the customer's payment(s) and the refund the admin sent back. Filled by the script below. -->
      <div class="admin-modal-section" id="sale-modal-payments-section" style="display:none;">
        <div class="admin-modal-label">Payments</div>
        <div id="sale-modal-payments"></div>
      </div>

      <div class="admin-modal-section" id="sale-modal-note-section">
        <div class="admin-modal-label">Customer note</div>
        <div class="admin-modal-text" id="sale-modal-note"></div>
      </div>

      <div class="admin-modal-section">
        <div class="admin-modal-label">Items</div>
        <div class="om-items" id="sale-modal-items"></div>
        <div class="admin-modal-totals" id="sale-modal-totals"></div>
      </div>
    </div>
  </div>
</div>

<footer class="site-footer">
  <div class="container">
    <div class="footer-bottom">
      <span>© 2026 Kaye's Barbeque. All rights reserved.</span>
      <span>Smoked daily, served fresh.</span>
    </div>
  </div>
</footer>




<script src="<?= base_url('js/admin/notifications.js') ?>"></script>
<script>
// Refund orders: show the customer's payment and the refund payment in the details modal.
(function () {
  var backdrop = document.getElementById('sale-modal-backdrop');
  var body = document.getElementById('daily-table-body');
  var plain = document.getElementById('sale-modal-payment-section');
  var section = document.getElementById('sale-modal-payments-section');
  var box = document.getElementById('sale-modal-payments');
  if (!backdrop || !body || !plain || !section || !box) return;
  var base = (backdrop.dataset.uploadBase || '') + 'payments/';

  function el(tag, text, style) {
    var n = document.createElement(tag);
    if (text) n.textContent = text;
    if (style) n.style.cssText = style;
    return n;
  }

  // One payment: a heading, then its reference, date and screenshot.
  function block(title, lines, proof, first) {
    var wrap = el('div', '', first ? '' : 'margin-top:14px;padding-top:14px;border-top:1px solid var(--line);');
    wrap.appendChild(el('div', title, 'font-family:var(--font-mono);font-size:11px;letter-spacing:.06em;text-transform:uppercase;color:var(--ember);margin-bottom:4px;'));
    lines.forEach(function (t) { if (t) wrap.appendChild(el('div', t, 'font-size:14px;')); });
    if (proof) {
      var a = el('a', '', 'display:inline-block;margin-top:8px;');
      a.href = base + encodeURIComponent(proof); a.target = '_blank'; a.rel = 'noopener';
      var img = el('img', '', 'max-width:100%;max-height:200px;border-radius:8px;border:1px solid var(--line);');
      img.src = a.href; img.alt = 'Payment screenshot';
      a.appendChild(img); wrap.appendChild(a);
    }
    return wrap;
  }

  body.addEventListener('click', function (e) {
    var tr = e.target.closest('tr[data-order]');
    if (!tr) return;
    var d;
    try { d = JSON.parse(tr.getAttribute('data-order')); } catch (err) { return; }
    if (!d) return;

    var isRefund = String(d.status || '').toLowerCase() === 'refund';
    plain.style.display = isRefund ? 'none' : '';
    section.style.display = isRefund ? '' : 'none';
    box.textContent = '';
    if (!isRefund) return;

    box.appendChild(block('Customer payment',
      [d.payment || '', d.reference ? 'Reference: ' + d.reference : '', d.paymentDate ? 'Paid: ' + d.paymentDate : ''],
      d.customerProof, true));

    (d.extraPayments || []).forEach(function (p, i) {
      box.appendChild(block('Added payment' + (d.extraPayments.length > 1 ? ' ' + (i + 1) : ''),
        [p.reference ? 'Reference: ' + p.reference : '', p.date ? 'Paid: ' + p.date : ''], p.proof, false));
    });

    if (d.refund) {
      box.appendChild(block('Refund payment (sent to customer)',
        [d.refund.reference ? 'Reference: ' + d.refund.reference : '', d.refund.date ? 'Refunded: ' + d.refund.date : ''],
        d.refund.proof, false));
    } else {
      box.appendChild(el('div', 'No refund payment was recorded for this order.', 'margin-top:14px;font-size:13px;color:var(--ink-soft);'));
    }
  });
})();
</script>
<script src="<?= base_url('js/admin/sales.js') ?>"></script>

</body>
</html>