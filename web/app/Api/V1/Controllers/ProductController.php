<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Exception;
use Illuminate\Http\Request;
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
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         description="Product attribute",
     *         @OA\Schema(
     *             type="bool"
     *         )
     *     ),
     *
     *     @OA\Response(
     *          response="200",
     *          description="Getting product list for start presale"
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {

            // Get products
            $query = Product::select(['id', 'title', 'ticker', 'start_date', 'end_date', 'icon'])
                ->with('price');

            if ($request->has('status')) {
                $query = $query->where('status', $request->status);
            }

            $products = $query->get();

            if ($request->has('status')) {
                // Transform collection objects
                $products->map(function ($object) {
                    $price = $object->price;
                    unset($object->price);

                    $object->setAttribute('start_stage', $price->stage);
                    $object->setAttribute('start_price', $price->price);
                    $object->setAttribute('start_date', Carbon::create($object->start_date)->format('jS F Y'));
                    $object->setAttribute('end_date', Carbon::create($object->end_date)->format('jS F Y'));
                });
            }

            // Return response
            return response()->jsonApi([
                'type' => 'success',
                'title' => 'Products list',
                'message' => "Products list been received",
                'data' => $products->toArray(),
            ], 200);
        } catch (Exception $e) {
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
     *
     * @return mixed
     */
    public function show($id): mixed
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
                'data' => $product->toArray(),
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Product detail',
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
