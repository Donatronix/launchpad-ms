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

            $table->string('number', 20)->index();

            $table->foreignUuid('product_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->decimal('investment_amount', 12, 0);
            $table->unsignedTinyInteger('deposit_percentage')->default(0);
            $table->decimal('deposit_amount', 12, 0);
            $table->unsignedTinyInteger('filled')->default('0');
            $table->uuid('user_id')->index();

            $table->decimal('amount_token', 20, 0)->nullable();
            $table->decimal('amount_usd', 20, 0)->nullable();

            $table->unsignedTinyInteger('status')
                ->default(0)
                ->index();
            $table->unsignedTinyInteger('percentage_completed')->nullable();

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
