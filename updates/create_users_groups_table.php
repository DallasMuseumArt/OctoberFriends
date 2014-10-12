<?php namespace DMA\Friends\Updates;

use Schema;
use DMA\Friends\Models\UserGroup;
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
            $table->enum('membership_status',  [
            		UserGroup::MEMBERSHIP_PENDING,
            		UserGroup::MEMBERSHIP_ACCEPTED,
            		UserGroup::MEMBERSHIP_REJECTED,
            		UserGroup::MEMBERSHIP_CANCELLED
            ])->default(UserGroup::MEMBERSHIP_PENDING);
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('group_id');     

            $table->unique( array('user_id', 'group_id') );
        });
    }

    public function down()
    {
        Schema::dropIfExists('dma_friends_users_groups');
    }

}
