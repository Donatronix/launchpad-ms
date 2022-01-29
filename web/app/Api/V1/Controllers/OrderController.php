<?php

namespace App\Api\V1\Controllers;

use App\Exceptions\OrderException;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sumra\JsonApi\JsonApiResponse;

/**
 * Class OrderController
 *
 * @package App\Api\V1\Controllers
 */
class OrderController extends Controller
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Order::class;

    /**
     * OrderController constructor.
     *
     * @param Order $model
     */
    public function __construct(Order $model)
    {
        $this->model = $model;
    }

    /**
     * Save a new order data
     *
     * @OA\Post(
     *     path="/orders",
     *     summary="Save a new order data",
     *     description="Save a new order data",
     *     tags={"Orders"},
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
     *         @OA\JsonContent(ref="#/components/schemas/Order")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successfully save"
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="Order created"
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
     */
    public function store(Request $request)
    {
        // Validate input
        try {
            $this->validate($request, $this->model::validationRules);
        } catch (ValidationException $e){
            return response()->jsonApi([
                'type' => 'warning',
                'title' => 'New Order details data',
                'message' => "Validation error",
                'data' => $e->getMessage()
            ], 400);
        }

        // Try to save received data
        try {
            // Create new
            $order = $this->model::create([
                'status' => Order::STATUS_NEW
            ]);

            $order->fill($request->all());
            $order->save();

            // Return response to client
            return response()->jsonApi($order, 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'New order registration',
                'message' => $e->getMessage(),
                'data' => null
            ], 400);
        }
    }

    /**
     * Getting data about order
     *
     * @OA\Get(
     *     path="/orders/{id}",
     *     summary="Getting data about order",
     *     description="Getting data about order",
     *     tags={"Orders"},
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
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Orders ID",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Detail data of order"
     *     ),
     *     @OA\Response(
     *          response="404",
     *          description="Order not found"
     *     )
     * )
     */
    public function show($id)
    {
        // Get object
        $order = $this->getObject($id);

        if ($order instanceof JsonApiResponse) {
            return $order;
        }

        // Load linked relations data
        $order->load([
            'contributors'
        ]);

        return response()->jsonApi([
            'type' => 'success',
            'title' => 'Order details data',
            'message' => "Order detail data has been received",
            'data' => $order->toArray()
        ], 200);
    }

    /**
     * Get order object
     *
     * @param $id
     * @return mixed
     */
    private function getObject($id)
    {
        try {
            return $this->model::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Get order",
                'message' => "Order with id #{$id} not found: {$e->getMessage()}",
                'data' => null
            ], 404);
        }
    }
}
