<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContractDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contract_documents', function (Blueprint $table) {
            $table->increments('id');
            $table->nullableTimestamps();
            $table->enum('type', ['contract', 'annex'])->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->string('file');
            $table->char('uuid', 36);
            $table->string('extension');
            $table->string('size');

            $table->integer('contract_year_id')->unsigned();
            $table->index('contract_year_id');
            $table->foreign('contract_year_id')->references('id')->on('contract_years')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('contract_documents');
    }
}
