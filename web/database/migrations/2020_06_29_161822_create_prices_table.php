<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prices', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->integer('stage')->default(0);
            $table->double('price', 9, 6, true);
            $table->unsignedBigInteger('amount');
            $table->unsignedTinyInteger('period_in_days')->default(0);
            $table->double('percent_profit', 8, 4, true)->default(0);

            $table->foreignUuid('product_id')->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->boolean('status')->default(true);
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
        Schema::dropIfExists('prices');
    }
}
