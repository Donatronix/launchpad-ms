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
            'wallet_address' => $params['wallet_address'],
            'total_amount' => $params['investment_amount'],
            'order_id' => $params['order_id'],
            'user_id' => Auth::user()->getAuthIdentifier(),
            'credit_card_type_id' => $params['credit_card_type_id'] ?? 0
        ]);

        return $transaction;
    }

    public function getOne($transaction_id)
    {
        return $this->model::findOrFail($transaction_id);
    }
}
