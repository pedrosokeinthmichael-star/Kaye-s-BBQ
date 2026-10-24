<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\BarangayModel;
use App\Models\BarangayHistoryModel;
use App\Models\AddressModel;

class BarangayController extends BaseController
{

    // Insert a new barangay (delivery area) into the database.
    // Called with AJAX by order_settings.js (answers JSON, so the new row
    // shows up straight away); a normal form post still works and redirects.
    public function store()
    {
        $model = new BarangayModel();

        $name   = $this->cleanName( $this->request->getPost( 'barangayName' ) );
        $fee    = $this->request->getPost( 'barangayFee' );
        $active = $this->request->getPost( 'barangayActive' ) ? true : false; // unchecked box isn't submitted

        if ( $name === '' ) {
            return $this->fail( 'Enter a barangay name.' );
        }
        if ( $fee === null || $fee === '' || ! is_numeric( $fee ) || (float) $fee < 0 ) {
            return $this->fail( 'Enter a valid delivery fee.' );
        }

        // Duplicate warning (not case-sensitive, extra spaces ignored).
        $duplicate = $this->findDuplicate( $name );
        if ( $duplicate ) {
            return $this->fail( $this->duplicateMessage( $duplicate ) );
        }

        $id = $model->insert( [
            'BARANGAY_NAME'   => $name,
            'DELIVERY_FEE'    => $fee,
            'BARANGAY_STATUS' => $active ? 'Active' : 'Inactive',
            'ADDED_AT'        => date( 'Y-m-d H:i:s' ),
        ] );

        if ( $this->request->isAJAX() ) {
            return $this->response->setJSON( [
                'success'   => true,
                'message'   => 'Barangay added successfully!',
                'barangay'  => [
                    'id'     => (int) $id,
                    'name'   => $name,
                    'fee'    => (float) $fee,
                    'active' => $active,
                ],
                'csrf'      => csrf_hash(),
            ] );
        }

        return redirect()->to( '/admin/orders#order-settings' )->with( 'success', 'Barangay added successfully!' );
    }

    // Update an existing barangay's name, fee, and/or active status.
    public function update( $id )
    {
        $model = new BarangayModel();

        // Grab the current record first so we can log the old fee.
        $existing = $model->find( $id );
        $newFee   = $this->request->getPost( 'barangayFee' );
        $name     = $this->cleanName( $this->request->getPost( 'barangayName' ) );

        // Same duplicate warning as store(), ignoring the barangay being edited.
        $duplicate = $name !== '' ? $this->findDuplicate( $name, (int) $id ) : null;
        if ( $duplicate ) {
            return $this->fail( $this->duplicateMessage( $duplicate ) );
        }

        $model->update( $id, [
            'BARANGAY_NAME' => $name,
            'DELIVERY_FEE'  => $newFee,
            // Unchecked checkboxes aren't submitted, so a missing value means Inactive.
            'BARANGAY_STATUS' => $this->request->getPost( 'barangayActive' ) ? 'Active' : 'Inactive',
        ] );

        // Only log when the delivery fee actually changed.
        if ( $existing && (float) $existing['DELIVERY_FEE'] !== (float) $newFee ) {
            ( new BarangayHistoryModel() )->insert( [
                'BARANGAY_ID' => $id,
                'OLD_FEE'      => $existing['DELIVERY_FEE'],
                'NEW_FEE'      => $newFee,
                'UPDATED_AT'   => date( 'Y-m-d H:i:s' ),
            ] );
        }

        return redirect()->to( '/admin/orders#order-settings' )->with( 'success', 'Barangay updated successfully!' );
    }

    // Remove a barangay from the delivery area list.
    public function delete( $id )
    {
        // delivery_address.BARANGAY_ID is a FK into barangay, so a barangay
        // that past orders were delivered to can't be deleted — set it to
        // Inactive instead (hidden at checkout, order history stays intact).
        if ( ( new AddressModel() )->where( 'BARANGAY_ID', $id )->countAllResults() > 0 ) {
            return redirect()->to( '/admin/orders#order-settings' )
                ->with( 'error', 'This barangay has past orders, so it can\'t be deleted. Set it to Inactive instead.' );
        }

        // Its fee-change log rows reference it too (delivery_fee_history FK).
        ( new BarangayHistoryModel() )->where( 'BARANGAY_ID', $id )->delete();
        ( new BarangayModel() )->delete( $id );

        return redirect()->to( '/admin/orders#order-settings' )->with( 'success', 'Barangay removed.' );
    }

    /* ----------------------------- helpers ----------------------------- */

    /** Trim and collapse repeated spaces: "  Santo   Niño " -> "Santo Niño". */
    private function cleanName( $name ): string
    {
        return trim( preg_replace( '/\s+/u', ' ', (string) $name ) );
    }

    /** An existing barangay with the same name (ignoring case), or null. */
    private function findDuplicate( string $name, int $exceptId = 0 ): ?array
    {
        $builder = ( new BarangayModel() )->where( 'LOWER(BARANGAY_NAME) =', mb_strtolower( $name ) );
        if ( $exceptId > 0 ) {
            $builder->where( 'BARANGAY_ID !=', $exceptId );
        }
        return $builder->first();
    }

    private function duplicateMessage( array $existing ): string
    {
        $msg = '"' . $existing['BARANGAY_NAME'] . '" is already on the delivery list';
        if ( ( $existing['BARANGAY_STATUS'] ?? '' ) === 'Inactive' ) {
            $msg .= ' (currently Inactive — use Update to turn it back on)';
        }
        return $msg . '.';
    }

    /** Error answer: JSON for the AJAX form, redirect + flash message otherwise. */
    private function fail( string $message )
    {
        if ( $this->request->isAJAX() ) {
            return $this->response->setStatusCode( 422 )->setJSON( [
                'success' => false,
                'message' => $message,
                'csrf'    => csrf_hash(),
            ] );
        }
        return redirect()->to( '/admin/orders#order-settings' )->with( 'error', $message );
    }

}