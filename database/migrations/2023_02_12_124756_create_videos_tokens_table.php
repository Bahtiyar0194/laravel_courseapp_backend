<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVideosTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('videos_tokens', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('lesson_video_id')->unsigned();
            $table->foreign('lesson_video_id')->references('lesson_video_id')->on('lesson_videos');
            $table->string('token');
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
        Schema::dropIfExists('videos_tokens');
    }
}
