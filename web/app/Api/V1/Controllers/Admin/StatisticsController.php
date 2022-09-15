<?php

namespace App\Api\V1\Controllers\Admin;

use App\Api\V1\Controllers\Controller;
use App\Models\Price;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Throwable;

class StatisticsController extends Controller
{
    /**
     * Display total number of new users
     *
     * @OA\Get(
     *     path="/admin/summary/token-sales",
     *     summary="Count all new users",
     *     description="Get the status count of all users in the system",
     *     tags={"Admin | Statistics"},
     *
     *     security={{ "bearerAuth": {} }},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Data retrieved",
     *         @OA\JsonContent(ref="#/components/schemas/OkResponse")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error",
     *         @OA\JsonContent(ref="#/components/schemas/WarningResponse")
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(ref="#/components/schemas/DangerResponse")
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Server error",
     *         @OA\JsonContent(ref="#/components/schemas/DangerResponse")
     *     )
     * )
     *
     * @return JsonResponse
     */
    public function getTokenSales()
    {
        try {
            $data = [];
            $stages = 5;

            for ($stage = 1; $stage <= $stages; $stage++) {

                $pricedProducts = Price::query()->where('stage', $stage)->get();

                $products = Product::distinct('ticker')->where('status', true)
                    ->where('start_date', '<=', Carbon::now())
                    ->where('end_date', '>=', Carbon::now())
                    ->whereIn('id', $pricedProducts->pluck('product_id'))
                    ->byStage($stage)
                    ->get();

                $data[] = $products->map(function ($product) use ($stage) {
                    $tokenAmount = $product->purchases()->sum('token_amount');
                    return [
                        'id' => $product->id,
                        'title' => $product->title,
                        'ticker' => $product->ticker,
                        'supply' => $product->supply,
                        'sold' => $product->sold,
                        'presale_percentage' => $product->presale_percentage,
                        'icon' => $product->icon,
                        'start_date' => $product->start_date,
                        'end_date' => $product->end_date,
                        'status' => $product->status,
                        'stage' => $stage,
                        'sales' => $tokenAmount,
                        'percentage_sales' => ($tokenAmount / $product->supply) * 100,
                    ];
                })->all();
            }

            return response()->json([
                'status' => 'success',
                'title' => 'Launchpad Statistics',
                'message' => 'Statistics',
                'data' => $data,
            ]);
        } catch (Throwable $e) {
            return response()->jsonApi([
                'title' => 'Price Product List',
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }
}
