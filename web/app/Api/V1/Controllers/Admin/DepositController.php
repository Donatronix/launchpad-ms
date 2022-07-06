<?php

namespace App\Api\V1\Controllers\Admin;

use App\Api\V1\Controllers\Controller;
use App\Models\Deposit;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Class DepositController
 *
 * @package App\Api\V1\Controllers
 */
class DepositController extends Controller
{
    /**
     * Display list of all deposits
     *
     * @OA\Get(
     *     path="/admin/deposits",
     *     description="Getting all data about deposits for all users",
     *     tags={"Admin / Deposits"},
     *
     *
     *     @OA\Parameter(
     *         name="limit",
     *         description="Count of deposits in response",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=20,
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         description="Page of list",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=1,
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Success"
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
            $allDeposits = Deposit::orderBy('created_at', 'Desc')
                ->with(['order' => function ($query) {
                    $query->select(
                        'id',
                        'product_id',
                        'investment_amount',
                        'deposit_percentage',
                        'deposit_amount',
                        'user_id',
                        'status',
                        'order_no',
                        'amount_token',
                        'amount_usd'
                    );
                }])
                ->paginate($request->get('limit', 20));

            $resp['type'] = "Success";
            $resp['title'] = "List all deposits";
            $resp['message'] = "List all deposits";
            $resp['data'] = $allDeposits;
            return response()->json($resp, 200);
        } catch (Exception $e) {
            return response()->json([
                'type' => 'danger',
                'title' => 'List all deposits',
                'message' => 'Error in getting list of all deposits',
                'data' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Create new deposit
     *
     * @OA\Post(
     *     path="/admin/deposits",
     *     description="Adding new deposit for user",
     *     tags={"Admin / Deposits"},
     *
     *
     *       @OA\RequestBody(
     *            @OA\JsonContent(
     *                type="object",
     *                @OA\Property(
     *                    property="amount",
     *                    type="decimal",
     *                    description="amount to deposit",
     *                    example="1500.00"
     *                ),
     *                @OA\Property(
     *                    property="currency_id",
     *                    type="string",
     *                    description="currency id",
     *                    example="8000000-3000000-20000"
     *                ),
     *                @OA\Property(
     *                    property="order_id",
     *                    type="string",
     *                    description="order id",
     *                    example="5590000-9800000-38380000"
     *                )
     *           ),
     *       ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Successfully saved"
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
                'currency_id' => 'required|string',
                'amount'    => 'required|decimal',
                'order_id'  => 'required|string',
            ]);

            $depositSaved = Deposit::create([
                'currency_id'   => $request['currency_id'],
                'amount'        => $request['amount'],
                'order_id'      => $request['order_id'],
                'user_id'       => Auth::user()->getAuthIdentifier(),
            ]);

            $resp['type']   = "Success";
            $resp['title'] = "Create new deposit";
            $resp['message'] = "Deposit was created";
            $resp['data'] = $depositSaved;
            return response()->json($resp, 200);
        } catch (ValidationException $e) {
            return response()->json([
                'type'      => 'warning',
                'title'     => 'Create new deposit',
                'message'   => 'Validation error',
                'data'      => $e->getMessage()
            ], 400);
        } catch (Exception $e) {
            return response()->json([
                'type'      => 'danger',
                'title'     => 'Create new deposit',
                'message'   => 'Error occurred when creating new deposit',
                'data'      => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Display a single Deposit
     *
     * @OA\Get(
     *     path="/admin/deposits/{id}",
     *     description="Get a single deposit",
     *     tags={"Admin / Deposits"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="deposit ID",
     *         required=true,
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Success"
     *     ),
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        try {
            $deposit = Deposit::findOrFail($id);

            $resp['type']       = "Success";
            $resp['title']      = "Get deposit";
            $resp['message']    = "Get deposit";
            $resp['data']       = $deposit ? $deposit->with('order') : [];
            return response()->json($resp, 200);
        } catch (\Exception $e) {
            return response()->json([
                'type'      => 'danger',
                'title'     => 'Get deposit',
                'message'   => 'Error in getting deposit',
                'data'      => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Update a single Deposit
     *
     * @OA\Put(
     *     path="/admin/deposits/{id}",
     *     description="Update one deposit",
     *     tags={"Admin / Deposits"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Deposit id",
     *         required=true,
     *     ),
     *
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                    property="currency_id",
     *                    type="string",
     *                    description="currency id",
     *                    example="8000000-3000000-20000"
     *             ),
     *             @OA\Property(
     *                    property="amount",
     *                    type="decimal",
     *                    description="amount to deposit",
     *                    example="100.00"
     *             ),
     *             @OA\Property(
     *                    property="order_id",
     *                    type="string",
     *                    description="order id",
     *                    example="490000-9800000-38380000"
     *             ),
     *         ),
     *     ),
     *
     *
     *     @OA\Response(
     *         response="200",
     *         description="Success"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Unknown error"
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Invalid request"
     *     ),
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
    public function update(Request $request, $id): JsonResponse
    {
        try {
            //validate input
            $this->validate($request, [
                'currency_id' => 'required|string',
                'amount' => 'required|decimal',
                'order_id' => 'required|string',
            ]);

            $depositSaved = Deposit::where('id', $id)->update([
                'currency_id' => $request['currency_id'],
                'amount' => $request['amount'],
                'order_id' => $request['order_id'],
            ]);

            $resp['type'] = "Success";
            $resp['title'] = "Update Deposit";
            $resp['message'] = "Record was updated";
            $resp['data'] = $depositSaved;
            return response()->json($resp, 200);
        } catch (ValidationException $e) {
            return response()->json([
                'type' => 'warning',
                'title' => 'Update deposit',
                'message' => 'Validation Error',
                'data' => $e->getMessage()
            ], 400);
        } catch (Exception $e) {
            return response()->json([
                'type' => 'danger',
                'title' => 'Update deposit',
                'message' => 'Error occurred when updating deposit',
                'data' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Delete a single deposits
     *
     * @OA\Delete(
     *     path="/admin/deposits/{id}",
     *     description="Deletes one deposit",
     *     tags={"Admin / Deposits"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="deposits ID",
     *         required=true,
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
     *     ),
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function destroy($id)
    {
        try {
            Deposit::findOrFail($id)->delete();

            return response()->json([
                'type'       => "Success",
                'title'      => "Soft delete deposit",
                'message'    => "Deleted successfully",
                'data'       => null
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'danger',
                'title' => 'Soft delete deposit',
                'message' => 'Error in deleting deposit',
                'data' => $e->getMessage()
            ], 400);
        }
    }
}