<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContributorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contributors', function (Blueprint $table) {
            $table->uuid('id')->index();

            /**
             * Contributor common data
             */
            $table->string('first_name', 60)->nullable();
            $table->string('last_name', 60)->nullable();
            $table->enum('gender', [null, 'm', 'f'])->nullable();
            $table->date('date_birthday')->nullable(); // (YYYY-MM-DD) Date of birth

            // ????? поле под вопросом
            $table->string('phone', 50)->nullable();

            $table->string('email', 100)->nullable();
            $table->string('id_number')->nullable();        // National identification number

            /**
             * Contributor address
             */
            $table->unsignedSmallInteger('country_id')->default(0);
//            $table->foreign('country_id')->references('id')->on('countries');

            $table->string('address_line1', 150)->nullable();
            $table->string('address_line2', 100)->nullable();
            $table->string('city', 50)->nullable();
            $table->string('zip', 10)->nullable();

            /**
             * Contributor document
             */
            $table->string('document_number')->nullable();  // Document number
            $table->string('document_country')->nullable(); // ISO-2- String Country that issued the document
            $table->tinyInteger('document_type')->default(0);  // Document type
            $table->text('document_file')->nullable();  // Document file

            $table->uuid('user_id')->nullable()->index();
            $table->tinyInteger('status')->default(0);

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('whitelists');
    }
}
