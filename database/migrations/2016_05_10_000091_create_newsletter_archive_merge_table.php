<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewsletterArchiveMergeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('newsletter_archive_merge', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('newsletter_archive_id')->unsigned()->index();
            $table->string('key');
            $table->text('value')->nullable();

            $table->foreign('newsletter_archive_id')->references('id')->on('newsletter_archive')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('newsletter_archive_merge');
    }
}
