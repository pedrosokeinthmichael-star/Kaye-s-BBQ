<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\OrderModel;
use App\Models\OrderDetailsModel;
use App\Models\BBQModel;

/**
 * OrderDetailController
 * -----------------------------------------------------------------
 * The customer's live cart. A cart is the customer's `orders` row
 * with ORDER_STATUS = 'Cart'; its order_item rows are the cart lines
 * (one row per MENU_ID per order — see uq_order_item_order_menu — so
 * quantity is bumped in place rather than duplicated).
 *
 * order_item stores UNIT_PRICE (not a subtotal). While in the cart it
 * tracks the current menu price; OrderController::placeOrder() locks
 * it in at checkout. getCartForUser() also returns ORDER_DETAIL_ID
 * (= ORDER_ITEM_ID) and SUB_TOTAL so the cart JS keeps working as is.
 */
class OrderDetailController extends BaseController {

    /**
     * GET /cart
     * Returns the logged-in customer's current cart as JSON.
     */
    public function index() {

        $userId = session()->get( 'user_id' );
        if ( ! $userId ) {
            return $this->response->setStatusCode( 401 )->setJSON( [
                'success' => false,
                'message' => 'Please log in to view your cart.',
            ] );
        }

        $orderDetails = new OrderDetailsModel();

        return $this->response->setJSON( [
            'success' => true,
            'items'   => $orderDetails->getCartForUser( (int) $userId ),
        ] );
    }

    /**
     * POST /cart/add
     * Body (JSON): { menu_id, quantity }
     * Adds an item to the cart. If it's already in the cart, bumps
     * the existing row's quantity instead of creating a duplicate.
     */
    public function add() {

        $userId = session()->get( 'user_id' );
        if ( ! $userId ) {
            return $this->response->setStatusCode( 401 )->setJSON( [
                'success' => false,
                'message' => 'Please log in to add items to your cart.',
            ] );
        }
        $userId = (int) $userId;

        $input = $this->request->getJSON( true );
        if ( ! $input ) {
            $input = $this->request->getPost();
        }

        $menuId   = (int) ( $input['menu_id']  ?? 0 );
        $quantity = (int) ( $input['quantity'] ?? 1 );

        if ( $menuId <= 0 || $quantity <= 0 ) {
            return $this->response->setStatusCode( 422 )->setJSON( [
                'success' => false,
                'message' => 'A valid menu item and quantity are required.',
            ] );
        }

        $menuModel = new BBQModel();
        $menuItem  = $menuModel->find( $menuId );

        if ( ! $menuItem || ( $menuItem['STATUS'] ?? '' ) !== 'Available' ) {
            return $this->response->setStatusCode( 404 )->setJSON( [
                'success' => false,
                'message' => 'That item is not available right now.',
            ] );
        }

        $price        = (float) $menuItem['PRICE'];
        $orderDetails = new OrderDetailsModel();
        $existing     = $orderDetails->findCartLine( $userId, $menuId );

        if ( $existing ) {
            $orderDetails->update( $existing['ORDER_ITEM_ID'], [
                'QUANTITY'   => (int) $existing['QUANTITY'] + $quantity,
                'UNIT_PRICE' => $price,
            ] );
        } else {
            // First item: make sure the customer has a 'Cart' order to put it in.
            $cartOrderId = ( new OrderModel() )->getCartOrderId( $userId, true );

            $orderDetails->insert( [
                'ORDER_ID'   => $cartOrderId,
                'MENU_ID'    => $menuId,
                'QUANTITY'   => $quantity,
                'UNIT_PRICE' => $price,
            ] );
        }

        return $this->response->setJSON( [
            'success' => true,
            'message' => $menuItem['NAME'] . ' added to your cart.',
            'items'   => $orderDetails->getCartForUser( $userId ),
        ] );
    }

    /**
     * POST /cart/update
     * Body (JSON): { order_detail_id, quantity }
     * Sets a cart line's quantity. Quantity <= 0 deletes the line.
     * order_detail_id is the line's ORDER_ITEM_ID.
     */
    public function updateQuantity() {

        $userId = session()->get( 'user_id' );
        if ( ! $userId ) {
            return $this->response->setStatusCode( 401 )->setJSON( [
                'success' => false,
                'message' => 'Please log in to update your cart.',
            ] );
        }
        $userId = (int) $userId;

        $input = $this->request->getJSON( true );
        if ( ! $input ) {
            $input = $this->request->getPost();
        }

        $orderItemId = (int) ( $input['order_detail_id'] ?? 0 );
        $quantity    = (int) ( $input['quantity'] ?? 0 );

        $orderDetails = new OrderDetailsModel();
        // Only finds lines that are in THIS user's cart.
        $line = $orderDetails->findCartLineById( $userId, $orderItemId );

        if ( ! $line ) {
            return $this->response->setStatusCode( 404 )->setJSON( [
                'success' => false,
                'message' => 'Cart item not found.',
            ] );
        }

        if ( $quantity <= 0 ) {
            $orderDetails->delete( $orderItemId );
        } else {
            $menuItem = ( new BBQModel() )->find( $line['MENU_ID'] );

            $orderDetails->update( $orderItemId, [
                'QUANTITY'   => $quantity,
                'UNIT_PRICE' => (float) ( $menuItem['PRICE'] ?? $line['UNIT_PRICE'] ),
            ] );
        }

        return $this->response->setJSON( [
            'success' => true,
            'items'   => $orderDetails->getCartForUser( $userId ),
        ] );
    }

    /**
     * POST /cart/remove
     * Body (JSON): { order_detail_id }
     */
    public function remove() {

        $userId = session()->get( 'user_id' );
        if ( ! $userId ) {
            return $this->response->setStatusCode( 401 )->setJSON( [
                'success' => false,
                'message' => 'Please log in to update your cart.',
            ] );
        }
        $userId = (int) $userId;

        $input = $this->request->getJSON( true );
        if ( ! $input ) {
            $input = $this->request->getPost();
        }

        $orderItemId = (int) ( $input['order_detail_id'] ?? 0 );

        $orderDetails = new OrderDetailsModel();
        if ( $orderDetails->findCartLineById( $userId, $orderItemId ) ) {
            $orderDetails->delete( $orderItemId );
        }

        return $this->response->setJSON( [
            'success' => true,
            'items'   => $orderDetails->getCartForUser( $userId ),
        ] );
    }
}