<?php namespace DMA\Friends\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateUserBadgesTable extends Migration
{

    public function up()
    {   
        Schema::create('dma_friends_user_badges', function($table)
        {   
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->timestamps();
            $table->integer('user_id');
            $table->integer('badge_id');
            $table->integer('location_id');

            $table->index('user_id');
            $table->index('badge_id');
            $table->index('location_id');
        }); 
    }   

    public function down()
    {   
        Schema::dropIfExists('dma_friends_user_badges');
    }   

}
