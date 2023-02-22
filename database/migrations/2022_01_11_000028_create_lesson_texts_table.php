<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLessonTextsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lesson_texts', function (Blueprint $table) {
            $table->increments('lesson_text_id');
            $table->integer('lesson_block_id')->unsigned();
            $table->foreign('lesson_block_id')->references('lesson_block_id')->on('lesson_blocks')->onDelete('cascade');
            $table->text('content')->nullable();
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
        Schema::dropIfExists('lesson_texts');
    }
}
