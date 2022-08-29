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

            $table->string('number', 20)->index();

            $table->foreignUuid('product_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->decimal('token_amount', 20, 8, true);
            $table->decimal('bonus', 20, 8, true);
            $table->decimal('total_token', 20, 8, true);

            $table->decimal('payment_amount', 16, 8, true);
            $table->string('currency_ticker');
            $table->enum('currency_type', ['fiat', 'crypto', 'token', 'virtual']);
            $table->uuid('user_id')->index();

            $table->unsignedTinyInteger('status')
                ->default(0)
                ->index();

            $table->uuid('payment_order_id')
                ->default(config('settings.empty_uuid'))
                ->index();

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
