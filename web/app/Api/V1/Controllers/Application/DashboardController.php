<?php

namespace App\Api\V1\Controllers\Application;

use App\Api\V1\Controllers\Controller;
use App\Models\Purchase;
use Illuminate\Http\Request;
use App\Models\Product;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Sumra\SDK\JsonApiResponse;

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
     *     path="/token-sales-progress",
     *     summary="Token Sales Progress",
     *     description="Get the progress for the sales of tokens",
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
    public function tokenSalesProgress(Request $request): mixed
    {
        
        // Try to get token sales progress
        try {
            // check if product id is available 
            if(!$request->has("product_id")){
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Token Sales Progress',
                'message' => "You must send product_id as a parameter",
                'data' => null
            ], 400);
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
                'type' => 'success',
                'title' => 'Token Sales Progress',
                'message' => "Token sales progress generate",
                'data' => $data
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Creating new token purchase order',
                'message' => $e->getMessage(),
                'data' => null
            ], 400);
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
                'type' => 'danger',
                'title' => "Get product",
                'message' => "Product with id #{$id} not found: {$e->getMessage()}",
                'data' => ''
            ], 404);
        }
    }
}
