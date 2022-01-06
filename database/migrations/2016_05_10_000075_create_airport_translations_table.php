<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAirportTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('airport_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');

            $table->string('locale')->index();
            $table->foreign('locale')->references('locale')->on('locales')->onDelete('cascade');

            $table->integer('airport_id')->unsigned()->index();
            $table->foreign('airport_id')->references('id')->on('airports')->onDelete('cascade');

            $table->unique(['airport_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('airport_translations');
    }
}
