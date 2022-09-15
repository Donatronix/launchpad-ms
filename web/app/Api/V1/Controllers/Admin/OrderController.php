<?php

namespace App\Api\V1\Controllers\Admin;

use App\Api\V1\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sumra\SDK\Traits\Resolve\IdentityResolveTrait;

/**
 * Class OrderController
 *
 * @package App\Api\V1\Controllers
 */
class OrderController extends Controller
{
    use IdentityResolveTrait;

    /**
     * Display list of all orders
     *
     * @OA\Get(
     *     path="/admin/orders",
     *     description="Getting all data about order for all users",
     *     tags={"Admin | Orders"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Deposits status (created, paid, failed, canceled)",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         description="Count of orders in response",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=20
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         description="Page of list",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=1
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
     *         response="200",
     *         description="Data fetched",
     *         @OA\JsonContent(ref="#/components/schemas/OkResponse")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error",
     *         @OA\JsonContent(ref="#/components/schemas/DangerResponse")
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Server error",
     *         @OA\JsonContent(ref="#/components/schemas/DangerResponse")
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(ref="#/components/schemas/WarningResponse")
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
            $orders = Order::orderBy('created_at', 'Desc')
                ->with(['transaction', 'product'])
                ->orderBy($request->get('sort-by', 'created_at'), $request->get('sort-order', 'desc'))
                ->paginate($request->get('limit', config('settings.pagination_limit')));

            // Transform objects
            $orders->map(function ($object) {
                // Get User detail
                $user = [
                    'id' => $object->user_id,
                    'name' => $this->getUserDetail($object->user_id)
                ];
                $object->setAttribute('user', $user);
                unset($object->user_id);

                // Update date
                $date = $object->created_at->format('d m Y h:i');
                unset($object->created_at);
                $object->setAttribute('created_date', $date);

                // Add status label
                $statuses = array_flip(Order::$statuses);
                $object->setAttribute('status_label', $statuses[$object->status]);
            });

            // Return response
            return response()->jsonApi([
                'title' => 'List all orders',
                'message' => 'List all orders',
                'data' => $orders
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'List all orders',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new orders
     *
     * @OA\Post(
     *     path="/admin/orders",
     *     description="Adding new orders",
     *     tags={"Admin | Orders"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="product_id",
     *                 type="string",
     *                 description="product id",
     *                 example="969ff58b-5d48-4de4-8e9e-cb6bb39e6041"
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
     *                 example="26"
     *             ),
     *             @OA\Property(
     *                 property="deposit_amount",
     *                 type="decimal",
     *                 description="deposit_amount",
     *                 example="1"
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
     *         description="Data fetched",
     *         @OA\JsonContent(ref="#/components/schemas/OkResponse")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error",
     *         @OA\JsonContent(ref="#/components/schemas/DangerResponse")
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Server error",
     *         @OA\JsonContent(ref="#/components/schemas/DangerResponse")
     *     )
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            //validate input
            $this->validate($request, [
                'product_id' => 'required|string',
                'investment_amount' => 'required|numeric',
                'deposit_amount' => 'required|numeric',
                'deposit_percentage' => 'required|numeric',
                'user_id' => 'required|string',
            ]);

            if (!Product::where('id', $request->product_id)->exists()) {
                return response()->jsonApi([
                    'title' => 'Create new order',
                    'message' => 'Error occurred when creating new order',
                    'data' => "Product id is invalid"
                ], 400);
            }

            $orderSaved = Order::create($request->all());

            // Return response
            return response()->jsonApi([
                'title' => "Create new order",
                'message' => "Order was created",
                'data' => $orderSaved->toArray()
            ]);
        } catch (ValidationException $e) {
            return response()->jsonApi([
                'title' => 'Create new order',
                'message' => 'Error occurred when creating new order: ' . $e->getMessage(),
            ], 422);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Create new order',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a single order
     *
     * @OA\Get(
     *     path="/admin/orders/{id}",
     *     description="Get a single order",
     *     tags={"Admin | Orders"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="order ID",
     *         required=true,
     *         example="96c890e5-7246-4714-a4db-70b63b16c8ef"
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Data fetched",
     *         @OA\JsonContent(ref="#/components/schemas/OkResponse")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error",
     *         @OA\JsonContent(ref="#/components/schemas/DangerResponse")
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(ref="#/components/schemas/WarningResponse")
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Server error",
     *         @OA\JsonContent(ref="#/components/schemas/DangerResponse")
     *     )
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        try {
            $order = Order::with(['product', 'transaction'])->findOrFail($id);

            // Return response
            return response()->jsonApi([
                'title' => "Get order",
                'message' => "Get order",
                'data' => $order
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Get order',
                'message' => 'Error in getting order: ' . $e->getMessage()
            ], 400);
        } catch (ModelNotFoundException $e) {
            return response()->jsonApi([
                'title' => 'Get order',
                'message' => 'Error in getting order: ' . $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Update single Order
     *
     * @OA\Put(
     *     path="/admin/orders/{id}",
     *     description="Update one order",
     *     tags={"Admin | Orders"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Order id",
     *         required=true,
     *         example="96c890e5-7246-4714-a4db-70b63b16c8ef"
     *     ),
     *
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="product_id",
     *                 type="string",
     *                 description="product id",
     *                 example="969ff58b-5d48-4de4-8e9e-cb6bb39e6041"
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
     *         description="Data fetched",
     *         @OA\JsonContent(ref="#/components/schemas/OkResponse")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error",
     *         @OA\JsonContent(ref="#/components/schemas/DangerResponse")
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(ref="#/components/schemas/WarningResponse")
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Server error",
     *         @OA\JsonContent(ref="#/components/schemas/DangerResponse")
     *     )
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            //validate input
            $this->validate($request, [
                'product_id' => 'required|string',
                'investment_amount' => 'required|numeric',
                'deposit_amount' => 'required|numeric',
                'deposit_percentage' => 'required|string',
                'user_id' => 'required|string',
            ]);

            if (!Product::where('id', $request->product_id)->exists()) {
                return response()->jsonApi([
                    'title' => 'Create new order',
                    'message' => 'Error occurred when creating new order',
                    'data' => "Product id is invalid"
                ], 400);
            }

            $orderUpdated = Order::findOrFail($id);
            $orderUpdated->update($request->all());

            // Return response
            return response()->jsonApi([
                'title' => "Order was updated",
                'message' => "Order was updated",
                'data' => $orderUpdated
            ]);
        } catch (ValidationException $e) {
            return response()->jsonApi([
                'title' => 'update Order',
                'message' => 'Error occurred when updating order: ' . $e->getMessage()
            ], 422);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Update Order',
                'message' => 'Error occurred when updating order: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Approve single Order
     *
     * @OA\get(
     *     path="/admin/orders/{id}/approve",
     *     description="Update one order",
     *     tags={"Admin | Orders"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Order id",
     *         required=true,
     *         example="96c890e5-7246-4714-a4db-70b63b16c8ef"
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Data fetched",
     *         @OA\JsonContent(ref="#/components/schemas/OkResponse")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error",
     *         @OA\JsonContent(ref="#/components/schemas/DangerResponse")
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(ref="#/components/schemas/WarningResponse")
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Server error",
     *         @OA\JsonContent(ref="#/components/schemas/DangerResponse")
     *     )
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function approve($id): JsonResponse
    {
        try {
            $order = Order::findOrFail($id);
            $order->update([
                'status' => Order::STATUS_COMPLETED
            ]);

            // Return response
            return response()->jsonApi([
                'title' => "Approve Order",
                'message' => "Order was approved",
                'data' => $order
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Approve Order',
                'message' => 'Error occurred when approving order: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Approve single Order
     *
     * @OA\get(
     *     path="/admin/orders/{id}/reject",
     *     description="Update one order",
     *     tags={"Admin | Orders"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Order id",
     *         required=true,
     *         example="96c890e5-7246-4714-a4db-70b63b16c8ef"
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Data fetched",
     *         @OA\JsonContent(ref="#/components/schemas/OkResponse")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error",
     *         @OA\JsonContent(ref="#/components/schemas/DangerResponse")
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(ref="#/components/schemas/WarningResponse")
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Server error",
     *         @OA\JsonContent(ref="#/components/schemas/DangerResponse")
     *     )
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function reject($id): JsonResponse
    {
        try {
            $order = Order::findOrFail($id);
            $order->update([
                'status' => Order::STATUS_CANCELED
            ]);

            // Return response
            return response()->jsonApi([
                'title' => "Reject Order",
                'message' => "Order was rejected",
                'data' => $order
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Reject Order',
                'message' => 'Error occurred when rejecting order: ' . $e->getMessage(),
            ], 400);
        }
    }
}
