<?php

namespace App\Api\V1\Controllers\Public;

use App\Api\V1\Controllers\Controller;
use App\Models\Price;
use Exception;
use Illuminate\Http\Request;

class PriceController extends Controller
{
    /**
     * Getting a listing of product prices
     *
     * @OA\Get(
     *     path="/prices",
     *     summary="Getting a listing of product prices",
     *     description="Getting a listing of product prices",
     *     tags={"Public | Prices"},
     *
     *     @OA\Parameter(
     *         name="product_id",
     *         in="query",
     *         description="Get price by product with product id",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Response(
     *          response="200",
     *          description="Getting a listing of product prices"
     *     )
     * )
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function index(Request $request): mixed
    {
        // Get order
        $order = Price::where('status', true)
            ->select(['stage', 'price', 'period_in_days', 'percent_profit', 'amount'])
            ->where('product_id', $request->product_id)
            ->paginate($request->get('limit', config('settings.pagination_limit')));

        return response()->jsonApi([
            'title' => 'Product prices list',
            'message' => "Product prices list has been received",
            'data' => $order->toArray(),
        ]);
    }

    /**
     * Getting a listing of product prices by stage
     *
     * @OA\Get(
     *     path="/prices/{stage}",
     *     summary="Getting a listing of product prices by stage",
     *     description="Getting a listing of product prices by stage",
     *     tags={"Public | Prices"},
     *
     *     @OA\Parameter(
     *         name="stage",
     *         description="Stage",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             default="1"
     *         )
     *     ),
     *
     *     @OA\Response(
     *          response="200",
     *          description="Getting a listing of product prices"
     *     )
     * )
     *
     * @param Request $request
     * @param int $stage
     *
     * @return mixed
     */
    public function getPriceByStage(Request $request, int $stage): mixed
    {
        try {
            // Get prices
            if($request->has("product_id")){
                $prices = Price::where(['stage' => $stage, 'product_id' => $request->get("product_id")])
                    ->first();
            }else{
            $prices = Price::where('stage', $stage)
                ->paginate($request->get('limit', config('settings.pagination_limit')));
            }

            return response()->jsonApi([
                'title' => 'Product prices list by stage',
                'message' => "Product prices list by stage has been received",
                'data' => $prices->toArray(),
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Prices list',
                'message' => $e->getMessage(),
                'data'=> null
            ], 400);
        }
    }
}
