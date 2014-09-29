<?php namespace DMA\Friends\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateUsersGroupsTable extends Migration
{

    public function up()
    {
        Schema::create('dma_friends_users_groups', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('group_id');
            $table->integer('confirmed')->default(false);
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('group_id');            
        });
    }

    public function down()
    {
        Schema::dropIfExists('dma_friends_users_groups');
    }

}
