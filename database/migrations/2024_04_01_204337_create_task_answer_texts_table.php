<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskAnswerTextsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_answer_texts', function (Blueprint $table) {
            $table->increments('task_answer_text_id');
            $table->integer('task_answer_block_id')->unsigned();
            $table->foreign('task_answer_block_id')->references('task_answer_block_id')->on('task_answer_blocks')->onDelete('cascade');
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
        Schema::dropIfExists('task_answer_texts');
    }
}
