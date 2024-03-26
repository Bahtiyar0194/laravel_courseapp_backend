<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskBlocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_blocks', function (Blueprint $table) {
            $table->increments('task_block_id');
            $table->integer('task_block_type_id')->unsigned();
            $table->foreign('task_block_type_id')->references('task_block_type_id')->on('types_of_task_blocks');
            $table->integer('task_id')->unsigned();
            $table->foreign('task_id')->references('task_id')->on('tasks')->onDelete('cascade');
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
        Schema::dropIfExists('task_blocks');
    }
}
