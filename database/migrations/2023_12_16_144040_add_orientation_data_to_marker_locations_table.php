<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('marker_locations', function (Blueprint $table) {
            // Add heading, pitch, and roll columns. Each of them will be a representation in degrees of the orientation, or null.

            // Note that heading is different than course. Heading is the direction the device is pointing, whereas course is the direction the device is moving. We will compute course later in the model itself, based on the bearing between the current and previous location. That attribute will be called "course"
            $table->float('heading')->nullable();
            $table->float('pitch')->nullable();
            $table->float('roll')->nullable();

            // Add a column to store the indicated speed of the device in meters per second, or null. We will also later include in the model itself a computed attribute that will compute the actual speed based on the distance between the current and previous location, and the time between the two. That attribute will be called "groundspeed"
            $table->float('speed')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('marker_locations', function (Blueprint $table) {
            //
        });
    }
};
