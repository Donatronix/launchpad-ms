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
     *     tags={"User | Prices"},
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
    public function __invoke(Request $request): mixed
    {
        // Get order
        $order = Price::where('status', true)
            ->select(['stage', 'price', 'period_in_days', 'percent_profit', 'amount'])
            ->where('product_id', $request->product_id)
            ->get();

        return response()->jsonApi([
            'type' => 'success',
            'title' => 'Product prices list',
            'message' => "Product prices list has been received",
            'data' => $order->toArray(),
        ], 200);
    }

    /**
     * Getting a listing of product prices by stage
     *
     * @OA\Get(
     *     path="/prices/{stage}",
     *     summary="Getting a listing of product prices by stage",
     *     description="Getting a listing of product prices by stage",
     *     tags={"User | Prices"},
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
     *         name="stage",
     *         description="Stage",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="int",
     *              default="1"
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
            $prices = Price::where('stage', $stage)
                ->get();

            return response()->jsonApi([
                'type' => 'success',
                'title' => 'Product prices list by stage',
                'message' => "Product prices list by stage has been received",
                'data' => $prices->toArray(),
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Prices list',
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
