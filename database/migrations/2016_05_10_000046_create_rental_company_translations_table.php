<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRentalCompanyTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rental_company_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('address');
            $table->string('manager');

            $table->string('locale')->index();
            $table->foreign('locale')->references('locale')->on('locales')->onDelete('cascade');

            $table->integer('rental_company_id')->unsigned()->index();
            $table->foreign('rental_company_id')->references('id')->on('rental_companies')->onDelete('cascade');

            $table->unique(['rental_company_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('rental_company_translations');
    }
}
