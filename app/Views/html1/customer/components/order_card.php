<?php
/**
 * components/order_card.php — Kaye's Barbeque
 * -----------------------------------------------------------------
 * Renders ONE placed order as a card. Drop this into a foreach the
 * same way menu_card.php is used on barbeques.php / index.php.
 *
 * Expects a $order array in scope. Required (drives the card face):
 *
 *   ORDER_ID       int      e.g. 1042
 *   ORDER_DATE     string   any strtotime()-parsable date
 *   ORDER_TYPE     string   'Pickup' | 'Delivery'
 *   TOTAL_AMOUNT   float
 *   ORDER_STATUS   string   'Preparing' | 'Delivering' | 'Delivered' | 'Cancelled'
 *   ITEMS_SUMMARY  string   e.g. "6x Classic Pork BBQ, 2x Isaw Baboy"
 *
 * Optional — only used by the detail modals, omit any of these and
 * that section/row just doesn't render:
 *
 *   ITEMS            array   each row: NAME, IMAGE, PRICE, QUANTITY, SUB_TOTAL
 *   SUBTOTAL         float   defaults to TOTAL_AMOUNT if omitted
 *   DELIVERY_FEE     float   defaults to 0 if omitted
 *   CUSTOMER_NAME    string
 *   CUSTOMER_EMAIL   string
 *   CUSTOMER_PHONE   string
 *   DELIVERY_ADDRESS string  pre-computed (barangay + address detail),
 *                            already blank for pickup orders — leave
 *                            that logic in OrderModel, not here
 *   NOTE / NOTES     string  order notes left at checkout
 *   PAYMENT_METHOD   string  'Cash' | 'GCash'
 *   PAYMENT_REFERENCE string GCash reference number, if applicable
 *   PROOF_OF_PAYMENT string  GCash proof-of-payment filename (relative
 *                            to uploads/payments/), if applicable
 *   PAYMENT_REQUEST_AMOUNT float  balance the admin asked for (Pending GCash)
 *   EXTRA_PAYMENTS   array   extra payments already sent (REFERENCE_NUMBER,
 *                            PROOF_OF_PAYMENT, PAYMENT_DATE)
 *   REFUND_PAYMENT   array   the admin's refund payment row (REFERENCE_NUMBER,
 *                            PROOF_OF_PAYMENT, PAYMENT_DATE), or null
 *
 * All of the above is packed into a single data-order="<json>"
 * attribute on the card. The detail modals parse it:
 *   - orders.php  -> components/order_tracker_modal.php (and cancel_order.js)
 *   - history.php -> components/order_history_modal.php
 *
 * The card also carries a data-filter attribute (lowercased status)
 * for the chip-filter tabs, and is clickable (role="button",
 * tabindex) — the page's detail modal opens on click or Enter/Space.
 *
 * $show_cancel (set by orders.php) turns on the Cancel / Add Payment
 * buttons for tracking cards.
 */

$statusRaw = $order['ORDER_STATUS'] ?? '';
$statusKey = strtolower( $statusRaw );

// Empty values and the literal text 'None' both mean "nothing entered".
$noneToBlank = fn( $v ) => ( $v === null || $v === 'None' ) ? '' : $v;

// Pending GCash orders can have extra payments added by the customer (the
// "Add Payment" button), e.g. after the admin finds the first one short.
$isGcashOrder = strtolower( (string) ( $order['PAYMENT_METHOD'] ?? '' ) ) === 'gcash';
// The button only appears once the admin has requested a payment
// (Home::orders() fills PAYMENT_REQUEST_AMOUNT from the saved request).
$requestAmount = (float) ( $order['PAYMENT_REQUEST_AMOUNT'] ?? 0 );
$canAddPayment = $statusKey === 'pending' && $isGcashOrder && $requestAmount > 0;
$extraCount    = count( $order['EXTRA_PAYMENTS'] ?? [] );

// The refund the admin sent back (Home::history() fills REFUND_PAYMENT).
$refundRow = $order['REFUND_PAYMENT'] ?? null;

