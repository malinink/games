<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserIngameInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('userIngameInfo', function (Blueprint $table) {
            $table->increments('type_id')->unsigned;
            $table->increments('user_id')->unsigned;
            $table->foreign('user_id')->references('id')->on('user')->onDelete('cascade');
            $table->foreign('type_id')->references('id')->on('gamesType')->onDelete('cascade');
            $table->integer('game_rating');
            $table->integer('games');
            $table->integer('wins');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('userIngameInfo');
    }
    
}

