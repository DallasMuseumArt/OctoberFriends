<?php namespace DMA\Friends\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateRewardsTable extends Migration
{

    public function up()
    {
        Schema::create('dma_friends_rewards', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->timestamps();
            $table->integer('wordpress_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('excerpt')->nullable();
            $table->timestamps();
            $table->boolean('is_published')->default(false);
            $table->boolean('is_archived')->default(false);
            $table->integer('points')->default(0);
            $table->integer('image_id')->nullable();
            $table->string('barcode')->nullable();
            $table->timestamp('date_begin')->nullable();
            $table->timestamp('date_end')->nullable();
            $table->integer('days_valid')->nullable();
            $table->integer('inventory')->nullable();
            $table->boolean('hidden')->default(false);
            $table->text('fine_print')->nullable();
            $table->boolean('enable_email')->default(false);
            $table->text('redemption_email')->nullable();
        });

    }

    public function down()
    {
        Schema::dropIfExists('dma_friends_rewards');
    }

}
