<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOwnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('owners', function (Blueprint $table) {
            $table->increments('id');
            $table->softDeletes();
            $table->nullableTimestamps();
            $table->rememberToken();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_subscribed')->default(true);
            $table->boolean('apply_wt')->default(true);
            $table->boolean('outstanding_bills')->default(false);
            $table->boolean('letting_offer')->default(true);
            $table->boolean('srioc')->default(false);
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('email_cc')->nullable();
            $table->string('password')->nullable();
            $table->char('temp_password', 8)->nullable();
            $table->enum('sex', ['not-known', 'male', 'female', 'not-applicable'])->nullable(); // ISO/IEC 5218
            $table->string('city')->nullable();
            $table->string('postcode')->nullable();
            $table->string('address1')->nullable();
            $table->string('address2')->nullable();
            $table->text('comments')->nullable();
            $table->string('bulstat');
            $table->string('tax_pin');

            $table->integer('locale_id')->unsigned()->nullable();
            $table->index('locale_id');
            $table->foreign('locale_id')->references('id')->on('locales')->onDelete('set null');

            $table->integer('country_id')->unsigned()->nullable();
            $table->index('country_id');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('owners');
    }
}
