<?php

namespace App\Api\V1\Controllers\Application;

use App\Api\V1\Controllers\Controller;
use App\Models\Purchase;
use App\Models\Product;
use Illuminate\Http\Request;
use Exception;
use PubSub;
use Illuminate\Support\Facades\Validator;
use Auth;
use Illuminate\Support\Facades\Http;
use Sumra\SDK\Services\JsonApiResponse;

class PurchaseController extends Controller
{
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
     *     path="/purchase-token",
     *     description="Getting list of all purchase-token - shopping list",
     *     tags={"Token"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
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
     *         description="Success"
     *     )
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
                'message' => 'Error in getting list of all purchase',
                'data' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Purchase a Token
     *
     * @OA\Post(
     *     path="/purchase-token",
     *     summary="Purchase a token",
     *     description="Create a token purchase order",
     *     tags={"Token"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Purchase")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successfully save"
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="Purchase created"
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
     *         description="Not Found"
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
                throw new Exception($validator->errors()->first());
            }

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
            PubSub::transaction(function () {
            })->publish(self::RECEIVER_LISTENER, [
                'amount' => $purchase->token_amount,
                'token' => $product->ticker,
                'user_id' => $this->user_id,
            ], "UltainfinityWalletsMS");

            // Return response to client
            return response()->jsonApi([
                'title' => 'Creating new token purchase order',
                'message' => "New token purchase order has been created successfully",
                'data' => $purchase->toArray()
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Creating new token purchase order',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * List Token investors
     *
     * @OA\Get(
     *     path="/token-investors",
     *     description="List the users that have invested in a token",
     *     tags={"Token"},
     *
     *     security={{
     *          "bearerAuth": {},
     *          "apiKey": {}
     *     }},
     *
     *     @OA\Parameter(
     *         name="product_id",
     *         in="query",
     *         required=true,
     *         description="Product Id",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Details Fetched",
     *         @OA\JsonContent(ref="#/components/schemas/OkResponse")
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/WarningResponse")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Bad Request",
     *         @OA\JsonContent(ref="#/components/schemas/WarningResponse")
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Server Error",
     *         @OA\JsonContent(ref="#/components/schemas/DangerResponse")
     *     )
     * )
     *
     * @param Request $request
     *
     * @return JsonApiResponse
     */
    public function tokenInvestors(Request $request): JsonApiResponse
    {
        try {
            if (!$request->has('product_id')) {
                throw new Exception("Product_id required as query string");
            }

            // Check ID
            $product = Product::find($request->product_id);
            if (!$product) {
                return response()->jsonApi([
                    'title' => 'Token Investors',
                    'message' => 'The specified Token ID not recognized'
                ], 400);
            }

            $data = [];
            $investors = [];

            // Get unique user_id for the product
            $paginator = $this->purchase::where('product_id', $request->get('product_id'))
                ->select("user_id")->distinct()->latest()->paginate(20);

            if ($paginator->items()) {

                /**
                 * Prep IDS endpoint
                 *
                 */
                $endpoint = '/user-profile/details';
                $IDS = config('settings.api.identity');
                $url = $IDS['host'] . '/' . $IDS['version'] . $endpoint;

                /**
                 * Get Details from IDS
                 *
                 */
                $response = Http::withToken($request->bearerToken())->withHeaders([
                    'User-Id' => Auth::user()->getAuthIdentifier()
                ])->post($url, [
                    'users' => $paginator->items()
                ]);

                /**
                 * Handle Response
                 *
                 */
                if (!$response->successful()) {
                    $status = $response->status() ?? 400;
                    $message = $response->getReasonPhrase() ?? 'Error Processing Request';
                    throw new \Exception($message, $status);
                }

                $data = $response->object()->data ?? null;
            }

            // Get Token details
            if ($data) {
                foreach ($data[0] as $key => $investor) {

                    // Sum the tokens for the user
                    $tokens = $this->purchase::where([
                        'product_id' => $request->get('product_id'),
                        'user_id' => $investor->id
                    ])->sum("token_amount");

                    $investor->tokens = $tokens;
                    array_push($investors, $investor);
                }
            }

            // Update paginator items
            $paginator->setCollection(collect($investors));

            // Return response to client
            return response()->jsonApi([
                'title' => 'Token investors',
                'message' => "Token investors fetched successfully",
                'data' => $paginator->toArray()
            ]);
        }
        catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Token investors',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
