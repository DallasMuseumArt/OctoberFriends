<?php namespace DMA\Friends\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateBadgesTable extends Migration
{

    public function up()
    {
        Schema::create('dma_friends_badges', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->timestamps();
            $table->boolean('touch');
            $table->integer('wordpress_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('excerpt')->nullable();
            $table->text('congratulations_text')->nullable();
            $table->boolean('is_published')->default(true);
            $table->boolean('is_archived')->default(false);
            $table->integer('maximum_earnings')->nullable();
            $table->integer('points')->default(0);
            $table->integer('image_id')->nullable();
            //$table->string('earned_by')->nullable();
            $table->boolean('is_sequential')->default(false);
            $table->boolean('show_earners')->default(true);
            $table->boolean('is_hidden')->default(false);
            $table->integer('time_between_steps_min')->default(0)->nullable();
            $table->integer('time_between_steps_max')->default(0)->nullable();
            $table->integer('maximium_time')->nullable();
            $table->timestamp('date_begin')->nullable();
            $table->timestamp('date_end')->nullable();
            $table->string('special');
        });
    }

    public function down()
    {
        Schema::dropIfExists('dma_friends_badges');
    }

}
