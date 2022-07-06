<?php

namespace App\Api\V1\Controllers\Application;

use App\Api\V1\Controllers\Controller;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Validator;

use function PHPUnit\Framework\throwException;

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
     *         description="Not Found"
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

    /**
     * List Token investors
     *
     * @OA\Get(
     *     path="/token-investors/{product_id}",
     *     description="List the users that have invested in a token",
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
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Product Id",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     * 
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
     *         description="Not Found"
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
    public function tokenInvestors(Request $request): mixed
    {
        // Try to save purchased token data
        try {
            if(!$request->has('product_id')){
                throw new Exception("Product_id required as query string");
            }
            // get unique user_id for the product
            $purchases = $this->purchase::where([
                'product_id' => $request->get('product_id')
                ])->select("user_id")->distinct()->get();
            $investors = [];

                // Loop through each of the purchase to get the user details 
                foreach($purchases as $key => $value){
                    $user = \DB::connection('identity')->table('users')->where('id',$value['user_id'])->select(["id","first_name","last_name","phone","email_verified_at", "status", "id_number"])->first();
                    // sum the tokens for the user
                    $tokens = $this->purchase::where([
                        'product_id' => $request->get('product_id'), 'user_id' => $value['user_id']
                        ])->sum("token_amount");
                    $user->tokens = $tokens;
                    array_push($investors, $user);
                }


            // Return response to client
            return response()->jsonApi([
                'type' => 'success',
                'title' => 'Token investors',
                'message' => "Token investors listed successfully",
                'data' => $investors
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Token investors',
                'message' => $e->getMessage(),
                'data' => null
            ], 400);
        }
    }
}