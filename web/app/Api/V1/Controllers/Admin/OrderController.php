<?php

namespace App\Api\V1\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Carbon\Carbon;
use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Sumra\SDK\JsonApiResponse;


/**
 * Class OrderController
 *
 * @package App\Api\V1\Controllers
 */
class OrderController extends Controller
{
    /**
     * Display list of all orders
     *
     * @OA\Get(
     *     path="/admin/orders",
     *     description="Getting all data about order for all users",
     *     tags={"Admin / Orders"},
     *
     *      security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *      }},
     *
     *
     *       @OA\Parameter(
     *         name="limit",
     *         description="Count of orders in response",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *              type="integer",
     *              default=20,
     *         )
     *      ),
     *     @OA\Parameter(
     *         name="page",
     *         description="Page of list",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *              type="integer",
     *              default=1,
     *         )
     *     ),
     *
     *     @OA\Response(
     *          response="200",
     *          description="Success",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *
     *              )
     *          ),
     *     ),
     * )
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $allOrders = Order::orderBy('created_at', 'Desc')
                ->paginate($request->get('limit', 20));
            $resp['type']       = "Success";
            $resp['title']      = "List all orders";
            $resp['message']    = "List all orders";
            $resp['data']       = $allOrders;
            return response()->json($resp, 200);
        } catch (Exception $e) {
            return response()->json([
                'type'  => 'danger',
                'title'  => 'List all orders',
                'message' => 'Error in getting list of all orders',
                'data' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Create new orders
     *
     * @OA\Post(
     *     path="/admin/orders",
     *     description="Adding new orders",
     *     tags={"Admin / Orders"},
     *
     *      security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *      }},
     *
     *       @OA\RequestBody(
     *            @OA\JsonContent(
     *                type="object",
     *                @OA\Property(
     *                    property="product_id",
     *                    type="string",
     *                    description="product id",
     *                    example="2000-000-3000000-20000"
     *                ),
     *                @OA\Property(
     *                    property="investment_amount",
     *                    type="decimal",
     *                    description="amount to investment",
     *                    example="1500.00"
     *                ),
     *                @OA\Property(
     *                    property="deposit_percentage",
     *                    type="integer",
     *                    description="deposit percentage",
     *                    example="20000-9000000-90000"
     *                ),
     *                @OA\Property(
     *                    property="deposit_amount",
     *                    type="decimal",
     *                    description="deposit_amount",
     *                    example="1"
     *                ),
     *                @OA\Property(
     *                    property="order_no",
     *                    type="string",
     *                    description="order number",
     *                    example="283728323"
     *                ),
     *               @OA\Property(
     *                    property="amount_token",
     *                    type="string",
     *                    description="amount token",
     *                    example="5590"
     *                ),
     *                @OA\Property(
     *                    property="amount_usd",
     *                    type="string",
     *                    description="amount usd",
     *                    example="5590"
     *                ),
     *                @OA\Property(
     *                    property="user_id",
     *                    type="string",
     *                    description="user id",
     *                    example="550000-9000000-9000000"
     *                ),
     *           ),
     *       ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Successfully saved"
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="Order created"
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Invalid request"
     *     ),
     * )
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            //validate input
            $this->validate($request, [
                'product_id'            => 'required|string',
                'investment_amount'     => 'required|decimal',
                'deposit_amount'        => 'required|decimal',
                'order_no'              => 'required|string',
                'deposit_percentage'    => 'required|string',
                'amount_token'          => 'required|string',
                'amount_usd'            => 'required|string',
                'user_id'               => 'required|string',
            ]);

            $orderSaved = Order::create([
                'product_id'            => $request['product_id'],
                'investment_amount'     => $request['investment_amount'],
                'deposit_amount'        => $request['deposit_amount'],
                'deposit_percentage'    => $request['deposit_percentage'],
                'amount_token'          => $request['amount_token'],
                'amount_usd'            => $request['amount_usd'],
                'user_id'               => Auth::user()->getAuthIdentifier(),
            ]);

            $resp['type']       = "Success";
            $resp['title']      = "Create new order";
            $resp['message']    = "Order was created";
            $resp['data']       = $orderSaved;
            return response()->json($resp, 200);
        } catch (ValidationException $e) {
            return response()->json([
                'type'  => 'danger',
                'title'  => 'Create new order',
                'message' => 'Error occurred when creating new order',
                'data' => $e->getMessage()
            ], 400);
        } catch (Exception $e) {
            return response()->json([
                'type'  => 'danger',
                'title'  => 'Create new order',
                'message' => 'Error occurred when creating new order',
                'data' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Display a single order
     *
     * @OA\Get(
     *     path="/admin/order/{id}",
     *     description="Get a single order",
     *     tags={"Admin / Orders"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="order ID",
     *         required=true,
     *      ),
     *
     *      @OA\Response(
     *          response="200",
     *          description="Success",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *
     *             @OA\Property(
     *                    property="product_id",
     *                    type="string",
     *                    description="product id",
     *                    example="2000-000-3000000-20000"
     *                ),
     *                @OA\Property(
     *                    property="investment_amount",
     *                    type="decimal",
     *                    description="amount to investment",
     *                    example="1500.00"
     *                ),
     *                @OA\Property(
     *                    property="deposit_percentage",
     *                    type="integer",
     *                    description="deposit percentage",
     *                    example="20000-9000000-90000"
     *                ),
     *                @OA\Property(
     *                    property="deposit_amount",
     *                    type="decimal",
     *                    description="deposit_amount",
     *                    example="1"
     *                ),
     *                @OA\Property(
     *                    property="order_no",
     *                    type="string",
     *                    description="order number",
     *                    example="283728323"
     *                ),
     *               @OA\Property(
     *                    property="amount_token",
     *                    type="string",
     *                    description="amount token",
     *                    example="5590"
     *                ),
     *                @OA\Property(
     *                    property="amount_usd",
     *                    type="string",
     *                    description="amount usd",
     *                    example="5590"
     *                ),
     *                @OA\Property(
     *                    property="user_id",
     *                    type="string",
     *                    description="user id",
     *                    example="550000-9000000-9000000"
     *                ),
     *              )
     *          )
     *     ),
     *
     *     @OA\Response(
     *         response="500",
     *         description="Unknown error"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *
     *     @OA\Response(
     *         response="404",
     *         description="Not Found"
     *     ),
     * )
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {

        try {
            $order = Order::findOrFail($id);

            $resp['type']       = "Success";
            $resp['title']      = "Get order";
            $resp['message']    = "Get order";
            $resp['data']       = $order ? $order->with('product')->with('transaction') : [];
            return response()->json($resp, 200);
        } catch (\Exception $e) {
            return response()->json([
                'type'  => 'danger',
                'title'  => 'Get order',
                'message' => 'Error in getting order',
                'data' => $e->getMessage()
            ], 400);
        }
    }
}//end class
