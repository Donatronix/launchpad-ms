<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
    	return [
            'purchased_token_id' => '',
            'investment_amount' => '',
            'deposit_percentage' => '',
            'deposit_amount' => '',
            'contributor_id' => '',
            'status' => '',
            'payload' => ''
    	];
    }
}
