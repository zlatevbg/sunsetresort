<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCondominiumDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('condominium_documents', function (Blueprint $table) {
            $table->increments('id');
            $table->nullableTimestamps();
            $table->enum('type', ['minutes'])->nullable();
            $table->string('file');
            $table->char('uuid', 36);
            $table->string('extension');
            $table->string('size');

            $table->integer('condominium_id')->unsigned();
            $table->index('condominium_id');
            $table->foreign('condominium_id')->references('id')->on('condominium')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('condominium_documents');
    }
}
