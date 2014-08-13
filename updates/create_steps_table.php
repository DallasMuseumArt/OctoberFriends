<?php namespace DMA\Friends\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateStepsTable extends Migration
{

    public function up()
    {
        Schema::create('dma_friends_steps', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->timestamps();
            $table->string('title');
            $table->integer('wordpress_id');
            $table->string('achievement_type');
            $table->integer('count');
            $table->string('trigger_type');
            $table->string('unlock_options');
            $table->string('trigger_unlock_badge');
        });
    }

    public function down()
    {
        Schema::dropIfExists('dma_friends_steps');
    }

}
