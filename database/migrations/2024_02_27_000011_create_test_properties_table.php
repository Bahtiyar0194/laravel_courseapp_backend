<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestPropertiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('test_properties', function (Blueprint $table) {
            $table->increments('property_id');
            $table->string('property_name');
            $table->string('property_description')->nullable();
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
        Schema::dropIfExists('test_properties');
    }
}
