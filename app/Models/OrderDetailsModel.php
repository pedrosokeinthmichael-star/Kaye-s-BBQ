<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderDetailsModel extends Model
{
    protected $table            = 'order_item';
    protected $primaryKey       = 'ORDER_ITEM_ID';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['ORDER_ID', 'MENU_ID', 'QUANTITY', 'UNIT_PRICE'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * The line items that belong to one placed order, joined with
     * menu_item for display. Handy for an order-detail/receipt view.
     *
     * There is no SUB_TOTAL column; UNIT_PRICE is the price per unit
     * at the time of ordering, so SUB_TOTAL is QUANTITY * UNIT_PRICE.
     *
     * A customer's cart is simply their `orders` row with
     * ORDER_STATUS = 'Cart' — its order_item rows are the cart lines.
     */
    public function getItemsForOrder( int $orderId ): array
    {
        return $this->select( 'order_item.*, menu_item.NAME, menu_item.PRICE, menu_item.IMAGE, ' .
                              '(order_item.QUANTITY * order_item.UNIT_PRICE) AS SUB_TOTAL', false )
                    ->join( 'menu_item', 'menu_item.MENU_ID = order_item.MENU_ID' )
                    ->where( 'order_item.ORDER_ID', $orderId )
                    ->orderBy( 'order_item.ORDER_ITEM_ID', 'ASC' )
                    ->findAll();
    }

    /**
     * The customer's live cart: order_item rows of their `orders` row
     * whose ORDER_STATUS is 'Cart'. Prices are the CURRENT menu price
     * (the cart isn't a placed order yet). Empty array if no cart.
     */
    public function getCartForUser( int $userId ): array
    {
        return $this->select( 'order_item.*, order_item.ORDER_ITEM_ID AS ORDER_DETAIL_ID, ' .
                              'menu_item.NAME, menu_item.IMAGE, menu_item.PRICE, menu_item.STOCK, ' .
                              '(order_item.QUANTITY * menu_item.PRICE) AS SUB_TOTAL', false )
                    ->join( 'orders', 'orders.ORDER_ID = order_item.ORDER_ID' )
                    ->join( 'menu_item', 'menu_item.MENU_ID = order_item.MENU_ID' )
                    ->where( 'orders.USER_ID', $userId )
                    ->where( 'orders.ORDER_STATUS', 'Cart' )
                    ->orderBy( 'order_item.ORDER_ITEM_ID', 'ASC' )
                    ->findAll();
    }

    /** The cart line for one menu item in the user's cart, or null. */
    public function findCartLine( int $userId, int $menuId ): ?array
    {
        return $this->select( 'order_item.*' )
                    ->join( 'orders', 'orders.ORDER_ID = order_item.ORDER_ID' )
                    ->where( 'orders.USER_ID', $userId )
                    ->where( 'orders.ORDER_STATUS', 'Cart' )
                    ->where( 'order_item.MENU_ID', $menuId )
                    ->first();
    }

    /** One cart line by its ORDER_ITEM_ID, only if it is in this user's cart. */
    public function findCartLineById( int $userId, int $orderItemId ): ?array
    {
        return $this->select( 'order_item.*' )
                    ->join( 'orders', 'orders.ORDER_ID = order_item.ORDER_ID' )
                    ->where( 'orders.USER_ID', $userId )
                    ->where( 'orders.ORDER_STATUS', 'Cart' )
                    ->where( 'order_item.ORDER_ITEM_ID', $orderItemId )
                    ->first();
    }
}