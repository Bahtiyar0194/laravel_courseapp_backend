<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestQuestionBlocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('test_question_blocks', function (Blueprint $table) {
            $table->increments('test_question_block_id');
            $table->integer('test_question_block_type_id')->unsigned();
            $table->foreign('test_question_block_type_id')->references('test_question_block_type_id')->on('types_of_test_question_blocks');
            $table->integer('question_id')->unsigned();
            $table->foreign('question_id')->references('question_id')->on('test_questions')->onDelete('cascade');
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
        Schema::dropIfExists('test_question_blocks');
    }
}
