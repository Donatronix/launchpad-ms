<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Seeder;

class OrdersTableSeeder extends Seeder
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

        foreach ($products as $product) {
            // Create orders
            Order::factory()->count(10)->create([
                'product_id' => $product->id
            ]);
        }
    }
}
