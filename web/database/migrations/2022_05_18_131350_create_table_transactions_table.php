<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('transactions');

        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedTinyInteger('payment_type_id');  // fiat/crypto ID
            $table->string('wallet_address', 256)->nullable();
            $table->string('card_number', 21)->nullable();
            $table->decimal('total_amount', 12);

            $table->uuid('order_id');
            $table->uuid('user_id');

            $table->unsignedTinyInteger('credit_card_type_id')->default(0);


            $table->foreign('payment_type_id')->references('id')->on('payment_types');
            $table->foreign('order_id')->references('id')->on('orders');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
