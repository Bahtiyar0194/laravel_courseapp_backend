<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestQuestionCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('test_question_codes', function (Blueprint $table) {
            $table->increments('test_question_code_id');
            $table->integer('test_question_block_id')->unsigned();
            $table->foreign('test_question_block_id')->references('test_question_block_id')->on('test_question_blocks')->onDelete('cascade');
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
        Schema::dropIfExists('test_question_codes');
    }
}
