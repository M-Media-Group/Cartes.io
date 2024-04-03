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
        Schema::create('map_users', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->unsigned();
            $table->integer('map_id')->unsigned();
            $table->integer('added_by_user_id')->unsigned();
            $table->boolean('can_create_markers')->default(false);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('map_id')->references('id')->on('maps');
            $table->foreign('added_by_user_id')->references('id')->on('users');

            // We need to make sure that the user_id and map_id are unique together
            $table->unique(['user_id', 'map_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('map_users');
    }
};
