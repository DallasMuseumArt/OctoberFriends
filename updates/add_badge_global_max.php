<?php namespace DMA\Friends\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddBadgeGlobalMax extends Migration {

    /** 
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   
        Schema::table('dma_friends_badges', function($table)
        {   
            $table->integer('maximum_earnings_global')->nullable();
        });

    }   

    /** 
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {   
        Schema::table('dma_friends_badges', function($table)
        {
            $table->dropColumn('maximum_earnings_global');
        });

    }   

}

