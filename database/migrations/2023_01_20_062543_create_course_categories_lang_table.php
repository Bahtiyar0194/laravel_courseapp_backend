<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseCategoriesLangTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('course_categories_lang', function (Blueprint $table) {
           $table->increments('id');
           $table->string('course_category_name');
           $table->integer('course_category_id')->unsigned();
           $table->foreign('course_category_id')->references('course_category_id')->on('course_categories');
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
        Schema::dropIfExists('course_categories_lang');
    }
}
