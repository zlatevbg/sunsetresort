<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFloorTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('floor_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');

            $table->string('locale')->index();
            $table->foreign('locale')->references('locale')->on('locales')->onDelete('cascade');

            $table->integer('floor_id')->unsigned()->index();
            $table->foreign('floor_id')->references('id')->on('floors')->onDelete('cascade');

            $table->unique(['floor_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('floor_translations');
    }
}
