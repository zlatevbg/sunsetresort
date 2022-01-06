<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRentalContractsTrackerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rental_contracts_tracker', function (Blueprint $table) {
            $table->increments('id');
            $table->nullableTimestamps();
            $table->timestamp('sent_at')->nullable();
            $table->decimal('price', 8, 2)->unsigned()->nullable();
            $table->decimal('price_tc', 8, 2)->unsigned()->nullable()->default(0);
            $table->char('mm_for_year', 4)->nullable();
            $table->string('mm_for_years')->nullable();
            $table->tinyInteger('min_duration')->nullable();
            $table->tinyInteger('max_duration')->nullable();
            $table->tinyInteger('duration')->nullable();
            $table->boolean('is_exception')->default(false);
            $table->boolean('is_active')->default(false);
            $table->timestamp('contract_dfrom1')->nullable();
            $table->timestamp('contract_dto1')->nullable();
            $table->timestamp('contract_dfrom2')->nullable();
            $table->timestamp('contract_dto2')->nullable();
            $table->timestamp('personal_dfrom1')->nullable();
            $table->timestamp('personal_dto1')->nullable();
            $table->timestamp('personal_dfrom2')->nullable();
            $table->timestamp('personal_dto2')->nullable();
            $table->text('comments')->nullable();

            $table->integer('apartment_id')->unsigned();
            $table->index('apartment_id');
            $table->foreign('apartment_id')->references('id')->on('apartments')->onDelete('cascade');

            $table->integer('owner_id')->unsigned();
            $table->index('owner_id');
            $table->foreign('owner_id')->references('id')->on('owners')->onDelete('cascade');

            $table->integer('rental_contract_id')->unsigned();
            $table->index('rental_contract_id');
            $table->foreign('rental_contract_id')->references('id')->on('rental_contracts')->onDelete('cascade');

            $table->integer('poa_id')->unsigned()->nullable();
            $table->index('poa_id');
            $table->foreign('poa_id')->references('id')->on('poa')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('rental_contracts_tracker');
    }
}
