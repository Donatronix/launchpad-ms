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
use Illuminate\Validation\ValidationException;

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
     *                 type="number",
     *                 description="Investment amount in USD/EUR/GBP",
     *                 example="100000"
     *             ),
     *             @OA\Property(
     *                 property="deposit_percentage",
     *                 type="number",
     *                 description="Deposit percentage from investment amount",
     *                 example="10"
     *             ),
     *             @OA\Property(
     *                 property="currency",
     *                 type="string",
     *                 description="Currency in which the deposit is made",
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
            $inputData = (object)$this->validate($request, [
                'product_id' => 'required|string|min:36|max:36',
                'investment_amount' => 'required|integer|min:1000',
                'deposit_percentage' => 'required|integer|min:10|max:100',
                'currency' => 'required|string|min:3',
            ]);

            // Get / checking current product
            $product = Product::findOrFail($inputData->product_id);

            // Calculate deposit amount
            $deposit_amount = ($inputData->investment_amount * $inputData->deposit_percentage) / 100;

            // Convert currency
            $currency = strtolower($inputData->currency);

            // Check minimal deposit sum
            if ($deposit_amount < 250) {
                return response()->jsonApi([
                    'title' => 'Application for participation in the presale',
                    'message' => "Minimum deposit amount in the equivalent of 250 USD/EUR/GBP. Increase your investment"
                ], 422);
            }

            // Check maximum deposit sum in Fiat
            if (in_array($currency, ['usd', 'eur', 'gbp']) && $deposit_amount > 1000) {
                return response()->jsonApi([
                    'title' => 'Application for participation in the presale',
                    'message' => "You can't make fiat deposit more 1000 USD/EUR/GBP. Use crypto payment"
                ], 422);
            }

            // If deposit currency not fiat, then convert by market rate
            if (!in_array($currency, ['usd', 'eur', 'gbp'])) {
                $rate = $this->getTokenExchangeRate('usd', $currency);

                // get payment_amount
                $deposit_amount = $rate * $deposit_amount;
            }

            // get token worth
            $token_worth = $this->getTokenWorth($inputData->investment_amount, $product->ticker);

            // Create new order
            $order = Order::create([
                'product_id' => $product->id,
                'investment_amount' => $inputData->investment_amount,
                'deposit_percentage' => $inputData->deposit_percentage,
                'deposit_amount' => $deposit_amount,
                'user_id' => Auth::user()->getAuthIdentifier(),
                'status' => Order::STATUS_CREATED
            ]);

            // Create deposit
            $deposit = Deposit::create([
                'amount' => $deposit_amount,
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
                    ],
                    'token' => $token_worth
                ]
            ], 201);
        } catch (ValidationException $e) {
            return response()->jsonApi([
                'title' => 'Application for participation in the presale',
                'message' => "Validation error: " . $e->getMessage(),
                'data' => $e->errors()
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
            ], 500);
        }
    }
}
