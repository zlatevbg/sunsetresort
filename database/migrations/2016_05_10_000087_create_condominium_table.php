<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCondominiumTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('condominium', function (Blueprint $table) {
            $table->increments('id');
            $table->nullableTimestamps();
            $table->timestamp('assembly_at')->nullable();

            $table->integer('building_id')->unsigned();
            $table->index('building_id');
            $table->foreign('building_id')->references('id')->on('buildings')->onDelete('cascade');

            $table->integer('year_id')->unsigned();
            $table->index('year_id');
            $table->foreign('year_id')->references('id')->on('years')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('condominium');
    }
}
