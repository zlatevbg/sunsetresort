<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookingGuestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('booking_guests', function (Blueprint $table) {
            $table->increments('id');
            $table->text('name');
            $table->enum('type', ['adult', 'child'])->nullable();
            $table->integer('order')->unsigned()->default(0);

            $table->integer('booking_id')->unsigned();
            $table->index('booking_id');
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('booking_guests');
    }
}
