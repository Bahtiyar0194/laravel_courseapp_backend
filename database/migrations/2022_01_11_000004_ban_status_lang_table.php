<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BanStatusLangTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ban_status_lang', function (Blueprint $table) {
          $table->increments('id');
          $table->string('ban_status_name');
          $table->integer('ban_status_id')->unsigned();
          $table->foreign('ban_status_id')->references('ban_status_id')->on('ban_status');
          $table->integer('lang_id')->unsigned();
          $table->foreign('lang_id')->references('lang_id')->on('languages');
          $table->timestamps();
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
