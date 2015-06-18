<?php namespace DMA\Friends\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddActivityPointsToComplete extends Migration {

    /** 
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   
        Schema::table('dma_friends_activities', function($table)
        {   
            $table->integer('points_to_complete')->nullable();
        });

    }   


    /** 
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {   
        Schema::table('dma_friends_activities', function($table)
        {
            $table->dropColumn('points_to_complete');
        });

    }   

}

