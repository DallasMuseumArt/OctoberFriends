<?php namespace DMA\Friends\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddRewardUserLimit extends Migration {

    /** 
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   
        Schema::table('dma_friends_rewards', function($table)
        {   
            $table->integer('user_redeem_limit')->nullable();
        });

    }   


    /** 
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {   
        Schema::table('dma_friends_rewards', function($table)
        {
            $table->dropColumn('user_redeem_limit');
        });

    }   

}

