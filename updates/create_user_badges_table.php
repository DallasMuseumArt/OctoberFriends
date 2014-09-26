<?php namespace DMA\Friends\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateUserBadgesTable extends Migration
{

    public function up()
    {   

        Schema::create('dma_friends_badge_user', function($table)
        {   
            $table->increments('id');
            $table->integer('badge_id')->unsigned()->index();
            //$table->foreign('badge_id')->references('id')->on('dma_friends_badges')->onDelete('cascade');
            $table->integer('user_id')->unsigned()->index();
            //$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->integer('location_id')->unsigned()->index();
            $table->timestamps();
        }); 

    }   

    public function down()
    {   
        Schema::dropIfExists('dma_friends_badge_user');
    }   

}
