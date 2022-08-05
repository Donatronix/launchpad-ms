<?php

namespace App\Api\V1\Controllers\Application;

use App\Api\V1\Controllers\Controller;
use App\Models\Product;
use App\Models\Purchase;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Sumra\SDK\Services\JsonApiResponse;

class DashboardController extends Controller
{
    private Purchase $purchase;
    private Product $product;

    public function __construct(Purchase $purchase, Product $product)
    {
        $this->purchase = $purchase;
        $this->product = $product;
        $this->user_id = auth()->user()->getAuthIdentifier();
    }

    /**
     * Token Sales Progress
     *
     * @OA\Get(
     *     path="/app/token-sales-progress",
     *     summary="Token Sales Progress",
     *     description="Get the progress for the sales of tokens",
     *     tags={"Application | Dashboard"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\Parameter(
     *         name="product_id",
     *         in="path",
     *         required=true,
     *         description="product Id",
     *         example="0aa06e6b-35de-3235-b925-b0c43f8f7c75",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successfully save"
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
    public function tokenSalesProgress(Request $request): mixed
    {
        // Try to get token sales progress
        try {
            // check if product id is available
            if (!$request->has("product_id")) {
                return response()->jsonApi([
                    'title' => 'Token Sales Progress',
                    'message' => "You must send product_id as a parameter",
                ], 422);
            }

            // Read product model
            $product = $this->getProduct($request->get("product_id"));

            if ($product instanceof JsonApiResponse) {
                return $product;
            }

            // Sum all purchases for this token
            $total_sales = $this->purchase::where('product_id', $request->get("product_id"))->sum("token_amount");

            // Create new token purchase order
            $data = [
                'max_supply' => $product->supply,
                'total_sales' => $total_sales,
                'ticker' => $product->ticker,
                'title' => $product->title,
                'start_date' => $product->start_date,
                'end_date' => $product->end_date,
            ];

            // Return response to client
            return response()->jsonApi([
                'title' => 'Token Sales Progress',
                'message' => "Token sales progress generate",
                'data' => $data
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Creating new token purchase order',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * List Token investors
     *
     * @OA\Get(
     *     path="/app/token-investors",
     *     description="List the users that have invested in a token",
     *     tags={"Application | Dashboard"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
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
     *         description="Getting product list for start presale",
     *         @OA\JsonContent(ref="#/components/schemas/OkResponse")
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/WarningResponse")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error",
     *         @OA\JsonContent(ref="#/components/schemas/DangerResponse")
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
                 */
                $url = config('settings.api.identity') . '/user-profile/details';

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
                    throw new Exception($message, $status);
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
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Token investors',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get product object
     *
     * @param $id
     * @return mixed
     */
    private function getProduct($id): mixed
    {
        try {
            return $this->product::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->jsonApi([
                'title' => "Get product",
                'message' => "Product with id #{$id} not found: {$e->getMessage()}",
            ], 404);
        }
    }
}
