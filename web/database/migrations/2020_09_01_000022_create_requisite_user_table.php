<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequisiteUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('requisite_user', function (Blueprint $table) {
            $table->bigIncrements('id');

//            $table->unsignedBigInteger('user_id');
//            $table->foreign('user_id')->references('id')->on('users');

            $table->unsignedInteger('requisite_id');
            $table->foreign('requisite_id')->references('id')->on('requisites');

            $table->boolean('is_locked')->default(true);
            $table->text('value');
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
        Schema::dropIfExists('requisite_user');
    }
}
