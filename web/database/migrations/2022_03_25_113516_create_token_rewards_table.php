<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTokenRewardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('token_rewards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('purchase_band');
            $table->string('swap');
            $table->string('deposit_amount');
            $table->unsignedBigInteger('reward_bonus');
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
        Schema::dropIfExists('token_rewards');
    }
}
