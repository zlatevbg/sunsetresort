<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNavigationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('navigation', function (Blueprint $table) {
            $table->increments('id');
            $table->nullableTimestamps();
            $table->integer('parent')->unsigned()->nullable();
            $table->string('name');
            $table->string('title')->nullable();
            $table->string('description')->nullable();
            $table->mediumText('content')->nullable();
            $table->string('slug')->nullable();
            $table->string('route')->nullable();
            $table->string('route_method')->nullable();
            $table->integer('order')->unsigned()->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_category')->default(false);
            $table->boolean('is_popup')->default(false);
            $table->string('type')->nullable();

            $table->foreign('parent')->references('id')->on('navigation')->onDelete('cascade');

            $table->integer('locale_id')->unsigned();
            $table->index('locale_id');
            $table->foreign('locale_id')->references('id')->on('locales')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('navigation');
    }
}
