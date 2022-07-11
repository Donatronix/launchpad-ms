<?php

namespace Database\Factories;

use App\Models\Deposit;
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
        // $investment = $this->faker->numberBetween(2500, 100000);
        // $percentage = $this->faker->numberBetween(10, 100);
        // $deposit = $investment * $percentage / 100;

        // return [
        //     'product_id' => $this->faker->randomElement(Product::all()),
        //     'investment_amount' => $investment,
        //     'deposit_percentage' => $percentage,
        //     'deposit_amount' => $deposit,
        //     'user_id' => $this->faker->randomElement(config('settings.default_users_ids')),
        //     'status' => Deposit::STATUS_CREATED,
        //     'payload' => ''
        // ];
        return [];
    }
}
