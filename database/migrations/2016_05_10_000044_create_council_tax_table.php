<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCouncilTaxTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('council_tax', function (Blueprint $table) {
            $table->increments('id');
            $table->nullableTimestamps();
            $table->decimal('tax', 6, 2)->unsigned()->nullable();
            $table->timestamp('checked_at')->nullable();

            $table->integer('owner_id')->unsigned();
            $table->index('owner_id');
            $table->foreign('owner_id')->references('id')->on('owners')->onDelete('cascade');

            $table->integer('apartment_id')->unsigned();
            $table->index('apartment_id');
            $table->foreign('apartment_id')->references('id')->on('apartments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('council_tax');
    }
}
