<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMarkerLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('marker_locations', function (Blueprint $table) {
            $table->id();

            $table->integer('marker_id')->unsigned();
            $table->point('location');
            $table->string('address', 510)->nullable();
            $table->integer('user_id')->unsigned()->nullable();
            $table->integer('elevation')->nullable();
            $table->json('geocode')->nullable();
            $table->timestamps();

            $table->foreign('marker_id')->references('id')->on('markers')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users');
            $table->spatialIndex('location');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('marker_locations');
    }
}
