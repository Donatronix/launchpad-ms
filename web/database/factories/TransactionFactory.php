<?php

namespace Database\Factories;

use App\Models\Order;
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
            'number' => $this->faker->randomNumber(),
            'payment_type_id' => $this->faker->numberBetween(1, 3),
            'wallet_address' => uniqid('WKSHA273FSHS'),
            'card_number' => $this->faker->creditCardNumber(),
            'payment_gateway' => $this->faker->creditCardNumber(),
            'currency_code' => $this->faker->creditCardNumber(),
            'payment_token' => $this->faker->creditCardNumber(),
            'token_stage' => $this->faker->numberBetween(4, 5),
            'payment_date' => Carbon::now(),
            'amount_received' => $this->faker->numberBetween(5, 1000001),
            'total_amount' => $this->faker->numberBetween(5, 1000001),
            'bonus' => $this->faker->numberBetween(5, 1000001),
            'sol_received' => $this->faker->numberBetween(5, 1000001),
            'status' => $this->faker->randomElement(Transaction::$statuses),
            'order_id' => $this->faker->randomElement(Order::all())->id,
            'admin_id' => $this->faker->randomElement(config('settings.default_users_ids')),
            'user_id' => $this->faker->randomElement(config('settings.default_users_ids')),
        ];
    }
}
