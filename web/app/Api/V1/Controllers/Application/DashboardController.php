<?php

namespace App\Api\V1\Controllers\Application;

use App\Api\V1\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Product;
use App\Models\Purchase;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Sumra\SDK\Services\JsonApiResponse;

class DashboardController extends Controller
{
    /**
     * Token Sales Progress
     *
     * @OA\Get(
     *     path="/app/dashboard/token-sales-progress",
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
            try {
                $product = Product::findOrFail($request->get("product_id"));
            } catch (ModelNotFoundException $e) {
                return response()->jsonApi([
                    'title' => "Get product",
                    'message' => "Product with id #{$request->get('product_id')} not found: {$e->getMessage()}",
                ], 404);
            }

            // Sum all purchases for this token
            $total_sales = Purchase::where('product_id', $request->get("product_id"))->sum("token_amount");

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
     *     path="/app/dashboard/token-investors",
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
            $paginator = Purchase::where('product_id', $request->get('product_id'))
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
                    'user-id' => Auth::user()->getAuthIdentifier()
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
                    $tokens = Purchase::where([
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
     * Get user validate for using dashboard
     *
     * @OA\Get(
     *     path="/app/dashboard/user-validate",
     *     summary="Get user validate for using dashboard",
     *     description="Get user validate for using dashboard",
     *     tags={"Application | Dashboard"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Getting deposits list",
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
     * )
     *
     * @return mixed
     */
    public function getUserValidate(): mixed
    {
        try {
            // Validate status if need
            $depositsCount = Deposit::byOwner()
                ->where('status', Deposit::STATUS_SUCCEEDED)
                ->count();

            $influencer = DB::connection('identity')
                ->table('users')
                ->leftJoin('model_has_roles', function ($join) {
                    $join->on('users.id', '=', 'model_has_roles.model_id')
                        ->where('model_has_roles.model_type', '=', 'App\Models\User');
                })
                ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->where('users.id', Auth::user()->getAuthIdentifier())
                ->where('roles.name', '=', 'Influencer')
                ->first();

            // Return response
            return response()->jsonApi([
                'title' => 'User dashboard validation',
                'message' => 'Validation retrieved successfully',
                'data' => [
                    'can_access_dashboard' => $depositsCount > 0,
                    'is_influencer' => $influencer ? true : false,
                ],
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'List all deposits',
                'message' => 'Error in getting list of all deposits: ' . $e->getMessage(),
            ], 500);
        }
    }
}
