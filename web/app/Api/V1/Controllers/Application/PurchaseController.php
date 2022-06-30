<?php

namespace App\Api\V1\Controllers\Application;

use App\Api\V1\Controllers\Controller;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Validator;

class PurchaseController extends Controller
{
    /**
     * @param Purchase $purchase
     */
    private Purchase $purchase;

    public function __construct(Purchase $purchase)
    {
        $this->purchase = $purchase;
        $this->user_id = auth()->user()->getAuthIdentifier();
    }


    /**
     * Purchase a Token
     *
     * @OA\Post(
     *     path="/purchase-token",
     *     summary="Purchase a token",
     *     description="Create a token purchase order",
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
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Purchase")
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
    public function store(Request $request): mixed
    {
        // Try to save purchased token data
        try {
            // Validate input
            $validator = Validator::make($request->all(), $this->purchase::validationRules());
            if ($validator->fails()) {
                throw new Exception($validator->errors()->first());
            }

            // Create new token purchase order
            $purchase = $this->purchase::create([
                'product_id' => $request->get('product_id'),
                'user_id' => $this->user_id,
                'amount_usd' => $request->get('amount_usd'),
                'token_amount' => $request->get('token_amount'),
                'payment_method' => $request->get('payment_method'),
                'payment_status' => $request->get('payment_status'),
            ]);

            // Return response to client
            return response()->jsonApi([
                'type' => 'success',
                'title' => 'Creating new token purchase order',
                'message' => "New token purchase order has been created successfully",
                'data' => $purchase->toArray()
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
}
