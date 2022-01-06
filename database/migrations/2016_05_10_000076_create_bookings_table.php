<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->increments('id');
            $table->nullableTimestamps();
            $table->boolean('is_confirmed')->default(false);
            $table->timestamp('arrive_at')->nullable();
            $table->timestamp('departure_at')->nullable();
            $table->string('arrival_time')->nullable();
            $table->string('departure_time')->nullable();
            $table->string('arrival_flight')->nullable();
            $table->string('departure_flight')->nullable();
            $table->enum('arrival_transfer', ['car', 'minibus'])->nullable();
            $table->enum('departure_transfer', ['car', 'minibus'])->nullable();
            $table->boolean('loyalty_card')->nullable();
            $table->boolean('club_card')->nullable();
            $table->boolean('kitchen_items')->nullable();
            $table->boolean('deposit_paid')->nullable();
            $table->boolean('hotel_card')->nullable();
            $table->text('message')->nullable();
            $table->text('comments')->nullable();
            $table->string('services')->nullable();
            $table->boolean('accommodation_costs')->default(false);
            $table->boolean('transfer_costs')->default(false);
            $table->boolean('services_costs')->default(false);

            $table->integer('project_id')->unsigned();
            $table->index('project_id');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');

            $table->integer('building_id')->unsigned();
            $table->index('building_id');
            $table->foreign('building_id')->references('id')->on('buildings')->onDelete('cascade');

            $table->integer('apartment_id')->unsigned();
            $table->index('apartment_id');
            $table->foreign('apartment_id')->references('id')->on('apartments')->onDelete('cascade');

            $table->integer('owner_id')->unsigned();
            $table->index('owner_id');
            $table->foreign('owner_id')->references('id')->on('owners')->onDelete('cascade');

            $table->integer('arrival_airport_id')->unsigned()->nullable();
            $table->index('arrival_airport_id');
            $table->foreign('arrival_airport_id')->references('id')->on('airports')->onDelete('set null');

            $table->integer('departure_airport_id')->unsigned()->nullable();
            $table->index('departure_airport_id');
            $table->foreign('departure_airport_id')->references('id')->on('airports')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('bookings');
    }
}
