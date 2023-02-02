<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->increments('course_id');
            $table->string('course_name');
            $table->text('course_description');
            $table->string('course_poster_file')->default('default.svg');
            $table->integer('course_category_id')->unsigned();
            $table->foreign('course_category_id')->references('course_category_id')->on('course_categories');
            $table->integer('school_id')->unsigned();
            $table->foreign('school_id')->references('school_id')->on('schools');
            $table->integer('course_lang_id')->unsigned();
            $table->foreign('course_lang_id')->references('lang_id')->on('languages');
            $table->float('course_cost')->default(0);
            $table->integer('show_status_id')->default(1)->unsigned();
            $table->foreign('show_status_id')->references('show_status_id')->on('show_status');
            $table->integer('verification_status_id')->default(1)->unsigned();
            $table->foreign('verification_status_id')->references('verification_status_id')->on('verification_status');
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
        Schema::dropIfExists('courses');
    }
}
