<?php

    namespace App\Api\V1\Controllers\Application;

    use App\Api\V1\Controllers\Controller;
    use App\Models\Deposit;
    use Exception;
    use Illuminate\Database\Eloquent\ModelNotFoundException;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Validation\Rule;
    use Illuminate\Validation\ValidationException;

    /**
     * Class DepositController
     *
     * @package App\Api\V1\Controllers
     */
    class DepositController extends Controller
    {
        /**
         * Getting created deposit by user if exist
         *
         * @OA\Get(
         *     path="/app/deposits",
         *     summary="Getting all created deposits by user if exist",
         *     description="Getting all created deposits by user if exist",
         *     tags={"Application | Deposits"},
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
         *         description="Count of deposits in response",
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
         *
         *     @OA\Response(
         *         response="200",
         *         description="Getting deposits list",
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
         * )
         *
         * @param Request $request
         *
         * @return mixed
         */
        public function index(Request $request): mixed
        {
            try {
                // Validate status if need
                $this->validate($request, [
                    'status' => [
                        'sometimes',
                        Rule::in(['created', 'paid', 'failed', 'canceled']),
                    ],
                ]);

                $result = Deposit::byOwner()
                    ->when($request->has('status'), function ($q) use ($request) {
                        $status = "STATUS_" . mb_strtoupper($request->get('status'));

                        return $q->where('status', intval(constant("App\Models\Deposit::{$status}")));
                    })
                    ->orderBy('created_at', 'desc')
                    ->paginate($request->get('limit', config('settings.pagination_limit')));

                // Return response
                return response()->jsonApi([
                    'title' => "List all deposits",
                    'message' => "List all deposits retrieved successfully.",
                    'data' => $result,
                ]);
            } catch (Exception $e) {
                return response()->jsonApi([
                    'title' => 'List all deposits',
                    'message' => 'Error in getting list of all deposits: ' . $e->getMessage(),
                ], $e->getCode());
            }
        }

        /**
         * Create a new investment deposit
         *
         * @OA\Post(
         *     path="/app/deposits",
         *     summary="Create a new investment deposit",
         *     description="Create a new investment deposit",
         *     tags={"Application | Deposits"},
         *
         *     security={{
         *         "bearerAuth": {},
         *         "apiKey": {}
         *     }},
         *
         *     @OA\RequestBody(
         *         required=true,
         *         @OA\JsonContent(ref="#/components/schemas/DepositUserAccess")
         *     ),
         *
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
         *
         * @param Request $request
         *
         * @return mixed
         */
        public function store(Request $request): mixed
        {
            // Validate input
            try {
                $this->validate($request, Deposit::validationRules());
            } catch (ValidationException $e) {
                return response()->jsonApi([
                    'title' => 'Creating a new deposit',
                    'message' => "Validation error: " . $e->getMessage(),
                    'data' => $e->validator->errors()->first(),
                ], 422);
            }

            // Try to save received data
            try {
                // Create deposit
                $deposit = Deposit::create([
                    'amount' => $request->get('amount'),
                    'currency_code' => $request->get('currency'),
                    'order_id' => config('settings.empty_uuid'),
                    'status' => Deposit::STATUS_CREATED,
                    'user_id' => Auth::user()->getAuthIdentifier(),
                ]);

//            // create new transaction
//            $paramsTransactions = $request->all();
//            $paramsTransactions['order_id'] = $deposit->id;
//            $transaction = (new TransactionService())->store($paramsTransactions);
                $notificationHandler = app()->make(sprintf("App\Listeners\PaymentUpdate\SendNotificationListener"));
                $notificationHandler::exec($deposit);
                // Return response to client
                return response()->jsonApi([
                    'title' => 'Creating a new deposit',
                    'message' => 'New deposit has been created successfully',
                    'data' => [
                        'amount' => $deposit->amount,
                        'currency' => $request->get('currency'),
                        'document' => [
                            'id' => $deposit->id,
                            'object' => class_basename(get_class($deposit)),
                            'service' => env('RABBITMQ_EXCHANGE_NAME'),
                        ],
                    ],
                ], 201);
            } catch (Exception $e) {
                return response()->jsonApi([
                    'title' => 'Creating a new deposit',
                    'message' => $e->getMessage(),
                ], $e->getCode());
            }
        }

        /**
         * Getting data about deposit by deposit ID
         *
         * @OA\Get(
         *     path="/app/deposits/{id}",
         *     summary="Getting data about deposit by deposit ID",
         *     description="Getting data about deposit by deposit ID",
         *     tags={"Application | Deposits"},
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
         *         description="Deposits ID",
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
         * @param $id
         *
         * @return mixed
         */
        public function show($id): mixed
        {
            try {
                $deposit = Deposit::findOrFail($id);

                // Load linked relations data
                $deposit->load([
                    'order',
                ]);

                return response()->jsonApi([
                    'title' => 'Deposit details data',
                    'message' => "Deposit detail data has been received",
                    'data' => $deposit,
                ]);
            } catch (ModelNotFoundException $e) {
                return response()->jsonApi([
                    'title' => 'Deposit details data',
                    'message' => "Deposit not found: {$e->getMessage()}",
                ], 404);
            } catch (Exception $e) {
                return response()->jsonApi([
                    'title' => 'Deposit details data',
                    'message' => $e->getMessage(),
                ], $e->getCode());
            }
        }

        /**
         * Get paid deposits by user if exist
         *
         * @OA\Get(
         *     path="/app/paid-deposits",
         *     summary="Get paid deposits by user if exist",
         *     description="Get paid deposits by user if exist",
         *     tags={"Application | Paid Deposits"},
         *
         *     security={{
         *         "bearerAuth": {},
         *         "apiKey": {}
         *     }},
         *
         *     @OA\Response(
         *         response="200",
         *         description="Getting deposits list",
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
         * )
         *
         * @param Request $request
         *
         * @return mixed
         */
        public function getPaidDeposits(Request $request): mixed
        {
            try {
                // Validate status if need
                $status = 'paid';

                $result = Deposit::byOwner()
                    ->when($status, function ($q) use ($status) {
                        $status = "STATUS_" . mb_strtoupper($status);

                        return $q->where('status', (int)constant("App\Models\Deposit::{$status}"));
                    })->count();


                // Return response
                return response()->jsonApi([
                    'title' => "List all paid deposits",
                    'message' => "List all paid deposits retrieved successfully.",
                    'data' => [
                        'can_access_dashboard' => $result > 0,
                        'is_influencer' => DB::connection('identity')->table('users')->where('id', Auth::user()->getAuthIdentifier())->first()->hasRole(['Influencer', 'influencer']),
                    ],
                ]);
            } catch (Exception $e) {
                return response()->jsonApi([
                    'title' => 'List all deposits',
                    'message' => 'Error in getting list of all deposits: ' . $e->getMessage(),
                ], $e->getCode());
            }
        }
    }
