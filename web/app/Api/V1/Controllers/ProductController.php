<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Getting product detail by platform
     *
     * @OA\Get(
     *     path="/products",
     *     summary="Getting product detail by platform",
     *     description="Getting product detail by platform",
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
     *          description="Getting product detail by platform"
     *     )
     * )
     *
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        // Get order
        $order = Product::where('currency_code', '$utta')
            ->where('status', true)
            ->first();

        return response()->jsonApi([
            'type' => 'success',
            'title' => 'Product detail',
            'message' => "Product detail been received",
            'data' => $order->toArray()
        ], 200);
    }
}
