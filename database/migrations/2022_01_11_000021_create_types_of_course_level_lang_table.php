<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTypesOfCourseLevelLangTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('types_of_course_level_lang', function (Blueprint $table) {
          $table->increments('id');
          $table->string('level_type_name');
          $table->integer('level_type_id')->unsigned();
          $table->foreign('level_type_id')->references('level_type_id')->on('types_of_course_level');
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
        Schema::dropIfExists('types_of_course_level_lang');
    }
}
