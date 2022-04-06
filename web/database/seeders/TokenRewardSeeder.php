<?php

namespace Database\Seeders;

use App\Models\TokenReward;
use Illuminate\Database\Seeder;

class TokenRewardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TokenReward::firstOrCreate([
            'purchase_band' => 1,
            'swap' => 5,
            'deposit_amount' => 1000,
            'reward_bonus' => 5,
        ]);
        
        TokenReward::firstOrCreate([
            'purchase_band' => 2,
            'swap' => 1001,
            'deposit_amount' => 10000,
            'reward_bonus' => 10,
        ]);

        TokenReward::firstOrCreate([
            'purchase_band' => 3,
            'swap' => 10001,
            'deposit_amount' => 100000,
            'reward_bonus' => 20,
        ]);

        TokenReward::firstOrCreate([
            'purchase_band' => 4,
            'swap' => 100001,
            'deposit_amount' => 500000,
            'reward_bonus' => 30,
        ]);

        TokenReward::firstOrCreate([
            'purchase_band' => 5,
            'swap' => 500001,
            'deposit_amount' => 1000000,
            'reward_bonus' => 40,
        ]);

        TokenReward::firstOrCreate([
            'purchase_band' => 6,
            'swap' => 1000001,
            'deposit_amount' => '+++',
            'reward_bonus' => 50,
        ]);
    }
}
