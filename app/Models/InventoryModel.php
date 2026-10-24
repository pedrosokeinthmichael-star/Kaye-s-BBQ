<?php

namespace App\Models;

use CodeIgniter\Model;

class InventoryModel extends Model
{
    protected $table            = 'stock';
    protected $primaryKey       = 'STOCK_HISTORY_ID';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['MENU_ID', 'ADDED_STOCK', 'UPDATED_AT'];

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
     * Every stock change joined against the menu item it belongs to,
     * most recent first. Mirrors PriceHistoryModel::getHistoryWithItemNames().
     */
    public function getHistoryWithItemNames()
    {
        return $this->select( 'stock_history.*, menu_item.NAME as ITEM_NAME, menu_category.CATEGORY_NAME as MENU_CATEGORY' )
                     ->join( 'menu_item', 'menu_item.MENU_ID = stock_history.MENU_ID', 'left' )
                     ->join( 'menu_category', 'menu_category.CATEGORY_ID = menu_item.CATEGORY_ID', 'left' )
                     ->orderBy( 'stock_history.UPDATED_AT', 'DESC' )
                     ->findAll();
    }
}