<?php

namespace Database\Seeders;

use App\Models\CreditCardType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CreditCardTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = CreditCardType::all();
        if ($types->count() > 0) {
            return;
        }

        DB::table('credit_card_types')->insert([
            ['id' => 1, 'name' => 'visa', 'label' => 'Visa'],
            ['id' => 2, 'name' => 'mastercard', 'label' => 'Master Card']
        ]);
    }
}
