<?php

namespace App\Controllers;

use App\Models\NotificationModel;

class NotificationController extends BaseController
{
    // GET /admin/notifications  -> JSON, polled by js/admin/notifications.js
    // Live low-stock / sold-out alerts are merged in with the stored ones.
    public function index()
    {
        $model = new NotificationModel();

        return $this->response->setJSON( [
            'notifications' => array_merge( $model->stockAlerts(), $model->all() ),
        ] );
    }

    // GET /admin/notifications/read/(:segment)
    public function markRead( string $id )
    {
        $model = new NotificationModel();

        if ( strpos( $id, 'stock-' ) === 0 ) {
            $model->markStockRead( $id );
        } else {
            $model->markRead( $id );
        }

        return $this->response->setJSON( [ 'ok' => true ] );
    }

    // GET /admin/notifications/read-all
    public function markAllRead()
    {
        $model = new NotificationModel();
        $model->markAllStockRead();
        $model->markAllRead();

        return $this->response->setJSON( [ 'ok' => true ] );
    }

    /* ---------------- CUSTOMER BELL (scoped to the logged-in user) ---------------- */

    // GET /customer/notifications  -> JSON, polled by js/customer/order_notifications.js
    public function customerIndex()
    {
        $userId = (int) session()->get( 'user_id' );
        return $this->response->setJSON( [ 'notifications' => ( new NotificationModel() )->allForCustomer( $userId ) ] );
    }

    // GET /customer/notifications/read/(:segment)
    public function customerMarkRead( string $id )
    {
        ( new NotificationModel() )->markReadForCustomer( (int) session()->get( 'user_id' ), $id );
        return $this->response->setJSON( [ 'ok' => true ] );
    }

    // GET /customer/notifications/read-all
    public function customerMarkAllRead()
    {
        ( new NotificationModel() )->markAllReadForCustomer( (int) session()->get( 'user_id' ) );
        return $this->response->setJSON( [ 'ok' => true ] );
    }
}