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
                'end_date' => Carbon::parse('20th June 2022'),
                'icon' => "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAAAXNSR0IArs4c6QAAAC1JREFUWEft0EERAAAAAUH6lxbDZxU4s815PffjAAECBAgQIECAAAECBAgQIDAaPwAh6O5R/QAAAABJRU5ErkJggg==",
            ],
            [
                'title' => 'DIVIT Token',
                'ticker' => 'divit',
                'supply' => 1000000000000,
                'presale_percentage' => '0.7',
                'start_date' => Carbon::parse('7th June 2022'),
                'end_date' => Carbon::parse('20th June 2022'),
                'icon' => "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAAAXNSR0IArs4c6QAAAC1JREFUWEft0EERAAAAAUH6lxbDZxU4s815PffjAAECBAgQIECAAAECBAgQIDAaPwAh6O5R/QAAAABJRU5ErkJggg==",
            ]
        ];

        // Create Products
        foreach ($list as $item){
            Product::factory()->create($item);
        }
    }
}
