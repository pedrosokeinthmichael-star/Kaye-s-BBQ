<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\OrderModel;
use App\Models\OrderDetailsModel;
use App\Models\PaymentModel;
use App\Models\AddressModel;
use App\Models\BarangayModel;
use App\Models\BBQModel;
use App\Models\NotificationModel;

/**
 * OrderController
 * -----------------------------------------------------------------
 * Handles checkout from the Place Order modal (place_order_modal.php
 * / placeorder.js): validates the form, then turns the customer's
 * live cart into a placed order.
 *
 * The cart IS an `orders` row with ORDER_STATUS = 'Cart' whose
 * order_item rows are the cart lines. Placing the order therefore
 * doesn't create a second order or copy any lines: it fills in the
 * cart's `orders` row (type, note, fees, total), moves its status on
 * from 'Cart', locks in each line's UNIT_PRICE, and attaches the
 * payment and (for delivery) the delivery_address row to that same
 * ORDER_ID. The next time the customer adds something, a fresh
 * 'Cart' order is created (see OrderModel::getCartOrderId()).
 */
class OrderController extends BaseController {

    /**
     * POST /orders/place
     * multipart/form-data:
     *   - orderType       ('delivery' | 'pickup')
     *   - barangay        (BARANGAY_ID, required when delivery)
     *   - addressDetail   (required when delivery)
     *   - notes           (optional)
     *   - paymentMethod   ('cod' | 'gcash')
     *   - gcashReference  (required when paymentMethod = 'gcash')
     *   - gcashProof      (file, required when paymentMethod = 'gcash')
     *
     * Ignores any client-side item list and reads the cart straight
     * from the database, since that can't be tampered with client-side.
     * Stock deduction, the order update, the payment, the address and
     * the item prices all happen in ONE database transaction, so a
     * failure part-way leaves stock and the cart untouched.
     */
    public function placeOrder() {

        $userId = session()->get( 'user_id' );
        if ( ! $userId ) {
            return $this->response->setStatusCode( 401 )->setJSON( [
                'success' => false,
                'message' => 'Please log in to place an order.',
            ] );
        }

        $orderDetailsModel = new OrderDetailsModel();
        $cartItems = $orderDetailsModel->getCartForUser( (int) $userId );

        if ( empty( $cartItems ) ) {
            return $this->response->setStatusCode( 422 )->setJSON( [
                'success' => false,
                'message' => 'Your cart is empty.',
            ] );
        }

        // Minimum order: the cart subtotal (before delivery fee) must reach
        // MIN_ORDER_AMOUNT (app/Config/Constants.php). Checked before the
        // GCash screenshot is saved, so a rejected order leaves no file behind.
        $cartSubtotal = array_reduce( $cartItems, fn( $sum, $i ) => $sum + (float) $i['SUB_TOTAL'], 0 );
        if ( MIN_ORDER_AMOUNT > 0 && $cartSubtotal < MIN_ORDER_AMOUNT ) {
            return $this->response->setStatusCode( 422 )->setJSON( [
                'success' => false,
                'message' => 'The minimum order is ₱' . number_format( MIN_ORDER_AMOUNT, 0 )
                           . '. Your cart is ₱' . number_format( $cartSubtotal, 0 )
                           . ', so please add ₱' . number_format( MIN_ORDER_AMOUNT - $cartSubtotal, 0 ) . ' more.',
            ] );
        }

        // The cart's own `orders` row — every cart line shares it.
        $orderId = (int) $cartItems[0]['ORDER_ID'];

        $orderType  = $this->request->getPost( 'orderType' ) === 'pickup' ? 'pickup' : 'delivery';
        $isDelivery = $orderType === 'delivery';

        // Admin switches (Orders > Order Settings): refuse Delivery / GCash when turned off,
        // even if the checkout page was left open from before the switch was flipped.
        $checkoutError = ( new \App\Models\ShopSettingModel() )->checkoutError(
            $orderType,
            (string) $this->request->getPost( 'paymentMethod' )
        );
        if ( $checkoutError !== null ) {
            return $this->response->setStatusCode( 422 )->setJSON( [
                'success' => false,
                'message' => $checkoutError,
            ] );
        }

        $barangayId    = $isDelivery ? (int) $this->request->getPost( 'barangay' ) : null;
        $addressDetail = $isDelivery ? trim( (string) $this->request->getPost( 'addressDetail' ) ) : '';

        // Must be a real barangay AND still Active (inactive ones are
        // hidden at checkout, so a stale form can't sneak one through).
        $barangayRow   = null;
        $barangayValid = false;
        if ( $isDelivery && $barangayId ) {
            $barangayRow   = ( new BarangayModel() )->find( $barangayId );
            $barangayValid = $barangayRow && ( $barangayRow['BARANGAY_STATUS'] ?? '' ) === 'Active';
        }

        if ( $isDelivery && ( ! $barangayValid || $addressDetail === '' ) ) {
            return $this->response->setStatusCode( 422 )->setJSON( [
                'success' => false,
                'message' => 'Please select a barangay and enter your address for delivery.',
            ] );
        }

        $notes = trim( (string) $this->request->getPost( 'notes' ) );
        $notes = $notes === '' ? 'None' : $notes;
        $paymentMethod = $this->request->getPost( 'paymentMethod' );
        $isGcash       = $paymentMethod === 'gcash';

        $reference = null;
        if ( $isGcash ) {
            $reference = trim( (string) $this->request->getPost( 'gcashReference' ) );
            $proofFile = $this->request->getFile( 'gcashProof' );

            if ( $reference === '' || ! $proofFile || ! $proofFile->isValid() ) {
                return $this->response->setStatusCode( 422 )->setJSON( [
                    'success' => false,
                    'message' => 'A GCash reference number and proof of payment screenshot are required.',
                ] );
            }
        }

        // Save the proof screenshot (payment.PROOF_OF_PAYMENT is varchar(100)).
        $proofImage = null;
        if ( $isGcash && ( $img = $this->request->getFile( 'gcashProof' ) ) ) {
            if ( $img->isValid() && ! $img->hasMoved() ) {
                $proofImage = $img->getRandomName();
                $img->move( 'uploads/payments/', $proofImage );
            }
        }

        // Totals. Cart SUB_TOTALs use the CURRENT menu prices; those same
        // prices are written to order_item.UNIT_PRICE below.
        $subtotal    = array_reduce( $cartItems, fn( $sum, $i ) => $sum + (float) $i['SUB_TOTAL'], 0 );
        $deliveryFee = $isDelivery ? (float) ( $barangayRow['DELIVERY_FEE'] ?? 0 ) : 0;
        $totalAmount = $subtotal + $deliveryFee;

        // GCash orders start as 'Pending' (payment proof still has to be
        // verified); cash orders go straight to 'Preparing'.
        $initialStatus = $isGcash ? 'Pending' : 'Preparing';

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // ---- 1. Check stock and deduct it ----
            // The UPDATE only succeeds when STOCK >= the quantity ordered, so
            // two customers checking out at once can't both take the last items.
            $shortItem = null;

            foreach ( $cartItems as $item ) {
                $qty = (int) $item['QUANTITY'];

                $db->table( 'menu_item' )
                   ->set( 'STOCK', 'STOCK - ' . $qty, false )
                   ->where( 'MENU_ID', (int) $item['MENU_ID'] )
                   ->where( 'STOCK >=', $qty )
                   ->update();

                if ( $db->affectedRows() < 1 ) {
                    $shortItem = $item;
                    break;
                }
            }

            if ( $shortItem !== null ) {
                // Rolls back any stock already taken for earlier lines.
                $db->transRollback();

                $current = ( new BBQModel() )->find( (int) $shortItem['MENU_ID'] );
                $left    = (int) ( $current['STOCK'] ?? 0 );

                return $this->response->setStatusCode( 422 )->setJSON( [
                    'success' => false,
                    'message' => $left > 0
                        ? "Sorry, only {$left} of {$shortItem['NAME']} left in stock. Please lower the quantity."
                        : "Sorry, {$shortItem['NAME']} is out of stock. Please remove it from your cart.",
                ] );
            }

            // ---- 2. Turn the cart order into a placed order ----
            ( new OrderModel() )->update( $orderId, [
                'ORDER_TYPE'   => $orderType,
                'NOTE'         => $notes,
                'ORDER_DATE'   => date( 'Y-m-d H:i:s' ),
                'TOTAL_AMOUNT' => $totalAmount,
                'ORDER_STATUS' => $initialStatus,
            ] );

            // ---- 3. Lock in each line's price ----
            foreach ( $cartItems as $item ) {
                $orderDetailsModel->update( $item['ORDER_ITEM_ID'], [
                    'UNIT_PRICE' => $item['PRICE'],
                ] );
            }

            // ---- 4. Record the payment (payment.ORDER_ID is NOT NULL, so it
            //         can only be inserted now that the order exists) ----
            ( new PaymentModel() )->insert( [
                'ORDER_ID'         => $orderId,
                'PAYMENT_METHOD'   => $isGcash ? 'GCash' : 'Cash',
                'PAYMENT_DATE'     => date( 'Y-m-d H:i:s' ),
                'REFERENCE_NUMBER' => $isGcash ? $reference : 'None',
                'PROOF_OF_PAYMENT' => $isGcash ? $proofImage : 'None',
            ] );

            // ---- 5. Delivery address (pickup orders get none) ----
            if ( $isDelivery ) {
                ( new AddressModel() )->insert( [
                    'ORDER_ID'       => $orderId,
                    'BARANGAY_ID'    => $barangayId,
                    'ADDRESS_DETAIL' => $addressDetail,
                ] );
            }

            if ( ! $db->transStatus() ) {
                $db->transRollback();
                throw new \RuntimeException( 'Checkout transaction failed.' );
            }

            $db->transCommit();

        } catch ( \Throwable $e ) {
            $db->transRollback();
            log_message( 'error', 'placeOrder failed: ' . $e->getMessage() );

            return $this->response->setStatusCode( 500 )->setJSON( [
                'success' => false,
                'message' => 'Something went wrong placing your order. Please try again.',
            ] );
        }

