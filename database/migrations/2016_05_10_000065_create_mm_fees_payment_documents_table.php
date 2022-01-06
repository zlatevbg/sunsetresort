<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMmFeesPaymentDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mm_fees_payment_documents', function (Blueprint $table) {
            $table->increments('id');
            $table->nullableTimestamps();
            $table->enum('type', ['invoice'])->nullable();
            $table->string('file');
            $table->char('uuid', 36);
            $table->string('extension');
            $table->string('size');

            $table->integer('mm_fees_payment_id')->unsigned();
            $table->index('mm_fees_payment_id');
            $table->foreign('mm_fees_payment_id')->references('id')->on('mm_fees_payments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('mm_fees_payment_documents');
    }
}
