<?php

namespace App\Models;

use CodeIgniter\Model;

class GcashModel extends Model
{
    protected $table            = 'company_gcash';
    protected $primaryKey       = 'GCASH_ID';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['GCASH_ID','GCASH_NAME','GCASH_NUMBER','GCASH_QR','ADDED_AT','ARCHIVED_AT'];

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

    // The single active GCash record shown to customers at checkout
    // and on the admin settings page. There should only ever be one
    // row with ARCHIVED_AT still null at a time.
    public function getCurrent()
    {
        return $this->where( 'ARCHIVED_AT', null )->orderBy( 'GCASH_ID', 'DESC' )->first();
    }

    // Previously-replaced GCash records, most recently archived first,
    // for the admin's GCash Archive tab.
    public function getArchived()
    {
        return $this->where( 'ARCHIVED_AT IS NOT NULL' )->orderBy( 'ARCHIVED_AT', 'DESC' )->findAll();
    }

    // Marks whatever record is currently active as archived, so a
    // freshly-inserted record becomes the sole current one. Called
    // right before inserting a new/replacement GCash record.
    public function archiveCurrent(): void
    {
        $this->set( [ 'ARCHIVED_AT' => date( 'Y-m-d H:i:s' ) ] )
             ->where( 'ARCHIVED_AT', null )
             ->update();
    }
}