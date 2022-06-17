<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $this->call([
            ContributorsTableSeeder::class,
            ProductsTableSeeder::class,
            PricesTableSeeder::class,
            OrdersTableSeeder::class,
            TokenRewardSeeder::class,
            PaymentTypeSeeder::class,
            CreditCardTypeSeeder::class,
            FaqSeeder::class,
        ]);
    }
}
