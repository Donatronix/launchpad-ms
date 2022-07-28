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
use PubSub;

class PurchaseController extends Controller
{
    use CryptoConversionTrait;

    private const RECEIVER_LISTENER = 'PurchaseToken';

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
     *     description="Create a token purchase order",
     *     tags={"Application | Purchases"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Purchase")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Getting product list for start presale",
     *         @OA\JsonContent(ref="#/components/schemas/OkResponse")
     *     ),
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
    public function store(Request $request): mixed
    {
        // Try to save purchased token data
        try {
            // Validate input
            $validator = Validator::make($request->all(), $this->purchase::validationRules());

            if ($validator->fails()) {
                return response()->jsonApi([
                    'title' => 'Creating new token purchase order',
                    'message' => "Validation error occurred!",
                    'data' => $validator->errors()
                ], 422);
            }

        //    return $this->getTokenWorth("btc", 5, "utta");

            // Create new token purchase order
            $purchase = $this->purchase::create([
                'product_id' => $request->get('product_id'),
                'user_id' => $this->user_id,
                'amount_usd' => $request->get('amount_usd'),
                'token_amount' => $request->get('token_amount'),
                'payment_method' => $request->get('payment_method'),
                'payment_status' => $request->get('payment_status'),
            ]);

            // get the product details
            $product = $this->product::find($request->get('product_id'));

            // send token purchased to wallet
            PubSub::publish(self::RECEIVER_LISTENER, [
                'amount' => $purchase->token_amount,
                'token' => $product->ticker,
                'user_id' => $this->user_id,
            ], config('pubsub.queue.crypto_wallets'));

            // Return response to client
            return response()->jsonApi([
                'title' => 'Creating new token purchase order',
                'message' => "New token purchase order has been created successfully",
                'data' => $purchase
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Creating new token purchase order',
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
