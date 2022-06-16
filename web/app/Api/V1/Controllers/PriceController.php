<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
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
     *     tags={"Prices"},
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
            ->where('product_id', '957d387a-e1a3-44ef-af29-6ce9118d67b4')
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
     *     tags={"Prices"},
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
     * @param int     $stage
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
