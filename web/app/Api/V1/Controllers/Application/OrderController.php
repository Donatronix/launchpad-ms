<?php

namespace App\Api\V1\Controllers\Application;

use App\Api\V1\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Sumra\SDK\Services\JsonApiResponse;

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
     *     path="/app/orders",
     *     summary="Getting created order by user if exist",
     *     description="Getting created order by user if exist",
     *     tags={"Application | Orders"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Getting product list for start presale",
     *         @OA\JsonContent(ref="#/components/schemas/OkResponse")
     *     ),
     * )
     */
    public function index()
    {
        try {
            // Get order
            $order = Order::byOwner()
                ->where('status', Order::STATUS_NEW)
                ->with(['transaction' => function ($query) {
                    $query->select('id', 'order_id', 'wallet_address', 'card_number', 'payment_type_id');
                }, 'transaction.payment_type'])
                ->get();

            if (!empty($order) && $order != null) {
                return response()->jsonApi([
                    'title' => 'Orders details data',
                    'message' => "Orders list has been received",
                    'data' => $order
                ]);
            }

            return response()->jsonApi([
                'title' => 'Order details data',
                'message' => "Orders is missing"
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Order details data',
                'message' => "Unable to retrieve order details" . $e->getMessage()
            ], $e->getCode());
        }
    }

    /**
     * Create a new investment order
     *
     * @OA\Post(
     *     path="/app/orders",
     *     summary="Create a new investment order",
     *     description="Create a new investment order",
     *     tags={"Application | Orders"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Order")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Getting product list for start presale",
     *         @OA\JsonContent(ref="#/components/schemas/OkResponse")
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="New record addedd successfully",
     *         @OA\JsonContent(ref="#/components/schemas/OkResponse")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error",
     *         @OA\JsonContent(ref="#/components/schemas/DangerResponse")
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(ref="#/components/schemas/WarningResponse")
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Validation Failed",
     *         @OA\JsonContent(ref="#/components/schemas/WarningResponse")
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
            $validator = Validator::make($request->all(), $this->model::validationRules());

            if ($validator->fails()) {
                return response()->jsonApi([
                    'title' => 'Creating new order',
                    'message' => "Validation error occurred!",
                    'data' => $validator->errors()
                ], 422);
            }

            // Create new order
            $order = $this->model::create([
                'product_id' => $request->get('product_id'),
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
            // $transaction = (new TransactionService())->store($paramsTransactions);
            $order->transaction;

            // Return response to client
            return response()->jsonApi([
                'title' => 'Creating new order',
                'message' => "New order has been created successfully",
                'data' => $order
            ]);
        } catch (ValidationException $e) {
            return response()->jsonApi([
                'title' => 'Creating new order',
                'message' => "Validation error: " . $e->getMessage()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->jsonApi([
                'title' => 'Creating new order',
                'message' => "This product does not exist",
            ], 404);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Creating new order',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Getting data about order
     *
     * @OA\Get(
     *     path="/app/orders/{id}",
     *     summary="Getting data about order",
     *     description="Getting data about order",
     *     tags={"Application | Orders"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
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
     *         response="200",
     *         description="Getting product list for start presale",
     *         @OA\JsonContent(ref="#/components/schemas/OkResponse")
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(ref="#/components/schemas/WarningResponse")
     *     ),
     * )
     */
    public function show($id)
    {
        try {
            // Get order object
            $order = $this->model::findOrFail($id);

            // Load linked relations data
            $order->load([
                'product',
                'deposits'
            ]);

            return response()->jsonApi([
                'title' => 'Order details data',
                'message' => "Order detail data has been received",
                'data' => $order
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->jsonApi([
                'title' => "Get order",
                'message' => "Order with id #{$id} not found: {$e->getMessage()}"
            ], 404);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Order details data',
                'message' => "Unable to retrieve order details" . $e->getMessage()
            ], $e->getCode());
        }
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
                'title' => "Get order",
                'message' => "Order with id #{$id} not found: {$e->getMessage()}"
            ], 404);
        }
    }
}
