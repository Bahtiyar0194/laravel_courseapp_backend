<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskAnswerImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_answer_images', function (Blueprint $table) {
            $table->increments('task_answer_image_id');
            $table->integer('task_answer_block_id')->unsigned();
            $table->foreign('task_answer_block_id')->references('task_answer_block_id')->on('task_answer_blocks')->onDelete('cascade');
            $table->integer('file_id')->unsigned();
            $table->foreign('file_id')->references('file_id')->on('media_files')->onDelete('cascade');
            $table->string('image_width')->default('w-full');
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
        Schema::dropIfExists('task_answer_images');
    }
}
