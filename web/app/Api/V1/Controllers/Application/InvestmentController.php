<?php

namespace App\Api\V1\Controllers\Application;

use App\Api\V1\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Order;
use App\Models\Product;
use App\Traits\CryptoConversionTrait;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Class InvestmentController
 * @package App\Api\V1\Controllers\Application
 */
class InvestmentController extends Controller
{
    use CryptoConversionTrait;

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
     *                 type="integer",
     *                 description="Investment amount in USD/EUR/GBP",
     *                 example="100000"
     *             ),
     *             @OA\Property(
     *                 property="payment_amount",
     *                 type="integer",
     *                 description="Deposit payment amount",
     *                 example="100000"
     *             ),
     *             @OA\Property(
     *                 property="payment_currency",
     *                 type="string",
     *                 description="Payment currency in which the deposit is made",
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
     *
     * @return mixed
     */
    public function __invoke(Request $request): mixed
    {
        // Validate status if need
        $validation = Validator::make(
            $request->all(),
            [
                'product_id' => 'required|string|min:36|max:36',
                'investment_amount' => 'required|integer|min:250',
                'payment_amount' => 'required|integer|min:250',
                'payment_currency' => 'required|string|min:3',
            ],
            [
                'investment_amount' => 'Minimum investment amount in the equivalent of 250 USD/EUR/GBP',
                'payment_amount' => 'Minimum deposit amount in the equivalent of 250 USD/EUR/GBP',
            ]
        );

        // If validation error, the stop
        if ($validation->fails()) {
            return response()->jsonApi([
                'title' => 'Application for participation in the presale',
                'message' => $validation->errors()
            ], 422);
        }

        // Try to save received data
        try {
            // Validate input
            $inputData = (object) $validation->validated();

            // Convert payment currency
            $currency = strtolower($inputData->payment_currency);

            // Check maximum deposit sum in Fiat
            if (in_array($currency, ['usd', 'eur', 'gbp']) && $inputData->payment_amount > 1000) {
                return response()->jsonApi([
                    'title' => 'Application for participation in the presale',
                    'message' => "You can't make fiat deposit more 1000 USD/EUR/GBP. Use crypto payment"
                ], 422);
            }

            // If deposit currency not fiat, then convert by market rate
            if (!in_array($currency, ['usd', 'eur', 'gbp'])) {
                $currency_type = 'crypto';
            } else {
                $currency_type = 'fiat';
            }

            // Get / checking current product
            $product = Product::findOrFail($inputData->product_id);

            // get token worth
            $token_worth = $this->getTokenWorth($inputData->investment_amount, $product->ticker, $currency_type);

            // Create new order
            $order = Order::create([
                'product_id' => $product->id,
                'investment_amount' => $inputData->investment_amount,
                'deposit_percentage' => ($inputData->payment_amount / $inputData->investment_amount) * 100,
                'deposit_amount' => $inputData->payment_amount,
                'user_id' => Auth::user()->getAuthIdentifier(),
                'status' => Order::STATUS_CREATED
            ]);

            // If currency is crypto, then re-calculate payment amount
            if ($currency_type === 'crypto') {
                $rate = $this->getTokenExchangeRate('usd', $currency);

                // calculate crypto payment amount
                $inputData->payment_amount = round($rate * $inputData->payment_amount, 8, PHP_ROUND_HALF_UP);
            }

            // Create deposit
            $deposit = Deposit::create([
                'amount' => $inputData->payment_amount,
                'currency_code' => $currency,
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
                    'currency' => $currency,
                    'document' => [
                        'id' => $deposit->id,
                        'object' => class_basename(get_class($deposit)),
                        'service' => env('RABBITMQ_EXCHANGE_NAME'),
                        'meta' => []
                    ],
                    'token' => $token_worth
                ]
            ], 201);
        } catch (ModelNotFoundException $e) {
            return response()->jsonApi([
                'title' => 'Application for participation in the presale',
                'message' => 'This product does not exist',
            ], 404);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Application for participation in the presale',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
