<?php

namespace App\Api\V1\Controllers\Admin;

use App\Api\V1\Controllers\Controller;
use App\Api\V1\Services\TransactionDashboardService;
use App\Models\Transaction;
use Exception;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Method for transaction dashboard.
     *
     * @OA\Get(
     *     path="/admin/dashboard",
     *     description="Transaction dashboard",
     *     tags={"Admin | Dashboard"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     }},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *     )
     * )
     *
     *
     * @param Request $request
     *
     * @return
     *
     * @throws Exception
     */
    public function index()
    {
        try {
            $transaction = new Transaction;
            $transaction_count = $transaction->count();

            $currency_code = $transaction->distinct('currency_code')->get('currency_code')->pluck('currency_code');
            $user_count = $transaction->distinct('user_id')->count();
            $amount_received = $transaction->sum('amount_received');

            $data = [
                'transactions_count' => $transaction_count,
                'users_count' => $user_count,
                'currency_code' => $currency_code,
                'amount_received' => $amount_received,
                'transactions' => $transaction->toArray(),
                'statistics' => TransactionDashboardService::getTransactionsStatistics()
            ];

            return response()->jsonApi([
                'title' => 'Operation was success',
                'message' => 'The data was displayed successfully',
                'Transactions' => $data,
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => "Getting transaction dashboard data failed",
                'message' => $e->getMessage()
            ], 404);
        }
    }
}
