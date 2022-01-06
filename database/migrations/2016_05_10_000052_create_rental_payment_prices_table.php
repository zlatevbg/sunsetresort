<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRentalPaymentPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rental_payment_prices', function (Blueprint $table) {
            $table->increments('id');
            $table->softDeletes();
            $table->decimal('price', 6, 2)->unsigned()->default(0);

            $table->integer('rental_payment_id')->unsigned();
            $table->index('rental_payment_id');
            $table->foreign('rental_payment_id')->references('id')->on('rental_payments')->onDelete('cascade');

            $table->integer('room_id')->unsigned()->nullable();
            $table->index('room_id');
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('cascade');

            $table->integer('furniture_id')->unsigned()->nullable();
            $table->index('furniture_id');
            $table->foreign('furniture_id')->references('id')->on('furniture')->onDelete('cascade');

            $table->integer('view_id')->unsigned()->nullable();
            $table->index('view_id');
            $table->foreign('view_id')->references('id')->on('views')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('rental_payment_prices');
    }
}
