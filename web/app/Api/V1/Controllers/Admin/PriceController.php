<?php

namespace App\Api\V1\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Price;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PriceController extends Controller
{
    /**
     * Getting a listing of product prices
     *
     * @OA\Get(
     *     path="/admin/prices",
     *     summary="Getting a listing of product prices",
     *     description="Getting a listing of product prices",
     *     tags={"Admin / Prices"},
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
     * @return mixed
     */
    public function index(Request $request): mixed
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
            'data' => $order->toArray()
        ], 200);
    }

    /**
     * Store a newly stage price in storage.
     *
     * @OA\Post(
     *     path="/admin/prices",
     *     summary="Saving new stage price",
     *     description="Saving new stage price",
     *     tags={"Admin / Prices"},
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
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ProductPrice")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successfully save"
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="Contributor created"
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
     *
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        // Validate input
        try {
            $this->validate($request, Price::validationRules());
        } catch (ValidationException $e) {
            return response()->jsonApi([
                'type' => 'warning',
                'title' => 'Contributor person details data',
                'message' => "Validation error",
                'data' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param Price $price
     */
    public function show(Price $price)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Price $price
     */
    public function update(Request $request, Price $price)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Price $price
     * @return Response
     */
    public function destroy(Price $price)
    {
        //
    }
}
