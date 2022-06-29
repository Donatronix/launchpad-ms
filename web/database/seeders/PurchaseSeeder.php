<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Purchase;
use App\Models\Product;

class PurchaseSeeder extends Seeder
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
                    "amount_usd" => 5000,
                    "token_amount"  => 25312.046,
                    "payment_method" => "Credit card",
                    "payment_status"  => true
                ],
                [
                    "amount_usd" => 3000,
                    "token_amount"  => 24312.046,
                    "payment_method" => "Credit card",
                    "payment_status"  => true
                ],
            ],

            'divit' => [
                [
                    "amount_usd" => 5000,
                    "token_amount"  => 25312.046,
                    "payment_method" => "Credit card",
                    "payment_status"  => true
                ],
                [
                    "amount_usd" => 3000,
                    "token_amount"  => 24312.046,
                    "payment_method" => "Credit card",
                    "payment_status"  => true
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

