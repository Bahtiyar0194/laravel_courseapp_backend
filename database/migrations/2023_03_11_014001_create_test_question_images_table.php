<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestQuestionImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('test_question_images', function (Blueprint $table) {
            $table->increments('test_question_image_id');
            $table->integer('test_question_block_id')->unsigned();
            $table->foreign('test_question_block_id')->references('test_question_block_id')->on('test_question_blocks')->onDelete('cascade');
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
        Schema::dropIfExists('test_question_images');
    }
}
