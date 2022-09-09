<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Database\Seeder;

class PurchasesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $pricesList = Product::all();

        // Create Prices
        foreach ($pricesList as $product) {
            Purchase::factory()->count(3)->create([
                'product_id' => $product->id
            ]);
        }
    }
}

