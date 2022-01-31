<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
    	return [
            'title' => $this->faker->title(),
            'currency_code' => '',
            'supply' => $this->faker->numberBetween(1000000, 1000000000000),
            'presale_percentage' => 5,
    	];
    }
}
