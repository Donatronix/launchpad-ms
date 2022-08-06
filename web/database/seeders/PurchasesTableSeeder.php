<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Purchase;
use App\Models\Product;

class PurchasesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $pricesList = [
            'utta' => [
                [
                    "payment_amount" => 5000,
                    "currency_type"  => "crypto",
                    "token_amount"  => 25312.046,
                    "bonus"  => 25312.046,
                    "total_token"  => 25312.046,
                    "currency_ticker" => "btc",
                    "status"  => 0
                ],
                [
                    "payment_amount" => 5000,
                    "currency_type"  => "crypto",
                    "token_amount"  => 25312.046,
                    "bonus"  => 25312.046,
                    "total_token"  => 25312.046,
                    "currency_ticker" => "btc",
                    "status"  => 0
                ],
            ],

            'divit' => [
                [
                    "payment_amount" => 5000,
                    "currency_type"  => "crypto",
                    "token_amount"  => 25312.046,
                    "bonus"  => 25312.046,
                    "total_token"  => 25312.046,
                    "currency_ticker" => "btc",
                    "status"  => 0
                ],
                [
                    "payment_amount" => 5000,
                    "currency_type"  => "crypto",
                    "token_amount"  => 25312.046,
                    "bonus"  => 25312.046,
                    "total_token"  => 25312.046,
                    "currency_ticker" => "btc",
                    "status"  => 0
                ],
            ]
        ];

        // Create Prices
        foreach ($pricesList as $code => $prices) {
            $product = Product::where('ticker', $code)->first();

            foreach ($prices as $price) {
                Purchase::factory()->create(array_merge(['product_id' => $product->id], $price));
            }
        }
    }
}

