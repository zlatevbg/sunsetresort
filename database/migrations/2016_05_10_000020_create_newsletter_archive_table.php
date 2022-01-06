<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewsletterArchiveTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('newsletter_archive', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('is_read')->default(false);
            $table->integer('newsletter_id')->unsigned()->index();
            $table->integer('apartment_id')->unsigned()->index()->nullable();
            $table->integer('owner_id')->unsigned()->index();

            $table->foreign('newsletter_id')->references('id')->on('newsletters')->onDelete('cascade');
            $table->foreign('apartment_id')->references('id')->on('apartments')->onDelete('cascade');
            $table->foreign('owner_id')->references('id')->on('owners')->onDelete('cascade');

            $table->unique(['newsletter_id', 'apartment_id', 'owner_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('newsletter_archive');
    }
}
