<?php

namespace App\Api\V1\Controllers\User;

use App\Api\V1\Controllers\Controller;
use App\Api\V1\Services\TransactionService;
use App\Models\Deposit;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class InvestmentController extends Controller
{
    /**
     * Create a new investment order
     *
     * @OA\Post(
     *     path="/orders",
     *     summary="Create a new investment order",
     *     description="Create a new investment order",
     *     tags={"Orders"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     }},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Order")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successfully save"
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="Order created"
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="not found"
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Validation failed"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Unknown error"
     *     )
     * )
     * @param Request $request
     * @return mixed
     */
    public function __invoke(Request $request): mixed
    {
        // Try to save received data
        try {
            // Validate input
            $this->validate($request, $this->model::validationRules());

            // Get / checking current product
            $product = Product::findOrFail($request->get('product_id', config('settings.empty_uuid')));

            // Create new order
            $order = $this->model::create([
                'product_id' => $product->id,
                'investment_amount' => $request->get('investment_amount'),
                'deposit_percentage' => $request->get('deposit_percentage'),
                'deposit_amount' => $request->get('deposit_amount'),
                'user_id' => Auth::user()->getAuthIdentifier(),
                'status' => Order::STATUS_NEW,
                'amount_token' => $request->get('investment_amount'),
                'amount_usd' => $request->get('investment_amount'),
            ]);

            // create new transaction
            $paramsTransactions = $request->all();
            $paramsTransactions['order_id'] = $order->id;
            $transaction = (new TransactionService())->store($paramsTransactions);
            $order->transaction;

            // create deposit
            $depositObj = [
                'user_id' => Auth::user()->getAuthIdentifier(),
                'deposit_amount' => $request->get('deposit_amount'),
                'currency_id' => $request->get('currency_id'),
            ];

            $deposit = Deposit::create($depositObj);

            // Return response to client
            return response()->jsonApi([
                'type' => 'success',
                'title' => 'Creating new order',
                'message' => "New order has been created successfully",
                'data' => [
                    'order' => $order->toArray(),
                    'deposit' => $deposit->toArray()
                ]
            ], 200);
        } catch (ValidationException $e) {
            return response()->jsonApi([
                'type' => 'warning',
                'title' => 'Creating new order',
                'message' => "Validation error: " . $e->getMessage(),
                'data' => null
            ], 400);
        } catch (ModelNotFoundException $e) {
            return response()->jsonApi([
                'type' => 'warning',
                'title' => 'Creating new order',
                'message' => "This product does not exist",
                'data' => null
            ], 400);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Creating new order',
                'message' => $e->getMessage(),
                'data' => null
            ], 400);
        }
    }
}
