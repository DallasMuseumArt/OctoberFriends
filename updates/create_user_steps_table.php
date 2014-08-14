<?php namespace DMA\Friends\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateUserStepsTable extends Migration
{

    public function up()
    {   
        Schema::create('dma_friends_user_steps', function($table)
        {   
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->timestamps();
            $table->integer('user_id');
            $table->integer('step_id');
            $table->integer('location_id');

            $table->index('user_id');
            $table->index('step_id');
            $table->index('location_id');
        }); 
    }   

    public function down()
    {   
        Schema::dropIfExists('dma_friends_step_badges');
    }   

}
