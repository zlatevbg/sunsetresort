<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRentalCompanyYearTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rental_company_year', function (Blueprint $table) {
            $table->nullableTimestamps();
            $table->integer('rental_company_id')->unsigned();
            $table->integer('year_id')->unsigned();

            $table->foreign('rental_company_id')->references('id')->on('rental_companies')->onDelete('cascade');
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
        Schema::drop('rental_company_year');
    }
}
