<?php

namespace App\Api\V1\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Carbon\Carbon;
use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Sumra\SDK\JsonApiResponse;


/**
 * Class DepositController
 *
 * @package App\Api\V1\Controllers
 */
class OrderController extends Controller
{

    /**
     * Display list of all orders
     *
     * @OA\Get(
     *     path="/admin/orders",
     *     description="Getting all data about order for all users",
     *     tags={"Admin / Orders"},
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
     *         description="Count of orders in response",
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
            $allOrders = Order::orderBy('created_at', 'Desc')
                ->paginate($request->get('limit', 20));
            $resp['type']       = "Success";
            $resp['title']      = "List all orders";
            $resp['message']    = "List all orders";
            $resp['data']       = $allOrders;
            return response()->json($resp, 200);
        } catch (Exception $e) {
            return response()->json([
                'type'  => 'danger',
                'title'  => 'List all orders',
                'message' => 'Error in getting list of all orders',
                'data' => $e->getMessage()
            ], 400);
        }
    }
}//end class