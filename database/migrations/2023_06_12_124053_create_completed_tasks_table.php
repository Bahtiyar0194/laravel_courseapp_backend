<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompletedTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(){
        Schema::create('completed_tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('task_id')->unsigned();
            $table->foreign('task_id')->references('task_id')->on('lesson_tasks')->onDelete('cascade');
            $table->integer('executor_id')->unsigned();
            $table->foreign('executor_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->integer('inspector_id')->unsigned()->nullable();
            $table->foreign('inspector_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->integer('status_type_id')->default(8)->unsigned();
            $table->foreign('status_type_id')->references('status_type_id')->on('types_of_status');
            $table->text('answer')->nullable();
            $table->integer('grade')->nullable();
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
        Schema::dropIfExists('completed_tasks');
    }
}
