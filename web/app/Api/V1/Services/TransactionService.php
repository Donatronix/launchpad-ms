<?php

namespace App\Api\V1\Services;

use App\Models\Price;
use App\Models\Transaction;
use Exception;
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
        $total_amount = "";
        $bonus = 0;

        if ($params['bonus_adjusted'] && $params['amount_received'] > 1000) {
            $tokenBonus = Price::where('stage', $params['token_stage'])->get('price')->first();
            $stage_amount = $params['amount_received'] * $tokenBonus->price;
            $sol = $stage_amount / 10;

            if ($sol >= 5 && $sol <= 1000) {
                $total_amount = ($params['amount_received'] * 0.05) + $params['amount_received'];
                $bonus = 0.05;
            } else if ($sol > 1000 && $sol <= 10000) {
                $total_amount = ($params['amount_received'] * 0.10) + $params['amount_received'];
                $bonus = 0.10;
            } else if ($sol > 10000 && $sol <= 100000) {
                $total_amount = ($params['amount_received'] * 0.20) + $params['amount_received'];
                $bonus = 0.20;
            } else if ($sol > 100000 && $sol <= 500000) {
                $total_amount = ($params['amount_received'] * 0.30) + $params['amount_received'];
                $bonus = 0.30;
            } else if ($sol > 500000 && $sol <= 1000000) {
                $total_amount = ($params['amount_received'] * 0.40) + $params['amount_received'];
                $bonus = 0.40;
            } else if ($sol > 1000000) {
                $total_amount = ($params['amount_received'] * 0.50) + $params['amount_received'];
                $bonus = 0.50;
            }
        } else {
            $tokenBonus = Price::where('stage', $params['token_stage'])->get('price')->first();
            $stage_amount = $params['amount_received'] * $tokenBonus->price;
            $sol = $stage_amount / 10;
            $total_amount = $params['amount_received'];
        }

        try {
            $transaction = $this->model::create([
                'payment_type_id' => $params['transaction_type_id'],
                'payment_date' => $params['transaction_date'],
                'total_amount' => $total_amount,
                'wallet_address' => $params['wallet_address'],
                'admin_id' => Auth::user()->getAuthIdentifier(), #authenticated admin
                'user_id' => $params['user_id'],
                'payment_token' => $params['transaction_token'],
                'payment_gateway' => $params['transaction_gateway'],
                'currency_code' => $params['currency_code'],
                'token_stage' => $params['token_stage'],
                'amount_received' => $params['amount_received'],
                'bonus' => $bonus,
                'sol_received' => $sol,
            ]);

            return $transaction;
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => "Error adding transactions",
                'message' => $e->getMessage->toArray(),
            ], 404);
        }
    }

    public function getOne($transaction_id)
    {
        return $this->model::findOrFail($transaction_id);
    }
}
