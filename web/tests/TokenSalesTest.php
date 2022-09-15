<?php

    namespace Tests;

    use App\Models\Price;
    use App\Models\Product;
    use Carbon\Carbon;

    class TokenSalesTest extends TestCase
    {
        /**
         * A basic test example.
         *
         * @return void
         */
        public function testTokenSales()
        {
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
                        'stage' => $stage,
                        'sales' => $tokenAmount,
                        'product' => [
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
                            'created_at' => $product->created_at->format('Y-m-d H:i:s'),
                            'updated_at' => $product->updated_at->format('Y-m-d H:i:s'),
                        ],
                        'percentage_sales' => ($tokenAmount / $product->supply) * 100,
                    ];
                })->all();
            }

            $this->assertTrue(count($data) > 0);
        }

        public function testTokenSalesDemo()
        {
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
                        'unsold' => (float)$product->supply - (float)$tokenAmount,
                    ];
                })->all();
            }
            dd($data);

            $this->assertTrue(count($data) > 0);
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
