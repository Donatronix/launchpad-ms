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
            $table->uuid('id')->primary();

            $table->string('number', 15);
            $table->uuid('product_id');

            $table->decimal('investment_amount', 12, 0);
            $table->unsignedTinyInteger('deposit_percentage');
            $table->decimal('deposit_amount', 12, 0);
            $table->unsignedTinyInteger('filled')->default('0');
            $table->uuid('user_id');

            $table->decimal('amount_token', 12, 2)->nullable();
            $table->decimal('amount_usd', 12, 2)->nullable();

            $table->smallInteger('status')->nullable();

            $table->text('payload')->nullable();

            $table->timestamps();
            $table->softDeletes();
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
