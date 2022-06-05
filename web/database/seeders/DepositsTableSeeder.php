<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Deposit;
use App\Models\Product;

class DepositsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         // Get products
         $products = Product::all();

         foreach($products as $product){
             // Create Deposits
             Deposit::factory()->count(10)->create([
                 'product_id' => $product->id
             ]);
         }
    }
}
