<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLessonCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lesson_codes', function (Blueprint $table) {
            $table->increments('lesson_code_id');
            $table->integer('lesson_block_id')->unsigned();
            $table->foreign('lesson_block_id')->references('lesson_block_id')->on('lesson_blocks')->onDelete('cascade');
            $table->text('code');
            $table->string('code_language');
            $table->string('code_theme');
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
        Schema::dropIfExists('lesson_codes');
    }
}
