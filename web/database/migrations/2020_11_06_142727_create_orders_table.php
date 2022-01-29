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
            $table->decimal('purchased_token_id');
            $table->decimal('investment_amount');
            $table->decimal('deposit_percentage');
            $table->decimal('deposit_amount');

            $table->foreignUuid('contributor_id')->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');

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
