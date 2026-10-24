<?php

namespace App\Models;

/**
 * Shop-wide on/off switches set by the admin on Orders > Order Settings:
 *
 *   delivery_enabled  - customers may choose "Delivery" at checkout
 *   gcash_enabled     - customers may pay with GCash at checkout
 *
 * Stored as JSON in writable/shop_settings.json, so no database table or
 * migration is needed. Both switches default to ON until the admin turns
 * one off. Unlike the cache, this file is not wiped by a cache clear.
 */
class ShopSettingModel
{
    private const DEFAULTS = [
        'delivery_enabled' => true,
        'gcash_enabled'    => true,
    ];

    private function path(): string
    {
        return WRITEPATH . 'shop_settings.json';
    }

    /** Raw switches: [ 'delivery_enabled' => bool, 'gcash_enabled' => bool ]. */
    public function all(): array
    {
        $saved = [];
        if ( is_file( $this->path() ) ) {
            $json = json_decode( (string) file_get_contents( $this->path() ), true );
            if ( is_array( $json ) ) { $saved = $json; }
        }

        $out = [];
        foreach ( self::DEFAULTS as $key => $default ) {
            $out[ $key ] = array_key_exists( $key, $saved ) ? (bool) $saved[ $key ] : $default;
        }
        return $out;
    }

    public function set( string $key, bool $value ): bool
    {
        if ( ! array_key_exists( $key, self::DEFAULTS ) ) { return false; }

        $all         = $this->all();
        $all[ $key ] = $value;

        return file_put_contents( $this->path(), json_encode( $all ), LOCK_EX ) !== false;
    }

    public function deliveryEnabled(): bool
    {
        return $this->all()['delivery_enabled'];
    }

    /** GCash is only offered when the switch is on AND an active GCash account exists. */
    public function gcashAvailable(): bool
    {
        return $this->all()['gcash_enabled'] && ! empty( ( new GcashModel() )->getCurrent() );
    }

    /**
     * Server-side check for checkout. Returns an error message when the chosen
     * order type / payment method is switched off, or null when it's fine.
     * Call it from OrderController::placeOrder() (see the notes in the reply).
     */
    public function checkoutError( string $orderType, string $paymentMethod ): ?string
    {
        if ( stripos( $orderType, 'deliver' ) !== false && ! $this->deliveryEnabled() ) {
            return 'Delivery is currently unavailable. Please choose Pickup.';
        }
        if ( stripos( $paymentMethod, 'gcash' ) !== false && ! $this->gcashAvailable() ) {
            return 'GCash payments are currently unavailable. Please pay with cash.';
        }
        return null;
    }
}