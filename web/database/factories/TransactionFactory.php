<?php

namespace Database\Factories;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid,
            'number' => $this->faker->randomNumber(),
            'payment_type_id' => $this->faker->numberBetween(5, 1000001),
            'wallet_address' => uniqid('WKSHA273FSHS'),
            'card_number' => $this->faker->creditCardNumber(),
            'payment_gateway' => $this->faker->creditCardNumber(),
            'currency_code' => $this->faker->creditCardNumber(),
            'payment_token' => $this->faker->creditCardNumber(),
            'token_stage' => $this->faker->creditCardNumber(),
            'payment_date' => Carbon::now(),
            'amount_received' => $this->faker->numberBetween(5, 1000001),
            'total_amount' => $this->faker->numberBetween(5, 1000001),
            'bonus' => $this->faker->numberBetween(5, 1000001),
            'sol_received' => $this->faker->numberBetween(5, 1000001),
            'status' => 1,
            'admin_id' => $this->faker->uuid,
            'user_id' => $this->faker->uuid,

        ];
    }
}
