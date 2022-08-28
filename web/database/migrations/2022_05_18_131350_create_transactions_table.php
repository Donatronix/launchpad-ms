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
            $table->string('number', 20)->index();

            $table->string('wallet_address', 256)->nullable();
            $table->string('card_number', 21)->nullable();
            $table->string('payment_gateway', 100)->nullable();
            $table->string('currency_code', 100)->nullable();
            $table->string('payment_token', 100)->nullable();
            $table->tinyInteger('token_stage');
            $table->date('payment_date')->nullable();

            $table->decimal('amount_received', 20, 9, true);
            $table->decimal('total_amount', 20, 9, true);
            $table->decimal('bonus', 20, 9, true);
            $table->decimal('sol_received', 20, 9, true);

            $table->unsignedTinyInteger('status')->default(1);

            $table->string('admin_id', 100)->nullable();
            $table->string('user_id', 100)->nullable();

            $table->foreignUuid('order_id')
                ->constrained()
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
        Schema::dropIfExists('transactions');
    }
}
