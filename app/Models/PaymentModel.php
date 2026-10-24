<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentModel extends Model
{
    protected $table            = 'payment';
    protected $primaryKey       = 'PAYMENT_ID';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['PAYMENT_ID', 'ORDER_ID', 'PAYMENT_METHOD', 'PAYMENT_DATE', 'REFERENCE_NUMBER', 'PROOF_OF_PAYMENT',
                                'PAYMENT_REQUEST_AMOUNT', 'ADD_PAYMENT_REFERENCE', 'ADD_PAYMENT_PROOF'];

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
     * The ORIGINAL (checkout) payment for an order, or null.
     * An order can have several payment rows (the checkout payment plus
     * any the customer added later), so the order is explicit.
     */
    public function getPaymentForOrder( int $orderId ): ?array
    {
        return $this->where( 'ORDER_ID', $orderId )
                    ->orderBy( 'PAYMENT_ID', 'ASC' )
                    ->first();
    }

    /** Every payment on an order, oldest first (checkout payment, then added ones). */
    public function getPaymentsForOrder( int $orderId ): array
    {
        return $this->where( 'ORDER_ID', $orderId )
                    ->orderBy( 'PAYMENT_ID', 'ASC' )
                    ->findAll();
    }

    /**
     * The admin submits a payment on an order: the refund they sent back to the
     * customer. Like the customer's added payments, it is a NEW payment row for
     * the same ORDER_ID (PAYMENT_METHOD = 'Refund') using the existing payment
     * columns, so an order's payments read: checkout payment, any the customer
     * added, then the refund.
     * $refund = ['reference' => string, 'proof' => stored file name]
     */
    public function saveRefund( int $orderId, array $refund ): bool
    {
        return $this->insert( [
            'ORDER_ID'         => $orderId,
            'PAYMENT_METHOD'   => 'Refund',
            'PAYMENT_DATE'     => date( 'Y-m-d H:i:s' ),
            'REFERENCE_NUMBER' => $refund['reference'],
            'PROOF_OF_PAYMENT' => $refund['proof'],
        ] ) !== false;
    }

    /** The latest refund payment row on an order, or null if the admin hasn't refunded it. */
    public function getRefundForOrder( int $orderId ): ?array
    {
        return $this->where( 'ORDER_ID', $orderId )
                    ->where( 'PAYMENT_METHOD', 'Refund' )
                    ->orderBy( 'PAYMENT_ID', 'DESC' )
                    ->first();
    }
}