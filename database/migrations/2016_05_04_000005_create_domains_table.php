<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDomainsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('domains', function (Blueprint $table) {
            $table->increments('id');
            $table->nullableTimestamps();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('namespace');
            $table->string('route');
            $table->string('description')->nullable();
            $table->boolean('hide_default_locale')->default(true);

            $table->integer('default_locale_id')->unsigned()->nullable();
            $table->index('default_locale_id');
            $table->foreign('default_locale_id')->references('id')->on('locales')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('domains');
    }
}