$orderPayload = [
  'orderId'          => (int) ( $order['ORDER_ID'] ?? 0 ),
  'date'             => date( 'M j, Y', strtotime( $order['ORDER_DATE'] ?? 'now' ) ),
  'type'             => $order['ORDER_TYPE'] ?? '',
  'status'           => $statusRaw,
  'statusKey'        => $statusKey,
  'items'            => $order['ITEMS'] ?? [],
  'itemsSummary'     => $order['ITEMS_SUMMARY'] ?? '',
  'subtotal'         => (float) ( $order['SUBTOTAL'] ?? $order['TOTAL_AMOUNT'] ?? 0 ),
  'deliveryFee'      => (float) ( $order['DELIVERY_FEE'] ?? 0 ),
  'total'            => (float) ( $order['TOTAL_AMOUNT'] ?? 0 ),
  'customerName'     => $order['CUSTOMER_NAME'] ?? '',
  'customerEmail'    => $order['CUSTOMER_EMAIL'] ?? '',
  'customerPhone'    => $order['CUSTOMER_PHONE'] ?? '',
  // DELIVERY_ADDRESS is already computed (barangay + address detail,
  // and already empty for pickup orders) by OrderModel — no need to
  // re-check ORDER_TYPE here.
  'deliveryAddress'  => $order['DELIVERY_ADDRESS'] ?? '',
  'notes'            => $order['NOTES'] ?? ( $order['NOTE'] ?? '' ),
  'paymentMethod'    => $order['PAYMENT_METHOD'] ?? '',
  'paymentReference' => $order['PAYMENT_REFERENCE'] ?? '',
  'paymentProof'     => $order['PROOF_OF_PAYMENT'] ?? '',
  'refund'           => $refundRow ? [
      'reference' => $noneToBlank( $refundRow['REFERENCE_NUMBER'] ?? '' ),
      'proof'     => $noneToBlank( $refundRow['PROOF_OF_PAYMENT'] ?? '' ),
      'date'      => ! empty( $refundRow['PAYMENT_DATE'] ) ? date( 'M j, Y g:i A', strtotime( $refundRow['PAYMENT_DATE'] ) ) : '',
  ] : null,
  'extraPaymentList' => array_map( fn( $p ) => [
      'reference' => $noneToBlank( $p['REFERENCE_NUMBER'] ?? '' ),
      'proof'     => $noneToBlank( $p['PROOF_OF_PAYMENT'] ?? '' ),
      'date'      => ! empty( $p['PAYMENT_DATE'] ) ? date( 'M j, g:i A', strtotime( $p['PAYMENT_DATE'] ) ) : '',
  ], $order['EXTRA_PAYMENTS'] ?? [] ),
];
?>
<div
  class="order-card"
  data-filter="<?= esc( $statusKey ) ?>"
  data-order="<?= esc( json_encode( $orderPayload ) ) ?>"
  role="button"
  tabindex="0"
  aria-haspopup="dialog"
>
  <div class="order-card-head">
    <div class="order-card-top">
      <div class="order-card-id">#KB-<?= (int) ( $order['ORDER_ID'] ?? 0 ) ?></div>
      <div class="order-card-date">
        <?= esc( date( 'M j, Y', strtotime( $order['ORDER_DATE'] ?? 'now' ) ) ) ?>
      </div>
    </div>
    <span class="status status-<?= esc( $statusKey ) ?>"><?= esc( $statusRaw ) ?></span>
  </div>

  <p class="order-card-items"><?= esc( $order['ITEMS_SUMMARY'] ?? '' ) ?></p>

  <?php if ( ! empty( $show_cancel ) && $canAddPayment ) : ?>
    <p class="order-card-payment-note">Payment requested: ₱<?= number_format( $requestAmount, 2 ) ?> · tap Add Payment to send it</p>
  <?php endif; ?>

  <?php if ( $statusKey === 'pending' && $extraCount > 0 ) : ?>
    <p class="order-card-payment-note is-sent"><?= $extraCount ?> extra payment<?= $extraCount > 1 ? 's' : '' ?> sent · waiting for verification</p>
  <?php endif; ?>

  <div class="order-card-bottom">
    <div class="order-card-meta">
      <span class="order-card-type"><?= esc( $order['ORDER_TYPE'] ?? '' ) ?></span>
      <span class="order-card-total">₱<?= number_format( (float) ( $order['TOTAL_AMOUNT'] ?? 0 ), 0 ) ?></span>
    </div>
    <div class="order-card-actions">
    <?php if ( ! empty( $show_cancel ) && $canAddPayment ) : ?>
      <button type="button" class="order-card-addpay" data-order-id="<?= (int) ( $order['ORDER_ID'] ?? 0 ) ?>">Add Payment</button>
    <?php endif; ?>
    <?php
      // Cancel button: only on the Order page's tracking list ($show_cancel
      // is set in orders.php) and only while the order is Pending or
      // Preparing. Remove 'preparing' from the list to allow Pending only.
      if ( ! empty( $show_cancel ) && in_array( $statusKey, [ 'pending', 'preparing' ], true ) ) :
    ?>
      <button type="button" class="order-card-cancel" data-order-id="<?= (int) ( $order['ORDER_ID'] ?? 0 ) ?>">Cancel</button>
    <?php endif; ?>
    </div>
  </div>
</div>