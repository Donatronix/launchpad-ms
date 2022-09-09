<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseFactory extends Factory
{
    protected $model = Purchase::class;

    public function definition(): array
    {
        return [
            'product_id' => $this->faker->randomElement(Product::all()),
            'payment_amount' => 5000,
            'user_id' => $this->faker->randomElement(config('settings.default_users_ids')),
            'currency_type' => "crypto",
            'token_amount' => 25312.046,
            'bonus'  => 25312.046,
            'total_token'  => 25312.046,
            'currency_ticker' => "btc",
            'spent_amount_usd' => $this->faker->randomDigit(),
            "status" => $this->faker->randomElement(Purchase::$statuses),
        ];
    }
}
