<?php

namespace App\Api\V1\Services;
use App\Models\Transaction;

class TransactionService
{
    protected $model = Transaction::class;

    public function __construct(Transaction $model)
    {
        $this->model = $model;
    }

    public function store($params, $auth)
    {
        $transaction = $this->model::create([
            'payment_type_id' => $params['payment_type_id'],
            'payment_system' => $params['payment_system'],
            'wallet_address' => $params['wallet_address'],
            'total_amount' => $params['total_amount'],
            'order_id' => $params['order_id'],
            'user_id' => $auth::user()->getAuthIdentifier()
        ]);
    }
}
