<?php namespace DMA\Friends\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddACustomActivityMessageIndex extends Migration {

    /** 
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   
        Schema::table('dma_friends_activities', function($table)
        {
            $table->text('complete_message')->nullable();
            $table->text('feedback_message')->nullable();
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
            $table->dropColumn('complete_message');
            $table->dropColumn('feedback_message');
        });

    }   

}