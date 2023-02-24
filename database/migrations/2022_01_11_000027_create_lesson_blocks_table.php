<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLessonBlocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lesson_blocks', function (Blueprint $table) {
            $table->increments('lesson_block_id');
            $table->integer('lesson_block_type_id')->unsigned();
            $table->foreign('lesson_block_type_id')->references('lesson_block_type_id')->on('types_of_lesson_blocks');
            $table->integer('lesson_id')->unsigned();
            $table->foreign('lesson_id')->references('lesson_id')->on('lessons')->onDelete('cascade');
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
        Schema::dropIfExists('lesson_blocks');
    }
}
