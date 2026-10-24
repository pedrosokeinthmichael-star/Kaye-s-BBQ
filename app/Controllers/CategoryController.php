<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class CategoryController extends BaseController
{
    // POST /admin/categories/store  { categoryName }
    // Returns JSON so the Add Item modal can add the option without a reload.
    public function store()
    {
        $name = preg_replace( '/\s+/', ' ', trim( (string) $this->request->getPost( 'categoryName' ) ) );

        if ( $name === '' ) {
            return $this->fail( 'Enter a category name.' );
        }
        if ( mb_strlen( $name ) > 30 ) { // CATEGORY_NAME is varchar(30)
            return $this->fail( 'Category name must be 30 characters or fewer.' );
        }

        $db  = \Config\Database::connect();
        $row = $db->table( 'menu_category' )
                  ->where( 'LOWER(CATEGORY_NAME)', mb_strtolower( $name ) )
                  ->get()->getRowArray();

        // Already exists (case-insensitive): hand it back, no duplicate.
        if ( $row ) {
            return $this->response->setJSON( [
                'success'  => true,
                'existing' => true,
                'id'       => (int) $row['CATEGORY_ID'],
                'name'     => $row['CATEGORY_NAME'],
                'csrf'     => csrf_hash(),
            ] );
        }

        $db->table( 'menu_category' )->insert( [ 'CATEGORY_NAME' => $name ] );

        return $this->response->setJSON( [
            'success'  => true,
            'existing' => false,
            'id'       => (int) $db->insertID(),
            'name'     => $name,
            'csrf'     => csrf_hash(),
        ] );
    }

    private function fail( string $message )
    {
        return $this->response->setStatusCode( 422 )->setJSON( [
            'success' => false,
            'message' => $message,
            'csrf'    => csrf_hash(),
        ] );
    }
}