<?php

namespace Database\Factories;

use App\Models\Price;
use Illuminate\Database\Eloquent\Factories\Factory;

class PriceFactory extends Factory
{
    protected $model = Price::class;

    public function definition(): array
    {
    	return [
            'product_id' => 'slp',
            'stage' => 1,
            'price' => 0.001,
            'amount' => 15750000
    	];
    }
}
