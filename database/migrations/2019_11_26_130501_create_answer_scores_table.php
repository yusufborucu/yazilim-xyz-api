<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnswerScoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('answer_scores', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('answer_id');
            $table->foreign('answer_id')->references('id')->on('answers');
            $table->integer('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->boolean('status');
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
        Schema::dropIfExists('answer_scores');
    }
}
