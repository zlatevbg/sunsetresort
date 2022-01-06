<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePoolUsageContractsTrackerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pool_usage_contracts_tracker', function (Blueprint $table) {
            $table->increments('id');
            $table->nullableTimestamps();
            $table->timestamp('sent_at')->nullable();
            $table->boolean('is_active')->default(false);
            $table->text('comments')->nullable();

            $table->integer('apartment_id')->unsigned();
            $table->index('apartment_id');
            $table->foreign('apartment_id')->references('id')->on('apartments')->onDelete('cascade');

            $table->integer('owner_id')->unsigned();
            $table->index('owner_id');
            $table->foreign('owner_id')->references('id')->on('owners')->onDelete('cascade');

            $table->integer('year_id')->unsigned();
            $table->index('year_id');
            $table->foreign('year_id')->references('id')->on('years')->onDelete('cascade');

            $table->unique(['year_id', 'apartment_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('pool_usage_contracts_tracker');
    }
}
