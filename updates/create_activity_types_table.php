<?php namespace DMA\Friends\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateActivityTypesTable extends Migration
{

    public function up()
    {
        Schema::create('dma_friends_activity_types', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name');
            $table->text('description');
            $table->string('slug');
        });

        Schema::create('dma_friends_activity_activity_types', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('activity_id');
            $table->integer('activity_type_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('dma_friends_activity_types');
        Schema::dropIfExists('dma_friends_activity_activity_types');
    }

}
