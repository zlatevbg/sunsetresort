<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApartmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('apartments', function (Blueprint $table) {
            $table->increments('id');
            $table->softDeletes();
            $table->nullableTimestamps();
            $table->string('number')->unique();
            $table->decimal('apartment_area', 6, 2)->unsigned()->nullable();
            $table->decimal('balcony_area', 6, 2)->unsigned()->nullable();
            $table->decimal('extra_balcony_area', 6, 2)->unsigned()->nullable();
            $table->decimal('common_area', 6, 2)->unsigned()->nullable();
            $table->decimal('total_area', 6, 2)->unsigned()->nullable();
            $table->text('comments')->nullable();
            $table->tinyInteger('mm_tax_formula')->nullable();

            $table->integer('room_id')->unsigned()->nullable();
            $table->index('room_id');
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('set null');

            $table->integer('furniture_id')->unsigned()->nullable();
            $table->index('furniture_id');
            $table->foreign('furniture_id')->references('id')->on('furniture')->onDelete('set null');

            $table->integer('view_id')->unsigned()->nullable();
            $table->index('view_id');
            $table->foreign('view_id')->references('id')->on('views')->onDelete('set null');

            $table->integer('project_id')->unsigned()->nullable();
            $table->index('project_id');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('set null');

            $table->integer('building_id')->unsigned()->nullable();
            $table->index('building_id');
            $table->foreign('building_id')->references('id')->on('buildings')->onDelete('set null');

            $table->integer('floor_id')->unsigned()->nullable();
            $table->index('floor_id');
            $table->foreign('floor_id')->references('id')->on('floors')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('apartments');
    }
}
