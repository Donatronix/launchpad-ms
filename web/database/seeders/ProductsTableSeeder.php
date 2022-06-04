<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

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
                'supply' => 100000000000,
                //'presale_percentage' => '',

            ],
            [
                'title' => 'DIVIT Token',
                'supply' => 1000000000000,
                //'presale_percentage' => '',
            ]
        ];

        // Create Products
        foreach ($list as $item){
            Product::factory()->create($item);
        }
    }
}
