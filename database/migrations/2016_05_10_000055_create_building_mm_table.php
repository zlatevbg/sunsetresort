<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBuildingMmTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('building_mm', function (Blueprint $table) {
            $table->increments('id');
            $table->nullableTimestamps();
            $table->decimal('mm_tax', 6, 2)->unsigned()->nullable();
            $table->timestamp('deadline_at')->nullable();

            $table->integer('building_id')->unsigned();
            $table->index('building_id');
            $table->foreign('building_id')->references('id')->on('buildings')->onDelete('cascade');

            $table->integer('year_id')->unsigned();
            $table->index('year_id');
            $table->foreign('year_id')->references('id')->on('years')->onDelete('cascade');

            $table->integer('management_company_id')->unsigned();
            $table->index('management_company_id');
            $table->foreign('management_company_id')->references('id')->on('management_companies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('building_mm');
    }
}
