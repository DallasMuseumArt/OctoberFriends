<?php namespace DMA\Friends\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddActivityUserIndex extends Migration {

    /** 
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   
        Schema::table('dma_friends_activity_user', function($table)
        {
            $table->index('activity_id');
            $table->index('user_id');
        });

    }   


    /** 
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {   
        Schema::table('dma_friends_activity_user', function($table)
        {
            $table->dropIndex('activity_id');
            $table->dropIndex('user_id');
        });

    }   

}