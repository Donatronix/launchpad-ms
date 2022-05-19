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
            $table->unsignedTinyInteger('payment_system');
            $table->string('wallet_address', 256)->nullable();
            $table->decimal('total_amount', 12);
            $table->unsignedInteger('order_id');
            $table->uuid('user_id');

            $table->foreign('user_id')->references('id')->on('contributors');
            $table->foreign('payment_type_id')->references('id')->on('payment_types');

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
