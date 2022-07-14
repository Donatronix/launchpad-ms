<?php

namespace App\Api\V1\Controllers\Admin;

use Exception;
use App\Models\Order;
use App\Models\Deposit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Api\V1\Controllers\Controller;
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
    public function index(Request $request)
    {
        try {
            $allDeposits = Deposit::with('order')
                ->orderBy('created_at', 'desc')
                ->paginate($request->get('limit', config('settings.pagination_limit')));

            // Return response
            return response()->json([
                'type' => 'success',
                'title' => "List all deposits",
                'message' => "List all deposits",
                'data' => $allDeposits->toArray()
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'List all deposits',
                'message' => 'Error in getting list of all deposits: '.$e->getMessage(),
                'data' => null
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
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     }},
     *
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="amount",
     *                 type="decimal",
     *                 description="amount to deposit",
     *                 example="1500.00"
     *             ),
     *             @OA\Property(
     *                 property="currency_code",
     *                 type="string",
     *                 description="currency code",
     *                 example="USD"
     *             ),
     *             @OA\Property(
     *                 property="order_id",
     *                 type="string",
     *                 description="order id",
     *                 example="5590000-9800000-38380000"
     *             )
     *         ),
     *     ),
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
                'currency_code' => 'required|string',
                'amount' => 'required|numeric',
                'order_id' => 'required|string',
            ]);

            $checkIfOrderExists = Order::where('id', $request->order_id)->exists();

            if(!$checkIfOrderExists){
                return response()->jsonApi([
                    'type' => 'warning',
                    'title' => 'Create new deposit',
                    'message' => 'Validation error',
                    'data' => 'Order id is invalid'
                ], 404);
            }

            $depositSaved = Deposit::create([
                'currency_code' => $request['currency_code'],
                'amount' => $request['amount'],
                'order_id' => $request['order_id'],
                'status' => Deposit::STATUS_CREATED,
                'user_id' => Auth::user()->getAuthIdentifier(),
            ]);

            // Return response
            return response()->jsonApi([
                'type' => 'success',
                'title' => "Create new deposit",
                'message' => "Deposit was created",
                'data' => $depositSaved
            ], 200);
        } catch (ValidationException $e) {
            return response()->jsonApi([
                'type' => 'warning',
                'title' => 'Create new deposit',
                'message' => 'Validation error',
                'data' => $e->getMessage()
            ], 400);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Create new deposit',
                'message' => 'Error occurred when creating new deposit',
                'data' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Display a single Deposit - View details
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
            $deposit = Deposit::with('order')->findOrFail($id);
            return response()->json([
                'type' => 'success',
                'title' => "Get deposit",
                'message' => "Get deposit",
                'data' => $deposit
            ], 200);
        } catch (\Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Get deposit',
                'message' => 'Error in getting deposit',
                'data' => $e->getMessage()
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
     *         description="Deposit id",
     *         required=true,
     *     ),
     *
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="currency_code",
     *                 type="string",
     *                 description="currency code",
     *                 example="USD"
     *             ),
     *             @OA\Property(
     *                 property="amount",
     *                 type="decimal",
     *                 description="amount to deposit",
     *                 example="100.00"
     *             ),
     *             @OA\Property(
     *                 property="order_id",
     *                 type="string",
     *                 description="order id",
     *                 example="490000-9800000-38380000"
     *             )
     *         )
     *     ),
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
                'currency_code' => 'required|string',
                'amount' => 'required|numeric',
                'order_id' => 'required|string',
            ]);

            $checkIfOrderExists = Order::where('id', $request->order_id)->exists();

            if(!$checkIfOrderExists){
                return response()->jsonApi([
                    'type' => 'warning',
                    'title' => 'Create new deposit',
                    'message' => 'Validation error',
                    'data' => 'Order id is invalid'
                ], 404);
            }

            $deposit = Deposit::findOrFail($id);

            $deposit->update([
                'currency_code' => $request['currency_code'],
                'amount' => $request['amount'],
                'order_id' => $request['order_id'],
            ]);

            // Return response
            return response()->jsonApi([
                'type' => 'success',
                'title' => "Update Deposit",
                'message' => "Record was updated",
                'data' => $deposit
            ], 200);
        } catch (ValidationException $e) {
            return response()->jsonApi([
                'type' => 'warning',
                'title' => 'Update deposit',
                'message' => 'Validation Error',
                'data' => $e->getMessage()
            ], 400);
        } catch (Exception $e) {
            return response()->jsonApi([
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

            return response()->jsonApi([
                'type' => 'success',
                'title' => "Soft delete deposit",
                'message' => "Deleted successfully",
                'data' => []
            ], 200);
        } catch (\Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Soft delete deposit',
                'message' => 'Error in deleting deposit',
                'data' => $e->getMessage()
            ], 400);
        }
    }
}//end class
