<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskAudiosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_audios', function (Blueprint $table) {
            $table->increments('task_audio_id');
            $table->integer('task_block_id')->unsigned();
            $table->foreign('task_block_id')->references('task_block_id')->on('task_blocks')->onDelete('cascade');
            $table->integer('file_id')->unsigned();
            $table->foreign('file_id')->references('file_id')->on('media_files')->onDelete('cascade');
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
        Schema::dropIfExists('task_audios');
    }
}
