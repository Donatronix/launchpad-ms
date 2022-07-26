<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('number', 15);
            $table->unsignedTinyInteger('payment_type_id');  // fiat/crypto ID
            $table->string('wallet_address', 256)->nullable();
            $table->string('card_number', 21)->nullable();
            $table->string('payment_gateway', 100)->nullable();
            $table->string('currency_code', 100)->nullable();
            $table->string('payment_token', 100)->nullable();
            $table->tinyInteger('token_stage')->nullable();
            $table->date('payment_date')->nullable();
            $table->decimal('amount_received', 12, 2, true);
            $table->decimal('total_amount', 12, 2, true);
            $table->decimal('bonus', 12, 2, true);
            $table->decimal('sol_received', 12, 2, true);
            $table->enum('status', ['0','1','2','3'])->default(1);
            $table->string('admin_id', 100)->nullable();
            $table->string('user_id', 100)->nullable();

            $table->uuid('order_id');

            $table->unsignedTinyInteger('credit_card_type_id')->default(0);

            $table->foreign('payment_type_id')->references('id')->on('payment_types');
            $table->foreign('order_id')->references('id')->on('orders');

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
        Schema::dropIfExists('transactions');
    }
}
