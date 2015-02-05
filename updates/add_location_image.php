<?php namespace DMA\Friends\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddLocationImage extends Migration
{

    public function up()
    {
        Schema::table('dma_friends_locations', function($table)
        {
            $table->integer('image_id')->nullable();
        });
    }

    public function down()
    {
        Schema::table('dma_friends_locations', function($table)
        {
            $table->dropIfExists('image_id');
        });
    }
}