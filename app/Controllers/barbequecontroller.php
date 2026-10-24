<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\BBQModel;
use App\Models\PriceHistoryModel;
use App\Models\InventoryModel;

class BarbequeController extends BaseController {

    // List all menu items -> admin/menus page

    public function index() {

        $fetchAllBarbequeInfo = new BBQModel(); // connection for model
        $data['barbeque_list'] = $fetchAllBarbequeInfo->getAllWithStock(); // menu_item JOIN menu_category

        $priceHistory = new PriceHistoryModel();
        $data['price_history_list'] = $priceHistory->getHistoryWithItemNames(); // SELECT * FROM price_history JOIN menu ...

        $inventory = new InventoryModel();
        $data['inventory_list'] = $inventory->getHistoryWithItemNames(); // SELECT * FROM inventory JOIN menu ...

        // Categories for the Add Item modal's dropdown.
        $data['category_list'] = \Config\Database::connect()
            ->table( 'menu_category' )
            ->orderBy( 'CATEGORY_NAME', 'ASC' )
            ->get()->getResultArray();

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
            'DESCRIPTION'    => $this->request->getPost( 'menuDescription' ) ?? '',
            'PRICE'          => $this->request->getPost( 'menuPrice' ),
            'CATEGORY_ID'    => $this->resolveCategoryId( $this->request->getPost( 'menuCategory' ) ),
            'STOCK'          => (int) $this->request->getPost( 'menuStock' ),
            'STATUS'         => $this->request->getPost( 'menuAvailable' ) ? 'Available' : 'Unavailable',
            'IMAGE'          => $image ?? '', // IMAGE is NOT NULL, so '' when no image uploaded
            'DATE_ADDED'     => date( 'Y-m-d H:i:s' ),
        );



        $insert_menu->insert( $data );

        // Log the starting stock in the inventory table too, so a brand
        // new item shows up on the "Inventory" tab from day one.
        $newMenuId = $insert_menu->getInsertID();
        $startingStock = (int) ( $data['STOCK'] ?? 0 );

        if ( $newMenuId && $startingStock > 0 ) {
            ( new InventoryModel() )->insert( [
                'MENU_ID'     => $newMenuId,
                'ADDED_STOCK' => $startingStock,
                'UPDATED_AT'  => date( 'Y-m-d H:i:s' ),
            ] );
        }

        return redirect()->to( '/admin/menus' )->with( 'success', 'Menu item added successfully!' );

    }



    // Edit function to update a menu item in the database

    public function update( $id ) {

        $updateMenuInfo = new BBQModel();

        // Grab the item as it stands right now so we can tell
        // whether this update actually changes anything worth logging.
        $existingItem = $updateMenuInfo->find( $id );

        $formAction = $this->request->getPost( 'formAction' );

        // "Add Stock" modal -- only ever adds to the existing stock,
        // it never lets you type an absolute stock number.
        if ( $formAction === 'add_stock' ) {

            $addedStock   = (int) $this->request->getPost( 'menuAddStock' );
            $currentStock = (int) ( $existingItem['STOCK'] ?? 0 );
            $newStock     = $currentStock + $addedStock;

            $updateMenuInfo->update( $id, [ 'STOCK' => $newStock ] );

            if ( $existingItem && $addedStock !== 0 ) {
                $inventory = new InventoryModel();
                $inventory->insert( [
                    'MENU_ID'     => $id,
                    'ADDED_STOCK' => $addedStock,
                    'UPDATED_AT'  => date( 'Y-m-d H:i:s' ),
                ] );
            }

            return redirect()->to( '/admin/menus' )->with( 'success', 'Stock updated successfully!' );
        }

        // "Update Price" modal -- price and availability only.
        if ( $formAction === 'update_price' ) {

            $data = array(
                'PRICE'  => $this->request->getPost( 'menuPrice' ),
                'STATUS' => $this->request->getPost( 'menuAvailable' ) ? 'Available' : 'Unavailable',
            );

            $updateMenuInfo->update( $id, $data );

            // Record the change in price_history so it shows up on the
            // "Price History" tab. Only log it when the price actually
            // moved.
            if ( $existingItem && array_key_exists( 'PRICE', $data ) && $data['PRICE'] !== null
                && (float) $existingItem['PRICE'] !== (float) $data['PRICE'] ) {

                $priceHistory = new PriceHistoryModel();
                $priceHistory->insert( [
                    'MENU_ID'    => $id,
                    'OLD_PRICE'  => $existingItem['PRICE'],
                    
                    'UPDATED_AT' => date( 'Y-m-d H:i:s' ),
                ] );
            }

            return redirect()->to( '/admin/menus' )->with( 'success', 'Price updated successfully!' );
        }

        // Unknown / missing formAction -- nothing to safely do.
        return redirect()->to( '/admin/menus' );

    }

    /**
     * menu_item stores CATEGORY_ID (FK -> menu_category), not a name.
     * The form's "menuCategory" may be a category ID or a category
     * name: an ID/name that exists is used as-is, a new name creates
     * a menu_category row, and a blank value falls back to the first
     * category.
     */
    private function resolveCategoryId( $value ): int
    {
        $db    = \Config\Database::connect();
        $value = trim( (string) $value );

        if ( $value !== '' && ctype_digit( $value ) ) {
            $row = $db->table( 'menu_category' )->where( 'CATEGORY_ID', (int) $value )->get()->getRowArray();
            if ( $row ) {
                return (int) $row['CATEGORY_ID'];
            }
        }

        if ( $value !== '' ) {
            $value = mb_substr( $value, 0, 30 ); // CATEGORY_NAME is varchar(30)
            $row   = $db->table( 'menu_category' )->where( 'CATEGORY_NAME', $value )->get()->getRowArray();
            if ( $row ) {
                return (int) $row['CATEGORY_ID'];
            }
            $db->table( 'menu_category' )->insert( [ 'CATEGORY_NAME' => $value ] );
            return (int) $db->insertID();
        }

        $row = $db->table( 'menu_category' )->orderBy( 'CATEGORY_ID', 'ASC' )->get( 1 )->getRowArray();
        return (int) ( $row['CATEGORY_ID'] ?? 0 );
    }

}