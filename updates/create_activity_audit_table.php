<?php namespace DMA\Friends\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateActivityAuditTable extends Migration
{

    public function up()
    {
        Schema::create('dma_friends_activity_audit', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('activity_id');
            $table->string('key');
            $table->longText('value');

            $table->index('user_id');
            $table->index('activity_id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('dma_friends_activity_audit');
    }

}
