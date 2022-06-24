<?php

namespace App\Api\V1\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use Carbon\Carbon;
use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Sumra\SDK\JsonApiResponse;
use Illuminate\Database\Eloquent\SoftDeletes;


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
     *      security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *      }},
     *
     *
     *       @OA\Parameter(
     *         name="limit",
     *         description="Count of deposits in response",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *              type="integer",
     *              default=20,
     *         )
     *      ),
     *     @OA\Parameter(
     *         name="page",
     *         description="Page of list",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *              type="integer",
     *              default=1,
     *         )
     *     ),
     *
     *     @OA\Response(
     *          response="200",
     *          description="Success",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *
     *              )
     *          ),
     *     ),
     * )
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $allDeposits = Deposit::orderBy('created_at', 'Desc')
                ->paginate($request->get('limit', 20));
            $resp['type']       = "Success";
            $resp['title']      = "List all deposits";
            $resp['message']    = "List all deposits";
            $resp['data']       = $allDeposits;
            return response()->json($resp, 200);
        } catch (Exception $e) {
            return response()->json([
                'type'  => 'danger',
                'title'  => 'List all deposits',
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
     *      security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *      }},
     *
     *       @OA\RequestBody(
     *            @OA\JsonContent(
     *                type="object",
     *                @OA\Property(
     *                    property="currency_id",
     *                    type="string",
     *                    description="currency id",
     *                    example="8000000-3000000-20000"
     *                ),
     *                @OA\Property(
     *                    property="deposit_amount",
     *                    type="decimal",
     *                    description="amount to deposit",
     *                    example="1500.00"
     *                ),
     *                @OA\Property(
     *                    property="user_id",
     *                    type="string",
     *                    description="user id",
     *                    example="800000-8000000-2290000"
     *                ),
     *                @OA\Property(
     *                    property="order_id",
     *                    type="string",
     *                    description="order id",
     *                    example="5590000-9800000-38380000"
     *                ),
     *           ),
     *       ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Successfully saved"
     *     ),
     * )
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            //validate input
            $this->validate($request, [
                'currency_id'       => 'required|string',
                'deposit_amount'    => 'required|decimal',
                'order_id'          => 'required|string',
            ]);

            $depositSaved = Deposit::create([
                'currency_id'       => $request['currency_id'],
                'deposit_amount'    => $request['deposit_amount'],
                'order_id'          => $request['order_id'],
                'user_id'           => Auth::user()->getAuthIdentifier(),
            ]);

            $resp['type']       = "Success";
            $resp['title']      = "Create new deposit";
            $resp['message']    = "Deposit was created";
            $resp['data']       = $depositSaved;
            return response()->json($resp, 200);
        } catch (ValidationException $e) {
            return response()->json([
                'type'  => 'warning',
                'title'  => 'Create new deposit',
                'message' => 'Error occurred when creating new deposit',
                'data' => $e->getMessage()
            ], 400);
        } catch (Exception $e) {
            return response()->json([
                'type'  => 'danger',
                'title'  => 'Create new deposit',
                'message' => 'Error occurred when creating new deposit',
                'data' => $e->getMessage()
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
     *      ),
     *
     *      @OA\Response(
     *          response="200",
     *          description="Success",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *
     *             @OA\Property(
     *                  property="id",
     *                  type="string",
     *                  description="deposit's id",
     *                  example="90000009-9009-9009-9009-900000000009"
     *              ),
     *              @OA\Property(
     *                  property="currency_id",
     *                  type="string",
     *                  description="currency id",
     *                  example="90000009-9009-9009-9009-900000000"
     *              ),
     *              @OA\Property(
     *                  property="deposit_amount",
     *                  type="decimal",
     *                  description="deposit amount",
     *                  example="100.00"
     *              ),
     *              @OA\Property(
     *                  property="user_id",
     *                  type="string",
     *                  description="user id",
     *                  example="90000009-9009-9009-9009-900000000
     *              ),
     *              @OA\Property(
     *                  property="order_id",
     *                  type="string",
     *                  description="order id",
     *                  example="20000-90000000-3009-900"
     *              ),
     *              )
     *          )
     *     ),
     *
     *     @OA\Response(
     *         response="500",
     *         description="Unknown error"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Not Found"
     *     ),
     * )
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
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
                'type'  => 'danger',
                'title'  => 'Get deposit',
                'message' => 'Error in getting deposit',
                'data' => $e->getMessage()
            ], 400);
        }
    }


    /**
     * Update a single Deposit
     *
     * @OA\Put(
     *      path="/admin/deposits/{id}",
     *     description="Update one deposit",
     *     tags={"Admin / Deposits"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Deposit id",
     *         required=true,
     *      ),
     *
     *     @OA\RequestBody(
     *            @OA\JsonContent(
     *                type="object",
     *                @OA\Property(
     *                    property="currency_id",
     *                    type="string",
     *                    description="currency id",
     *                    example="8000000-3000000-20000"
     *                ),
     *                @OA\Property(
     *                    property="deposit_amount",
     *                    type="decimal",
     *                    description="amount to deposit",
     *                    example="100.00"
     *                ),
     *                @OA\Property(
     *                    property="user_id",
     *                    type="string",
     *                    description="user id",
     *                    example="90000-8000000-2290000"
     *                ),
     *                @OA\Property(
     *                    property="order_id",
     *                    type="string",
     *                    description="order id",
     *                    example="490000-9800000-38380000"
     *                ),
     *           ),
     *       ),
     *
     *
     *     @OA\Response(
     *          response="200",
     *          description="Success",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *
     *             @OA\Property(
     *                  property="id",
     *                  type="string",
     *                  description="deposit's id",
     *                  example="90000009-9009-9009-9009-900000000009"
     *              ),
     *              @OA\Property(
     *                  property="currency_id",
     *                  type="string",
     *                  description="currency id",
     *                  example="90000009-9009-9009-9009-900000000"
     *              ),
     *              @OA\Property(
     *                  property="deposit_amount",
     *                  type="decimal",
     *                  description="deposit amount",
     *                  example="100.00"
     *              ),
     *              @OA\Property(
     *                  property="user_id",
     *                  type="string",
     *                  description="user id",
     *                  example="90000009-9009-9009-9009-900000000
     *              ),
     *              @OA\Property(
     *                  property="order_id",
     *                  type="string",
     *                  description="order id",
     *                  example="20000-90000000-3009-900"
     *              ),
     *              )
     *          )
     *     ),
     *
     *     @OA\Response(
     *         response="500",
     *         description="Unknown error"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Not Found"
     *     ),
     * )
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            //validate input
            $this->validate($request, [
                'currency_id'       => 'required|string',
                'deposit_amount'    => 'required|decimal',
                'order_id'          => 'required|string',
            ]);

            $depositSaved = Deposit::where('id', $id)->update([
                'currency_id'       => $request['currency_id'],
                'deposit_amount'    => $request['deposit_amount'],
                'order_id'          => $request['order_id'],
            ]);

            $resp['type']       = "Success";
            $resp['title']      = "Update Deposit";
            $resp['message']    = "Record was updated";
            $resp['data']       = $depositSaved;
            return response()->json($resp, 200);
        } catch (ValidationException $e) {
            return response()->json([
                'type'  => 'warning',
                'title'  => 'Update deposit',
                'message' => 'Error occurred when updating deposit',
                'data' => $e->getMessage()
            ], 400);
        } catch (Exception $e) {
            return response()->json([
                'type'  => 'danger',
                'title'  => 'Update deposit',
                'message' => 'Error occurred when updating deposit',
                'data' => $e->getMessage()
            ], 400);
        }
    }


    /**
     * Delete a single deposits
     *
     * @OA\Delete(
     *    path="/admin/deposits/{id}",
     *     description="deposits id",
     *     tags={"Admin / Deposits"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="deposits ID",
     *         required=true,
     *      ),
     *
     *     @OA\Response(
     *         response="500",
     *         description="Unknown error"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Not Found"
     *     ),
     * )
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $deleteRecord = Deposit::findOrFail($id)->delete();
            $resp['type']       = "Success";
            $resp['title']      = "Soft delete deposit";
            $resp['message']    = "Deleted successfully";
            $resp['data']       = $deleteRecord;
            return response()->json($resp, 200);
        } catch (\Exception $e) {
            return response()->json([
                'type'  => 'danger',
                'title'  => 'Soft delete deposit',
                'message' => 'Error in deleting deposit',
                'data' => $e->getMessage()
            ], 400);
        }
    }
}//end class