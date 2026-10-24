<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\AddressModel;
use App\Models\PriceHistoryModel;

class AddressController extends BaseController {

    // List all menu items -> admin/menus page

    public function index() {

        $fetchAllBarbequeInfo = new BBQModel(); // connection for model
        $data['barbeque_list'] = $fetchAllBarbequeInfo->findAll(); // SELECT * FROM menu;

        $priceHistory = new PriceHistoryModel();
        $data['price_history_list'] = $priceHistory->getHistoryWithItemNames(); // SELECT * FROM price_history JOIN menu ...

        //var_dump($data);
        return view( 'html1/admin/menus.php', $data );

    }



    // Insert function to store a new menu item in the database

    public function store() {

    //var_dump($this->request->getPost());
        $insert_menu = new BBQModel();

        $image = null; // to prevent undefined variable error

        if ( $img = $this->request->getFile( 'menuPicture' ) ) {
            if ( $img->isValid() && !$img->hasMoved() ) {
                $image = $img->getRandomName();
                $img->move( 'uploads/', $image );
            }
        }

        $data = array(
            'NAME'           => $this->request->getPost( 'menuName' ),
            'DESCRIPTION'    => $this->request->getPost( 'menuDescription' ),
            'PRICE'          => $this->request->getPost( 'menuPrice' ),
            'MENU_CATEGORY'  => $this->request->getPost( 'menuCategory' ),
            'STOCK'          => $this->request->getPost( 'menuStock' ),
            'STATUS'         => $this->request->getPost( 'menuAvailable' ) ? 'Available' : 'Unavailable',
            'IMAGE'          => $image, // if no image uploaded this will be null
            'DATE_ADDED'     => date( 'Y-m-d H:i:s' ),
        );



        $insert_menu->insert( $data );

        return redirect()->to( '/admin/menus' )->with( 'success', 'Menu item added successfully!' );

    }



    // Edit function to update a menu item in the database

    public function update( $id ) {

        $updateMenuInfo = new BBQModel();

        // Grab the item as it stands right now so we can tell
        // whether this update actually changes the price.
        $existingItem = $updateMenuInfo->find( $id );

        $data = array(
            'NAME'          => $this->request->getPost( 'menuName' ),
            'DESCRIPTION'   => $this->request->getPost( 'menuDescription' ),
            'PRICE'         => $this->request->getPost( 'menuPrice' ),
            'MENU_CATEGORY' => $this->request->getPost( 'menuCategory' ),
            'STOCK'         => $this->request->getPost( 'menuStock' ),
            'STATUS'        => $this->request->getPost( 'menuAvailable' ) ? 'Available' : 'Unavailable',
        );

        if ( $img = $this->request->getFile( 'menuPicture' ) ) {
            if ( $img->isValid() && !$img->hasMoved() ) {
                $data['IMAGE'] = $img->getRandomName();
                $img->move( 'uploads/', $data['IMAGE'] );
            }
        }

        $updateMenuInfo->update( $id, $data );

        // Record the change in price_history so it shows up on the
        // "Price History" tab. Only log it when the price actually
        // moved -- editing stock/description/etc shouldn't create
        // a price-history entry.
        if ( $existingItem && array_key_exists( 'PRICE', $data ) && $data['PRICE'] !== null
            && (float) $existingItem['PRICE'] !== (float) $data['PRICE'] ) {

            $priceHistory = new PriceHistoryModel();
            $priceHistory->insert( [
                'MENU_ID'    => $id,
                'OLD_PRICE'  => $existingItem['PRICE'],
                'NEW_PRICE'  => $data['PRICE'],
                'UPDATED_AT' => date( 'Y-m-d H:i:s' ),
            ] );
        }

        return redirect()->to( '/admin/menus' )->with( 'success', 'Menu Item Updated Successfully' );

    }





}