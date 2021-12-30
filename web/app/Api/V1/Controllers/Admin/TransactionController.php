<?php

namespace App\Api\V1\Controllers\Admin;

use App\Http\Controllers\Controller;

class TransactionController extends Controller
{
    /**
     * Method for list of un-approved user's transaction.
     *
     * @OA\Get(
     *     path="/admin/transactions",
     *     description="Get list of un-approved user's transaction",
     *     tags={"Admin / Transactions"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     }},
     *     x={
     *         "auth-type": "Application & Application User",
     *         "throttling-tier": "Unlimited",
     *         "wso2-application-security": {
     *             "security-types": {"oauth2"},
     *             "optional": "false"
     *         }
     *     },
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
     *         response=200,
     *         description="Success",
     *     )
     * )
     *
     * Method for list of un-approved  transaction of users.
     *
     * @param Request $request
     *
     * @return \Sumra\JsonApi\
     *
     * @throws \Exception
     */
    public function index(Request $request)
    {
        try {
            $result = Transaction::where('status', Transaction::STATUS_WAITING)
                ->paginate($request->get('limit', 20));

//            $result = DB::table('transactions')
//                ->select('transactions.*', 'users.last_name', 'users.first_name')
//                ->join('users', 'users.id', '=', 'transactions.transactionable_id')
//                ->where('transactions.transactionable_type', Transaction::TYPE_CONTRACT)
//                ->orderBy('transactions.id', 'asc')


            // Return response
            return response()->jsonApi($result->toArray());
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
     *     tags={"Admin / Transactions"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     }},
     *     x={
     *         "auth-type": "Application & Application User",
     *         "throttling-tier": "Unlimited",
     *         "wso2-application-security": {
     *             "security-types": {"oauth2"},
     *             "optional": "false"
     *         }
     *     },
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
     *         response=200,
     *         description="Success",
     *     )
     * )
     *
     * @param         $transaction_id
     *
     * @return \Sumra\JsonApi\
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
                'transaction' => $transaction
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
     *     tags={"Admin / Transactions"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     }},
     *     x={
     *         "auth-type": "Application & Application User",
     *         "throttling-tier": "Unlimited",
     *         "wso2-application-security": {
     *             "security-types": {"oauth2"},
     *             "optional": "false"
     *         }
     *     },
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
     *         response=200,
     *         description="Success",
     *     )
     * )
     *
     * @param $transaction_id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function approve($transaction_id)
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
                'success' => true
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Method for delete transaction by transaction_id
     *
     * @OA\Delete(
     *     path="/admin/transactions/{transaction_id}",
     *     description="destroy user's transactions by transaction_id",
     *     tags={"Admin / Transactions"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     }},
     *     x={
     *         "auth-type": "Application & Application User",
     *         "throttling-tier": "Unlimited",
     *         "wso2-application-security": {
     *             "security-types": {"oauth2"},
     *             "optional": "false"
     *         }
     *     },
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
     *         response=200,
     *         description="Success",
     *     )
     * )
     *
     * @param $transaction_id
     *
     * @return \Illuminate\Http\JsonResponse
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
