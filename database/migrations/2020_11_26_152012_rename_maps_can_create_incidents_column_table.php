<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameMapsCanCreateIncidentsColumnTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('maps', function (Blueprint $table) {

            // Apparently its not possible to rename enum types, so just do it manually in MySQL;

            // $table->renameColumn('users_can_create_incidents', 'users_can_create_markers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('maps', function (Blueprint $table) {
            // $table->renameColumn('users_can_create_markers', 'users_can_create_incidents');
        });
    }
}
