<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('number', 15);
            $table->decimal('amount_usd', 12, 2)->nullable();
            $table->decimal('token_amount', 12, 5);
            $table->decimal('crypto_amount', 12, 5)->nullable();
            $table->string('crypto')->nullable();
            $table->enum('currency_type', ['fiat', 'crypto']);
            $table->string('payment_method')->nullable();
            $table->boolean('payment_status')->default(0);

            $table->foreignUuid('user_id');

            $table->foreignUuid('product_id')->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');

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
        Schema::dropIfExists('purchases');
    }
}
