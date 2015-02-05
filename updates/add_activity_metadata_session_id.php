<?php namespace DMA\Friends\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddActivityMetadataSessionID extends Migration {

    /** 
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   
        Schema::table('dma_friends_activity_metadata', function($table)
        {   
            $table->string('session_id')->nullable();
        });

    }   


    /** 
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {   
        Schema::table('dma_friends_activity_metadata', function($table)
        {
            $table->dropColumn('session_id');
        });

    }   

}

