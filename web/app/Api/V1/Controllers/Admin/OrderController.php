<?php

namespace App\Api\V1\Controllers\Admin;

use App\Api\V1\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     }},
     *
     *     @OA\Parameter(
     *         name="limit",
     *         description="Count of orders in response",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *              type="integer",
     *              default=20,
     *         )
     *     ),
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
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search keywords",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sort-by",
     *         in="query",
     *         description="Sort by field ()",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sort-order",
     *         in="query",
     *         description="Sort order (asc, desc)",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Response(
     *          response="200",
     *          description="Success",
     *     ),
     *
     *     @OA\Response(
     *         response="500",
     *         description="Unknown error"
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Invalid request"
     *     ),
     *
     *     @OA\Response(
     *         response="404",
     *         description="Not Found"
     *     )
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $allOrders = Order::orderBy('created_at', 'Desc')
                ->with(['transaction', 'product'])
                ->orderBy($request->get('sort-by', 'created_at'), $request->get('sort-order', 'desc'))
                ->paginate($request->get('limit', config('settings.pagination_limit')));

            return response()->json([
                'type' => 'success',
                'title' => "List all orders",
                'message' => "List all orders",
                'data' => $allOrders->toArray()
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'List all orders',
                'message' => 'Error in getting list of all orders: '.$e->getMessage(),
                'data' => null
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
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="product_id",
     *                 type="string",
     *                 description="product id",
     *                 example="2000-000-3000000-20000"
     *             ),
     *             @OA\Property(
     *                 property="investment_amount",
     *                 type="decimal",
     *                 description="amount to investment",
     *                 example="1500.00"
     *             ),
     *             @OA\Property(
     *                 property="deposit_percentage",
     *                 type="integer",
     *                 description="deposit percentage",
     *                 example="20000-9000000-90000"
     *             ),
     *             @OA\Property(
     *                 property="deposit_amount",
     *                 type="decimal",
     *                 description="deposit_amount",
     *                 example="1"
     *             ),
     *             @OA\Property(
     *                 property="amount_token",
     *                 type="string",
     *                 description="amount token",
     *                 example="5590"
     *             ),
     *             @OA\Property(
     *                 property="amount_usd",
     *                 type="string",
     *                 description="amount usd",
     *                 example="5590"
     *             ),
     *             @OA\Property(
     *                 property="user_id",
     *                 type="string",
     *                 description="user id",
     *                 example="550000-9000000-9000000"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *     ),
     *
     *     @OA\Response(
     *         response="500",
     *         description="Unknown error"
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Invalid request"
     *     ),
     *
     *     @OA\Response(
     *         response="404",
     *         description="Not Found"
     *     )
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        try {
            //validate input
            $this->validate($request, [
                'product_id' => 'required|string',
                'investment_amount' => 'required|numeric',
                'deposit_amount' => 'required|numeric',
                'deposit_percentage' => 'required|string',
                'amount_token' => 'required|string',
                'amount_usd' => 'required|string',
                'user_id' => 'required|string',
            ]);

            if(!Product::where('id', $request->product_id)->exists()){
                return response()->jsonApi([
                    'type' => 'danger',
                    'title' => 'Create new order',
                    'message' => 'Error occurred when creating new order',
                    'data' => "Product id is invalid"
                ], 400);
            }

            $orderSaved = Order::create($request->all());


            return response()->jsonApi([
                'type' => 'success',
                'title' => "Create new order",
                'message' => "Order was created",
                'data' => $orderSaved->toArray()
            ], 200);
        } catch (ValidationException $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Create new order',
                'message' => 'Error occurred when creating new order: '.$e->getMessage(),
                'data' => null
            ], 400);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Create new order',
                'message' => 'Error occurred when creating new order: '.$e->getMessage(),
                'data' => null
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
     *     ),
     *
     *     @OA\Response(
     *         response="500",
     *         description="Unknown error"
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Invalid request"
     *     ),
     *
     *     @OA\Response(
     *         response="404",
     *         description="Not Found"
     *     )
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function show($id)
    {
        try {
            $order = Order::with(['product', 'transaction'])->findOrFail($id);

            // Return response
            return response()->json([
                'type' => 'success',
                'title' => "Get order",
                'message' => "Get order",
                'data' => $order
            ], 200);
        } catch (\Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Get order',
                'message' => 'Error in getting order: '.$e->getMessage(),
                'data' => null
            ], 400);
        }
    }

    /**
     * Update single Order
     *
     * @OA\Put(
     *      path="/admin/order/{id}",
     *     description="Update one order",
     *      tags={"Admin / Orders"},
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
     *         in="query",
     *         description="Order id",
     *         required=true,
     *      ),
     *
     *    @OA\RequestBody(
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
     *         response="500",
     *         description="Unknown error"
     *     ),
     *     @OA\Response(
     *         response="400",
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
    public function update(Request $request, $id)
    {
        try {
            //validate input
            $this->validate($request, [
                'product_id' => 'required|string',
                'investment_amount' => 'required|numeric',
                'deposit_amount' => 'required|numeric',
                'deposit_percentage' => 'required|string',
                'amount_token' => 'required|string',
                'amount_usd' => 'required|string',
                'user_id' => 'required|string',
            ]);

            if(!Product::where('id', $request->product_id)->exists()){
                return response()->jsonApi([
                    'type' => 'danger',
                    'title' => 'Create new order',
                    'message' => 'Error occurred when creating new order',
                    'data' => "Product id is invalid"
                ], 400);
            }

            $orderUpdated = Order::findOrFail($id);
            $orderUpdated->update($request->all());

            // Return response
            return response()->jsonApi([
                'type' => 'success',
                'title' => "Order was updated",
                'message' => "Order was updated",
                'data' => $orderUpdated
            ], 200);
        } catch (ValidationException $e) {
            return response()->jsonApi([
                'type' => 'warning',
                'title' => 'update Order',
                'message' => 'Error occurred when updating order: '.$e->getMessage(),
                'data' => null
            ], 400);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Update Order',
                'message' => 'Error occurred when updating order: '.$e->getMessage(),
                'data' => null
            ], 400);
        }
    }

    /**
     * Approve single Order
     *
     * @OA\get(
     *      path="/admin/orders/approve/{id}",
     *     description="Update one order",
     *      tags={"Admin / Orders"},
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
     *         in="query",
     *         description="Order id",
     *         required=true,
     *      ),
     *
     *
     *     @OA\Response(
     *         response="500",
     *         description="Unknown error"
     *     ),
     *     @OA\Response(
     *         response="400",
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
    public function approve($id)
    {
        try {
            $order = Order::findOrFail($id);
            $approveOrder = $order->where('id', $id)->update(['status' => Order::STATUS_COMPLETED]);

            // Return response
            return response()->jsonApi([
                'type' => 'success',
                'title' => "Approve Order",
                'message' => "Order was approved",
                'data' => $approveOrder
            ], 200);
        } catch (\Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Approve Order',
                'message' => 'Error occurred when approving order: '.$e->getMessage(),
                'data' => null
            ], 400);
        }
    }

    /**
     * Approve single Order
     *
     * @OA\get(
     *      path="/admin/orders/reject/{id}",
     *     description="Update one order",
     *      tags={"Admin / Orders"},
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
     *         in="query",
     *         description="Order id",
     *         required=true,
     *      ),
     *
     *     @OA\Response(
     *         response="500",
     *         description="Unknown error"
     *     ),
     *     @OA\Response(
     *         response="400",
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
    public function reject($id)
    {
        try {
            $order = Order::findOrFail($id);
            $approveOrder = $order->where('id', $id)
                ->update(['status' => Order::STATUS_CANCELED]);

            // Return response
            return response()->jsonApi([
                'type' => 'success',
                'title' => "Reject Order",
                'message' => "Order was rejected",
                'data' => $approveOrder
            ], 200);
        } catch (\Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Reject Order',
                'message' => 'Error occurred when rejecting order: '.$e->getMessage(),
                'data' => null
            ], 400);
        }
    }
}
