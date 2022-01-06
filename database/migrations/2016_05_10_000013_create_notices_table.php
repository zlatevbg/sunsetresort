<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNoticesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notices', function (Blueprint $table) {
            $table->increments('id');
            $table->nullableTimestamps();
            $table->string('name');
            $table->mediumText('content')->nullable();
            $table->boolean('auto_assign')->default(false);
            $table->boolean('is_active')->default(true);

            $table->integer('locale_id')->unsigned();
            $table->index('locale_id');
            $table->foreign('locale_id')->references('id')->on('locales')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('notices');
    }
}
