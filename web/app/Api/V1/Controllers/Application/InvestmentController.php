<?php

namespace App\Api\V1\Controllers\Application;

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
     * Create a first investment after registration
     *
     * @OA\Post(
     *     path="/investment",
     *     summary="Create a first investment after registration",
     *     description="Create a first investment after registration",
     *     tags={"Investment"},
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
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="product_id",
     *                 type="string",
     *                 description="Product ID",
     *                 example="9a778e5d-61aa-4a2b-b511-b445f6a67909"
     *             ),
     *             @OA\Property(
     *                 property="investment_amount",
     *                 type="number",
     *                 description="Investment amount",
     *                 example="100000"
     *             ),
     *             @OA\Property(
     *                 property="deposit_percentage",
     *                 type="number",
     *                 description="Deposit percentage",
     *                 example="10"
     *             ),
     *             @OA\Property(
     *                 property="deposit_amount",
     *                 type="number",
     *                 description="Deposit amount",
     *                 example="10000"
     *             ),
     *             @OA\Property(
     *                 property="currency",
     *                 type="string",
     *                 description="Deposit currency code",
     *                 example="usd"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="201",
     *         description="Successfully save"
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
     *         description="Product not found"
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
            $this->validate($request, [
                'product_id' => 'required|string|min:36|max:36',
                'investment_amount' => 'required|integer|min:2500',
                'deposit_percentage' => 'required|integer|min:10|max:100',
                'deposit_amount' => 'required|integer|min:250',
                'currency' => 'required|string|min:3',
            ]);

            // Get / checking current product
            $product = Product::findOrFail($request->get('product_id', config('settings.empty_uuid')));

            // Create new order
            $order = Order::create([
                'product_id' => $product->id,
                'investment_amount' => $request->get('investment_amount'),
                'deposit_percentage' => $request->get('deposit_percentage'),
                'deposit_amount' => $request->get('deposit_amount'),
                'user_id' => Auth::user()->getAuthIdentifier(),
                'status' => Order::STATUS_NEW
            ]);

            // Create deposit
            $deposit = Deposit::create([
                'amount' => $request->get('deposit_amount'),
                'currency_code' => $request->get('currency'),
                'order_id' => $order->id,
                'status' => Deposit::STATUS_CREATED,
                'user_id' => Auth::user()->getAuthIdentifier(),
            ]);

            // Return response to client
            return response()->jsonApi([
                'type' => 'success',
                'title' => 'Application for participation in the presale',
                'message' => "Application for participation in the presale has been successfully created",
                'data' => [
                    'amount' => $deposit->amount,
                    'currency' => $request->get('currency'),
                    'document' => [
                        'id' => $deposit->id,
                        'object' => 'Deposit',
                        'service' => 'CryptoLaunchpadMS',
                    ]
                ]
            ], 201);
        } catch (ValidationException $e) {
            return response()->jsonApi([
                'type' => 'warning',
                'title' => 'Application for participation in the presale',
                'message' => "Validation error: " . $e->getMessage(),
                'data' => null
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->jsonApi([
                'type' => 'warning',
                'title' => 'Application for participation in the presale',
                'message' => "This product does not exist",
                'data' => null
            ], 404);
        } catch (\Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Application for participation in the presale',
                'message' => $e->getMessage(),
                'data' => null
            ], $e->getCode());
        }
    }
}
