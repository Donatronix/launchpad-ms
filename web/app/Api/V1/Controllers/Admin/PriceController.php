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
     *         description="Price created"
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
            $price = Price::create($request->all());
            return response()->json([
                'type' => 'success',
                'title' => "Create new Price",
                'message' => 'Price was successful created',
                'data' => $price
            ], 201);
            
        } catch (ValidationException $e) {
            return response()->jsonApi([
                'type' => 'warning',
                'title' => 'Saving new stage price',
                'message' => "Validation error",
                'data' => $e->getMessage()
            ], 400);
        }
    }

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

    /**
     * Display the specified resource.
     *
     * @param Price $price
     */
    public function show(Price $price)
    {
        $price->load('product');
        return response()->json([
            'type' => 'success',
            'data' => $price
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Price $price
     */
    public function update(Request $request, Price $price)
    {
        try {
            $this->validate($request, Price::validationRules());
            $price = Price::update($request->all());
            return response()->json([
                'type' => 'success',
                'title' => "Update Price",
                'message' => 'Price was successful updated',
                'data' => $price
            ], 201);
            
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
     * Remove the specified resource from storage.
     *
     * @param Price $price
     * @return Response
     */
    public function destroy(Price $price)
    {
        $price->delete();
        return response()->json([
            'type' => 'success',
            'title' => "Delete Price",
            'message' => 'Price was successful deleted',
        ], 201);

    }
}
