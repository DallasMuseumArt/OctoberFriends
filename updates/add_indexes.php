<?php namespace DMA\Friends\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddIndexesTable extends Migration
{

    public function up()
    {   
        Schema::table('dma_friends_activity_logs', function($table)
        {   
            $table->index('user_id');
        }); 

        Schema::table('dma_friends_usermetas', function($table)
        {   
            $table->index('user_id');
        }); 
    }   

    public function down()
    {   
        Schema::table('dma_friends_usermetas', function($table)
        {
            $table->dropIndex('user_id');
        });

        Schema::table('dma_friends_activity_logs', function($table)
        {
            $table->dropIndex('user_id');
        });
    }   

}
