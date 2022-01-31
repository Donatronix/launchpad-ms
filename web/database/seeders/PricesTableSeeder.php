<?php

namespace Database\Seeders;

use App\Models\Price;
use App\Models\Product;
use Illuminate\Database\Seeder;

class PricesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $pricesList = [
            '$utta' => [
                [
                    'stage' => 1,
                    'price' => 0.001,
                    'amount' => 15750000
                ],
                [
                    'stage' => 2,
                    'price' => 0.0095,
                    'amount' => 31500000
                ],
                [
                    'stage' => 3,
                    'price' => 0.0855,
                    'amount' => 47250000
                ],
                [
                    'stage' => 4,
                    'price' => 0.72675,
                    'amount' => 63000000
                ],
                [
                    'stage' => 5,
                    'price' => 5.814,
                    'amount' => 367500000
                ],
            ],

            '$divit' => [
                [
                    'stage' => 1,
                    'price' => 0.0001,
                    'amount' => 157500000
                ],
                [
                    'stage' => 2,
                    'price' => 0.00095,
                    'amount' => 315000000
                ],
                [
                    'stage' => 3,
                    'price' => 0.00855,
                    'amount' => 472500000
                ],
                [
                    'stage' => 4,
                    'price' => 0.072675,
                    'amount' => 630000000
                ],
                [
                    'stage' => 5,
                    'price' => 0.5814,
                    'amount' => 3675000000
                ],
            ]
        ];

        // Create Prices
        foreach ($pricesList as $code => $prices) {
            $product = Product::where('currency_code', $code)->first();

            foreach ($prices as $price){
                Price::factory()->create(array_merge(['product_id' => $product->id], $price));
            }
        }
    }
}
