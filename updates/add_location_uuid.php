<?php namespace DMA\Friends\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddLocationUUID extends Migration {

    /** 
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   
        Schema::table('dma_friends_locations', function($table)
        {   
            $table->string('uuid')->nullable();
        });
    }   


    /** 
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {}    

}

