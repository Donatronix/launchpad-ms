<?php

namespace App\Api\V1\Controllers\Admin;

use App\Api\V1\Services\TransactionService;
use App\Api\V1\Controllers\Controller;
use App\Models\Transaction;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    /**
     * Method for list of un-approved user's transaction.
     *
     * @OA\Get(
     *     path="/admin/transactions",
     *     description="Get list of un-approved user's transaction",
     *     tags={"Admin | Transactions"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     }},
     *
     *     @OA\Parameter(
     *         name="limit",
     *         description="Count of transactions in one page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *              default=20
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *     )
     * )
     *
     * Method for list of un-approved  transaction of users.
     *
     * @param Request $request
     *
     * @return
     *
     * @throws Exception
     */
    public function index(Request $request)
    {
        try {
            $result = Transaction::where('status', Transaction::STATUS_WAITING)
                ->paginate($request->get('limit', 20));

            // Return response
            return response()->json([
                'type' => 'success',
                'title' => "list of un-approved",
                'message' => '',
                'data' => $result->toArray()
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Transactions list',
                'message' => $e->getMessage()
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
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
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
     *         description="Success",
     *     )
     * )
     *
     * @param         $transaction_id
     *
     * @return
     */
    public function show($transaction_id)
    {
        try {
            $transaction = Transaction::find($transaction_id);

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'error' => 'No transaction of user with id=' . $transaction_id
                ], 400);
            }

            return response()->json([
                'success' => true,
                'title' => "A transaction by id",
                'transaction' => $transaction->toArray()
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
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
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
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
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *     )
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
                return response()->json([
                    'success' => false,
                    'error' => 'No transaction with id=' . $transaction_id
                ], 400);

            $transaction->status = Transaction::STATUS_CONFIRMED;
            $transaction->save();

            return response()->json([
                'success' => true,
                'title' => 'Transaction approved',
                'message' => "Transaction updated successfully",
                'data' => $transaction
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
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
     *          "default" :{
     *              "ManagerRead",
     *              "Admin",
     *              "ManagerWrite"
     *          },
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
     * *    @OA\Parameter(
     *         name="transaction_token",
     *         in="query",
     *         description="Payment token",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     * *    @OA\Parameter(
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
     *         description="Success send data"
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response="403",
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not found"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     */
    public function store(Request $request)
    {
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

    /**
     * Method for delete transaction by transaction_id
     *
     * @OA\Delete(
     *     path="/admin/transactions/{transaction_id}",
     *     description="destroy user's transactions by transaction_id",
     *     tags={"Admin | Transactions"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
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
     *         description="Success",
     *     )
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
            if (!$transaction)
                return response()->json([
                    'success' => false,
                    'error' => 'No transaction  with id=' . $transaction_id
                ], 400);

            if ($transaction->status == Transaction::STATUS_CONFIRMED)
                return response()->json([
                    'success' => false,
                    'error' => 'transaction  with id=' . $transaction_id . ' is already accepted'
                ], 400);

            $transaction->delete();

            return response()->json(['success' => true], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
