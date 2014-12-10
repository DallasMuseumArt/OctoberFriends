<?php namespace DMA\Friends\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddPriorityTable extends Migration {

    /** 
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   
        Schema::table('dma_friends_badges', function($table)
        {   
            $table->integer('priority')->nullable();
        });

        Schema::table('dma_friends_activities', function($table)
        {   
            $table->integer('priority')->nullable();
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
            $table->dropColumn('priority');
        });

        Schema::table('dma_friends_activities', function($table)
        {
            $table->dropColumn('priority');
        });
    }   

}

