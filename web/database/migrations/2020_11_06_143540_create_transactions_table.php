<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
//            $table->string('name', 50);

            // $table->tinyInteger('payment_method');
//            $table->tinyInteger('payment_system');
//            $table->unsignedTinyInteger('payment_currency_id')->nullable();

//            $table->uuid('sender_id');

//            $table->uuid('receiver_id');
//
//            $table->decimal('amount', 12);
//
//            $table->unsignedTinyInteger('currency_id');
//
//            $table->smallInteger('status')->default('1');
//
//            $table->unsignedBigInteger('transactionable_id');
//            $table->unsignedTinyInteger('transactionable_type');
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
