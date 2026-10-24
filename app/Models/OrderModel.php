<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * OrderModel — the `orders` table.
 *
 * One row per checkout, not per item. `orders` holds USER_ID, ORDER_TYPE, NOTE, ORDER_DATE
 * and TOTAL_AMOUNT (items + delivery fee). The payment row points back at the
 * order (payment.ORDER_ID), not the other way round. The individual items bought are the order_item
 * rows whose ORDER_ID points back at this row (order_item.ORDER_ID)
 * — order_item is the child, order is the parent, so a checkout
 * with several cart items still produces exactly one `order` row.
 * See OrderController::placeOrder().
 *
 * There is no ADDRESS_ID column on `order` — for delivery checkouts
 * the link runs the other way: OrderController::placeOrder() inserts
 * an `address` row tagged with ORDER_ID pointing back at the order it
 * belongs to (a pickup order simply has no matching `address` row at
 * all). See the `address`/`barangay` joins in
 * getTrackingOrdersForUser() and getActiveOrdersForAdmin().
 */
class OrderModel extends Model
{
    protected $table            = 'orders';
    protected $primaryKey       = 'ORDER_ID';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    =
    ['ORDER_ID', 'ORDER_TYPE', 'USER_ID', 'NOTE', 'ORDER_DATE', 'TOTAL_AMOUNT', 'ORDER_STATUS'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * NOTE: `address` (AddressModel) is not linked via an ADDRESS_ID
     * column on `order` — that column doesn't exist. Instead, each
     * delivery order's `address` row carries an ORDER_ID pointing
     * back at the order it belongs to, so DELIVERY_ADDRESS below is
     * built with a normal join against `address` on ORDER_ID (which
     * already stores BARANGAY_ID + ADDRESS_DETAIL per row, joined
     * against `barangay` to resolve the name) rather than any
     * hardcoded lookup.
     */

    /**
     * All order rows placed by a user, most recent first. Each row is
     * already one full checkout, so this can be rendered straight
     * onto the Order History page with no grouping needed.
     */
    public function getOrdersForUser( int $userId ): array
    {
        return $this->where( 'USER_ID', $userId )
                    ->where( 'ORDER_STATUS !=', 'Cart' )
                    ->orderBy( 'ORDER_DATE', 'DESC' )
                    ->findAll();
    }

    /**
     * Orders still in progress for this user — i.e. everything that
     * hasn't been delivered/completed yet — with an ITEMS_SUMMARY
     * built from order_item + menu so it can be rendered straight
     * into the "Track Your Order" table on orders.php.
     *
     * A freshly-placed order starts at ORDER_STATUS = 'Pending'
     * (see OrderController::placeOrder()), so that status has to be
     * included here or a brand-new order would never show up.
     *
     * Beyond the card-face summary, this also attaches everything
     * components/order_modal.php needs to show the order in full:
     * an itemized ITEMS array (with each menu item's IMAGE), the
     * delivery address (barangay + address detail, only when the
     * order is a delivery), and the payment method / reference /
     * proof-of-payment image. COD/pickup orders store the literal
     * string 'None' for the fields that don't apply to them (see
     * OrderController::placeOrder()) — those are normalized to an
     * empty string here so the modal simply hides that row/section
     * instead of showing "Reference: None".
     */
    public function getTrackingOrdersForUser( int $userId ): array
    {
        $orders = $this->select(
                'orders.ORDER_ID, orders.ORDER_DATE, orders.ORDER_TYPE, ' .
                'orders.NOTE, orders.TOTAL_AMOUNT, orders.ORDER_STATUS, ' .
                "CONCAT(user.FIRST_NAME, ' ', user.LAST_NAME) AS CUSTOMER_NAME, " .
                'user.EMAIL AS CUSTOMER_EMAIL, user.PHONE_NO AS CUSTOMER_PHONE, ' .
                'barangay.BARANGAY_NAME, delivery_address.ADDRESS_DETAIL, ' .
                'payment.PAYMENT_METHOD, payment.REFERENCE_NUMBER, payment.PROOF_OF_PAYMENT, ' .
                'GROUP_CONCAT(menu_item.NAME SEPARATOR ", ") AS ITEMS_SUMMARY',
                false
            )
            ->join( 'user', 'user.USER_ID = orders.USER_ID', 'left' )
            ->join( 'order_item', 'order_item.ORDER_ID = orders.ORDER_ID' )
            ->join( 'menu_item', 'menu_item.MENU_ID = order_item.MENU_ID' )
            ->join( 'delivery_address', 'delivery_address.ORDER_ID = orders.ORDER_ID', 'left' )
            ->join( 'barangay', 'barangay.BARANGAY_ID = delivery_address.BARANGAY_ID', 'left' )
            ->join( 'payment', 'payment.PAYMENT_ID = (SELECT MIN(p0.PAYMENT_ID) FROM payment p0 WHERE p0.ORDER_ID = orders.ORDER_ID)', 'left', false )
            ->where( 'orders.USER_ID', $userId )
            ->whereIn( 'orders.ORDER_STATUS', [ 'Pending', 'Preparing', 'Delivering' ] )
            ->groupBy( 'orders.ORDER_ID' )
            ->orderBy( 'orders.ORDER_DATE', 'DESC' )
            ->findAll();

        if ( empty( $orders ) ) {
            return [];
        }

        $orderIds    = array_column( $orders, 'ORDER_ID' );
        $detailModel = new OrderDetailsModel();

        $details = $detailModel
            ->select( 'order_item.ORDER_ID, order_item.QUANTITY, order_item.UNIT_PRICE, menu_item.NAME, menu_item.IMAGE' )
            ->join( 'menu_item', 'menu_item.MENU_ID = order_item.MENU_ID' )
            ->whereIn( 'order_item.ORDER_ID', $orderIds )
            ->findAll();

        $itemsByOrder = [];
        foreach ( $details as $row ) {
            $qty = (int) $row['QUANTITY'];
            $itemsByOrder[ $row['ORDER_ID'] ][] = [
                'NAME'      => $row['NAME'],
                'IMAGE'     => $row['IMAGE'],
                'QUANTITY'  => $qty,
                'PRICE'     => (float) $row['UNIT_PRICE'],
                'SUB_TOTAL' => round( $qty * (float) $row['UNIT_PRICE'], 2 ),
            ];
        }

        foreach ( $orders as &$order ) {
            $order['ITEMS'] = $itemsByOrder[ $order['ORDER_ID'] ] ?? [];
            $order['SUBTOTAL'] = array_sum( array_column( $order['ITEMS'], 'SUB_TOTAL' ) );
            // No DELIVERY_FEE column on `orders`; the fee charged = total - items subtotal.
            $order['DELIVERY_FEE'] = max( 0, round( (float) $order['TOTAL_AMOUNT'] - (float) $order['SUBTOTAL'], 2 ) );

            $isDelivery = strtolower( $order['ORDER_TYPE'] ?? '' ) === 'delivery';
            $order['DELIVERY_ADDRESS'] = $isDelivery
                ? trim( ( $order['BARANGAY_NAME'] ?? '' ) . ', ' . ( $order['ADDRESS_DETAIL'] ?? '' ), ', ' )
                : '';

            $order['PAYMENT_REFERENCE'] = ( $order['REFERENCE_NUMBER'] ?? '' ) === 'None'
                ? '' : ( $order['REFERENCE_NUMBER'] ?? '' );
            $order['PROOF_OF_PAYMENT'] = ( $order['PROOF_OF_PAYMENT'] ?? '' ) === 'None'
                ? '' : ( $order['PROOF_OF_PAYMENT'] ?? '' );
        }

        return $orders;
    }

    /**
     * A customer's finished orders (Delivered / Cancelled / Refund), most
     * recent first, for the Order History page (history.php).
     *
     * Same row shape as getTrackingOrdersForUser() so the same
     * components/order_card.php can render it — ITEMS (itemized, with
     * IMAGE), ITEMS_SUMMARY, DELIVERY_ADDRESS, PAYMENT_METHOD,
     * PAYMENT_REFERENCE, PROOF_OF_PAYMENT, NOTE — plus the customer's
     * own name/email/phone, SUBTOTAL and DELIVERY_FEE for the details
     * modal. 'None' placeholders are normalized to ''.
     *
     * Items are fetched in a separate query (not GROUP_CONCAT) to
     * avoid mixing one-to-many and one-to-one joins under a GROUP BY.
     */
    public function getHistoryOrdersForUser( int $userId ): array
    {
        $orders = $this->select(
                "orders.ORDER_ID, orders.ORDER_DATE, orders.ORDER_TYPE, " .
                "orders.NOTE,  orders.TOTAL_AMOUNT, orders.ORDER_STATUS, " .
                "CONCAT(user.FIRST_NAME, ' ', user.LAST_NAME) AS CUSTOMER_NAME, " .
                "user.EMAIL AS CUSTOMER_EMAIL, user.PHONE_NO AS CUSTOMER_PHONE, " .
                "barangay.BARANGAY_NAME, delivery_address.ADDRESS_DETAIL, " .
                "payment.PAYMENT_METHOD, payment.REFERENCE_NUMBER, payment.PROOF_OF_PAYMENT",
                false
            )
            ->join( 'user', 'user.USER_ID = orders.USER_ID', 'left' )
            ->join( 'delivery_address', 'delivery_address.ORDER_ID = orders.ORDER_ID', 'left' )
            ->join( 'barangay', 'barangay.BARANGAY_ID = delivery_address.BARANGAY_ID', 'left' )
            ->join( 'payment', 'payment.PAYMENT_ID = (SELECT MIN(p0.PAYMENT_ID) FROM payment p0 WHERE p0.ORDER_ID = orders.ORDER_ID)', 'left', false )
            ->where( 'orders.USER_ID', $userId )
            ->whereIn( 'orders.ORDER_STATUS', [ 'Delivered', 'Cancelled', 'Refund' ] )
            ->orderBy( 'orders.ORDER_DATE', 'DESC' )
            ->findAll();

        if ( empty( $orders ) ) {
            return [];
        }

        $orderIds = array_column( $orders, 'ORDER_ID' );

        $details = ( new OrderDetailsModel() )
            ->select( 'order_item.ORDER_ID, order_item.QUANTITY, order_item.UNIT_PRICE, menu_item.NAME, menu_item.IMAGE' )
            ->join( 'menu_item', 'menu_item.MENU_ID = order_item.MENU_ID' )
            ->whereIn( 'order_item.ORDER_ID', $orderIds )
            ->findAll();

        $itemsByOrder = [];
        foreach ( $details as $row ) {
            $qty = (int) $row['QUANTITY'];
            $itemsByOrder[ $row['ORDER_ID'] ][] = [
                'NAME'      => $row['NAME'],
                'IMAGE'     => $row['IMAGE'],
                'QUANTITY'  => $qty,
                'PRICE'     => (float) $row['UNIT_PRICE'],
                'SUB_TOTAL' => round( $qty * (float) $row['UNIT_PRICE'], 2 ),
            ];
        }

        foreach ( $orders as &$order ) {
            $items = $itemsByOrder[ $order['ORDER_ID'] ] ?? [];
            $order['ITEMS'] = $items;

            $order['ITEMS_SUMMARY'] = implode( ', ', array_map(
                fn( $i ) => ( $i['QUANTITY'] > 1 ? $i['QUANTITY'] . 'x ' : '' ) . $i['NAME'],
                $items
            ) );

            // No DELIVERY_FEE column on `orders`; the fee charged = total - items subtotal.
            $order['SUBTOTAL'] = array_sum( array_column( $items, 'SUB_TOTAL' ) );
            $order['DELIVERY_FEE'] = max( 0, round( (float) $order['TOTAL_AMOUNT'] - (float) $order['SUBTOTAL'], 2 ) );

            $isDelivery = strtolower( $order['ORDER_TYPE'] ?? '' ) === 'delivery';
            $order['DELIVERY_ADDRESS'] = $isDelivery
                ? trim( ( $order['BARANGAY_NAME'] ?? '' ) . ', ' . ( $order['ADDRESS_DETAIL'] ?? '' ), ', ' )
                : '';

            $order['PAYMENT_REFERENCE'] = ( $order['REFERENCE_NUMBER'] ?? '' ) === 'None'
                ? '' : ( $order['REFERENCE_NUMBER'] ?? '' );
            $order['PROOF_OF_PAYMENT'] = ( $order['PROOF_OF_PAYMENT'] ?? '' ) === 'None'
                ? '' : ( $order['PROOF_OF_PAYMENT'] ?? '' );
        }

        return $orders;
    }

    /**
     * Every order still awaiting action (Pending / Preparing /
     * Delivering) across ALL customers, for the admin Orders page.
     * Delivered and Cancelled orders belong on the Sales page instead
     * (per the copy on admin/orders.php), so they're excluded here.
     *
     * The row is shaped to match what components/order_card.php /
     * order_modal.js expect directly (CUSTOMER_NAME, CUSTOMER_EMAIL,
     * CUSTOMER_PHONE, DELIVERY_ADDRESS, PAYMENT_REFERENCE,
     * PROOF_OF_PAYMENT, and an ITEMS array keyed NAME/IMAGE/QUANTITY/
     * PRICE/SUB_TOTAL) so the order-detail modal can render the full
     * order — customer info, delivery address when applicable,
     * itemized items with their menu image, and payment method +
     * reference + proof-of-payment screenshot when paid via GCash.
     *
     * ASSUMPTIONS — please correct if wrong, since I don't have the
     * UserModel / PaymentModel / OrderDetailsModel files to confirm
     * real column names:
     *   - `user` table has USER_ID, FIRST_NAME, LAST_NAME, EMAIL, and
     *     a phone column I've guessed as PHONE_NO.
     *   - `address` table (AddressModel) has ADDRESS_ID, USER_ID,
     *     ORDER_ID, BARANGAY_ID, ADDRESS_DETAIL — address.ORDER_ID is
     *     the FK back into `order` (there is no ADDRESS_ID column on
     *     `order` itself; a pickup order has no `address` row).
     *     BARANGAY_ID is itself a FK into `barangay` (BarangayModel),
     *     so getting a displayable name means joining `barangay` too.
     *   - `payment` table has PAYMENT_ID, PAYMENT_METHOD,
     *     REFERENCE_NUMBER, PROOF_OF_PAYMENT (per PaymentModel usage
     *     in OrderController::placeOrder()).
     *   - `menu` has an IMAGE column (per menu_card.php).
     *   - `order_item` rows join to `menu_item` via MENU_ID, and store
     *     QUANTITY + UNIT_PRICE (no SUB_TOTAL column), so SUB_TOTAL
     *     here is derived as QUANTITY * UNIT_PRICE.
     */
    public function getActiveOrdersForAdmin(): array
    {
        $orders = $this->select(
                "orders.ORDER_ID, orders.ORDER_TYPE, orders.ORDER_DATE, " .
                "orders.NOTE, orders.TOTAL_AMOUNT, " .
                "orders.ORDER_STATUS, orders.USER_ID, " .
                "CONCAT(user.FIRST_NAME, ' ', user.LAST_NAME) AS CUSTOMER_NAME, " .
                "user.EMAIL AS CUSTOMER_EMAIL, user.PHONE_NO AS CUSTOMER_PHONE, " .
                "barangay.BARANGAY_NAME, delivery_address.ADDRESS_DETAIL, " .
                "payment.PAYMENT_METHOD, payment.PAYMENT_DATE, " .
                "payment.REFERENCE_NUMBER, payment.PROOF_OF_PAYMENT",
                false
            )
            ->join( 'user', 'user.USER_ID = orders.USER_ID', 'left' )
            ->join( 'delivery_address', 'delivery_address.ORDER_ID = orders.ORDER_ID', 'left' )
            ->join( 'barangay', 'barangay.BARANGAY_ID = delivery_address.BARANGAY_ID', 'left' )
            ->join( 'payment', 'payment.PAYMENT_ID = (SELECT MIN(p0.PAYMENT_ID) FROM payment p0 WHERE p0.ORDER_ID = orders.ORDER_ID)', 'left', false )
            ->whereIn( 'orders.ORDER_STATUS', [ 'Pending', 'Preparing', 'Delivering' ] )
            ->orderBy( 'orders.ORDER_DATE', 'DESC' )
            ->findAll();

        if ( empty( $orders ) ) {
            return [];
        }

        // Pull the actual line items for every order in one query
        // rather than one query per row.
        $orderIds = array_column( $orders, 'ORDER_ID' );

        $detailModel = new OrderDetailsModel();
        $details = $detailModel
            ->select( 'order_item.ORDER_ID, order_item.QUANTITY, order_item.UNIT_PRICE, menu_item.NAME, menu_item.IMAGE' )
            ->join( 'menu_item', 'menu_item.MENU_ID = order_item.MENU_ID' )
            ->whereIn( 'order_item.ORDER_ID', $orderIds )
            ->findAll();

        $itemsByOrder = [];
        foreach ( $details as $row ) {
            $qty = (int) $row['QUANTITY'];
            $itemsByOrder[ $row['ORDER_ID'] ][] = [
                'NAME'      => $row['NAME'],
                'IMAGE'     => $row['IMAGE'],
                'QUANTITY'  => $qty,
                'PRICE'     => (float) $row['UNIT_PRICE'],
                'SUB_TOTAL' => round( $qty * (float) $row['UNIT_PRICE'], 2 ),
            ];
        }

        foreach ( $orders as &$order ) {
            $order['ITEMS'] = $itemsByOrder[ $order['ORDER_ID'] ] ?? [];
            $order['SUBTOTAL'] = array_sum( array_column( $order['ITEMS'], 'SUB_TOTAL' ) );
            // No DELIVERY_FEE column on `orders`; the fee charged = total - items subtotal.
            $order['DELIVERY_FEE'] = max( 0, round( (float) $order['TOTAL_AMOUNT'] - (float) $order['SUBTOTAL'], 2 ) );

            // ORDER_TYPE is stored lowercase ('delivery' / 'pickup',
            // see OrderController::placeOrder()) — compare
            // case-insensitively rather than against 'Delivery'.
            $isDelivery = strtolower( $order['ORDER_TYPE'] ?? '' ) === 'delivery';
            $order['DELIVERY_ADDRESS'] = $isDelivery
                ? trim( ( $order['BARANGAY_NAME'] ?? '' ) . ', ' . ( $order['ADDRESS_DETAIL'] ?? '' ), ', ' )
                : '';

            // Cash/pickup orders store the literal string 'None' for
            // fields that only apply to GCash — normalize those to
            // empty so the modal hides the row instead of showing it.
            $order['PAYMENT_REFERENCE'] = ( $order['REFERENCE_NUMBER'] ?? '' ) === 'None'
                ? '' : ( $order['REFERENCE_NUMBER'] ?? '' );
            $order['PROOF_OF_PAYMENT'] = ( $order['PROOF_OF_PAYMENT'] ?? '' ) === 'None'
                ? '' : ( $order['PROOF_OF_PAYMENT'] ?? '' );
        }

        return $orders;
    }

    /**
     * Every order that has reached a terminal state (Delivered,
     * Cancelled or Refund) across ALL customers, for the admin Sales page.
     * Pending / Preparing / Delivering orders live on the admin
     * Orders page instead (per the copy on admin/orders.php), so
     * they're excluded here.
     *
     * "Completed" simply means ORDER_STATUS is Delivered, Cancelled or Refund.
     * There is no separate sales table: sales are read straight from
     * `orders` by filtering on the status.
     *
     * Also joins `user`, `address` (on ORDER_ID — see the class doc
     * comment above), and `payment` — same shape as
     * getActiveOrdersForAdmin() — so sales.php can show the customer
     * name, delivery address, and payment method as their own table
     * columns instead of just the items/total/status summary.
     *
     * Items are fetched in a SEPARATE query and stitched together in
     * PHP (same two-step approach getActiveOrdersForAdmin() uses)
     * rather than joining order_item/menu into this same query and
     * GROUP_CONCAT'ing them: mixing a one-to-many join (order has
     * many order_item rows) with one-to-one joins (user/address/
     * payment) under a single GROUP BY is exactly the kind of query
     * MySQL doesn't guarantee correct results for — it was silently
     * dropping CUSTOMER_NAME/PAYMENT_METHOD to blank on some rows
     * instead of erroring. Keeping items in their own query sidesteps
     * that entirely.
     */
    public function getCompletedOrdersForAdmin(): array
    {
        $orders = $this->select(
                "orders.ORDER_ID, orders.ORDER_DATE, orders.ORDER_TYPE, " .
                "orders.NOTE, orders.TOTAL_AMOUNT, orders.ORDER_STATUS, " .
                "CONCAT(user.FIRST_NAME, ' ', user.LAST_NAME) AS CUSTOMER_NAME, " .
                "user.EMAIL AS CUSTOMER_EMAIL, user.PHONE_NO AS CUSTOMER_PHONE, " .
                "barangay.BARANGAY_NAME, delivery_address.ADDRESS_DETAIL, " .
                "payment.PAYMENT_METHOD, payment.PAYMENT_DATE, payment.REFERENCE_NUMBER, payment.PROOF_OF_PAYMENT",
                false
            )
            ->whereIn( 'orders.ORDER_STATUS', [ 'Delivered', 'Cancelled', 'Refund' ] )
            ->join( 'user', 'user.USER_ID = orders.USER_ID', 'left' )
            ->join( 'delivery_address', 'delivery_address.ORDER_ID = orders.ORDER_ID', 'left' )
            ->join( 'barangay', 'barangay.BARANGAY_ID = delivery_address.BARANGAY_ID', 'left' )
            ->join( 'payment', 'payment.PAYMENT_ID = (SELECT MIN(p0.PAYMENT_ID) FROM payment p0 WHERE p0.ORDER_ID = orders.ORDER_ID)', 'left', false )
            ->orderBy( 'orders.ORDER_DATE', 'DESC' )
            ->findAll();

        if ( empty( $orders ) ) {
            return [];
        }

        // Pull the line items for every order in one query, same as
        // getActiveOrdersForAdmin(). Each order gets an itemized ITEMS
        // array (for the Sales "View" modal) plus an ITEMS_SUMMARY
        // string (for the table row), both built in PHP instead of via
        // GROUP_CONCAT.
        $orderIds = array_column( $orders, 'ORDER_ID' );

        $detailModel = new OrderDetailsModel();
        $details = $detailModel
            ->select( 'order_item.ORDER_ID, order_item.QUANTITY, order_item.UNIT_PRICE, menu_item.NAME, menu_item.IMAGE' )
            ->join( 'menu_item', 'menu_item.MENU_ID = order_item.MENU_ID' )
            ->whereIn( 'order_item.ORDER_ID', $orderIds )
            ->findAll();

        $itemsByOrder = [];
        foreach ( $details as $row ) {
            $qty = (int) $row['QUANTITY'];
            $itemsByOrder[ $row['ORDER_ID'] ][] = [
                'NAME'      => $row['NAME'],
                'IMAGE'     => $row['IMAGE'],
                'QUANTITY'  => $qty,
                'PRICE'     => (float) $row['UNIT_PRICE'],
                'SUB_TOTAL' => round( $qty * (float) $row['UNIT_PRICE'], 2 ),
            ];
        }

        foreach ( $orders as &$order ) {
            $order['ITEMS'] = $itemsByOrder[ $order['ORDER_ID'] ] ?? [];
            $order['ITEMS_SUMMARY'] = implode( ', ', array_map(
                fn( $i ) => ( $i['QUANTITY'] > 1 ? $i['QUANTITY'] . 'x ' : '' ) . $i['NAME'],
                $order['ITEMS']
            ) );
            $order['SUBTOTAL'] = array_sum( array_column( $order['ITEMS'], 'SUB_TOTAL' ) );
            // No DELIVERY_FEE column on `orders`; the fee charged = total - items subtotal.
            $order['DELIVERY_FEE'] = max( 0, round( (float) $order['TOTAL_AMOUNT'] - (float) $order['SUBTOTAL'], 2 ) );

            $isDelivery = strtolower( $order['ORDER_TYPE'] ?? '' ) === 'delivery';
            $order['DELIVERY_ADDRESS'] = $isDelivery
                ? trim( ( $order['BARANGAY_NAME'] ?? '' ) . ', ' . ( $order['ADDRESS_DETAIL'] ?? '' ), ', ' )
                : '';

            // Cash/pickup orders store the literal string 'None' for
            // the reference number since it only applies to GCash —
            // normalize that to empty so the view can skip printing it.
            $order['PAYMENT_REFERENCE'] = ( $order['REFERENCE_NUMBER'] ?? '' ) === 'None'
                ? '' : ( $order['REFERENCE_NUMBER'] ?? '' );
        }

        return $orders;
    }

    /**
     * The ORDER_ID of the user's live cart (their `orders` row with
     * ORDER_STATUS = 'Cart'). With $create = true a cart order is
     * created if they don't have one yet; otherwise null is returned.
     */
    public function getCartOrderId( int $userId, bool $create = false ): ?int
    {
        $row = $this->where( 'USER_ID', $userId )
                    ->where( 'ORDER_STATUS', 'Cart' )
                    ->orderBy( 'ORDER_ID', 'DESC' )
                    ->first();

        if ( $row ) {
            return (int) $row['ORDER_ID'];
        }
        if ( ! $create ) {
            return null;
        }

        $id = $this->insert( [
            'USER_ID'     => $userId,
            'ORDER_TYPE'  => 'None',
            'NOTE'        => 'None',
            'ORDER_STATUS' => 'Cart',
        ], true );
        return $id ? (int) $id : null;
    }

    /**
     * Attaches EXTRA_PAYMENTS to each order: the payments the customer added
     * after checkout (when the admin found the first payment short). Each
     * extra is REFERENCE_NUMBER / PROOF_OF_PAYMENT / PAYMENT_DATE.
     *
     * The added payment lives on the order's single payment row in
     * ADD_PAYMENT_REFERENCE / ADD_PAYMENT_PROOF. Any further payment ROWS for
     * the same order (if the table ever allows them) are included too.
     * PAYMENT_REQUEST_AMOUNT (the balance the admin asked for) is attached as
     * PAYMENT_REQUEST_AMOUNT on the order.
     */
    public function withExtraPayments( array $orders ): array
    {
        if ( empty( $orders ) ) {
            return $orders;
        }

        $rows = ( new PaymentModel() )
            ->whereIn( 'ORDER_ID', array_column( $orders, 'ORDER_ID' ) )
            ->orderBy( 'PAYMENT_ID', 'ASC' )
            ->findAll();

        $byOrder = [];
        foreach ( $rows as $r ) {
            $byOrder[ $r['ORDER_ID'] ][] = $r;
        }

        $blank = function ( $v ) {
            return $v === null || trim( (string) $v ) === '' || strcasecmp( trim( (string) $v ), 'None' ) === 0;
        };

        foreach ( $orders as &$order ) {
            $all    = $byOrder[ $order['ORDER_ID'] ] ?? [];

            // A 'Refund' row is the admin sending money BACK, not a payment the
            // customer added, so keep it out of the extra payments (and out of
            // "first") and expose it separately as REFUND_PAYMENT.
            $refunds = array_values( array_filter( $all, fn( $r ) => ( $r['PAYMENT_METHOD'] ?? '' ) === 'Refund' ) );
            $all     = array_values( array_filter( $all, fn( $r ) => ( $r['PAYMENT_METHOD'] ?? '' ) !== 'Refund' ) );
            $order['REFUND_PAYMENT'] = $refunds ? $refunds[ count( $refunds ) - 1 ] : null;

            $first  = $all[0] ?? null;
            $extras = array_slice( $all, 1 );

            if ( $first && ! $blank( $first['ADD_PAYMENT_REFERENCE'] ?? null ) ) {
                array_unshift( $extras, [
                    'REFERENCE_NUMBER' => $first['ADD_PAYMENT_REFERENCE'],
                    'PROOF_OF_PAYMENT' => $blank( $first['ADD_PAYMENT_PROOF'] ?? null ) ? '' : $first['ADD_PAYMENT_PROOF'],
                    'PAYMENT_DATE'     => null,
                ] );
            }

            $order['EXTRA_PAYMENTS']         = array_values( $extras );
            $order['PAYMENT_REQUEST_AMOUNT'] = $first['PAYMENT_REQUEST_AMOUNT'] ?? null;
        }
        unset( $order );

        return $orders;
    }

    /**
     * Everything the admin dashboard (admin.php) shows, in the exact
     * variable names the view expects. All figures are for the CURRENT
     * calendar month except the weekly chart (current Mon–Sun week).
     *
     *  - Revenue / average order / weekly chart: Delivered orders only.
     *  - Orders count, items sold, best/slowest seller, menus ordered:
     *    every placed order except 'Cart' and 'Cancelled'.
     *  - Pending count: all Pending orders, regardless of date.
     *  - The month/week is taken from orders.ORDER_DATE (there is no
     *    separate delivered-at date column).
     */
    public function getDashboardData(): array
    {
        $db         = $this->db;
        $monthStart = date( 'Y-m-01 00:00:00' );
        $nextMonth  = date( 'Y-m-01 00:00:00', strtotime( 'first day of next month' ) );
        $weekStart  = date( 'Y-m-d', strtotime( 'monday this week' ) );

        // Orders still waiting to be accepted (badge on the Orders quick link).
        $pendingCount = $db->table( 'orders' )
            ->where( 'ORDER_STATUS', 'Pending' )
            ->countAllResults();

        // Orders placed this month (not carts, not cancelled).
        $ordersThisMonth = $db->table( 'orders' )
            ->whereNotIn( 'ORDER_STATUS', [ 'Cart', 'Cancelled', 'Refund' ] )
            ->where( 'ORDER_DATE >=', $monthStart )
            ->where( 'ORDER_DATE <', $nextMonth )
            ->countAllResults();

        // Revenue + average order size come from delivered orders.
        $delivered = $db->table( 'orders' )
            ->select( 'COUNT(*) AS N, COALESCE(SUM(TOTAL_AMOUNT), 0) AS REVENUE', false )
            ->where( 'ORDER_STATUS', 'Delivered' )
            ->where( 'ORDER_DATE >=', $monthStart )
            ->where( 'ORDER_DATE <', $nextMonth )
            ->get()->getRowArray();

        $deliveredCount = (int) ( $delivered['N'] ?? 0 );
        $revenue        = (float) ( $delivered['REVENUE'] ?? 0 );
        $avgOrderValue  = $deliveredCount > 0 ? $revenue / $deliveredCount : 0;

        // Units sold + sales per menu item this month.
        $soldRows = $db->table( 'order_item' )
            ->select( 'order_item.MENU_ID, SUM(order_item.QUANTITY) AS SOLD, ' .
                      'SUM(order_item.QUANTITY * order_item.UNIT_PRICE) AS SALES', false )
            ->join( 'orders', 'orders.ORDER_ID = order_item.ORDER_ID' )
            ->whereNotIn( 'orders.ORDER_STATUS', [ 'Cart', 'Cancelled', 'Refund' ] )
            ->where( 'orders.ORDER_DATE >=', $monthStart )
            ->where( 'orders.ORDER_DATE <', $nextMonth )
            ->groupBy( 'order_item.MENU_ID' )
            ->get()->getResultArray();

        $soldByMenu = [];
        foreach ( $soldRows as $r ) {
            $soldByMenu[ $r['MENU_ID'] ] = [ 'sold' => (int) $r['SOLD'], 'sales' => (float) $r['SALES'] ];
        }

        $menuItems = $db->table( 'menu_item' )->select( 'MENU_ID, NAME, STATUS' )->get()->getResultArray();
        $menuCount = count( $menuItems );

        $stats = [];
        foreach ( $menuItems as $m ) {
            $stats[] = [
                'name'      => $m['NAME'],
                'available' => $m['STATUS'] === 'Available',
                'sold'      => $soldByMenu[ $m['MENU_ID'] ]['sold']  ?? 0,
                'sales'     => $soldByMenu[ $m['MENU_ID'] ]['sales'] ?? 0.0,
            ];
        }

        // Most-ordered first, ties broken alphabetically.
        usort( $stats, fn( $a, $b ) => [ $b['sold'], $a['name'] ] <=> [ $a['sold'], $b['name'] ] );

        $withSales = array_values( array_filter( $stats, fn( $s ) => $s['sold'] > 0 ) );

        $bestSeller = $withSales ? [ 'name' => $withSales[0]['name'], 'sold' => $withSales[0]['sold'] ] : null;

        // Item that brought in the most money this month.
        $topEarner = null;
        foreach ( $withSales as $s ) {
            if ( $topEarner === null || $s['sales'] > $topEarner['sales'] ) {
                $topEarner = [ 'name' => $s['name'], 'sales' => $s['sales'] ];
            }
        }

        // Slowest seller: lowest units sold among items currently on the
        // menu (zero sales counts). Needs at least two items to mean anything.
        $slowest   = null;
        $available = array_values( array_filter( $stats, fn( $s ) => $s['available'] ) );
        if ( count( $available ) >= 2 ) {
            $last    = end( $available );
            $slowest = [ 'name' => $last['name'], 'sold' => $last['sold'] ];
        }

        $menusOrdered = array_map(
            fn( $s ) => [ 'name' => $s['name'], 'count' => $s['sold'] ],
            array_slice( $withSales, 0, 5 )
        );

        // Delivered revenue for each day of this Mon–Sun week.
        $weekRows = $db->table( 'orders' )
            ->select( 'DATE(ORDER_DATE) AS D, SUM(TOTAL_AMOUNT) AS T', false )
            ->where( 'ORDER_STATUS', 'Delivered' )
            ->where( 'ORDER_DATE >=', $weekStart . ' 00:00:00' )
            ->where( 'ORDER_DATE <', date( 'Y-m-d', strtotime( $weekStart . ' +7 days' ) ) . ' 00:00:00' )
            ->groupBy( 'DATE(ORDER_DATE)' )
            ->get()->getResultArray();
        $totalsByDay = array_column( $weekRows, 'T', 'D' );

        $salesWeek = [];
        for ( $i = 0; $i < 7; $i++ ) {
            $day         = date( 'Y-m-d', strtotime( $weekStart . " +{$i} days" ) );
            $salesWeek[] = [
                'label' => date( 'l', strtotime( $day ) ),
                'total' => (float) ( $totalsByDay[ $day ] ?? 0 ),
            ];
        }

        return [
            'pending_count'      => $pendingCount,
            'orders_this_month'  => $ordersThisMonth,
            'revenue_this_month' => $revenue,
            'avg_order_value'    => $avgOrderValue,
            'menu_count'         => $menuCount,
            'best_seller'        => $bestSeller,
            'top_earner'         => $topEarner,
            'slowest_seller'     => $slowest,
            'sales_week'         => $salesWeek,
            'menus_ordered'      => $menusOrdered,
        ];
    }

}