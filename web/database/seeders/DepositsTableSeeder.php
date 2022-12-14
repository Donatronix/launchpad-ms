<?php

namespace Database\Seeders;
use App\Models\Order;
use App\Models\Deposit;

use Illuminate\Database\Seeder;

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
        $orders = Order::where('status', 1)->get();

         foreach($orders as $order){
             // Create Deposits
             Deposit::factory()->count(1)->create([
                 'order_id' => $order->id
             ]);
         }
    }
}
