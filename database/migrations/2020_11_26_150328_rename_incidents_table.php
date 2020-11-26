<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameIncidentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        // Schema::table('incidents', function (Blueprint $table) {
        //     $table->dropForeign(['category_id', 'user_id']);
        // });

        Schema::rename('incidents', 'markers');

        // Schema::table('markers', function (Blueprint $table) {
        //     $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        //     $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('incidents', function (Blueprint $table) {
            //
        });
    }
}
