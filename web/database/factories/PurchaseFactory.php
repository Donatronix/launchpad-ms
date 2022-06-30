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
            "amount_usd" => 5000,
            'user_id' => $this->faker->randomElement(config('settings.default_users_ids')),
            "token_amount"  => 25312.046,
            "payment_method" => "Credit card",
            "payment_status"  => true
        ];
    }
}
