<?php

namespace Database\Seeders;

use App\Models\PaymentType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = PaymentType::all();

        if ($types->count() > 0) {
            return;
        }

        DB::table('payment_types')->insert([
            ['id' => 1, 'name' => 'fiat', 'label' => 'Fiat'],
            ['id' => 2, 'name' => 'crypto', 'label' => 'Crypto'],
            ['id' => 3, 'name' => 'card', 'label' => 'Debit Card']
        ]);
    }
}
