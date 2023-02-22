<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTypesOfLessonBlocksLangTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('types_of_lesson_blocks_lang', function (Blueprint $table) {
          $table->increments('id');
          $table->string('lesson_block_name');
          $table->integer('lesson_block_type_id')->unsigned();
          $table->foreign('lesson_block_type_id')->references('lesson_block_type_id')->on('types_of_lesson_blocks');
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
        Schema::dropIfExists('types_of_lesson_blocks_lang');
    }
}
