<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeDepositAmountColumnTypeInTokenRewardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('token_rewards', function (Blueprint $table) {
            $table->string('deposit_amount')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('token_rewards', function (Blueprint $table) {
            $table->unsignedBigInteger('deposit_amount')->change();
        });
    }
}
