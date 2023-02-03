<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLessonVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lesson_videos', function (Blueprint $table) {
            $table->increments('lesson_video_id');
            $table->integer('lesson_id')->unsigned();
            $table->foreign('lesson_id')->references('lesson_id')->on('lessons');
            $table->integer('lesson_video_type_id')->unsigned();
            $table->foreign('lesson_video_type_id')->references('lesson_video_type_id')->on('types_of_lesson_videos');
            $table->string('content');
            $table->float('size')->nullable();
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
        Schema::dropIfExists('lesson_videos');
    }
}
