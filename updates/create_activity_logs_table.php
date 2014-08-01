<?php namespace DMA\Friends\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateActivityLogsTable extends Migration
{

    public function up()
    {
        Schema::create('dma_friends_activity_logs', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('user_id');
            $table->string('site_id')->nullable();
            $table->string('action');
            $table->longText('message');
            $table->string('object_type');
            $table->integer('object_id')->nullable();
            $table->string('artwork_id')->nullable();
            $table->string('wordpress_object_id')->default('')->nullable();
            $table->integer('points_earned')->default(0)->nullable();
            $table->integer('total_points')->default(0)->nullable();
            $table->timestamp('timestamp');
            $table->string('timezone');
        });
    }

    public function down()
    {
        Schema::dropIfExists('dma_friends_activity_logs');
    }

}
