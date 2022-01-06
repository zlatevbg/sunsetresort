<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRentalContractTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rental_contract_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('benefits')->nullable();

            $table->string('locale')->index();
            $table->foreign('locale')->references('locale')->on('locales')->onDelete('cascade');

            $table->integer('rental_contract_id')->unsigned()->index();
            $table->foreign('rental_contract_id')->references('id')->on('rental_contracts')->onDelete('cascade');

            $table->unique(['rental_contract_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('rental_contract_translations');
    }
}
