<?php

namespace App\Api\V1\Controllers\Admin;

    use App\Api\V1\Controllers\Controller;
    use App\Models\Price;
    use App\Models\Product;
    use Carbon\Carbon;
    use Illuminate\Http\Request;
    use Throwable;

    class StatisticsController extends Controller
    {
        /**
         * Display token sales at the various stages
         *
         * @OA\Get(
         *     path="/admin/summary/token-sales",
         *     summary="Get token sales at the various stages",
         *     description="Display token sales at the various stages",
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
         * @param Request $request
         *
         * @return mixed
         */
        public function getTokenSales(Request $request): mixed
        {
            try {
                $data = [];
                $stages = 5;

                for ($stage = 1; $stage <= $stages; $stage++) {

                    $products = $this->getProducts($stage);

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

                return response()->jsonApi([
                    'status' => 'success',
                    'title' => 'Launchpad Statistics',
                    'message' => 'Statistics',
                    'data' => $data,
                ]);
            } catch (Throwable $e) {
                return response()->jsonApi([
                    'title' => 'Launchpad Statistics',
                    'message' => $e->getMessage(),
                ], 500);
            }
        }

        public function getStageStatistics(Request $request): mixed
        {
            try {
                $data = [];
                $stages = 5;

                for ($stage = 1; $stage <= $stages; $stage++) {

                    $products = $this->getProducts($stage);

                    $data[] = $products->map(function ($product) use ($stage) {
                        $tokenAmount = $product->purchases()->sum('token_amount');
                        return [
                            'stage' => $stage,
                            'token' => $product->ticker,
                            'token_supply' => $product->supply,
                            'sold' => $tokenAmount,
                            'unsold' => $product->supply - $tokenAmount,
                        ];
                    })->all();
                }

                return response()->jsonApi([
                    'status' => 'success',
                    'title' => 'Launchpad Statistics',
                    'message' => 'Statistics',
                    'data' => $data,
                ]);
            } catch (Throwable $e) {
                return response()->jsonApi([
                    'status' => 'danger',
                    'title' => 'Launchpad Statistics',
                    'message' => $e->getMessage(),
                ], 500);
            }
        }

        protected function getProducts($stage)
        {
            $pricedProducts = Price::query()->where('stage', $stage)->get();

            return Product::distinct('ticker')->where('status', true)
                ->where('start_date', '<=', Carbon::now())
                ->where('end_date', '>=', Carbon::now())
                ->whereIn('id', $pricedProducts->pluck('product_id'))
                ->byStage($stage)
                ->get();
        }
    }
