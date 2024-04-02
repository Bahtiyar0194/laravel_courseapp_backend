<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskAnswerBlocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_answer_blocks', function (Blueprint $table) {
            $table->increments('task_answer_block_id');
            $table->integer('task_answer_block_type_id')->unsigned();
            $table->foreign('task_answer_block_type_id')->references('task_answer_block_type_id')->on('types_of_task_answer_blocks');
            $table->integer('completed_task_id')->unsigned();
            $table->foreign('completed_task_id')->references('id')->on('completed_tasks')->onDelete('cascade');
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
        Schema::dropIfExists('task_answer_blocks');
    }
}
