<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Services\TransactionService;
use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\PaymentType;
use App\Models\Product;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Sumra\SDK\JsonApiResponse;
use Barryvdh\DomPDF\Facade\Pdf;


/**
 * Class DepositController
 *
 * @package App\Api\V1\Controllers
 */
class DepositController extends Controller
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Deposit::class;

    /**
     * DepositController constructor.
     *
     * @param Deposit $model
     */
    public function __construct(Deposit $model)
    {
        $this->model = $model;
    }

    /**
     * Getting created deposit by contributor if exist
     *
     * @OA\Get(
     *     path="/deposits",
     *     summary="Getting created deposit by contributor if exist",
     *     description="Getting created deposit by contributor if exist",
     *     tags={"Deposits"},
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
     *          description="Detail data of deposit"
     *     )
     * )
     */
    public function index(){
        // Get deposit
        $deposit = Deposit::where('contributor_id', Auth::user()->getAuthIdentifier())
            ->where('status', Deposit::STATUS_NEW)
            ->first();

        return response()->jsonApi([
            'type' => 'success',
            'title' => 'Deposit details data',
            'message' => "Deposit detail data has been received",
            'data' => $deposit->toArray()
        ], 200);
    }


    /**
     * Create a new investment deposit
     *
     * @OA\Post(
     *     path="/deposits",
     *     summary="Create a new investment deposit",
     *     description="Create a new investment deposit",
     *     tags={"Deposits"},
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
     *         @OA\JsonContent(ref="#/components/schemas/Deposit")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successfully save"
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="Deposit created"
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
                'title' => 'New Deposit details data',
                'message' => "Validation error",
                'data' => $e->getMessage()
            ], 400);
        }

        $product = Product::where('ticker', $request->get('product'))->first();
        if(!$product){
            return response()->jsonApi([
                'type' => 'warning',
                'title' => 'New Deposit details data',
                'message' => "This product does not exist",
                'data' => []
            ], 400);
        }

        // Try to save received data
        try {
            // Create new deposit
            $deposit = $this->model::create([
                'product_id' => $product->id,
                'investment_amount' => $request->get('investment_amount'),
                'deposit_percentage' => $request->get('deposit_percentage'),
                'deposit_amount' => $request->get('deposit_amount'),
                'contributor_id' => Auth::user()->getAuthIdentifier(),
                'status' => Deposit::STATUS_NEW
            ]);

            // create new transaction
            $paramsTransactions = $request->all();
            $paramsTransactions['order_id'] = $deposit->id;
            $transaction = (new TransactionService())->store($paramsTransactions);
            $deposit->transaction;

            // Return response to client
            return response()->jsonApi([
                'type' => 'success',
                'title' => 'New deposit registration',
                'message' => "New deposit has been created successfully",
                'data' => $deposit->toArray()
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'New deposit registration',
                'message' => $e->getMessage(),
                'data' => null
            ], 400);
        }
    }

    /**
     * Getting data about deposit by deposit ID
     *
     * @OA\Get(
     *     path="/deposits/{id}",
     *     summary="Getting data about deposit by deposit ID",
     *     description="Getting data about deposit by deposit ID",
     *     tags={"Deposits"},
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
     *         description="Deposits ID",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Detail data of deposit"
     *     ),
     *     @OA\Response(
     *          response="404",
     *          description="Deposit not found"
     *     )
     * )
     */
    public function show($id)
    {
        // Get object
        $deposit = $this->getObject($id);

        if ($deposit instanceof JsonApiResponse) {
            return $deposit;
        }

        // Load linked relations data
        $deposit->load([
            'product',
            'contributor'
        ]);

        return response()->jsonApi([
            'type' => 'success',
            'title' => 'Deposit details data',
            'message' => "Deposit detail data has been received",
            'data' => $deposit->toArray()
        ], 200);
    }

    public function generatePdfForTransaction($transaction_id)
    {
        try {
            $transaction = (new TransactionService())->getOne($transaction_id);
            $deposit = $transaction->deposit;
            $transaction->date =  $transaction->created_at->toDayDateTimeString();

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
                'title' => 'Deposit details data',
                'message' => "Deposit detail data has been received",
                'data' => $transaction->toArray()
            ], 200);

        } Catch(ModelNotFoundException $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Get deposit",
                'message' => "Transaction with id #{$transaction_id} not found: {$e->getMessage()}",
                'data' => ''
            ], 404);
        }

    }

    /**
     * Get deposit object
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
                'title' => "Get deposit",
                'message' => "Deposit with id #{$id} not found: {$e->getMessage()}",
                'data' => ''
            ], 404);
        }
    }
}
