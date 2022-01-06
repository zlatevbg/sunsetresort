<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExtraServiceTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('extra_service_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');

            $table->string('locale')->index();
            $table->foreign('locale')->references('locale')->on('locales')->onDelete('cascade');

            $table->integer('extra_service_id')->unsigned()->index();
            $table->foreign('extra_service_id')->references('id')->on('extra_services')->onDelete('cascade');

            $table->unique(['extra_service_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('extra_service_translations');
    }
}