        // Let the admin know (shows on the admin notification bell).
        $this->notifyAdmin(
            'order',
            'New order #' . $orderId,
            $this->customerName() . ' placed a ' . $orderType . ' order for ₱' . number_format( $totalAmount, 0 )
                . ' (' . ( $isGcash ? 'GCash' : 'Cash' ) . ').'
        );

        return $this->response->setJSON( [
            'success'  => true,
            'message'  => 'Your order has been placed!',
            'order_id' => $orderId,
        ] );
    }

    /**
     * Which ORDER_STATUS values an order is allowed to move to next,
     * keyed by its current status. Mirrors the buttons orders.php /
     * orders.js actually show for each status (Pending -> Preparing,
     * Preparing -> Delivering, Delivering -> Delivered) plus Cancel,
     * which is offered from any of those three. Delivered and
     * Cancelled are terminal. 'Cart' orders aren't listed, so an
     * unplaced cart can never be moved by the admin.
     *
     * Enforced server-side (not just by which buttons the UI shows)
     * so a stale tab or a replayed request can't skip a step or
     * revive an order that's already Delivered/Cancelled.
     */
    private static array $allowedStatusTransitions = [
        'Pending'    => [ 'Preparing', 'Cancelled' ],
        'Preparing'  => [ 'Delivering', 'Cancelled' ],
        'Delivering' => [ 'Delivered', 'Cancelled' ],
    ];

    /**
     * POST /admin/orders/(:num)/status
     * application/x-www-form-urlencoded:
     *   - status  ('Preparing' | 'Delivering' | 'Delivered' | 'Cancelled')
     *
     * Persists the new ORDER_STATUS to the `orders` row. Once an order
     * is Delivered or Cancelled it automatically appears on the admin
     * Sales page, which reads those orders directly from `orders`
     * (see OrderModel::getCompletedOrdersForAdmin()). No separate
     * sales table is involved.
     */
    public function updateStatus( $id ) {

        if ( session()->get( 'role' ) !== 'admin' ) {
            return $this->response->setStatusCode( 403 )->setJSON( [
                'success' => false,
                'message' => 'You must be logged in as an admin to do that.',
            ] );
        }

        $newStatus = trim( (string) $this->request->getPost( 'status' ) );

        $orderModel = new OrderModel();
        $order = $orderModel->find( $id );

        if ( ! $order ) {
            return $this->response->setStatusCode( 404 )->setJSON( [
                'success' => false,
                'message' => 'Order not found.',
            ] );
        }

        $currentStatus = $order['ORDER_STATUS'];
        $allowedNext   = self::$allowedStatusTransitions[ $currentStatus ] ?? [];

        if ( ! in_array( $newStatus, $allowedNext, true ) ) {
            return $this->response->setStatusCode( 422 )->setJSON( [
                'success' => false,
                'message' => "Order #{$id} can't move from {$currentStatus} to {$newStatus}.",
            ] );
        }

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // Conditional on the status we just read, so a stale tab or a
            // double-click can't apply the same change (or restore stock) twice.
            $updated = $db->table( 'orders' )
               ->where( 'ORDER_ID', (int) $id )
               ->where( 'ORDER_STATUS', $currentStatus )
               ->update( [ 'ORDER_STATUS' => $newStatus ] );

            // A failed query (e.g. a leftover trigger pointing at a dropped
            // table) also leaves affectedRows() at 0, so check for that first
            // instead of mislabelling it as "changed by someone else".
            if ( $updated === false ) {
                $dbError = $db->error();
                throw new \RuntimeException( 'orders update failed: ' . ( $dbError['message'] ?? 'unknown database error' ) );
            }

            if ( $db->affectedRows() < 1 ) {
                $db->transRollback();
                return $this->response->setStatusCode( 409 )->setJSON( [
                    'success' => false,
                    'message' => "Order #{$id} was just changed by someone else. Refresh and try again.",
                ] );
            }

            // Cancelled orders give their stock back.
            if ( $newStatus === 'Cancelled' ) {
                $this->restoreStock( $db, (int) $id );
            }

            if ( ! $db->transStatus() ) {
                throw new \RuntimeException( 'Status update failed.' );
            }

            $db->transCommit();

        } catch ( \Throwable $e ) {
            $db->transRollback();
            log_message( 'error', 'updateStatus failed: ' . $e->getMessage() );

            return $this->response->setStatusCode( 500 )->setJSON( [
                'success' => false,
                'message' => 'Could not update the order. Please try again.',
            ] );
        }

        // Tell the customer their order moved along (customer notification bell).
        $this->notifyCustomerOfStatus( $order, $currentStatus, $newStatus );

        return $this->response->setJSON( [
            'success'  => true,
            'order_id' => (int) $id,
            'status'   => $newStatus,
        ] );
    }

    /**
     * POST /orders/add-payment
     * multipart/form-data: order_id, gcashReference, gcashProof (file)
     *
     * The customer adds another GCash payment to a Pending order, e.g. after
     * the admin found their first payment short. It is saved as a NEW
     * `payment` row for the same order (the first row stays the checkout
     * payment), so an order can have many payments. The order stays Pending
     * until the admin verifies it. Needs payment.ORDER_ID to be a plain
     * (non-unique) index.
     */
    public function addPayment() {

        $userId = session()->get( 'user_id' );
        if ( ! $userId ) {
            return $this->response->setStatusCode( 401 )->setJSON( [
                'success' => false,
                'message' => 'Please log in to add a payment.',
            ] );
        }

        $orderId = (int) $this->request->getPost( 'order_id' );
        $order   = $orderId > 0 ? ( new OrderModel() )->find( $orderId ) : null;

        if ( ! $order || (int) $order['USER_ID'] !== (int) $userId ) {
            return $this->response->setStatusCode( 404 )->setJSON( [
                'success' => false,
                'message' => 'Order not found.',
            ] );
        }

        $paymentModel = new PaymentModel();
        $first = $paymentModel->getPaymentForOrder( $orderId );

        if ( $order['ORDER_STATUS'] !== 'Pending' || ! $first || $first['PAYMENT_METHOD'] !== 'GCash' ) {
            return $this->response->setStatusCode( 422 )->setJSON( [
                'success' => false,
                'message' => 'You can only add a payment to a Pending GCash order.',
            ] );
        }

        // A payment can only be added once the admin has requested one.
        if ( ! cache( 'payment_request_' . $orderId ) ) {
            return $this->response->setStatusCode( 422 )->setJSON( [
                'success' => false,
                'message' => 'No payment has been requested for this order.',
            ] );
        }

        $reference = trim( (string) $this->request->getPost( 'gcashReference' ) );
        $img       = $this->request->getFile( 'gcashProof' );

        if ( $reference === '' || ! $img || ! $img->isValid() || strpos( (string) $img->getMimeType(), 'image/' ) !== 0 ) {
            return $this->response->setStatusCode( 422 )->setJSON( [
                'success' => false,
                'message' => 'A GCash reference number and a screenshot image are required.',
            ] );
        }

        $proofImage = $img->getRandomName();
        $img->move( 'uploads/payments/', $proofImage );

        // A NEW payment row for the SAME order (same ORDER_ID), so one order can
        // have as many payments as needed. No new columns: it uses the same
        // payment columns as the checkout payment.
        $dbError = '';
        try {
            $saved = $paymentModel->insert( [
                'ORDER_ID'         => $orderId,
                'PAYMENT_METHOD'   => 'GCash',
                'PAYMENT_DATE'     => date( 'Y-m-d H:i:s' ),
                'REFERENCE_NUMBER' => $reference,
                'PROOF_OF_PAYMENT' => $proofImage,
            ] );
        } catch ( \Throwable $e ) {
            $saved   = false;
            $dbError = $e->getMessage();
            log_message( 'error', 'addPayment failed: ' . $dbError );
        }

        if ( $saved === false ) {
            @unlink( FCPATH . 'uploads/payments/' . $proofImage ); // no orphaned screenshot

            return $this->response->setStatusCode( 500 )->setJSON( [
                'success' => false,
                // TEMPORARY: shows the real DB error. Remove ". $dbError" once it works.
                'message' => 'Could not save your payment. ' . $dbError,
            ] );
        }

        // The customer answered the request, so clear it. If the order is still
        // short after verifying, the admin can request again.
        cache()->delete( 'payment_request_' . $orderId );

        $this->notifyAdmin(
            'order',
            'Extra payment for order #' . $orderId,
            $this->customerName() . ' sent another GCash payment (ref ' . $reference . ').'
        );

        return $this->response->setJSON( [
            'success' => true,
            'message' => 'Payment sent. We will verify it shortly.',
        ] );
    }

    /**
     * POST /admin/orders/{id}/request-payment   (admin)
     * multipart/form-data: amount
     *
     * The admin asks the customer to pay a balance on a Pending GCash order.
     * The request (the amount) is kept in the CodeIgniter cache under
     * payment_request_{id}, so no database column is needed. While it exists,
     * "Add Payment" shows on the customer's tracking card; addPayment() clears
     * it when the customer sends the payment.
     */
    public function requestPayment( $id ) {

        $id     = (int) $id;
        $amount = round( (float) $this->request->getPost( 'amount' ), 2 );
        $order  = $id > 0 ? ( new OrderModel() )->find( $id ) : null;

        if ( ! $order ) {
            return $this->response->setStatusCode( 404 )->setJSON( [
                'success' => false, 'message' => 'Order not found.', 'csrf' => csrf_hash(),
            ] );
        }

        $first = ( new PaymentModel() )->getPaymentForOrder( $id );

        if ( $order['ORDER_STATUS'] !== 'Pending' || ! $first || $first['PAYMENT_METHOD'] !== 'GCash' || $amount <= 0 ) {
            return $this->response->setStatusCode( 422 )->setJSON( [
                'success' => false,
                'message' => 'A payment can only be requested on a Pending GCash order, for an amount above 0.',
                'csrf'    => csrf_hash(),
            ] );
        }

        // Kept for 60 days (long enough for any pending order).
        cache()->save( 'payment_request_' . $id, $amount, 60 * 60 * 24 * 60 );

        $this->notifyCustomer(
            (int) $order['USER_ID'],
            'payment',
            'Payment requested for order #KB-' . $id,
            'Please send the remaining ₱' . number_format( $amount, 2 ) . ' via GCash, then tap "Add Payment" on your order.',
            'orders'
        );

        return $this->response->setJSON( [
            'success' => true,
            'amount'  => $amount,
            'csrf'    => csrf_hash(),
        ] );
    }

    /**
     * POST /admin/orders/{id}/refund   (multipart/form-data)
     *   - type       'full' | 'overpayment'
     *   - amount     the amount sent back to the customer
     *   - reference  the admin's GCash reference number for the refund
     *   - proof      screenshot of the refund (image)
     *
     * Called by verify_payment.js. Works like the customer's addPayment(), but the
     * admin is the one submitting: a NEW `payment` row for the same order (GCash
     * reference + screenshot, PAYMENT_METHOD 'Refund'), via PaymentModel::saveRefund(), and
     * the customer is told about it through their notification bell.
     *   - 'full'        also sets the order's status to 'Refund' and puts its
     *                   stock back, all in one transaction.
     *   - 'overpayment' only adds the refund payment; the admin then verifies the order.
     */
    public function saveRefund( $id ) {

        $id    = (int) $id;
        $order = $id > 0 ? ( new OrderModel() )->find( $id ) : null;

        if ( ! $order ) {
            return $this->response->setStatusCode( 404 )->setJSON( [
                'success' => false, 'message' => 'Order not found.', 'csrf' => csrf_hash(),
            ] );
        }

        $type      = $this->request->getPost( 'type' );
        $amount    = round( (float) $this->request->getPost( 'amount' ), 2 );
        $reference = trim( (string) $this->request->getPost( 'reference' ) );
        $proof     = $this->request->getFile( 'proof' );

        if ( ! in_array( $type, [ 'full', 'overpayment' ], true ) || $amount <= 0 ) {
            return $this->response->setStatusCode( 422 )->setJSON( [
                'success' => false, 'message' => 'Invalid refund type or amount.', 'csrf' => csrf_hash(),
            ] );
        }

        if ( $reference === '' || ! $proof || ! $proof->isValid() ) {
            return $this->response->setStatusCode( 422 )->setJSON( [
                'success' => false,
                'message' => 'A refund reference number and screenshot are required.',
                'csrf'    => csrf_hash(),
            ] );
        }

        if ( strpos( (string) $proof->getMimeType(), 'image/' ) !== 0 ) {
            return $this->response->setStatusCode( 422 )->setJSON( [
                'success' => false, 'message' => 'The proof must be an image file.', 'csrf' => csrf_hash(),
            ] );
        }

        // A full refund takes the order out of the normal flow, so it is only
        // allowed on an order that is still in progress.
        $fromStatus = $order['ORDER_STATUS'];
        if ( $type === 'full' && ! in_array( $fromStatus, [ 'Pending', 'Preparing', 'Delivering' ], true ) ) {
            return $this->response->setStatusCode( 422 )->setJSON( [
                'success' => false,
                'message' => "Order #{$id} is {$fromStatus} and can no longer be refunded.",
                'csrf'    => csrf_hash(),
            ] );
        }

        // Same rule as the customer's addPayment(): this is for a GCash order.
        $paymentModel = new PaymentModel();
        $first        = $paymentModel->getPaymentForOrder( $id );
        if ( ! $first || $first['PAYMENT_METHOD'] !== 'GCash' ) {
            return $this->response->setStatusCode( 422 )->setJSON( [
                'success' => false,
                'message' => 'A refund payment can only be added to a GCash order.',
                'csrf'    => csrf_hash(),
            ] );
        }

        // Saved with the other payment screenshots, like the customer's addPayment().
        $proofName = $proof->getRandomName();
        $proof->move( 'uploads/payments/', $proofName );

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // A NEW payment row for the SAME order, like the customer's addPayment().
            if ( ! $paymentModel->saveRefund( $id, [
                'reference' => $reference,
                'proof'     => $proofName,
            ] ) ) {
                throw new \RuntimeException( 'Could not add the refund payment.' );
            }

            // Full refund: the order's status becomes 'Refund' and its stock goes back.
            // Conditional on the status we just read, so a stale tab or a
            // double-click can't apply it twice.
            if ( $type === 'full' ) {
                $updated = $db->table( 'orders' )
                    ->where( 'ORDER_ID', $id )
                    ->where( 'ORDER_STATUS', $fromStatus )
                    ->update( [ 'ORDER_STATUS' => 'Refund' ] );

                if ( $updated === false || $db->affectedRows() < 1 ) {
                    $db->transRollback();
                    @unlink( FCPATH . 'uploads/payments/' . $proofName );
                    return $this->response->setStatusCode( 409 )->setJSON( [
                        'success' => false,
                        'message' => "Order #{$id} was just changed by someone else. Refresh and try again.",
                        'csrf'    => csrf_hash(),
                    ] );
                }

                $this->restoreStock( $db, $id );
            }

            if ( ! $db->transStatus() ) {
                throw new \RuntimeException( 'Refund save failed.' );
            }

            $db->transCommit();

        } catch ( \Throwable $e ) {
            $db->transRollback();
            @unlink( FCPATH . 'uploads/payments/' . $proofName );
            log_message( 'error', 'saveRefund failed: ' . $e->getMessage() );

            return $this->response->setStatusCode( 500 )->setJSON( [
                'success' => false,
                'message' => 'Could not save the refund. Please try again.',
                'csrf'    => csrf_hash(),
            ] );
        }

        // Tell the customer about the refund (customer notification bell).
        $money = '₱' . number_format( $amount, 2 );
        if ( $type === 'full' ) {
            $this->notifyCustomer(
                (int) $order['USER_ID'],
                'payment',
                'Order #KB-' . $id . ' refunded',
                'Your payment of ' . $money . ' was sent back to your GCash (ref ' . $reference . '). Sorry for the trouble, and thank you!',
                'history'
            );
        } else {
            $this->notifyCustomer(
                (int) $order['USER_ID'],
                'payment',
                'Refund for order #KB-' . $id,
                'You paid more than your order total, so ' . $money . ' was sent back to your GCash (ref ' . $reference . ').',
                'orders'
            );
        }

        return $this->response->setJSON( [
            'success' => true,
            'status'  => $type === 'full' ? 'Refund' : $fromStatus,
            'csrf'    => csrf_hash(),
        ] );
    }

    /**
     * POST /orders/cancel   (JSON: { order_id })
     * A customer cancels their OWN order while it is still Pending or
     * Preparing. The stock taken at checkout is put back, all inside
     * one transaction.
     */
    public function cancel() {

        $userId = session()->get( 'user_id' );
        if ( ! $userId ) {
            return $this->response->setStatusCode( 401 )->setJSON( [
                'success' => false,
                'message' => 'Please log in to cancel an order.',
            ] );
        }

        $input   = $this->request->getJSON( true ) ?: $this->request->getPost();
        $orderId = (int) ( $input['order_id'] ?? 0 );

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // FOR UPDATE locks the row so a double-click (or the admin
            // acting at the same moment) can't restore stock twice.
            $order = $db->query(
                'SELECT ORDER_ID, ORDER_STATUS FROM orders WHERE ORDER_ID = ? AND USER_ID = ? FOR UPDATE',
                [ $orderId, (int) $userId ]
            )->getRowArray();

            if ( ! $order ) {
                $db->transRollback();
                return $this->response->setStatusCode( 404 )->setJSON( [
                    'success' => false,
                    'message' => 'Order not found.',
                ] );
            }

            if ( ! in_array( $order['ORDER_STATUS'], [ 'Pending', 'Preparing' ], true ) ) {
                $db->transRollback();
                return $this->response->setStatusCode( 422 )->setJSON( [
                    'success' => false,
                    'message' => "This order is already {$order['ORDER_STATUS']} and can no longer be cancelled.",
                ] );
            }

            $this->restoreStock( $db, $orderId );

            $db->table( 'orders' )
               ->where( 'ORDER_ID', $orderId )
               ->update( [ 'ORDER_STATUS' => 'Cancelled' ] );

            if ( ! $db->transStatus() ) {
                throw new \RuntimeException( 'Cancel transaction failed.' );
            }

            $db->transCommit();

        } catch ( \Throwable $e ) {
            $db->transRollback();
            log_message( 'error', 'cancel failed: ' . $e->getMessage() );

            return $this->response->setStatusCode( 500 )->setJSON( [
                'success' => false,
                'message' => 'Something went wrong cancelling your order. Please try again.',
            ] );
        }

        $this->notifyAdmin(
            'system',
            'Order #' . $orderId . ' cancelled',
            $this->customerName() . ' cancelled their order.'
        );

        return $this->response->setJSON( [ 'success' => true, 'order_id' => $orderId ] );
    }

    /** Pushes a notification to ONE customer's bell. Never lets a failure break the order. */
    private function notifyCustomer( int $userId, string $type, string $title, string $message, ?string $href = 'orders' ): void
    {
        try {
            ( new NotificationModel() )->notifyCustomer( $userId, $type, $title, $message, $href );
        } catch ( \Throwable $e ) {
            log_message( 'error', 'notifyCustomer failed: ' . $e->getMessage() );
        }
    }

    /** Builds and sends the customer message for an admin status change. */
    private function notifyCustomerOfStatus( array $order, string $from, string $to ): void
    {
        $id       = (int) $order['ORDER_ID'];
        $isPickup = strtolower( (string) ( $order['ORDER_TYPE'] ?? '' ) ) === 'pickup';

        switch ( $to ) {
            case 'Preparing':
                $title = 'Order #KB-' . $id . ' is being prepared';
                $msg   = $from === 'Pending'
                    ? 'Your payment was verified and we are now preparing your order.'
                    : 'We are now preparing your order.';
                $href  = 'orders';
                break;

            case 'Delivering':
                $title = $isPickup ? 'Order #KB-' . $id . ' is ready for pickup' : 'Order #KB-' . $id . ' is out for delivery';
                $msg   = $isPickup
                    ? 'Your order is ready. Come by the Balongis, Balulang branch to pick it up.'
                    : 'Your order is on its way. Please keep your phone nearby.';
                $href  = 'orders';
                break;

            case 'Delivered':
                $title = $isPickup ? 'Order #KB-' . $id . ' completed' : 'Order #KB-' . $id . ' delivered';
                $msg   = 'Thank you for ordering from Kaye\'s Barbeque. Enjoy your meal!';
                $href  = 'history';
                break;

            case 'Cancelled':
                $title = 'Order #KB-' . $id . ' was cancelled';
                $msg   = 'Sorry, the shop had to cancel your order. Please contact us if you have questions.';
                $href  = 'history';
                break;

            default:
                return;
        }

        $this->notifyCustomer( (int) $order['USER_ID'], 'order', $title, $msg, $href );
    }

    /** Pushes a notification to the admin bell. Never lets a failure break the order. */
    private function notifyAdmin( string $type, string $title, string $message ): void
    {
        try {
            ( new NotificationModel() )->notifyAdmin( $type, $title, $message, 'orders' );
        } catch ( \Throwable $e ) {
            log_message( 'error', 'notifyAdmin failed: ' . $e->getMessage() );
        }
    }

    private function customerName(): string
    {
        $name = trim( (string) session()->get( 'first_name' ) . ' ' . (string) session()->get( 'last_name' ) );
        return $name !== '' ? $name : 'A customer';
    }

    /** Adds every line's QUANTITY back onto menu_item.STOCK for one order. */
    private function restoreStock( $db, int $orderId ): void
    {
        $db->query(
            'UPDATE menu_item mi
             JOIN order_item oi ON oi.MENU_ID = mi.MENU_ID
             SET mi.STOCK = mi.STOCK + oi.QUANTITY
             WHERE oi.ORDER_ID = ?',
            [ $orderId ]
        );
    }

}