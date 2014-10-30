<?php namespace DMA\Friends\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class ExtendActivityTable extends Migration
{

    public function up()
    {   
        Schema::table('dma_friends_activities', function($table)
        {   
            $table->string('activity_type');

        }); 

    }   

    public function down()
    {   
        Schema::table('dma_friends_activities', function($table)
        {
            $table->dropColumn('activity_type');
        });
    }   

}