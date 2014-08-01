<?php namespace DMA\Friends\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateActivitiesTable extends Migration
{

    public function up()
    {
        Schema::create('dma_friends_activities', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->timestamps();
            $table->integer('wordpress_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('excerpt')->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('is_archived')->default(false);
            $table->integer('points')->default(0);
            $table->integer('image_id')->nullable();
            $table->string('activity_code')->nullable();
            $table->integer('activity_lockout')->nullable();
            $table->timestamp('date_begin')->nullable();
            $table->timestamp('date_end')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('dma_friends_activities');
    }

}
