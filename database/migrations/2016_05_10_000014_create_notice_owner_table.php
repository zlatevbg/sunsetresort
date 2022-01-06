<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNoticeOwnerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notice_owner', function (Blueprint $table) {
            $table->nullableTimestamps();
            $table->integer('notice_id')->unsigned();
            $table->integer('owner_id')->unsigned();
            $table->boolean('is_read')->default(false);

            $table->foreign('notice_id')->references('id')->on('notices')->onDelete('cascade');
            $table->foreign('owner_id')->references('id')->on('owners')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('notice_owner');
    }
}
