<?php

namespace Database\Factories;

use App\Models\Deposit;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepositFactory extends Factory
{
    protected $model = Deposit::class;


    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid,
            'amount' => $this->faker->numberBetween(10000, 100000),
            'currency_code' => $this->faker->currencyCode(),
            'user_id' => $this->faker->randomElement(config('settings.default_users_ids')),
            'status' => Deposit::STATUS_CREATED
        ];
    }
}
