<?php

namespace App\Api\V1\Controllers\Admin;

use App\Api\V1\Controllers\Controller;
use App\Models\Product;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use PubSub;
use Sumra\SDK\Services\JsonApiResponse;

class ProductController extends Controller
{
    /**
     * @param Product $model
     */
    private Product $model;

    public function __construct(Product $model)
    {
        $this->model = $model;
    }

    /**
     * Getting product detail by platform
     *
     * @OA\Get(
     *     path="/admin/products",
     *     summary="Getting product detail by platform",
     *     description="Getting product detail by platform",
     *     tags={"Admin | Products"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Limit",
     *         @OA\Schema(
     *             type="number"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Count",
     *         @OA\Schema(
     *             type="number"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search keywords",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sort-by",
     *         in="query",
     *         description="Sort by field ()",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sort-order",
     *         in="query",
     *         description="Sort order (asc, desc)",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Data fetched",
     *         @OA\JsonContent(ref="#/components/schemas/OkResponse")
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error",
     *         @OA\JsonContent(ref="#/components/schemas/DangerResponse")
     *     ),
     *     @OA\Response(
     *         response="403",
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(ref="#/components/schemas/WarningResponse")
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function index(Request $request)
    {
        try {
            // Get products
            $products = $this->model
                ->orderBy($request->get('sort-by', 'created_at'), $request->get('sort-order', 'desc'))
                ->paginate($request->get('limit', config('settings.pagination_limit')));

            // Return response
            return response()->jsonApi([
                'title' => "Products list",
                'message' => 'List of products successfully received',
                'data' => $products->toArray()
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => "Products list",
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Save a new product
     *
     * @OA\Post(
     *     path="/admin/products",
     *     summary="Save a new product",
     *     description="Save a new product",
     *     tags={"Admin | Products"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Data fetched",
     *         @OA\JsonContent(ref="#/components/schemas/OkResponse")
     *     ),
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
     *         response="403",
     *         description="Forbidden"
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
     *         description="Internal server error"
     *     )
     * )
     */
    public function store(Request $request)
    {
        // Do validate input data
        $validator = Validator::make($request->all(), $this->model::validationRules());
        if ($validator->fails()) {
            throw new Exception($validator->errors()->first());
        }

        // transform the request object to format date
        if ($request->has('start_date')) {
            $request->merge([
                'start_date' => Carbon::parse($request->get('start_date')),
                'end_date' => Carbon::parse($request->get('end_date')),
            ]);
        }

        // Try to add new product
        try {
            // Create new
            $product = $this->model->create($request->all());

            // send product to the reference-books-ms
            PubSub::publish('CreateCurrency', [
                'currency_code' => $product->ticker,
                'title' => $product->title,
            ], config('pubsub.queue.reference_books'));

            // Return response to client
            return response()->jsonApi([
                'title' => 'New product registration',
                'message' => "Product successfully added",
                'data' => $product->toArray()
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'New product registration',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Getting product detail
     *
     * @OA\Get(
     *     path="/admin/products/{id}",
     *     summary="Getting product detail by ID",
     *     description="Getting product detail by ID",
     *     tags={"Admin | Products"},
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
     *         description="product Id",
     *         example="0aa06e6b-35de-3235-b925-b0c43f8f7c75",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Data fetched",
     *         @OA\JsonContent(ref="#/components/schemas/OkResponse")
     *     )
     * )
     *
     * @param $id
     */
    public function show($id)
    {
        try {
            // Read product model
            $product = $this->getObject($id);

            if ($product instanceof JsonApiResponse) {
                return $product;
            }

            return response()->jsonApi([
                'title' => 'Product detail',
                'message' => "Product detail been received",
                'data' => $product->toArray()
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Product detail',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get product object
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
                'title' => "Get product",
                'message' => "Product with id #{$id} not found: {$e->getMessage()}",
            ], 404);
        }
    }

    /**
     * Update product data
     *
     * @OA\Put(
     *     path="/admin/products/{id}",
     *     summary="Update product data",
     *     description="Update product data",
     *     tags={"Admin | Products"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Product Id",
     *         example="0aa06e6b-35de-3235-b925-b0c43f8f7c75",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Data fetched",
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
     *         response="403",
     *         description="Forbidden"
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
     *         description="Internal server error"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        // Do validate input data
        $validator = Validator::make($request->all(), [
            'title' => 'string',
            'ticker' => 'string|unique:products,ticker',
            'supply' => 'integer',
            'presale_percentage' => 'string',
            'start_date' => 'string',
            'end_date' => 'string',
        ]);
        if ($validator->fails()) {
            throw new Exception($validator->errors()->first());
        }

        // transform the request object to format date
        if ($request->has('start_date') || $request->has('end_date')) {
            $request->merge([
                'start_date' => Carbon::parse($request->get('start_date')),
                'end_date' => Carbon::parse($request->get('end_date')),
            ]);
        }

        // Read product model
        $product = $this->getObject($id);

        if ($product instanceof JsonApiResponse) {
            return $product;
        }

        // Try update product data
        try {
            // Update data
            $product->update($request->all());

            // Return response to client
            return response()->jsonApi([
                'title' => 'Changing product',
                'message' => "product successfully updated",
                'data' => $product->toArray()
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Change a product',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Delete product from storage
     *
     * @OA\Delete(
     *     path="/admin/products/{id}",
     *     summary="Delete product",
     *     description="Delete product",
     *     tags={"Admin | Products"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="product Id",
     *         example="0aa06e6b-35de-3235-b925-b0c43f8f7c75",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Data fetched",
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
     *         response="403",
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(ref="#/components/schemas/WarningResponse")
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     *
     * @return mixed
     */
    public function destroy($id)
    {
        // Read product model
        $product = $this->getObject($id);
        if ($product instanceof JsonApiResponse) {
            return $product;
        }

        // Try to delete product
        try {
            $product->delete();

            return response()->jsonApi([
                'title' => "Delete product",
                'message' => 'product is successfully deleted',
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => "Delete of product",
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
