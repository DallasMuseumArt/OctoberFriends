<?php namespace DMA\Friends\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class UpdateDmaFriendsStepUserTable extends Migration {

    /** 
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   
        Schema::table('dma_friends_step_user', function($table)
        {   
            $table->integer('location_id')->unsigned()->index();
        }); 
    }   


    /** 
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {   
        Schema::table('dma_friends_step_user', function($table)
        {
            $table->dropColumn('location_id');
        });
    }   

}

