<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ProductsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $list = [
            [
                'title' => 'UTTA Token',
                'ticker' => 'utta',
                'supply' => 100000000000,
                'presale_percentage' => '0.7',
                'start_date' => Carbon::parse('7th June 2022'),
            ],
            [
                'title' => 'DIVIT Token',
                'ticker' => 'divit',
                'supply' => 1000000000000,
                'presale_percentage' => '0.7',
                'start_date' => Carbon::parse('7th June 2022'),
            ]
        ];

        // Create Products
        foreach ($list as $item){
            Product::factory()->create($item);
        }
    }
}
