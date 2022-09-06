<?php

namespace App\Api\V1\Controllers\Admin;

use App\Api\V1\Controllers\Controller;
use App\Models\Purchase;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Class PurchaseController
 *
 * @package App\Api\V1\Controllers\Admin
 */
class PurchaseController extends Controller
{
    /**
     * Display list of all purchase - shopping List
     *
     * @OA\Get(
     *     path="/admin/purchases",
     *     summary="Getting list of all purchases tokens - shopping list",
     *     description="Getting list of all purchases tokens - shopping list",
     *     tags={"Admin | Purchases"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Purchases status (created, processing, partially_funded, confirmed, delayed, failed, succeeded, canceled)",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         description="Count of purchases in response",
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
     *         description="Getting purchases list",
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
     *     )
     * )
     *
     * @param Request $request
     */
    public function index(Request $request): mixed
    {
        // Validate status if need
        $validation = Validator::make($request->all(), [
            'status' => [
                'sometimes',
                'string',
                Rule::in(array_keys(Purchase::$statuses)),
            ]
        ]);

        // If validation error, the stop
        if ($validation->fails()) {
            return response()->jsonApi([
                'title' => 'List all purchases',
                'message' => $validation->errors()
            ], 422);
        }

        // Try get data
        try {
            $purchases = Purchase::query()
                ->with('product', function ($query) {
                    return $query->select('title', 'ticker', 'icon');
                })
                ->when($request->has('status'), function ($q) use ($request) {
                    return $q->where('status', intval(Purchase::$statuses[$request->get('status')]));
                })
                ->orderBy('created_at', 'desc')
                ->paginate($request->get('limit', config('settings.pagination_limit')));

            // Return response
            return response()->jsonApi([
                'title' => 'List all purchases',
                'message' => 'List all purchase retrieved successfully',
                'data' => $purchases
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'List all purchases',
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }
}
