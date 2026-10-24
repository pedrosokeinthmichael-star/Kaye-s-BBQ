<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */


//LOGIN
$routes->get('/', 'Home::login');
$routes->post('/login', 'UserController::login');
$routes->post('/logout', 'UserController::logout');

$routes->get('/register', 'Home::register');
$routes->post('/register', 'UserController::register');
$routes->get('/menu', 'Home::menu');

//FORGOT PASSWORD (public — the customer isn't logged in)
$routes->post('/forgot-password', 'PasswordResetController::checkEmail');
$routes->post('/forgot-password/reset', 'PasswordResetController::resetPassword');

//CUSTOMER — everything below requires a logged-in session with role=customer
$routes->group('', ['filter' => 'auth:customer'], function ($routes) {
    $routes->get('/customer/home', 'Home::home');
    $routes->get('/customer/barbeques', 'Home::barbeques');
    $routes->get('/customer/orders', 'Home::orders');
    $routes->get('/customer/history', 'Home::history');
    $routes->get('/customer/profile', 'Home::profile');

    //CART (order_detail acts as the customer's live cart)
    $routes->get('/cart', 'OrderDetailController::index');
    $routes->post('/cart/add', 'OrderDetailController::add');
    $routes->post('/cart/update', 'OrderDetailController::updateQuantity');
    $routes->post('/cart/remove', 'OrderDetailController::remove');

    //CUSTOMER - NOTIFICATIONS (cache-backed bell, per customer)
    $routes->get('/customer/notifications', 'NotificationController::customerIndex');
    $routes->get('/customer/notifications/read/(:segment)', 'NotificationController::customerMarkRead/$1');
    $routes->get('/customer/notifications/read-all', 'NotificationController::customerMarkAllRead');

    //PAYMENT
    $routes->post('/payment/store', 'PaymentController::store');

    //CHECKOUT
    $routes->post('/orders/place', 'OrderController::placeOrder');
    $routes->post('/orders/add-payment', 'OrderController::addPayment');
    $routes->post('/orders/cancel', 'OrderController::cancel');
});


//ADMIN — everything below requires a logged-in session with role=admin
$routes->group('', ['filter' => 'auth:admin'], function ($routes) {
    $routes->get('/admin/home', 'Home::admin');
    $routes->get('/admin/menus', 'BarbequeController::index');
    $routes->get('/admin/orders', 'Home::order');
    $routes->post('/admin/orders/(:num)/status', 'OrderController::updateStatus/$1');
    $routes->post('/admin/orders/(:num)/request-payment', 'OrderController::requestPayment/$1');
    $routes->post('/admin/orders/(:num)/refund', 'OrderController::saveRefund/$1');
    $routes->get('/admin/sales', 'Home::sales');
    $routes->get('/admin/profile', 'Home::admin_profile');

    //ADMIN - NOTIFICATIONS (cache-backed bell)
    $routes->get('/admin/notifications', 'NotificationController::index');
    $routes->get('/admin/notifications/read/(:segment)', 'NotificationController::markRead/$1');
    $routes->get('/admin/notifications/read-all', 'NotificationController::markAllRead');

    //ADMIN - DELIVERY & PAYMENT SETTINGS
    // TODO: point this at SettingsController::index() (and add a
    // POST '/admin/settings/update' route) once that controller and
    // its backing table/model exist. For now this 404s until then.
    $routes->get('/admin/settings', 'Home::admin_settings');

    //ADMIN - ENABLE / DISABLE DELIVERY and GCASH (AJAX, called by order_settings.js)
    $routes->post('/admin/settings/update', 'SettingsController::update');

    //ADMIN - DELIVERY AREA (BARANGAY) CRUD
    $routes->post('/admin/barangays/store', 'BarangayController::store');
    $routes->post('/admin/barangays/update/(:num)', 'BarangayController::update/$1');
    $routes->get('/admin/barangays/delete/(:num)', 'BarangayController::delete/$1');

    //ADMIN - GCASH DETAILS
    $routes->post('/admin/gcash/store', 'GcashController::store');
    $routes->post('/admin/gcash/update/(:num)', 'GcashController::update/$1');
    $routes->get('/admin/gcash/delete/(:num)', 'GcashController::delete/$1');

    //ADMIN - MENU CATEGORIES
    $routes->post('/admin/categories/store', 'CategoryController::store');

    //ADMIN - MENU ITEM CRUD
    $routes->get('/admin/menus/create', 'BarbequeController::create_form');
    $routes->post('/admin/menus/store', 'BarbequeController::store');
    $routes->get('/admin/menus/edit/(:num)', 'BarbequeController::edit_form/$1');
    $routes->post('/admin/menus/update/(:num)', 'BarbequeController::update/$1');
    $routes->get('/admin/menus/delete/(:num)', 'BarbequeController::delete/$1');
});