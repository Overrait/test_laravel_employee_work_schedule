<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductionCalendarTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('production_calendars', function (Blueprint $table) {
            $table->id();
            $table->dateTime('day')->unique();
            $table->boolean('working');
            $table->boolean('holiday');
            $table->boolean('shortened');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('production_calendar');
    }
}
