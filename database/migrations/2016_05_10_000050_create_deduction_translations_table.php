<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeductionTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deduction_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');

            $table->string('locale')->index();
            $table->foreign('locale')->references('locale')->on('locales')->onDelete('cascade');

            $table->integer('deduction_id')->unsigned()->index();
            $table->foreign('deduction_id')->references('id')->on('deductions')->onDelete('cascade');

            $table->unique(['deduction_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('deduction_translations');
    }
}
