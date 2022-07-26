<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Seeds for all
        $this->call([
            ProductsTableSeeder::class,
            PricesTableSeeder::class,
            TokenRewardsTableSeeder::class,
            PaymentTypesTableSeeder::class,
            CreditCardTypeSeeder::class,
            PurchaseSeeder::class,
            TransactionTableSeeder::class
        ]);

        // Seeds for local and staging
        if (App::environment(['local', 'staging'])) {
            $this->call([
                OrdersTableSeeder::class,
                DepositsTableSeeder::class,
            ]);
        }
    }
}
