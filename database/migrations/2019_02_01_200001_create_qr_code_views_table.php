<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQrCodeViewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('qr_code_views', function (Blueprint $table) {

            $table->increments('id');
            $table->unsignedInteger('qr_code_id');
            $table->unsignedInteger('user_id')->nullable();
            $table->ipAddress('ip');
            $table->timestamps();

            $table->foreign('qr_code_id')->references('id')->on('qr_codes')->onDelete('cascade');
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
        Schema::dropIfExists('qr_code_views');
    }
}
