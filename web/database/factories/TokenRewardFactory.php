<?php

namespace Database\Factories;

use App\Models\TokenReward;
use Illuminate\Database\Eloquent\Factories\Factory;

class TokenRewardFactory extends Factory
{
    protected $model = TokenReward::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid,
            'purchase_band' => $this->faker->randomNumber(),
            'swap' => $this->faker->numberBetween(5, 1000001),
            'deposit_amount' => $this->faker->numberBetween(1000, 2000000),
            'reward_bonus' => $this->faker->numberBetween(5, 50),
        ];
    }
}
