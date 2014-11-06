<?php namespace DMA\Friends\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateNotificationTable extends Migration
{

    public function up()
    {
        Schema::create('dma_friends_notification', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('from_user_id')->unsigned()->nullable();
            
            $table->string('subject', 128)->nullable();
            $table->text('message')->nullable();
 
            $table->integer('object_id')->unsigned();
            $table->string('object_type', 128);
 
            $table->boolean('is_read')->default(0);
            $table->dateTime('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('dma_friends_notification');
    }

}
