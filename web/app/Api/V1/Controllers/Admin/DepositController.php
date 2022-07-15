<?php

namespace App\Api\V1\Controllers\Admin;

use App\Api\V1\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Price;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class DepositController
 *
 * @package App\Api\V1\Controllers
 */
class DepositController extends Controller
{
    /**
     * Display admin deposits list
     *
     * @OA\Get(
     *     path="/admin/deposits",
     *     description="Getting all admin deposits for all users",
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
            //Retrive paginated list of deposits
            $deposits = Deposit::with('order')
                        ->orderBy('created_at', 'desc')
                        ->paginate($request->get('limit', config('settings.pagination_limit')));

            if(!empty($deposits) && $deposits!=null){
                return response()->jsonApi([
                    'type' => 'success',
                    'title' => 'Admin Deposit List',
                    'message' => "List of admin deposits retrieved successfully.",
                    "data" => $deposits
                ], 200);
            }

            return response()->jsonApi([
                    'type' => 'warning',
                    'title' => 'Admin Deposit List',
                    'message' => "No admin deposit found.",
                    "data" => null
                ], 404);

        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Admin Deposit List',
                'message' => 'Unable to retrieve admin deposits list',
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
     *                    property="currency_code",
     *                    type="string",
     *                    description="Deposit currency code",
     *                    example="USD"
     *                ),
     *                @OA\Property(
     *                    property="order_id",
     *                    type="string",
     *                    description="order id",
     *                    example="JNB28NVGCLIP"
     *                )
     *           ),
     *       ),
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
            $validate = Validator::make($request->all(), [
                'currency_code' => 'required|string',
                'amount'    => 'required|numeric',
                'order_id'  => 'required|string',
            ]);

            if($validate->fails()){
                return response()->jsonApi([
                    'type'      => 'warning',
                    'title'     => 'Create Admin Deposit',
                    'message'   => 'Validation errors occured.',
                    'data'      => null
                ], 400);
            }
            
            $input = $validate->validated();

            $depositSaved = Deposit::firstOrCreate([
                'order_id'=> $input['order_id'],
                'currency_code'=> $input['currency_code'],
                'amount'=> $input['amount'],
                'user_id' => 1
            ]);

            return response()->jsonApi([
                        'type' => 'success',
                        'title' => 'Create Admin Deposit',
                        'message'=> 'New deposit created successfully',
                        'data'=> $depositSaved
                    ], 200);

        } catch (ModelNotFoundException $ex) {
            return response()->jsonApi([
                'type'      => 'warning',
                'title'     => 'Create Admin Deposit',
                'message'   => 'Unable to create admin deposit.',
                'data'      => $ex->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->jsonApi([
                'type'      => 'danger',
                'title'     => 'Create Admin Deposit',
                'message'   => 'Unable to create admin deposit.',
                'data'      => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Display single admin deposit
     *
     * @OA\Get(
     *     path="/admin/deposits/{id}",
     *     description="Get single admin deposit",
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
            $deposit = Deposit::findOrFail($id);
            
            return response()->jsonApi([
                'type' => 'success',
                'title' => ' Admin Deposit',
                'message' => "Single admin deposits retrieved successfully.",
                "data" => $deposit
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type'      => 'danger',
                'title'     => 'Single Admin Deposit',
                'message'   => 'Unable to retrieve single admin deposit.',
                'data'      => $e->getMessage()
            ], 400);
        } catch (ModelNotFoundException $ex) {
            return response()->jsonApi([
                'type'      => 'warning',
                'title'     => 'Single Admin Deposit',
                'message'   => 'Single admin deposit not found',
                'data'      => $ex->getMessage()
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
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Deposit id",
     *         required=true,
     *         example="ef76a6e8-b287-345c-8b1f-beb96d088a33"
     *     ),
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
     *                    property="currency_code",
     *                    type="string",
     *                    description="Deposit currency code",
     *                    example="USD"
     *                ),
     *                @OA\Property(
     *                    property="order_id",
     *                    type="string",
     *                    description="order id",
     *                    example="JNB28NVGCLIP"
     *                )
     *           ),
     *       ),
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
            $validate = Validator::make($request->all(), [
                'currency_code' => 'required|string',
                'amount'    => 'required|numeric',
                'order_id'  => 'required|string',
            ]);

            if($validate->fails()){
                return response()->jsonApi([
                    'type' => 'warning',
                    'title' => 'Update Admin Deposit',
                    'message' => 'Validation errors occured.',
                    'data' => null
                ], 400);
            }
            
            $input = $validate->validated();
            $depositQuery = Deposit::where('id', $id);
            
            if($depositQuery->exists()){

               $dsaved =[
                    'currency_code' => $request['currency_code'],
                    'amount' => $request['amount'],
                    'order_id' => $request['order_id'],
               ]; 

               $depositQuery->update($dsaved);

                return response()->jsonApi([
                        'type'=> 'success',
                        'title'=> 'Update Admin Deposit',
                        'message'=> 'Admin deposit update successfully',
                        'data'=> $dsaved
                    ], 200); 
            }

            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Update Admin Deposit',
                'message' => 'Unable to update admin deposit',
                'data' => null
            ], 400);
            
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Update Admin Deposit',
                'message' => 'Unable to update admin deposit',
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
     *         in="path",
     *         description="Deposits ID",
     *         required=true,
     *         example="b68a4967-aeee-3ba5-824c-bc7f41a3ef9c"
     *     ),
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
                'type'       => "Success",
                'title'      => "Delete Single Deposit",
                'message'    => "Admin deposit deleted successfully",
                'data'       => null
            ], 200);
        } catch (\Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Delete Single Deposit',
                'message' => 'Unable to delete deposit',
                'data' => $e->getMessage()
            ], 400);
        } catch (ModelNotFoundException $ex) {
            return response()->jsonApi([
                'type'      => 'warning',
                'title'     => 'Single Admin Deposit',
                'message'   => 'Admin deposit not found',
                'data'      => $ex->getMessage()
            ], 404);
        }
    }
}