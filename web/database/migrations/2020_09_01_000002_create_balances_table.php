<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBalancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('balances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->decimal('amount', 12, 2);

            $table->unsignedTinyInteger('currency_id');
            $table->foreign('currency_id')->references('id')->on('currencies');

//            $table->unsignedBigInteger('user_id');
//            $table->foreign('user_id')->references('id')->on('contributors');

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
        Schema::dropIfExists('balances');
    }
}
