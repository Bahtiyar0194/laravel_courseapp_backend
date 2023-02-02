<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSchoolsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->increments('school_id');
            $table->string('school_domain');
            $table->string('school_name');
            $table->integer('school_type_id')->unsigned();
            $table->foreign('school_type_id')->references('school_type_id')->on('types_of_schools');
            $table->integer('owner_id')->unsigned();
            $table->foreign('owner_id')->references('user_id')->on('users');
            $table->integer('ban_status_id')->default(1)->unsigned();
            $table->foreign('ban_status_id')->references('ban_status_id')->on('ban_status');
            $table->string('email')->unique();
            $table->string('phone')->unique();
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
        Schema::dropIfExists('schools');
    }
}