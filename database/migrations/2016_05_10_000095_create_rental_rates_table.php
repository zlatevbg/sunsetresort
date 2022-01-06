<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRentalRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rental_rates', function (Blueprint $table) {
            $table->tinyInteger('rate')->nullable()->unsigned();
            $table->softDeletes();
            $table->integer('period_id')->unsigned();
            $table->index('period_id');
            $table->string('project')->index();
            $table->string('room')->index();
            $table->string('view')->index();

            $table->foreign('period_id')->references('id')->on('rental_rates_periods')->onDelete('cascade');

            $table->unique(['period_id', 'project', 'room', 'view']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('rental_rates');
    }
}
