<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\GcashModel;

class GcashController extends BaseController
{

    // Save the admin's GCash payment details (name, number, QR code)
    // for the first time. If a GCash record is already active for
    // some reason, it's archived first so this becomes the sole
    // current one.
    public function store()
    {
        $model = new GcashModel();

        $qr = null;
        if ( $img = $this->request->getFile( 'gcashQr' ) ) {
            if ( $img->isValid() && !$img->hasMoved() ) {
                $qr = $img->getRandomName();
                $img->move( 'uploads/', $qr );
            }
        }

        $model->archiveCurrent();

        $model->insert( [
            'GCASH_NAME'   => $this->request->getPost( 'gcashName' ),
            'GCASH_NUMBER' => $this->request->getPost( 'gcashNumber' ),
            'GCASH_QR'     => $qr ?? '', // GCASH_QR is NOT NULL
            'ADDED_AT'     => date( 'Y-m-d H:i:s' ),
            'ARCHIVED_AT'  => null,
        ] );

        return redirect()->to( '/admin/orders#order-settings' )->with( 'success', 'GCash details added successfully!' );
    }

    // Replace the current GCash details with a new set. Rather than
    // editing the row in place, the record being replaced ($id) is
    // archived — it stays visible in the GCash Archive tab — and the
    // submitted details are inserted as the new current record.
    public function update( $id )
    {
        $model = new GcashModel();
        $existing = $model->find( $id );

        $qr = null;
        if ( $img = $this->request->getFile( 'gcashQr' ) ) {
            if ( $img->isValid() && !$img->hasMoved() ) {
                $qr = $img->getRandomName();
                $img->move( 'uploads/', $qr );
            }
        }

        $model->archiveCurrent();

        $model->insert( [
            'GCASH_NAME'   => $this->request->getPost( 'gcashName' ),
            'GCASH_NUMBER' => $this->request->getPost( 'gcashNumber' ),
            // Keep the previous QR if no new one was uploaded, so
            // checkout still has a QR code to show.
            'GCASH_QR'     => $qr ?? ( $existing['GCASH_QR'] ?? '' ),
            'ADDED_AT'     => date( 'Y-m-d H:i:s' ),
            'ARCHIVED_AT'  => null,
        ] );

        return redirect()->to( '/admin/orders#order-settings' )->with( 'success', 'GCash details updated successfully!' );
    }

    // Remove a GCash record entirely.
    public function delete( $id )
    {
        ( new GcashModel() )->delete( $id );

        return redirect()->to( '/admin/orders#order-settings' )->with( 'success', 'GCash details removed.' );
    }

}