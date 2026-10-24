<?php

namespace App\Models;

use CodeIgniter\Model;

class BBQModel extends Model
{
    protected $table            = 'menu_item';
    protected $primaryKey       = 'MENU_ID';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['MENU_ID', 'CATEGORY_ID', 'NAME', 'IMAGE', 'DESCRIPTION', 'PRICE', 'STOCK', 'STATUS', 'DATE_ADDED'];

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
     * Menu items with their category name attached (menu_item only
     * stores CATEGORY_ID; the name lives in menu_category). STOCK is a
     * real column on menu_item, so it comes along with menu_item.*.
     */
    public function getAllWithStock(): array
    {
        return $this->select( 'menu_item.*, menu_category.CATEGORY_NAME AS MENU_CATEGORY' )
                    ->join( 'menu_category', 'menu_category.CATEGORY_ID = menu_item.CATEGORY_ID', 'left' )
                    ->findAll();
    }

    /** Current stock of one menu item (0 if the item doesn't exist). */
    public function getStock( int $menuId ): int
    {
        $row = $this->select( 'STOCK' )->where( 'MENU_ID', $menuId )->first();
        return (int) ( $row['STOCK'] ?? 0 );
    }
}