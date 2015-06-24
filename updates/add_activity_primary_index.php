<?php namespace DMA\Friends\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddActivityPrimaryIndex extends Migration {

    /** 
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   
        Schema::table('dma_friends_activity_user', function($table)
        {
            $table->increments('id');
            $table->index('id');
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
            $table->dropIndex('id');
            $table->dropColumn('id');
        });

    }   

}