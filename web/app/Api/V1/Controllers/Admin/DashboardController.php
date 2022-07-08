<?php

namespace App\Api\V1\Controllers\Admin;

use App\Api\V1\Controllers\Controller;
use App\Api\V1\Services\TransactionDashboardService;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

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
     * Method for transaction dashboard statistic
     *
     * @param Request $request
     *
     * @return
     *
     * @throws Exception
     */
    public function index()
    {

    try{

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
                'type' => 'success',
                'title' => 'Operation was success',
                'message' => 'The data was displayed successfully',
                    'Transactions' => $data,
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Getting transaction dashboard data failed",
                'message' => $e->getMessage(),
                'data' => null,
            ], 404);
        }
    }
}
