<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRentalContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rental_contracts', function (Blueprint $table) {
            $table->increments('id');
            $table->softDeletes();
            $table->nullableTimestamps();
            $table->boolean('mm_covered')->nullable();
            $table->timestamp('deadline_at')->nullable();
            $table->tinyInteger('min_duration')->default(1);
            $table->tinyInteger('max_duration')->default(1);
            $table->timestamp('contract_dfrom1')->nullable();
            $table->timestamp('contract_dto1')->nullable();
            $table->timestamp('contract_dfrom2')->nullable();
            $table->timestamp('contract_dto2')->nullable();
            $table->timestamp('personal_dfrom1')->nullable();
            $table->timestamp('personal_dto1')->nullable();
            $table->timestamp('personal_dfrom2')->nullable();
            $table->timestamp('personal_dto2')->nullable();

            $table->integer('rental_payment_id')->unsigned()->nullable();
            $table->index('rental_payment_id');
            $table->foreign('rental_payment_id')->references('id')->on('rental_payments')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('rental_contracts');
    }
}
