<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMaintenanceIssuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('maintenance_issues', function (Blueprint $table) {
            $table->increments('id');
            $table->softDeletes();
            $table->nullableTimestamps();
            $table->string('title');
            $table->text('comments')->nullable();
            $table->enum('status', ['open', 'pending', 'completed'])->default('open');
            $table->enum('responsibility', ['owner', 'condominium', 'rental-company'])->nullable();
            $table->boolean('is_visible')->default(false);

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
        Schema::drop('maintenance_issues');
    }
}
