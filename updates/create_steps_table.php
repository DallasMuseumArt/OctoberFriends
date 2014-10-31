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
            $table->boolean('touch');
            $table->string('title');
            $table->integer('wordpress_id');
            $table->integer('badge_id')
                ->references('id')
                ->on('dma_friends_badges')
                ->onDelete('cascade');
            $table->integer('activity_id')
                ->references('id')
                ->on('dma_friends_activities')
                ->onDelete('cascade');
            $table->integer('count');
            //$table->string('achievement_type');
            //$table->string('trigger_type');
            //$table->string('unlock_options');
            //$table->string('trigger_unlock_badge');

            $table->index('badge_id');
            $table->index('activity_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('dma_friends_steps');
    }

}
