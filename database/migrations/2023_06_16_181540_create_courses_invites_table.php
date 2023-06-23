<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoursesInvitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('courses_invites', function (Blueprint $table) {
            $table->increments('id');
            $table->string('subscriber_email');
            $table->string('url_hash');
            $table->integer('course_id')->unsigned();
            $table->foreign('course_id')->references('course_id')->on('courses')->onDelete('cascade');
            $table->integer('operator_id')->unsigned();
            $table->foreign('operator_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->integer('mentor_id')->unsigned();
            $table->foreign('mentor_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->float('course_cost')->default(0);
            $table->integer('status_type_id')->default(4)->unsigned();
            $table->foreign('status_type_id')->references('status_type_id')->on('types_of_status');
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
        Schema::dropIfExists('courses_invites');
    }
}
