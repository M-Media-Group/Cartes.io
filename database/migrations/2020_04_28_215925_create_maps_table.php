<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMapsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('maps', function (Blueprint $table) {
            $table->increments('id');

            $table->string('slug')->unique();
            $table->string('title')->nullable();
            $table->string('description')->nullable();
            $table->unsignedInteger('user_id')->nullable();

            $table->enum('privacy', ['public', 'unlisted', 'private'])->default('unlisted');

            $table->enum('users_can_create_incidents', ['yes', 'only_logged_in', 'no'])->default('only_logged_in');

            // Maybe replace this with a settings package
            // $table->boolean('users_can_engage_with_incidents')->default(0);

            $table->string('uuid')->unique();

            $table->string('token');

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('maps');
    }
}
