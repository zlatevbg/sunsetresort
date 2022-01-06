<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fees', function (Blueprint $table) {
            $table->increments('id');
            $table->softDeletes();
            $table->decimal('annual_communal_tax', 6, 2)->unsigned()->default(0);
            $table->decimal('daily_communal_tax', 6, 2)->unsigned()->default(0);
            $table->decimal('pool_tax', 6, 2)->unsigned()->default(0);
            $table->tinyInteger('pool_bracelets', 1)->unsigned()->default(0);
            $table->decimal('aquapark_tax', 6, 2)->unsigned()->default(0);
            $table->decimal('pool_aquapark_tax', 6, 2)->unsigned()->default(0);

            $table->integer('year_id')->unsigned();
            $table->index('year_id');
            $table->foreign('year_id')->references('id')->on('years')->onDelete('cascade');

            $table->integer('room_id')->unsigned()->nullable();
            $table->index('room_id');
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('fees');
    }
}
