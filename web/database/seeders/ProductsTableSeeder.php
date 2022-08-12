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
                'id' => '969ff58b-5d48-4de4-8e9e-cb6bb39e6041',
                'title' => 'UTTA Token',
                'ticker' => 'utta',
                'supply' => 100000000000,
                'presale_percentage' => '0.7',
                'start_date' => Carbon::parse('7th August 2022'),
                'end_date' => Carbon::parse('31st August 2022'),
                'icon' => 'https://ugg.s3.us-west-2.amazonaws.com/icons/currencies/utta-token.svg',
            ],
            [
                'id' => '969ff58b-6e9d-477a-90a7-87eb0039e426',
                'title' => 'DIVIT Token',
                'ticker' => 'divit',
                'supply' => 1000000000000,
                'presale_percentage' => '0.7',
                'start_date' => Carbon::parse('7th August 2022'),
                'end_date' => Carbon::parse('31st August 2022'),
                'icon' => 'https://ugg.s3.us-west-2.amazonaws.com/icons/currencies/divit-token.svg',
            ]
        ];

        // Create Products
        foreach ($list as $item) {
            Product::factory()->create($item);
        }
    }
}
