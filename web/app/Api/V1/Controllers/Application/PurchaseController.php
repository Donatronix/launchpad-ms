<?php

namespace App\Api\V1\Controllers\Application;

use App\Api\V1\Controllers\Controller;
use App\Models\Product;
use App\Models\Purchase;
use App\Traits\CryptoConversionTrait;
use Auth;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PurchaseController extends Controller
{
    use CryptoConversionTrait;

    /**
     * @param Purchase $purchase
     */
    private Purchase $purchase;

    public function __construct(Purchase $purchase, Product $product)
    {
        $this->purchase = $purchase;
        $this->product = $product;
        $this->user_id = auth()->user()->getAuthIdentifier();
    }

    /**
     * Display list of all purchase - shopping List
     *
     * @OA\Get(
     *     path="/app/purchases",
     *     description="Getting list of all purchases tokens - shopping list",
     *     tags={"Application | Purchases"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\Parameter(
     *         name="limit",
     *         description="Count of purchase in response",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=20,
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
     *         description="Getting product list for start presale",
     *         @OA\JsonContent(ref="#/components/schemas/OkResponse")
     *     ),
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function index(Request $request): mixed
    {
        try {
            $allPurchase = Purchase::orderBy('created_at', 'Desc')
                ->with(['product' => function ($query) {
                    $query->select('title', 'ticker', 'supply', 'presale_percentage', 'start_date', 'end_date', 'icon');
                }])
                ->paginate($request->get('limit', 20));

            // Return response
            return response()->jsonApi([
                'title' => 'List all purchase',
                'message' => 'List all purchase',
                'data' => $allPurchase
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'List all purchase',
                'message' => 'Error in getting list of all purchase: ' . $e->getMessage(),
            ], $e->getCode());
        }
    }

    /**
     * Purchase a Token
     *
     * @OA\Post(
     *     path="/app/purchases",
     *     summary="Purchase a token",
     *     description="Create a token purchase order. Currency ticker should be btc, eth, usd, gbp or eur. Only currency type of fiat and crypto is required",
     *     tags={"Application | Purchases"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Purchase")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Ok",
     *         @OA\JsonContent(ref="#/components/schemas/OkResponse")
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="Created",
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
    public function store(Request $request): mixed
    {
        // Try to save purchased token data
        try {
            $rules = [
                'currency_type' => "required|in:fiat,crypto",
                'product_id' => 'required|string',
                'currency_ticker' => 'required|string'
            ];

            if ($request->currency_type == "fiat") {
                $rules += [
                    "payment_amount" => 'required|numeric|min:250|max:1000',
                ];
            } else if ($request->currency_type == "crypto") {
                $rules += [
                    "payment_amount" => 'required|numeric',
                ];
            }

            // Do validate input data
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->jsonApi([
                    'title' => 'Creating new token purchase order',
                    'message' => "Validation error occurred!",
                    'data' => $validator->errors()
                ], 422);
            }

           // Get product details
            $product = $this->product::find($request->product_id);
            if (!$product) {
                throw new Exception("Product not found", 400);
            }

            // get rate of token
            $rate = $this->getTokenExchangeRate("usd", $request->currency_ticker);

            // get payment_amount
            $payment_amount = $rate * $request->payment_amount;

            // get token worth
            $token_worth = $this->getTokenWorth($request->payment_amount, $product->ticker);

            // Create new token purchase order
            $purchase = $this->purchase::create([
                'product_id' => $request->get('product_id'),
                'user_id' => $this->user_id,
                'payment_amount' => $payment_amount,
                'currency_ticker' => $request->get('currency_ticker'),
                'currency_type' => $request->get('currency_type'),
                "token_amount" => $token_worth["token_amount"],
                "bonus" => $token_worth["bonus"],
                "total_token" => $token_worth["total_token"],
            ]);

            // Send token purchased to wallet
            // PubSub::publish(self::RECEIVER_LISTENER, [
            //     'amount' => $purchase->token_amount,
            //     'token' => $product->ticker,
            //     'user_id' => $this->user_id,
            // ], config('pubsub.queue.crypto_wallets'));

            // Return response to client
            return response()->jsonApi([
                'title' => 'Creating new token purchase order',
                'message' => "New token purchase order has been created successfully",
                'data' => [
                    'amount' => $purchase->payment_amount,
                    'currency' => $purchase->currency_ticker,
                    'document' => [
                        'id' => $purchase->id,
                        'object' => class_basename(get_class($purchase)),
                        'service' => env('RABBITMQ_EXCHANGE_NAME'),
                        'meta' => $purchase
                    ]]
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Creating new token purchase order',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get token worth
     *
     * @OA\Post(
     *     path="/app/purchases/token-worth",
     *     summary="Get token worth",
     *     description="Get token worth",
     *     description="Get token worth. Currency ticker should be btc, eth, usd, gbp or eur. Only currency type of fiat and crypto is required",
     *     tags={"Application | Purchases"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Purchase")
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="ok",
     *         @OA\JsonContent(ref="#/components/schemas/OkResponse")
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="Created",
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
    public function tokenWorth(Request $request): mixed
    {
        // Try to save purchased token data
        try {
            $rules = [
                'currency_type' => "required|in:fiat,crypto",
                'product_id' => 'required|string',
                'currency_ticker' => 'required|string',
            ];

            if ($request->currency_type == "fiat") {
                $rules += [
                    "payment_amount" => 'required|numeric|min:250|max:1000',
                ];
            } else if ($request->currency_type == "crypto") {
                $rules += [
                    "payment_amount" => 'required|numeric',
                ];
            }

            // Do validate input data
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->jsonApi([
                    'title' => 'Get token worth',
                    'message' => "Validation error occurred!",
                    'data' => $validator->errors()
                ], 422);
            }

            // get product details
            $product = $this->product::find($request->product_id);
            if (!$product) {
                throw new Exception("Product not found", 400);
            }

            // get rate of token
            $rate = $this->getTokenExchangeRate("usd", $request->currency_ticker);

            // get payment_amount
            $payment_amount = $rate * $request->payment_amount;

            // get token worth
            $token_worth = $this->getTokenWorth($request->payment_amount, $product->ticker);

            // Return response to client
            return response()->jsonApi([
                'title' => 'Get token worth',
                'message' => "Get token worth",
                'data' => [
                    "currency_ticker" => $request->currency_ticker,
                    "rate" => $rate,
                    "payment_amount" => $payment_amount,
                    "token_amount" => $token_worth["token_amount"],
                    "bonus" => $token_worth["bonus"],
                    "total_token" => $token_worth["total_token"],
                ]
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Get token worth',
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
