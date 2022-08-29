<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepositsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deposits', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('number', 20)->index();
            $table->decimal('amount', 16, 8, true);
            $table->string('currency_code', 10)->index();

            $table->string('order_id')
                ->default(config('settings.empty_uuid'))
                ->index();

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
        Schema::dropIfExists('deposits');
    }
}
