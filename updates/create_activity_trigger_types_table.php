<?php namespace DMA\Friends\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateActivityTriggerTypesTable extends Migration
{

    public function up()
    {
        Schema::create('dma_friends_activity_trigger_types', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('slug');
            $table->timestamp('touch');
        });

        Schema::create('dma_friends_activity_activity_trigger_type', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('activity_id');
            $table->integer('activity_trigger_type_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('dma_friends_activity_trigger_types');
        Schema::dropIfExists('dma_friends_activity_activity_trigger_type');
    }

}
