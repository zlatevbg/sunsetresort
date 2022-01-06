<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContractYearsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contract_years', function (Blueprint $table) {
            $table->increments('id');
            $table->softDeletes();
            $table->nullableTimestamps();
            $table->char('year', 4);
            $table->char('mm_for_year', 4)->nullable();
            $table->string('mm_for_years')->nullable();
            $table->decimal('price', 8, 2)->unsigned()->nullable();
            $table->decimal('price_tc', 8, 2)->unsigned()->nullable();
            $table->timestamp('contract_dfrom1')->nullable();
            $table->timestamp('contract_dto1')->nullable();
            $table->timestamp('contract_dfrom2')->nullable();
            $table->timestamp('contract_dto2')->nullable();
            $table->timestamp('personal_dfrom1')->nullable();
            $table->timestamp('personal_dto1')->nullable();
            $table->timestamp('personal_dfrom2')->nullable();
            $table->timestamp('personal_dto2')->nullable();
            $table->text('comments')->nullable();
            $table->boolean('is_exception')->default(false);

            $table->integer('contract_id')->unsigned();
            $table->index('contract_id');
            $table->foreign('contract_id')->references('id')->on('contracts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('contract_years');
    }
}
