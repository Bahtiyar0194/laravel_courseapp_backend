<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTypesOfLessonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('types_of_lessons', function (Blueprint $table) {
            $table->increments('lesson_type_id');
            $table->string('lesson_type_slug');
            $table->integer('show_status_id')->default(1)->unsigned();
            $table->foreign('show_status_id')->references('show_status_id')->on('show_status');
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
        Schema::dropIfExists('type_of_lessons');
    }
}
