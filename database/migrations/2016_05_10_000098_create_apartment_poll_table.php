<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApartmentPollTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('apartment_poll', function (Blueprint $table) {
            $table->nullableTimestamps();
            $table->softDeletes();
            $table->tinyInteger('q1')->unsigned()->nullable();
            $table->tinyInteger('q2')->unsigned()->nullable();
            $table->integer('poll_id')->unsigned()->index();
            $table->integer('apartment_id')->unsigned()->index();
            $table->integer('owner_id')->unsigned()->index();

            $table->unique(['poll_id', 'apartment_id']);

            $table->foreign('poll_id')->references('id')->on('polls')->onDelete('cascade');
            $table->foreign('apartment_id')->references('id')->on('apartments')->onDelete('cascade');
            $table->foreign('owner_id')->references('id')->on('owners');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('apartment_poll');
    }
}
