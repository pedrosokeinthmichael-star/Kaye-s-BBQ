<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\OrderModel;
use App\Models\PaymentModel;

/**
 * PaymentController
 * -----------------------------------------------------------------
 * Standalone endpoint for recording a payment against an existing
 * order: Cash or GCash, and for GCash a reference number + proof
 * screenshot.
 *
 * `payment` has no USER_ID; it belongs to an order via ORDER_ID. An
 * order may have MANY payments (the checkout payment plus any extra
 * ones added later through OrderController::addPayment()). This
 * endpoint only records the FIRST payment, so it refuses if the order
 * already has one. orderId must be one of the logged-in customer's own
 * orders. Normal checkout doesn't go through this route:
 * OrderController::placeOrder() records the payment itself.
 */
class PaymentController extends BaseController {

    /**
     * POST /payment/store
     * multipart/form-data:
     *   - orderId        (required int, an order that belongs to the customer)
     *   - paymentMethod  ('cod' | 'gcash')
     *   - gcashReference (string, only when paymentMethod = 'gcash')
     *   - gcashProof     (file,   only when paymentMethod = 'gcash')
     */
    public function store() {

        $userId = session()->get( 'user_id' );
        if ( ! $userId ) {
            return $this->response->setStatusCode( 401 )->setJSON( [
                'success' => false,
                'message' => 'Please log in to complete your payment.',
            ] );
        }

        $orderId = (int) $this->request->getPost( 'orderId' );
        $order   = $orderId > 0 ? ( new OrderModel() )->find( $orderId ) : null;

        if ( ! $order || (int) $order['USER_ID'] !== (int) $userId ) {
            return $this->response->setStatusCode( 404 )->setJSON( [
                'success' => false,
                'message' => 'Order not found.',
            ] );
        }

        $paymentModel = new PaymentModel();

        if ( $paymentModel->getPaymentForOrder( $orderId ) ) {
            return $this->response->setStatusCode( 409 )->setJSON( [
                'success' => false,
                'message' => 'A payment is already recorded for this order.',
            ] );
        }

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

        $proofImage = null;
        if ( $isGcash && ( $img = $this->request->getFile( 'gcashProof' ) ) ) {
            if ( $img->isValid() && ! $img->hasMoved() ) {
                $proofImage = $img->getRandomName();
                $img->move( 'uploads/payments/', $proofImage );
            }
        }

        // Cash payments store the literal 'None' for the GCash-only
        // fields, the same as OrderController::placeOrder(), which is
        // what OrderModel's 'None' normalising expects.
        $paymentId = $paymentModel->insert( [
            'ORDER_ID'         => $orderId,
            'PAYMENT_METHOD'   => $isGcash ? 'GCash' : 'Cash',
            'PAYMENT_DATE'     => date( 'Y-m-d H:i:s' ),
            'REFERENCE_NUMBER' => $isGcash ? $reference : 'None',
            'PROOF_OF_PAYMENT' => $isGcash ? $proofImage : 'None',
        ], true );

        return $this->response->setJSON( [
            'success'    => true,
            'payment_id' => $paymentId,
            'message'    => 'Payment recorded.',
        ] );
    }
}