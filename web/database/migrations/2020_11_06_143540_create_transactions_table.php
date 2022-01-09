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
            $table->bigIncrements('id');
            $table->string('name', 50);

//            $table->unsignedBigInteger('sender_id');
//            $table->foreign('sender_id')->references('id')->on('contributors');

//            $table->unsignedBigInteger('receiver_id');
//            $table->foreign('receiver_id')->references('id')->on('contributors');

            $table->decimal('amount', 12);

            $table->unsignedTinyInteger('currency_id');

            $table->smallInteger('status')->default('1');

            $table->unsignedBigInteger('transactionable_id');
            $table->unsignedTinyInteger('transactionable_type');

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
