<?php namespace DMA\Friends\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateBookmarksTable extends Migration
{

    public function up()
    {
        Schema::create('dma_friends_bookmarks', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->timestamp('created_at');
            $table->integer('badge_id')
                ->references('id')
                ->on('dma_friends_badges')
                ->onDelete('cascade');
            $table->integer('user_id')->unsigned();

            $table->index('badge_id');
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('dma_friends_bookmarks');
    }

}
