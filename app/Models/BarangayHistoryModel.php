<?php

namespace App\Models;

use CodeIgniter\Model;

class BarangayHistoryModel extends Model
{
    protected $table            = 'delivery_fee';
    protected $primaryKey       = 'FEE_HISTORY_ID';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['BARANGAY_ID', 'OLD_FEE', 'NEW_FEE', 'UPDATED_AT'];

    protected $useTimestamps = false;

    // Fee changes with the barangay's name, newest first.
    public function getHistory(): array
    {
        return $this->select( 'delivery_fee_history.*, barangay.BARANGAY_NAME' )
            ->join( 'barangay', 'barangay.BARANGAY_ID = delivery_fee_history.BARANGAY_ID', 'left' )
            ->orderBy( 'delivery_fee_history.UPDATED_AT', 'DESC' )
            ->orderBy( 'delivery_fee_history.FEE_HISTORY_ID', 'DESC' )
            ->findAll();
    }
}