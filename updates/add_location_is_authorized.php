<?php namespace DMA\Friends\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddLocationIsAuthorized extends Migration {

    /** 
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   
        Schema::table('dma_friends_locations', function($table)
        {   
            $table->boolean('is_authorized')->nullable();
        });
    }   


    /** 
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {   
        Schema::table('dma_friends_locations', function($table)
        {
            $table->dropColumn('is_authorized');
        });
    }   

}

