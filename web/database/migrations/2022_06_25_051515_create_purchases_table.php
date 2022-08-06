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
            $table->decimal('payment_amount', 12, 5);
            $table->decimal('token_amount', 50, 12);
            $table->decimal('bonus', 50, 12);
            $table->decimal('total_token', 50, 12);
            $table->string('currency_ticker');
            $table->enum('currency_type', ['fiat', 'crypto', 'token', 'virtual']);
            $table->smallInteger('status')->default(0)->index();

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
