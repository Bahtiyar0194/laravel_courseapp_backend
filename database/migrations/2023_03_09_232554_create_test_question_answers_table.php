<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestQuestionAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('test_question_answers', function (Blueprint $table) {
            $table->increments('answer_id');
            $table->string('answer');
            $table->integer('question_id')->unsigned();
            $table->foreign('question_id')->references('question_id')->on('test_questions')->onDelete('cascade');
            $table->integer('is_correct');
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
        Schema::dropIfExists('test_question_answers');
    }
}
