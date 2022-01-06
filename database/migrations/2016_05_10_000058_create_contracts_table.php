<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->increments('id');
            $table->softDeletes();
            $table->nullableTimestamps();
            $table->tinyInteger('duration')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->text('comments')->nullable();
            $table->boolean('is_exception')->default(false);
            $table->boolean('is_cancelled')->default(false);

            $table->integer('apartment_id')->unsigned();
            $table->index('apartment_id');
            $table->foreign('apartment_id')->references('id')->on('apartments')->onDelete('cascade');

            $table->integer('rental_contract_id')->unsigned();
            $table->index('rental_contract_id');
            $table->foreign('rental_contract_id')->references('id')->on('rental_contracts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('contracts');
    }
}
