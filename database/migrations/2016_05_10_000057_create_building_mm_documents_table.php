<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBuildingMmDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('building_mm_documents', function (Blueprint $table) {
            $table->increments('id');
            $table->nullableTimestamps();
            $table->enum('type', ['rules', 'insurance', 'receipts', 'accounts', 'ier', 'budget', 'electricity', 'water', 'eur-account', 'bgn-account', 'communal-fee-en', 'communal-fee-ru', 'court-decision', 'audit-report-condominium', 'audit-conclusion-condominium', 'audit-report-management', 'audit-conclusion-management'])->nullable();
            $table->string('file');
            $table->char('uuid', 36);
            $table->string('extension');
            $table->string('size');

            $table->integer('building_mm_id')->unsigned();
            $table->index('building_mm_id');
            $table->foreign('building_mm_id')->references('id')->on('building_mm')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('building_mm_documents');
    }
}
