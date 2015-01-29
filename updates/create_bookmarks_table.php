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
            $table->integer('user_id')->unsigned();
            $table->string('object_type')->nullable();
            $table->integer('object_id')->nullable();

            $table->index('object_id');
            $table->index('object_type');
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('dma_friends_bookmarks');
    }

}
