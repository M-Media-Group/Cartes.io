<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropLocationFromMarkersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('markers', function (Blueprint $table) {
            /**
             * @note that the index may be called incidents_location_spatial rather than incidents_location_spatialindex locally
             */
            $table->dropIndex('incidents_location_spatialindex');
            $table->dropUnique('markers_location_map_id_category_id_created_at_expires_at_unique');
            $table->dropColumn('location');
            $table->dropColumn('elevation');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('markers', function (Blueprint $table) {
            //
        });
    }
}
