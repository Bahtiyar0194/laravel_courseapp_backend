<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTypesOfCourseSubscribesLangTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('types_of_course_subscribes_lang', function (Blueprint $table) {
          $table->increments('id');
          $table->string('subscribe_type_name');
          $table->integer('subscribe_type_id')->unsigned();
          $table->foreign('subscribe_type_id')->references('subscribe_type_id')->on('types_of_course_subscribes');
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
        Schema::dropIfExists('types_of_course_subscribes_lang');
    }
}
