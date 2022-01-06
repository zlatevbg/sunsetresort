<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePoolUsagePaymentDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pool_usage_payment_documents', function (Blueprint $table) {
            $table->increments('id');
            $table->nullableTimestamps();
            $table->enum('type', ['invoice'])->nullable();
            $table->string('file');
            $table->char('uuid', 36);
            $table->string('extension');
            $table->string('size');

            $table->integer('pool_usage_payment_id')->unsigned();
            $table->index('pool_usage_payment_id');
            $table->foreign('pool_usage_payment_id')->references('id')->on('pool_usage_payments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('pool_usage_payment_documents');
    }
}
