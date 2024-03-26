<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskTextsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_texts', function (Blueprint $table) {
            $table->increments('task_text_id');
            $table->integer('task_block_id')->unsigned();
            $table->foreign('task_block_id')->references('task_block_id')->on('task_blocks')->onDelete('cascade');
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
        Schema::dropIfExists('task_texts');
    }
}
