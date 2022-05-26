<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Services\TransactionService;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentType;
use App\Models\Product;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Sumra\SDK\JsonApiResponse;
use Barryvdh\DomPDF\Facade\Pdf;


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
     * Getting created order by contributor if exist
     *
     * @OA\Get(
     *     path="/orders",
     *     summary="Getting created order by contributor if exist",
     *     description="Getting created order by contributor if exist",
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
     *     @OA\Response(
     *          response="200",
     *          description="Detail data of order"
     *     )
     * )
     */
    public function index(){
        // Get order
        $order = Order::where('contributor_id', Auth::user()->getAuthIdentifier())
            ->where('status', Order::STATUS_NEW)
            ->first();

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
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request): mixed
    {
        // Validate input
        try {
            $this->validate($request, $this->model::validationRules());
        } catch (ValidationException $e){
            return response()->jsonApi([
                'type' => 'warning',
                'title' => 'New Order details data',
                'message' => "Validation error",
                'data' => $e->getMessage()
            ], 400);
        }

        $product = Product::where('currency_code', $request->get('product'))->first();
        if(!$product){
            return response()->jsonApi([
                'type' => 'warning',
                'title' => 'New Order details data',
                'message' => "This product does not exist",
                'data' => []
            ], 400);
        }

        // Try to save received data
        try {
            // Create new order
            $order = $this->model::create([
                'product_id' => $product->id,
                'investment_amount' => $request->get('investment_amount'),
                'deposit_percentage' => $request->get('deposit_percentage'),
                'deposit_amount' => $request->get('deposit_amount'),
                'contributor_id' => Auth::user()->getAuthIdentifier(),
                'status' => Order::STATUS_NEW
            ]);

            // create new transaction
            $paramsTransactions = $request->all();
            $paramsTransactions['order_id'] = $order->id;
            $transaction = (new TransactionService())->store($paramsTransactions);
            $order->transaction;

            // Return response to client
            return response()->jsonApi([
                'type' => 'success',
                'title' => 'New order registration',
                'message' => "New order has been created successfully",
                'data' => $order->toArray()
            ], 200);
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
     * Getting data about order by ORDER ID
     *
     * @OA\Get(
     *     path="/orders/{id}",
     *     summary="Getting data about order by ORDER ID",
     *     description="Getting data about order by ORDER ID",
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
            'product',
            'contributor'
        ]);

        return response()->jsonApi([
            'type' => 'success',
            'title' => 'Order details data',
            'message' => "Order detail data has been received",
            'data' => $order->toArray()
        ], 200);
    }

    public function generatePdfForTransaction($transaction_id)
    {
        try {
            $transaction = (new TransactionService())->getOne($transaction_id);
            $order = $transaction->order;

            if($transaction->payment_type_id == PaymentType::DEBIT_CARD) {
                $pdf = PDF::loadView('pdf.receipt.deposit-card', $transaction->toArray());
                return $pdf->download('pdf.receipt.deposit-card');
            }
            elseif ($transaction->payment_type_id == PaymentType::CRYPTO
                || $transaction->payment_type_id == PaymentType::FIAT ){
                $pdf = PDF::loadView('pdf.receipt.deposit-wallet', $transaction->toArray());
                return $pdf->download('pdf.receipt.deposit-wallet');
            }

            return response()->jsonApi([
                'type' => 'success',
                'title' => 'Order details data',
                'message' => "Order detail data has been received",
                'data' => $transaction->toArray()
            ], 200);

        } Catch(ModelNotFoundException $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Get order",
                'message' => "Transaction with id #{$transaction_id} not found: {$e->getMessage()}",
                'data' => ''
            ], 404);
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
                'type' => 'danger',
                'title' => "Get order",
                'message' => "Order with id #{$id} not found: {$e->getMessage()}",
                'data' => ''
            ], 404);
        }
    }
}
