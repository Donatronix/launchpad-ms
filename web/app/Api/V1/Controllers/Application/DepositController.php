<?php

namespace App\Api\V1\Controllers\Application;

use App\Api\V1\Controllers\Controller;
use App\Api\V1\Services\TransactionService;
use App\Models\Deposit;
use App\Models\PaymentType;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Sumra\SDK\JsonApiResponse;

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
     * Getting created deposit by user if exist
     *
     * @OA\Get(
     *     path="/application/deposits",
     *     summary="Getting created deposit by user if exist",
     *     description="Getting created deposit by user if exist",
     *     tags={"Deposits"},
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
     *          description="Detail data of deposit"
     *     )
     * )
     */
    public function index()
    {
        // Get deposit
        $deposit = Deposit::byOwner()
            ->where('status', Deposit::STATUS_CREATED)
            ->first();

        return response()->jsonApi([
            'title' => 'Deposit details data',
            'message' => "Deposit detail data has been received",
            'data' => $deposit
        ]);
    }

    /**
     * Create a new investment deposit
     *
     * @OA\Post(
     *     path="/application/deposits",
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
     *
     * @return mixed
     */
    public function store(Request $request): mixed
    {
        // Validate input
        try {
            $this->validate($request, $this->model::validationRules());
        } catch (ValidationException $e) {
            return response()->jsonApi([
                'title' => 'New Deposit details data',
                'message' => "Validation error",
                'data' => $e->getMessage()
            ], 422);
        }

        $product = Product::where('ticker', $request->get('product_id',))->first();
        if (!$product) {
            return response()->jsonApi([
                'title' => 'New Deposit details data',
                'message' => "This product does not exist",
            ], 400);
        }

        // Try to save received data
        try {
            // Create new deposit
            $deposit = $this->model::create([
                'product_id' => $product->id,
                'investment_amount' => $request->get('investment_amount'),
                'deposit_percentage' => $request->get('deposit_percentage'),
                'amount' => $request->get('deposit_amount'),
                'user_id' => Auth::user()->getAuthIdentifier(),
                'status' => Deposit::STATUS_CREATED
            ]);

            // create new transaction
            $paramsTransactions = $request->all();
            $paramsTransactions['order_id'] = $deposit->id;
            $transaction = (new TransactionService())->store($paramsTransactions);
            $deposit->transaction;

            // Return response to client
            return response()->jsonApi([
                'title' => 'New deposit registration',
                'message' => "New deposit has been created successfully",
                'data' => $deposit->toArray()
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'New deposit registration',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Getting data about deposit by deposit ID
     *
     * @OA\Get(
     *     path="/application/deposits/{id}",
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
            'product'
        ]);

        return response()->jsonApi([
            'title' => 'Deposit details data',
            'message' => "Deposit detail data has been received",
            'data' => $deposit->toArray()
        ]);
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
                'title' => "Get deposit",
                'message' => "Deposit with id #{$id} not found: {$e->getMessage()}",
            ], 404);
        }
    }

    public function generatePdfForTransaction($transaction_id)
    {
        try {
            $transaction = (new TransactionService())->getOne($transaction_id);
            $deposit = $transaction->deposit;
            $transaction->date = $transaction->created_at->toDayDateTimeString();

            if ($transaction->payment_type_id == PaymentType::DEBIT_CARD) {
                $pdf = PDF::loadView('pdf.receipt.deposit-card', $transaction->toArray());
                return $pdf->download('pdf.receipt.deposit-card');
            } elseif ($transaction->payment_type_id == PaymentType::CRYPTO
                || $transaction->payment_type_id == PaymentType::FIAT) {
                $pdf = PDF::loadView('pdf.receipt.deposit-wallet', $transaction->toArray());
                return $pdf->download('pdf.receipt.deposit-wallet');
            }

            return response()->jsonApi([
                'title' => 'Deposit details data',
                'message' => "Deposit detail data has been received",
                'data' => $transaction->toArray()
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->jsonApi([
                'title' => "Get deposit",
                'message' => "Transaction with id #{$transaction_id} not found: {$e->getMessage()}",
            ], 404);
        }
    }
}
