<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewslettersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('newsletters', function (Blueprint $table) {
            $table->increments('id');
            $table->nullableTimestamps();
            $table->timestamp('sent_at')->nullable();
            $table->string('subject');
            $table->string('teaser');
            $table->mediumText('body')->nullable();
            $table->string('projects')->nullable();
            $table->string('buildings')->nullable();
            $table->string('floors')->nullable();
            $table->string('rooms')->nullable();
            $table->string('furniture')->nullable();
            $table->string('views')->nullable();
            $table->text('apartments')->nullable();
            $table->text('owners')->nullable();
            $table->text('countries')->nullable();
            $table->tinyInteger('recipients')->default(1);
            $table->tinyInteger('merge_by')->nullable();
            $table->string('template')->nullable();

            $table->integer('signature_id')->unsigned()->nullable();
            $table->index('signature_id');
            $table->foreign('signature_id')->references('id')->on('signatures')->onDelete('set null');

            $table->integer('locale_id')->unsigned();
            $table->index('locale_id');
            $table->foreign('locale_id')->references('id')->on('locales')->onDelete('set null');

            $table->integer('year_id')->unsigned()->nullable();
            $table->index('year_id');
            $table->foreign('year_id')->references('id')->on('years')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('newsletters');
    }
}
