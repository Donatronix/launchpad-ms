<?php

namespace App\Http\Controllers;

use App\Api\V1\Services\TransactionService;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class TransactionController extends Controller
{

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token_stage' => 'required|string',
            'currency_code' => 'required|string',
            'transaction_gateway' => 'required|string',
            'amount_received' => 'required|string',
            'transaction_date' => 'required|date|before:tomorrow',
        ]);

        if ($validator->fails()) {
            return response()->jsonApi([
                'type' => 'success',
                'title' => 'New Transaction created',
                'message' => "New Transaction has been added successfully",
                'data' => $validator->errors()->toArray()
            ], 200);
        }
        // create new transaction
        $paramsTransactions = $request->all();
        $transaction = (new TransactionService())->store($paramsTransactions);

        // Return response to client
        return response()->jsonApi([
            'type' => 'success',
            'title' => 'New Transaction created',
            'message' => "New Transaction has been added successfully",
            'data' => $transaction->toArray()
        ], 200);
    }

}
