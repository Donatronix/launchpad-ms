<?php

namespace App\Api\V1\Controllers\Admin;

use App\Api\V1\Controllers\Controller;
use App\Api\V1\Services\TransactionService;
use App\Models\Transaction;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Class TransactionController
 *
 * @package App\Api\V1\Controllers\Admin
 */
class TransactionController extends Controller
{
    /**
     * Method for list of user's transaction.
     *
     * @OA\Get(
     *     path="/admin/transactions",
     *     description="Get list of user's transaction",
     *     tags={"Admin | Transactions"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Transaction status (pending, approved, bonuses, canceled)",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         description="Count of transactions in one page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=20
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         description="Page of list",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=1,
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Data fetched",
     *         @OA\JsonContent(ref="#/components/schemas/OkResponse")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error",
     *         @OA\JsonContent(ref="#/components/schemas/DangerResponse")
     *     )
     * )
     *
     * @param Request $request
     *
     * @throws Exception
     */
    public function index(Request $request)
    {
        try {
            // Validate status if need
            $this->validate($request, [
                'status' => [
                    'sometimes',
                    Rule::in(['pending', 'approved', 'bonuses', 'canceled']),
                ]
            ]);

            $result = Transaction::query()
                ->when($request->has('status'), function ($q) use ($request) {
                    $status = "STATUS_" . mb_strtoupper($request->get('status'));

                    return $q->where('status', intval(constant("App\Models\Transaction::{$status}")));
                })
                ->paginate($request->get('limit', config('settings.pagination_limit')));

            // Return response
            return response()->jsonApi([
                'title' => 'Transactions list',
                'message' => 'Transaction list received',
                'data' => $result
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Transactions list',
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }

    /**
     *  Store transaction data manually
     *
     * @OA\Post(
     *     path="/admin/transactions",
     *     description="Get all loans by filter",
     *     tags={"Admin | Transactions"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\Parameter(
     *         name="transaction_type",
     *         in="query",
     *         description="Transaction type",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="transaction_date",
     *         in="query",
     *         description="Transaction date",
     *         required=false,
     *         @OA\Schema(
     *             type="date"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="amount_received",
     *         in="query",
     *         description="Amount Invested",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *    @OA\Parameter(
     *         name="wallet_address",
     *         in="query",
     *         description="Payment address",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="transaction_token",
     *         in="query",
     *         description="Payment token",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="wallet_address",
     *         in="query",
     *         description="Payment address",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="transaction_gateway",
     *         in="query",
     *         description="transaction gateway (UTTA)",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *    @OA\Parameter(
     *         name="currency_code",
     *         in="query",
     *         description="Currency ($, €, £)",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="Transaction Owner's id (added automatically)",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="token_stage",
     *         in="query",
     *         description="reward stage",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Data fetched",
     *         @OA\JsonContent(ref="#/components/schemas/OkResponse")
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error",
     *         @OA\JsonContent(ref="#/components/schemas/DangerResponse")
     *     ),
     *     @OA\Response(
     *         response="403",
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(ref="#/components/schemas/WarningResponse")
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     */
    public function store(Request $request)
    {
        // Do validate input data
        $validator = Validator::make($request->all(), [
            'token_stage' => 'required|string',
            'currency_code' => 'required|string',
            'transaction_gateway' => 'required|string',
            'amount_received' => 'required|string',
            'transaction_date' => 'required|date|before:tomorrow',
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->jsonApi([
                'title' => 'Transaction data',
                'message' => 'Validation error: ' . $validator->errors()->toArray(),
            ], 400);
        }

        // Create a new transaction
        try {
            $paramsTransactions = $request->all();
            $transaction = (new TransactionService())->store($paramsTransactions);

            // Return response to client
            return response()->jsonApi([
                'title' => 'New Transaction created',
                'message' => "New Transaction has been added successfully",
                'data' => $transaction->toArray()
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Transaction data',
                'message' => 'Create new transaction data error: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Method for show user's transaction
     *
     * @OA\Get(
     *     path="/admin/transactions/{transaction_id}",
     *     description="Get transaction of user by transaction_id",
     *     tags={"Admin | Transactions"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\Parameter(
     *         name="transaction_id",
     *         description="Transaction ID",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Data fetched",
     *         @OA\JsonContent(ref="#/components/schemas/OkResponse")
     *     ),
     * )
     *
     * @param $transaction_id
     *
     */
    public function show($transaction_id)
    {
        try {
            $transaction = Transaction::find($transaction_id);

            if (!$transaction) {
                return response()->jsonApi([
                    'title' => 'Transaction data',
                    'message' => 'No transaction data with id ' . $transaction_id,
                ], 400);
            }

            return response()->jsonApi([
                'title' => 'Transaction data',
                'message' => 'Transaction data received',
                'data' => $transaction
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Transaction data',
                'message' => 'Get transaction data error: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Method for approve user's transaction
     *
     * @OA\Patch(
     *     path="/admin/transactions/{transaction_id}",
     *     description="Approve user's transaction",
     *     tags={"Admin | Transactions"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\Parameter(
     *         name="transaction_id",
     *         description="Transaction ID",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Data fetched",
     *         @OA\JsonContent(ref="#/components/schemas/OkResponse")
     *     ),
     * )
     *
     * @param $transaction_id
     *
     * @return JsonResponse
     */
    public function update($transaction_id)
    {
        try {
            $transaction = Transaction::find($transaction_id);

            if (!$transaction)
                return response()->jsonApi([
                    'title' => 'Transaction data',
                    'message' => 'No transaction data with id ' . $transaction_id,
                ], 400);

            $transaction->status = Transaction::STATUS_APPROVED;
            $transaction->save();

            return response()->jsonApi([
                'title' => 'Transaction approved',
                'message' => "Transaction updated successfully",
                'data' => $transaction
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Transaction data',
                'message' => 'Update transaction data error: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Method for delete transaction by transaction_id
     *
     * @OA\Delete(
     *     path="/admin/transactions/{transaction_id}",
     *     description="destroy user's transactions by transaction_id",
     *     tags={"Admin | Transactions"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\Parameter(
     *         name="transaction_id",
     *         description="Transaction ID",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Data fetched",
     *         @OA\JsonContent(ref="#/components/schemas/OkResponse")
     *     ),
     * )
     *
     * @param $transaction_id
     *
     * @return JsonResponse
     */
    public function destroy($transaction_id)
    {
        try {
            $transaction = Transaction::find($transaction_id);
            if (!$transaction) {
                return response()->jsonApi([
                    'title' => 'Transaction data',
                    'message' => 'No transaction  with id=' . $transaction_id,
                ], 400);
            }
            $transaction->delete();

            return response()->jsonApi([
                'title' => 'Delete Transaction',
                'message' => "Transaction has been deleted successfully",
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Transaction data',
                'message' => 'Delete transaction data error: ' . $e->getMessage(),
            ], 400);
        }
    }
}
