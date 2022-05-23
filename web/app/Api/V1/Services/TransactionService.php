<?php

namespace App\Api\V1\Services;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class TransactionService
{
    protected $model;

    public function __construct()
    {
        $this->model = new Transaction();
    }

    public function store($params)
    {
        $transaction = $this->model::create([
            'payment_type_id' => $params['payment_type_id'],
            'payment_system' => 1,
            'wallet_address' => $params['wallet_address'],
            'total_amount' => $params['investment_amount'],
            'order_id' => $params['order_id'],
            'user_id' => Auth::user()->getAuthIdentifier()
        ]);

        return $transaction;
    }
}
