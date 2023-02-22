<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLessonTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lesson_tasks', function (Blueprint $table) {
            $table->increments('task_id');
            $table->string('task_name');
            $table->text('task_description')->nullable();
            $table->integer('lesson_id')->unsigned();
            $table->foreign('lesson_id')->references('lesson_id')->on('lessons')->onDelete('cascade');
            $table->integer('task_type_id')->unsigned();
            $table->foreign('task_type_id')->references('task_type_id')->on('types_of_tasks');
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
        Schema::dropIfExists('lesson_tasks');
    }
}
