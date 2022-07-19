<?php

namespace App\Api\V1\Controllers\Application;

use App\Api\V1\Controllers\Controller;
use App\Api\V1\Services\TransactionService;
use App\Models\Order;
use App\Models\Product;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Sumra\SDK\JsonApiResponse;

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
     * @OA\Get(
     *     path="/orders",
     *     summary="Getting created order by user if exist",
     *     description="Getting created order by user if exist",
     *     tags={"Orders"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     }},
     *
     *     @OA\Response(
     *          response="200",
     *          description="Detail data of order"
     *     )
     * )
     */
    public function index()
    {
        // Get order
        $order = Order::byOwner()
            ->where('status', Order::STATUS_NEW)
            ->with(['transaction' => function ($query) {
                $query->select('id', 'order_id', 'wallet_address', 'payment_type_id');
            }, 'transaction.payment_type'])
            ->get();

        return response()->jsonApi([
            'type' => 'success',
            'title' => 'Order details data',
            'message' => "Order detail data has been received",
            'data' => $order->toArray()
        ], 200);
    }

    /**
     * Create a new investment order
     *
     * @OA\Post(
     *     path="/orders",
     *     summary="Create a new investment order",
     *     description="Create a new investment order",
     *     tags={"Orders"},
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
        // Try to save received data
        try {
            // Validate input
            $this->validate($request, $this->model::validationRules());

            // Get / checking current product
            $product = Product::findOrFail($request->get('product_id', config('settings.empty_uuid')));

            // Create new order
            $order = $this->model::create([
                'product_id' => $product->id,
                'investment_amount' => $request->get('investment_amount'),
                'deposit_percentage' => $request->get('deposit_percentage'),
                'deposit_amount' => $request->get('deposit_amount'),
                'user_id' => Auth::user()->getAuthIdentifier(),
                'status' => Order::STATUS_NEW,
                'amount_token' => $request->get('investment_amount'),
                'amount_usd' => $request->get('investment_amount'),
            ]);

            // create new transaction
            $paramsTransactions = $request->all();
            $paramsTransactions['order_id'] = $order->id;
            $transaction = (new TransactionService())->store($paramsTransactions);
            $order->transaction;

            // Return response to client
            return response()->jsonApi([
                'type' => 'success',
                'title' => 'Creating new order',
                'message' => "New order has been created successfully",
                'data' => [
                    'order' => $order->toArray()
                ]
            ], 200);
        } catch (ValidationException $e) {
            return response()->jsonApi([
                'type' => 'warning',
                'title' => 'Creating new order',
                'message' => "Validation error: " . $e->getMessage(),
                'data' => null
            ], 400);
        } catch (ModelNotFoundException $e) {
            return response()->jsonApi([
                'type' => 'warning',
                'title' => 'Creating new order',
                'message' => "This product does not exist",
                'data' => null
            ], 400);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Creating new order',
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
            'product'
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
    private function getObject($id): mixed
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
