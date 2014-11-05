<?php namespace DMA\Friends\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class UpdateDmaFriendsActivityTable extends Migration {

    /** 
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   
        Schema::table('dma_friends_activities', function($table)
        {   
            $table->boolean('touch');
        }); 

        Schema::create('dma_friends_activity_user', function($table)
        {
            $table->integer('activity_id');
            $table->integer('user_id');
            $table->timestamps();
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
            $table->dropColumn('touch');
        });

        Schema::dropIfExists('dma_friends_activity_user');
    }   

}