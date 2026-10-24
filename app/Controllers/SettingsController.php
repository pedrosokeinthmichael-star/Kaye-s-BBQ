<?php

namespace App\Controllers;

use App\Models\ShopSettingModel;

class SettingsController extends BaseController
{
    // POST /admin/settings/update   (setting = delivery|gcash, enabled = 1|0)
    // Called by js/admin/order_settings.js when the admin flips a switch.
    public function update()
    {
        $map = [
            'delivery' => 'delivery_enabled',
            'gcash'    => 'gcash_enabled',
        ];

        $setting = (string) $this->request->getPost( 'setting' );
        $enabled = $this->request->getPost( 'enabled' ) === '1';

        if ( ! isset( $map[ $setting ] ) ) {
            return $this->response->setStatusCode( 400 )->setJSON( [
                'success' => false,
                'message' => 'Unknown setting.',
                'csrf'    => csrf_hash(),
            ] );
        }

        $ok = ( new ShopSettingModel() )->set( $map[ $setting ], $enabled );

        return $this->response->setJSON( [
            'success' => $ok,
            'enabled' => $enabled,
            'csrf'    => csrf_hash(), // CI rotates the token; the JS swaps in the new one
        ] );
    }
}