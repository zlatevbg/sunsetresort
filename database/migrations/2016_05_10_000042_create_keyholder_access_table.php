<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKeyholderAccessTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('keyholder_access', function (Blueprint $table) {
            $table->increments('id');
            $table->softDeletes();
            $table->nullableTimestamps();

            $table->text('comments')->nullable();

            $table->integer('apartment_id')->unsigned();
            $table->index('apartment_id');
            $table->foreign('apartment_id')->references('id')->on('apartments')->onDelete('cascade');

            $table->integer('keyholder_id')->unsigned();
            $table->index('keyholder_id');
            $table->foreign('keyholder_id')->references('id')->on('keyholders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('keyholder_access');
    }
}
