<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentType;

class PaymentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = PaymentType::all();
        if($types->count() > 0){
            return;
        }
        DB::table('payment_types')->insert([
            ['id' => 1, 'name' => 'fiat', 'label' => 'Fiat'],
            ['id' => 2, 'name' => 'crypto', 'label' => 'Crypto']
        ]);
    }
}
