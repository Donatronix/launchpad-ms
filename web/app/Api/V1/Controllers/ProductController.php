<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Support\Carbon;

class ProductController extends Controller
{
    /**
     * Getting product list for start presale
     *
     * @OA\Get(
     *     path="/products",
     *     summary="Getting product list for start presale",
     *     description="Getting product list for start presale",
     *     tags={"Products"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     }},
     *     x={
     *         "auth-type": "Application & Application User",
     *         "throttling-tier": "Unlimited",
     *         "wso2-application-security": {
     *             "security-types": {"oauth2"},
     *             "optional": "false"
     *         }
     *     },
     *
     *     @OA\Response(
     *          response="200",
     *          description="Getting product list for start presale"
     *     )
     * )
     */
    public function index()
    {
        try {
            // Get order
            $products = Product::select(['id', 'title', 'ticker', 'start_date'])
                ->where('status', true)
                ->with('price')
                ->get();

            // Transform collection objects
            $products->map(function ($object) {
                $price = $object->price;
                unset($object->price);

                $object->setAttribute('start_stage', $price->stage);
                $object->setAttribute('start_price', $price->price);
                $object->setAttribute('start_date', Carbon::create($object->start_date)->format('dS F Y'));
            });

            // Return response
            return response()->jsonApi([
                'type' => 'success',
                'title' => 'Products list',
                'message' => "Products list been received",
                'data' => $products->toArray()
            ], 200);
        } catch (\Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Products list',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Getting product detail
     *
     * @OA\Get(
     *     path="/products/{id}",
     *     summary="Getting product detail by ID or ticker",
     *     description="Getting product detail by ID or ticker",
     *     tags={"Products"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     }},
     *     x={
     *         "auth-type": "Application & Application User",
     *         "throttling-tier": "Unlimited",
     *         "wso2-application-security": {
     *             "security-types": {"oauth2"},
     *             "optional": "false"
     *         }
     *     },
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Product ID or Ticker",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Getting product detail by platform"
     *     )
     * )
     * @param $id
     */
    public function show($id)
    {
        try {
            // Get order
            $product = Product::where('status', true)
                ->where('ticker', $id)
                ->orWhere('id', $id)
                ->with('prices')
                ->get();

            return response()->jsonApi([
                'type' => 'success',
                'title' => 'Product detail',
                'message' => "Product detail been received",
                'data' => $product->toArray()
            ], 200);
        } catch (\Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Product detail',
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
