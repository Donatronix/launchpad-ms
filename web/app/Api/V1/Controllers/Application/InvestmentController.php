<?php

namespace App\Api\V1\Controllers\Application;

use App\Api\V1\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Order;
use App\Models\Product;
use Exception;
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
     *     path="/app/investment",
     *     summary="Create a first investment after registration",
     *     description="Create a first investment after registration",
     *     tags={"Application | Investment"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
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
     *         description="New record addedd successfully",
     *         @OA\JsonContent(ref="#/components/schemas/OkResponse")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error",
     *         @OA\JsonContent(ref="#/components/schemas/DangerResponse")
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(ref="#/components/schemas/WarningResponse")
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Validation Failed",
     *         @OA\JsonContent(ref="#/components/schemas/WarningResponse")
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Unknown error"
     *     )
     * )
     *
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
                'title' => 'Application for participation in the presale',
                'message' => "Validation error: " . $e->getMessage()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->jsonApi([
                'title' => 'Application for participation in the presale',
                'message' => "This product does not exist",
            ], 404);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Application for participation in the presale',
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }
}
