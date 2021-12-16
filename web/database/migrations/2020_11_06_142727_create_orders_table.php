<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('gateway', 10);
            $table->decimal('amount');
            $table->unsignedTinyInteger('currency_id')->nullable();

//            $table->unsignedBigInteger('user_id');
//            $table->foreign('user_id')->references('id')->on('users');

            $table->tinyInteger('type');
            $table->smallInteger('status')->nullable();
            $table->text('payload')->nullable();
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
        Schema::dropIfExists('orders');
    }
}
