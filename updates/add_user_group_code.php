<?php namespace DMA\Friends\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddUserGroupCode extends Migration {

    /** 
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   
        Schema::table('dma_friends_user_groups', function($table)
        {   
            $table->string('code')->nullable();
        });

    }   


    /** 
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {   
        Schema::table('dma_friends_user_groups', function($table)
        {
            $table->dropColumn('code');
        });

    }   

}

