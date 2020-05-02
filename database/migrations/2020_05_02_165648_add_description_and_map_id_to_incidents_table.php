<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDescriptionAndMapIdToIncidentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->string('description')->nullable();
            $table->string('map_id')->nullable();
            $table->string('token');

            $table->timestamp('expires_at')->nullable();

            $table->foreign('map_id')->references('id')->on('maps')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->dropColumn('description');
            $table->dropColumn('map_id');
            $table->dropColumn('expires_at');

        });
    }
}
