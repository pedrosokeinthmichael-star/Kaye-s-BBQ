<?php

namespace App\Controllers;

use App\Models\BBQModel;

class Home extends BaseController
{


//LOGIN
public function login()
{
    // Already logged in (this tab, a new tab, or coming back to '/')?
    // Skip the login page entirely and go straight to the right home.
    if ( session()->get('isLoggedIn') ) {
        $role = session()->get('role');
        return redirect()->to( $role === 'admin' ? '/admin/home' : '/customer/home' );
    }

    return view('html1/login/login.php');

}
public function register()
{
    // Same guard here — a logged-in customer shouldn't land back on
    // the registration form either.
    if ( session()->get('isLoggedIn') ) {
        $role = session()->get('role');
        return redirect()->to( $role === 'admin' ? '/admin/home' : '/customer/home' );
    }

    return view('html1/login/register.php');
}
public function menu(): string
{
    return view('html1/login/menu.php');

}

//CUSTOMER
public function home(): string
{
    $fetchAllBarbequeInfo = new BBQModel();
    $data['barbeque_list'] = $fetchAllBarbequeInfo->getAllWithStock();

    return view('html1/customer/index.php', $data);
}
public function barbeques(): string
{
    $fetchAllBarbequeInfo = new BBQModel();
    $data['barbeque_list'] = $fetchAllBarbequeInfo->getAllWithStock();

    return view('html1/customer/barbeques.php', $data);
}

public function orders(): string
{
    $userId = session()->get('user_id');

    $orderDetails = new \App\Models\OrderDetailsModel();
    $data['cart_items'] = $userId ? $orderDetails->getCartForUser( $userId ) : [];

    $barangayModel = new \App\Models\BarangayModel();
    // Only active barangays are offered at checkout; inactive ones are hidden.
    $data['barangay_list'] = $barangayModel->where( 'BARANGAY_STATUS', 'Active' )->findAll();

    $gcashModel = new \App\Models\GcashModel();
    // The active (non-archived) GCash record = the one shown at checkout.
    $data['gcash'] = $gcashModel->getCurrent();

    $userModel = new \App\Models\UserModel();
    $data['user'] = $userId ? $userModel->find( $userId ) : null;

    $orderModel = new \App\Models\OrderModel();
    $data['tracking_orders'] = $userId ? $orderModel->withExtraPayments( $orderModel->getTrackingOrdersForUser( $userId ) ) : [];

    // A payment the admin requested (saved by OrderController::requestPayment()
    // in the cache, no database column) is what makes "Add Payment" show on the card.
    foreach ( $data['tracking_orders'] as &$trackingOrder ) {
        $trackingOrder['PAYMENT_REQUEST_AMOUNT'] = cache( 'payment_request_' . (int) $trackingOrder['ORDER_ID'] ) ?: null;
    }
    unset( $trackingOrder );

    // Minimum cart subtotal needed to place an order (Constants.php).
    $data['min_order'] = MIN_ORDER_AMOUNT;

    // Checkout switches set by the admin (Orders > Order Settings).
    // Delivery off  -> the customer can only pick Pickup.
    // GCash off     -> the customer can only pay with cash.
    // (GCash also counts as off when no GCash account has been saved.)
    $shopSettings = new \App\Models\ShopSettingModel();
    $data['delivery_enabled'] = $shopSettings->deliveryEnabled();
    $data['gcash_enabled']    = $shopSettings->gcashAvailable();

    return view('html1/customer/orders.php', $data);
}
public function history(): string
{
    $userId = session()->get('user_id');

    $orderModel = new \App\Models\OrderModel();
    // The logged-in customer's Delivered + Cancelled orders.
    // withExtraPayments() adds REFUND_PAYMENT (the payment the admin sent back)
    // so a refunded order's details can show the refund reference + screenshot.
    $data['order_history'] = $userId ? $orderModel->withExtraPayments( $orderModel->getHistoryOrdersForUser( (int) $userId ) ) : [];

    return view('html1/customer/history.php', $data);
}
public function profile(): string
{
    $userModel = new \App\Models\UserModel();
    $data['user'] = $userModel->find( session()->get('user_id') );

    return view('html1/customer/profile.php', $data);
}

//ADMIN

public function admin(): string
{
    // Dashboard figures (orders, revenue, best seller, weekly chart, etc.)
    // are all calculated in OrderModel::getDashboardData().
    $data = ( new \App\Models\OrderModel() )->getDashboardData();

    return view('html1/admin/admin.php', $data);
}
public function menus(): string
{
    return view('html1/admin/menus.php');
}

public function order(): string
{
    $orderModel = new \App\Models\OrderModel();
    $data['active_orders'] = $orderModel->withExtraPayments( $orderModel->getActiveOrdersForAdmin() );

    // Shop GCash details, used in the "request payment" message on the
    // Verify Payment modal, and in the Order Settings tab's GCash panel.
    $data['gcash'] = ( new \App\Models\GcashModel() )->getCurrent();

    // Order Settings tab (delivery & payment): all barangays -- active and
    // inactive, since the admin manages both -- plus the fee-change log.
    $data['barangay_list'] = ( new \App\Models\BarangayModel() )->findAll();
    $data['fee_history']   = ( new \App\Models\BarangayHistoryModel() )->getHistory();

    // Enable Delivery / Enable GCash switches (saved by SettingsController::update()).
    $data['shop_settings'] = ( new \App\Models\ShopSettingModel() )->all();

    return view('html1/admin/orders.php', $data);
}
public function sales(): string
{
    $orderModel = new \App\Models\OrderModel();
    // withExtraPayments() adds each order's EXTRA_PAYMENTS (added by the customer)
    // and REFUND_PAYMENT (sent back by the admin) for the Sales details modal.
    $completedOrders = $orderModel->withExtraPayments( $orderModel->getCompletedOrdersForAdmin() );

    $delivered = array_filter( $completedOrders, fn( $o ) => $o['ORDER_STATUS'] === 'Delivered' );
    $cancelled = array_filter( $completedOrders, fn( $o ) => $o['ORDER_STATUS'] === 'Cancelled' );
    // Refunded orders (the admin sent the customer's payment back) are listed in
    // the table but earn no revenue and aren't counted as cancellations.
    $refunded  = array_filter( $completedOrders, fn( $o ) => $o['ORDER_STATUS'] === 'Refund' );

    $deliveredCount = count( $delivered );
    $cancelledCount = count( $cancelled );
    $refundedCount  = count( $refunded );
    $totalCount     = count( $completedOrders );

    $netRevenue = array_reduce( $delivered, fn( $sum, $o ) => $sum + (float) $o['TOTAL_AMOUNT'], 0 );
    $avgOrder   = $deliveredCount > 0 ? $netRevenue / $deliveredCount : 0;
    $cancelRate = $totalCount > 0 ? round( $cancelledCount / $totalCount * 100 ) : 0;

    $data['completed_orders'] = $completedOrders;
    $data['net_revenue']      = $netRevenue;
    $data['delivered_count']  = $deliveredCount;
    $data['cancelled_count']  = $cancelledCount;
    $data['refunded_count']   = $refundedCount;
    $data['total_count']      = $totalCount;
    $data['avg_order']        = $avgOrder;
    $data['cancel_rate']      = $cancelRate;

    return view('html1/admin/sales.php', $data);
}
public function admin_profile(): string
{
    return view('html1/admin/profile.php');
}
public function admin_settings(): string
{
    $barangayModel = new \App\Models\BarangayModel();
    $data['barangay_list'] = $barangayModel->findAll();

    $data['fee_history'] = ( new \App\Models\BarangayHistoryModel() )->getHistory();

    $gcashModel = new \App\Models\GcashModel();
    // The active (non-archived) GCash record = the one currently shown at checkout.
    $data['gcash'] = $gcashModel->getCurrent();
    $data['gcash_archive'] = $gcashModel->getArchived();

    $data['shop_settings'] = ( new \App\Models\ShopSettingModel() )->all();

    return view('html1/admin/delivery_payment.php', $data);
}



}