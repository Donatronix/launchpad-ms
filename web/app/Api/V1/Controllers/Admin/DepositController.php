<?php

namespace App\Api\V1\Controllers\Admin;

use App\Api\V1\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Order;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
     *          response="200",
     *          description="Success",
     *
     *          @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                 property="type",
     *                 type="string",
     *                 example="success"
     *             ),
     *             @OA\Property(
     *                 property="title",
     *                 type="string",
     *                 example="Admin Deposit List"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="List of admin deposits retrieved successfully"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object"
     *             )
     *         )
     *     ),
     *    @OA\Response(
     *          response="400",
     *          description="Bad request",
     *
     *          @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                 property="type",
     *                 type="string",
     *                 example="danger"
     *             ),
     *             @OA\Property(
     *                 property="title",
     *                 type="string",
     *                 example="Admin Deposit List"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Unable to retrieve of admin deposit list"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *          response="404",
     *          description="Not Found",
     *
     *          @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                 property="type",
     *                 type="string",
     *                 example="warning"
     *             ),
     *             @OA\Property(
     *                 property="title",
     *                 type="string",
     *                 example="Admin Deposit List"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="No admin deposit found."
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object"
     *             )
     *         )
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
            $allDeposits = Deposit::with('order')
                ->orderBy('created_at', 'desc')
                ->paginate($request->get('limit', config('settings.pagination_limit')));

            // Return response
            return response()->jsonApi([
                'title' => "List all deposits",
                'message' => "List all deposits retrieved successfully.",
                'data' => $allDeposits->toArray()
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'List all deposits',
                'message' => 'Error in getting list of all deposits: ' . $e->getMessage()
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
     *                 description="Deposit currency code",
     *                 example="USD"
     *             ),
     *             @OA\Property(
     *                 property="order_id",
     *                 type="string",
     *                 description="order id",
     *                 example="5590000-9800000-38380000"
     *             )
     *        )
     *    ),
     *
     *     @OA\Response(
     *         response="201",
     *         description="Success",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                 property="type",
     *                 type="string",
     *                 example="success"
     *             ),
     *             @OA\Property(
     *                 property="title",
     *                 type="string",
     *                 example="Create Admin Deposit"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Admin deposit created successfully"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object"
     *             )
     *         )
     *     ),
     *    @OA\Response(
     *          response="400",
     *          description="Bad request",
     *
     *          @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                 property="type",
     *                 type="string",
     *                 example="danger"
     *             ),
     *             @OA\Property(
     *                 property="title",
     *                 type="string",
     *                 example="Create Admin Deposit"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Unable to create admin deposit"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *          response="422",
     *          description="Validation Failed",
     *
     *          @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                 property="type",
     *                 type="string",
     *                 example="warning"
     *             ),
     *             @OA\Property(
     *                 property="title",
     *                 type="string",
     *                 example="Create Admin Deposit"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Validation error occured"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object"
     *             )
     *         )
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

            if (!$checkIfOrderExists) {
                return response()->jsonApi([
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
                'title' => "Create new deposit",
                'message' => "Deposit was created",
                'data' => $depositSaved
            ]);
        } catch (ValidationException $e) {
            return response()->jsonApi([
                'title' => 'Create new deposit',
                'message' => 'Validation error',
                'data' => $e->getMessage()
            ], 422);
        } catch (Exception $e) {
            return response()->jsonApi([
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
     *         in="path",
     *         description="deposit ID",
     *         required=true,
     *         example="ef76a6e8-b287-345c-8b1f-beb96d088a33"
     *     ),
     *
     *     @OA\Response(
     *          response="200",
     *          description="Success",
     *
     *          @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                 property="type",
     *                 type="string",
     *                 example="success"
     *             ),
     *             @OA\Property(
     *                 property="title",
     *                 type="string",
     *                 example="Single Admin Deposit"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Single admin deposits retrieved successfully"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object"
     *             )
     *         )
     *     ),
     *    @OA\Response(
     *          response="400",
     *          description="Bad request",
     *
     *          @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                 property="type",
     *                 type="string",
     *                 example="danger"
     *             ),
     *             @OA\Property(
     *                 property="title",
     *                 type="string",
     *                 example="Single Admin Deposit"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Unable to retrieve of single admin deposit"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *          response="404",
     *          description="Not Found",
     *
     *          @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                 property="type",
     *                 type="string",
     *                 example="warning"
     *             ),
     *             @OA\Property(
     *                 property="title",
     *                 type="string",
     *                 example="Single Admin Deposit"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Admin deposit found."
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object"
     *             )
     *         )
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

            return response()->jsonApi([
                'title' => 'Admin Deposit',
                'message' => "Single admin deposits retrieved successfully.",
                'data' => $deposit
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Get deposit',
                'message' => 'Error in getting deposit',
                'data' => $e->getMessage()
            ], 400);
        } catch (ModelNotFoundException $ex) {
            return response()->jsonApi([
                'type' => 'warning',
                'title' => 'Single Admin Deposit',
                'message' => 'Single admin deposit not found',
                'data' => $ex->getMessage()
            ], 404);
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
     *         in="path",
     *         description="Deposit id",
     *         required=true,
     *         example="ef76a6e8-b287-345c-8b1f-beb96d088a33"
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
     *          response="201",
     *          description="Success",
     *
     *          @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                 property="type",
     *                 type="string",
     *                 example="success"
     *             ),
     *             @OA\Property(
     *                 property="title",
     *                 type="string",
     *                 example="Update Admin Deposit"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Admin deposit update successfully"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object"
     *             )
     *         )
     *     ),
     *    @OA\Response(
     *          response="400",
     *          description="Bad request",
     *
     *          @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                 property="type",
     *                 type="string",
     *                 example="danger"
     *             ),
     *             @OA\Property(
     *                 property="title",
     *                 type="string",
     *                 example="Update Admin Deposit"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Unable to update admin deposit"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *          response="422",
     *          description="Validation Failed",
     *
     *          @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                 property="type",
     *                 type="string",
     *                 example="warning"
     *             ),
     *             @OA\Property(
     *                 property="title",
     *                 type="string",
     *                 example="Update Admin Deposit"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Validation error occured"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object"
     *             )
     *         )
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

            if (!$checkIfOrderExists) {
                return response()->jsonApi([
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
                'title' => "Update Deposit",
                'message' => "Record was updated",
                'data' => $deposit
            ]);
        } catch (ValidationException $e) {
            return response()->jsonApi([
                'title' => 'Update deposit',
                'message' => 'Validation Error',
                'data' => $e->getMessage()
            ], 422);
        } catch (Exception $e) {
            return response()->jsonApi([
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
     *         in="path",
     *         description="Deposits ID",
     *         required=true,
     *         example="b68a4967-aeee-3ba5-824c-bc7f41a3ef9c"
     *     ),
     *
     *     @OA\Response(
     *          response="200",
     *          description="Success",
     *
     *          @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                 property="type",
     *                 type="string",
     *                 example="success"
     *             ),
     *             @OA\Property(
     *                 property="title",
     *                 type="string",
     *                 example="Delete Single Deposit"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Admin deposit deleted successfully"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object"
     *             )
     *         )
     *     ),
     *    @OA\Response(
     *          response="400",
     *          description="Bad request",
     *
     *          @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                 property="type",
     *                 type="string",
     *                 example="danger"
     *             ),
     *             @OA\Property(
     *                 property="title",
     *                 type="string",
     *                 example="Delete Single Deposit"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Unable to delete of admin deposit"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *          response="404",
     *          description="Not Found",
     *
     *          @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                 property="type",
     *                 type="string",
     *                 example="warning"
     *             ),
     *             @OA\Property(
     *                 property="title",
     *                 type="string",
     *                 example="Delete Single Deposit"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="No admin deposit found."
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object"
     *             )
     *         )
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
    public function destroy($id)
    {
        try {
            Deposit::findOrFail($id)->delete();

            return response()->jsonApi([
                'title' => "Soft delete deposit",
                'message' => "Deleted successfully",
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Delete Single Deposit',
                'message' => 'Unable to delete deposit',
                'data' => $e->getMessage()
            ], 400);
        } catch (ModelNotFoundException $ex) {
            return response()->jsonApi([
                'title' => 'Single Admin Deposit',
                'message' => 'Admin deposit not found',
                'data' => $ex->getMessage()
            ], 404);
        }
    }
}
