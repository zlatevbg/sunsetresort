<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProxyTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proxy_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('address');
            $table->string('issued_by')->nullable();

            $table->string('locale')->index();
            $table->foreign('locale')->references('locale')->on('locales')->onDelete('cascade');

            $table->integer('proxy_id')->unsigned()->index();
            $table->foreign('proxy_id')->references('id')->on('proxies')->onDelete('cascade');

            $table->unique(['proxy_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('proxy_translations');
    }
}
