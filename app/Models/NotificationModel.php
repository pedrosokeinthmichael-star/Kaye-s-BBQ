<?php

namespace App\Models;

/**
 * Notifications kept in CodeIgniter's cache (writable/cache) —
 * no database table needed. Same approach already used for
 * 'payment_request_<orderId>' in Home::orders().
 *
 *  - Admin bell:    one shared list           (key 'admin_notifications')
 *  - Customer bell: one list PER customer     (key 'customer_notifications_<userId>')
 *  - Admin stock alerts (low stock / sold out) are NOT stored in the list:
 *    they are calculated live from menu_item.STOCK (see stockAlerts()).
 */
class NotificationModel
{
    private const ADMIN_KEY    = 'admin_notifications';
    private const CUSTOMER_KEY = 'customer_notifications_';
    private const MAX_ITEMS    = 30;
    private const TTL          = 60 * 60 * 24 * 30; // keep for 30 days

    /* ------------------------------ ADMIN ------------------------------ */

    /** Push a notification to the admin bell. */
    public function notifyAdmin( string $type, string $title, string $message, ?string $href = 'orders' ): void
    {
        $this->push( self::ADMIN_KEY, $type, $title, $message, $href );
    }

    public function all(): array                { return $this->load( self::ADMIN_KEY ); }
    public function markRead( string $id ): void { $this->markReadIn( self::ADMIN_KEY, $id ); }
    public function markAllRead(): void          { $this->markAllReadIn( self::ADMIN_KEY ); }

    /* -------------------------- ADMIN: STOCK ALERTS -------------------------- */

    /**
     * Low-stock / sold-out alerts, calculated live from menu_item.STOCK so they
     * work no matter what changed the stock (customer order, admin edit, etc.).
     *
     * - Ids look like "stock-12-low" / "stock-12-out".
     * - Low -> sold out creates a NEW alert (the old "low" one disappears).
     * - Restocking an item clears its flags, so it notifies again the next
     *   time it runs low.
     * - Items marked Unavailable are skipped.
     */
    public function stockAlerts(): array
    {
        $threshold = defined( 'LOW_STOCK_THRESHOLD' ) ? LOW_STOCK_THRESHOLD : 10;
        $items     = ( new \App\Models\BBQModel() )->select( 'MENU_ID, NAME, STOCK, STATUS' )->findAll();
        $alerts    = [];

        foreach ( $items as $item ) {
            $menuId = (int) $item['MENU_ID'];
            $stock  = (int) $item['STOCK'];
            $state  = null;

            if ( $item['STATUS'] === 'Available' ) {
                if ( $stock <= 0 )              { $state = 'out'; }
                elseif ( $stock <= $threshold ) { $state = 'low'; }
            }

            // Clear flags for any state the item is no longer in.
            foreach ( [ 'low', 'out' ] as $s ) {
                if ( $s !== $state ) {
                    cache()->delete( 'stock_read_' . "stock-{$menuId}-{$s}" );
                    cache()->delete( 'stock_time_' . "stock-{$menuId}-{$s}" );
                }
            }

            if ( $state === null ) { continue; }

            $id   = "stock-{$menuId}-{$state}";
            $time = cache( 'stock_time_' . $id );
            if ( ! $time ) {
                $time = (int) round( microtime( true ) * 1000 ); // ms, for JS
                cache()->save( 'stock_time_' . $id, $time, self::TTL );
            }

            $name = mb_substr( (string) $item['NAME'], 0, 150 );

            $alerts[] = [
                'id'      => $id,
                'type'    => 'stock',
                'title'   => $state === 'out' ? 'Sold out' : 'Low stock',
                'message' => $state === 'out'
                    ? $name . ' is sold out. Add stock so customers can order it again.'
                    : 'Only ' . $stock . ' left of ' . $name . '.',
                'href'    => 'menus', // relative to /admin/, like the order notifications
                'read'    => (bool) cache( 'stock_read_' . $id ),
                'time'    => (int) $time,
            ];
        }

        return $alerts;
    }

    public function markStockRead( string $id ): void
    {
        cache()->save( 'stock_read_' . $id, 1, self::TTL );
    }

    public function markAllStockRead(): void
    {
        foreach ( $this->stockAlerts() as $a ) {
            $this->markStockRead( $a['id'] );
        }
    }

    /* ----------------------------- CUSTOMER ---------------------------- */

    /** Push a notification to ONE customer's bell. $href is relative to /customer/ (e.g. 'orders'). */
    public function notifyCustomer( int $userId, string $type, string $title, string $message, ?string $href = 'orders' ): void
    {
        if ( $userId < 1 ) { return; }
        $this->push( self::CUSTOMER_KEY . $userId, $type, $title, $message, $href );
    }

    public function allForCustomer( int $userId ): array
    {
        return $this->load( self::CUSTOMER_KEY . $userId );
    }

    public function markReadForCustomer( int $userId, string $id ): void
    {
        $this->markReadIn( self::CUSTOMER_KEY . $userId, $id );
    }

    public function markAllReadForCustomer( int $userId ): void
    {
        $this->markAllReadIn( self::CUSTOMER_KEY . $userId );
    }

    /* ----------------------------- internals --------------------------- */

    private function push( string $key, string $type, string $title, string $message, ?string $href ): void
    {
        $list = $this->load( $key );

        array_unshift( $list, [
            'id'      => bin2hex( random_bytes( 6 ) ),
            'type'    => $type,
            'title'   => mb_substr( $title, 0, 120 ),
            'message' => mb_substr( $message, 0, 255 ),
            'href'    => $href,
            'read'    => false,
            'time'    => (int) round( microtime( true ) * 1000 ), // ms, for JS
        ] );

        $this->save( $key, array_slice( $list, 0, self::MAX_ITEMS ) );
    }

    private function load( string $key ): array
    {
        $list = cache( $key );
        return is_array( $list ) ? $list : [];
    }

    private function markReadIn( string $key, string $id ): void
    {
        $list = $this->load( $key );
        foreach ( $list as &$n ) {
            if ( $n['id'] === $id ) { $n['read'] = true; }
        }
        unset( $n );
        $this->save( $key, $list );
    }

    private function markAllReadIn( string $key ): void
    {
        $list = $this->load( $key );
        foreach ( $list as &$n ) { $n['read'] = true; }
        unset( $n );
        $this->save( $key, $list );
    }

    private function save( string $key, array $list ): void
    {
        cache()->save( $key, $list, self::TTL );
    }
}