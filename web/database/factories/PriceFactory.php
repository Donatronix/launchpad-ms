<?php

namespace Database\Factories;

use App\Models\Price;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class PriceFactory extends Factory
{
    protected $model = Price::class;

    public function definition(): array
    {
    	return [
            'product_id' => $this->faker->randomElement(Product::all()),
            'stage' => 1,
            'price' => 0.001,
            'amount' => 1000000000,
            'period_in_days' => 2
    	];
    }
}
