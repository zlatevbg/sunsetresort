<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContractDeductionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contract_deductions', function (Blueprint $table) {
            $table->increments('id');
            $table->nullableTimestamps();
            $table->decimal('amount', 7, 2)->unsigned();
            $table->timestamp('signed_at')->nullable();
            $table->text('comments')->nullable();

            $table->integer('contract_year_id')->unsigned();
            $table->index('contract_year_id');
            $table->foreign('contract_year_id')->references('id')->on('contract_years')->onDelete('cascade');

            $table->integer('deduction_id')->unsigned();
            $table->index('deduction_id');
            $table->foreign('deduction_id')->references('id')->on('deductions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('contract_deductions');
    }
}
