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

            $table->string('product_id');

            $table->decimal('investment_amount', 12, 0);
            $table->unsignedTinyInteger('deposit_percentage');
            $table->decimal('deposit_amount', 12, 0);

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
        Schema::dropIfExists('deposits');
    }
}
