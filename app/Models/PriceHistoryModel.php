<?php

namespace App\Models;

use CodeIgniter\Model;

class PriceHistoryModel extends Model
{
    protected $table            = 'menu_price';
    protected $primaryKey       = 'PRICE_HISTORY_ID';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['PRICE_HISTORY_ID', 'MENU_ID', 'OLD_PRICE', 'UPDATED_AT'];

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
     * All price changes, newest first, with the item's current
     * name/category joined in so the admin "Price History" tab
     * doesn't need a second query per row.
     */
    public function getHistoryWithItemNames(): array
    {
        $rows = $this->select('menu_price_history.*, menu_item.NAME AS ITEM_NAME, menu_item.PRICE AS CURRENT_PRICE, menu_category.CATEGORY_NAME AS MENU_CATEGORY')
                     ->join('menu_item', 'menu_item.MENU_ID = menu_price_history.MENU_ID', 'left')
                     ->join('menu_category', 'menu_category.CATEGORY_ID = menu_item.CATEGORY_ID', 'left')
                     ->orderBy('menu_price_history.PRICE_HISTORY_ID', 'DESC')
                     ->findAll();

        $newPrice = []; // the price each item changed TO, tracked per item

        foreach ( $rows as &$row ) {
            $id = $row['MENU_ID'];

            // Newest row of an item uses the current price; older rows use
            // the old price of the change that came after them.
            $row['PRICE'] = $newPrice[$id] ?? $row['CURRENT_PRICE'];

            $newPrice[$id] = $row['OLD_PRICE'];
        }
        unset( $row );

        return $rows;
    }
}