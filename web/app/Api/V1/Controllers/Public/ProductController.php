<?php

namespace App\Api\V1\Controllers\Public;

use App\Api\V1\Controllers\Controller;
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
     *     tags={"Public | Products"},
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
     *     @OA\Parameter(
     *         name="stage",
     *         in="query",
     *         required=false,
     *         description="Stage for which to deduct the price of the product",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Getting product list for start presale",
     *         @OA\JsonContent(ref="#/components/schemas/OkResponse")
     *     ),
     * )
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request): mixed
    {
        try {
            // Get products
            $products = Product::query()
                ->select(['id', 'title', 'ticker', 'start_date', 'end_date', 'icon'])
                ->byStage($request->get('stage', 1))
                ->when($request->has('status'), function ($q) use ($request) {
                    return $q->where('status', $request->get('status', true));
                })->get();

            // Transform collection objects
            $products->map(function ($object) {
                $price = $object->price;
                unset($object->price);

                $object->setAttribute('start_stage', $price->stage);
                $object->setAttribute('start_price', $price->price);
                $object->setAttribute('start_date', Carbon::create($object->start_date)->format('jS F Y'));
                $object->setAttribute('end_date', Carbon::create($object->end_date)->format('jS F Y'));
            });

            // Return response
            return response()->jsonApi([
                'title' => 'Products list',
                'message' => 'Products list been received',
                'data' => $products,
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Products list',
                'message' => $e->getMessage()
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
     *     tags={"Public | Products"},
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
     *         response="200",
     *         description="Data fetched",
     *         @OA\JsonContent(ref="#/components/schemas/OkResponse")
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
            $product = Product::query()
                ->with('prices')
                ->where('ticker', $id)
                ->orWhere('id', $id)
                ->where('status', true)
                ->first();

            return response()->jsonApi([
                'title' => 'Product detail',
                'message' => 'Product detail been received',
                'data' => $product->toArray(),
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Product detail',
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
