<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 70);
            $table->string('root_url', 70);
            $table->tinyInteger('current_mp');
        });
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('username', 32);
            $table->string('password', 96);
            $table->char('verizon_num', 10)->nullable();
            $table->unsignedBigInteger('password', 96)->nullable();
            $table->char('signup_code', 6)->nullable();
            $table->string('real_name', 70)->nullable();
            $table->unsignedInteger('school_id');
            $table->index('username', 'uname');
            $table->index('school_id', 'schoolid');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('restrict')->onUpdate('cascade');
        });
        Schema::create('courses', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedTinyInteger('user_index');
            $table->string('name', 127);
            $table->unsignedTinyInteger('mp');
            $table->decimal('points', 6, 2);
            $table->decimal('max_points', 6, 2);
            $table->decimal('percent', 4, 1);
            $table->index('user_id', 'uid');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::create('assignments', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->unsignedInteger('course_id');
            $table->unsignedTinyInteger('course_index');
            $table->char('due_date', 5);
            $table->string('name', 200);
            $table->string('course_type', 31);
            $table->decimal('points', 6, 2);
            $table->decimal('max_points', 6, 2);
            $table->index('course_id', 'cid');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('assignments');
        Schema::drop('courses');
        Schema::drop('users');
        Schema::drop('schools');
    }
}
