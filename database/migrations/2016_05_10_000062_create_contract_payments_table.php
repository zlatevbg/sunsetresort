<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContractPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contract_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->nullableTimestamps();
            $table->decimal('amount', 7, 2)->unsigned();
            $table->timestamp('paid_at')->nullable();
            $table->text('comments')->nullable();

            $table->integer('contract_year_id')->unsigned();
            $table->index('contract_year_id');
            $table->foreign('contract_year_id')->references('id')->on('contract_years')->onDelete('cascade');

            $table->integer('payment_method_id')->unsigned();
            $table->index('payment_method_id');
            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->onDelete('cascade');

            $table->integer('rental_company_id')->unsigned();
            $table->index('rental_company_id');
            $table->foreign('rental_company_id')->references('id')->on('rental_companies')->onDelete('cascade');

            $table->integer('owner_id')->unsigned()->nullable();
            $table->index('owner_id');
            $table->foreign('owner_id')->references('id')->on('owners')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('contract_payments');
    }
}
