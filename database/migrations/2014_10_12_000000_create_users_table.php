<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('username');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('salt', 10);
            $table->string('google_id')->nullable();
            $table->string('image')->nullable();
            $table->integer('score')->default(0);
            $table->string('about', 500)->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
