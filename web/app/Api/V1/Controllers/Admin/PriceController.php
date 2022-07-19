<?php

namespace App\Api\V1\Controllers\Admin;

use App\Api\V1\Controllers\Controller;
use App\Models\Price;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PriceController extends Controller
{
    /**
     * Getting a listing of product prices
     *
     * @OA\Get(
     *     path="/admin/price",
     *     summary="Getting a listing of product prices",
     *     description="Getting a listing of product prices",
     *     tags={"Admin / Prices"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     }},
     *
     *      @OA\Response(
     *          response="200",
     *          description="Success",
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
     * @return mixed
     */
    public function index(Request $request): mixed
    {
        try {
            // Get order
            $price = Price::where('status', true)
                ->select(['stage', 'price', 'period_in_days', 'percent_profit', 'amount', 'id'])
                //->where('product_id', $request->product_id)
                ->paginate($request->get('limit', config('settings.pagination_limit')));


            return response()->jsonApi([
                'type' => 'success',
                'title' => 'Product prices list',
                'message' => "Product prices list has been received",
                'data' => $price->toArray()
            ], 200);
        } catch (ValidationException $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Product price list ',
                'message' => 'Validation error: '.$e->getMessage(),
                'data' => null
            ], 400);
        }
    }

    /**
     * Store a newly stage price in storage.
     *
     * @OA\Post(
     *     path="/admin/price",
     *     summary="Saving new stage price",
     *     description="Saving new stage price",
     *     tags={"Admin / Prices"},
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
     *         @OA\JsonContent(ref="#/components/schemas/ProductPrice")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successfully save"
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="Price created"
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
     * @return mixed
     */
    public function store(Request $request)
    {
        // Validate input
        try {
            $this->validate($request, Price::validationRules());
            $price = Price::create($request->all());
            return response()->jsonApi([
                'type' => 'success',
                'title' => "Create new Price",
                'message' => 'Price was successfully created',
                'data' => $price->toArray()
            ], 201);

        } catch (ValidationException $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Saving new stage price',
                'message' => 'Validation error: '.$e->getMessage(),
                'data' => null
            ], 400);
        }
    }

    /**
     * Getting a listing of product prices
     *
     * @OA\Get(
     *     path="/admin/price/{id}",
     *     summary="Getting a listing of product prices",
     *     description="Getting a listing of product prices",
     *     tags={"Admin / Prices"},
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
     *         description="Price's id",
     *         required=true,
     *      ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Successfully save"
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="Price created"
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
     *
     * @param Request $request
     * @return mixed
     * 
     */
    public function show($id)
    {
        try {
            $price = Price::with('product')->findOrFail($id);
            return response()->json([
                'type' => 'success',
                'title' => 'Price Product List',
                'message'=>'Price list received successfully',
                'data' => $price
            ], 200);
        } catch (ValidationException $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Price Product List',
                'message' => 'Validation error: '.$e->getMessage(),
                'data' => null
            ], 400);
        }
    }

    /**
     * Updates a stage price.
     *
     * @OA\Put(
     *     path="/admin/price",
     *     description="Updates a stage price",
     *     tags={"Admin / Prices"},
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
     *         description="Price's id",
     *         required=true,
     *      ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ProductPrice")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successfully save"
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="Price created"
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
     *
     * @param Request $request
     * @param $id
     * 
     * @return mixed
     */
    public function update(Request $request, $id)
    {
        try {
            $this->validate($request, Price::validationRules());
            $price = Price::findOrFail($id);
            $price->update($request->all());
            return response()->jsonApi([
                'type' => 'success',
                'title' => "Update Price",
                'message' => 'Price was successful updated',
                'data' => $price
            ], 201);

        } catch (ValidationException $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Update Price',
                'message' => 'Validation error: '.$e->getMessage(),
                'data' => null
            ], 400);
        }
    }

    /**
     * Delete a particular Price based on ID.
     *
     * @OA\Delete(
     *     path="/admin/price/{id}",
     *     description="Get a price",
     *     tags={"Admin / Prices"},
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
     *         description="price ID",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Success"
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not found"
     *     )
     * )
     *
     *
     * @param $id
     * @return Response
     */
    public function destroy($id)
    {
        try {
            // get price with id
            $price = Price::findOrFail($id);
            // delete price
            $price->delete();
            return response()->jsonApi([
                'type' => 'success',
                'title' => "Delete Price",
                'message' => 'Price was successful deleted',
            ], 201);
        } catch (ValidationException $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Delete Price',
                'message' => 'Validation error: '.$e->getMessage(),
                'data' => []
            ], 400);
        }
    }
}
