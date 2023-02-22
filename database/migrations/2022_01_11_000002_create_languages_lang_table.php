<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLanguagesLangTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('languages_lang', function (Blueprint $table) {
          $table->increments('id');
          $table->string('lang_name');
          $table->integer('lang_id')->unsigned();
          $table->foreign('lang_id')->references('lang_id')->on('languages');
          $table->string('lang_tag');
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
        Schema::dropIfExists('languages_lang');
    }
}
