<?php namespace DMA\Friends\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddDemoGraphicMetadata extends Migration {

    /** 
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   
        Schema::table('dma_friends_usermetas', function($table)
        {   
            $table->timestamp('birth_date')->nullable();
            $table->string('gender')->nullable();
            $table->string('race')->nullable();
            $table->string('household_income')->nullable();
            $table->string('household_size')->nullable();
            $table->string('education')->nullable();
            $table->string('facebook')->nullable();
            $table->string('twitter')->nullable();
            $table->string('instagram')->nullable();
        });
    }   


    /** 
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {   
        Schema::table('dma_friends_usermetas', function($table)
        {
            //$table->dropColumn('birth_date');
            $table->dropColumn('gender');
            $table->dropColumn('race');
            $table->dropColumn('household_income');
            $table->dropColumn('household_size');
            $table->dropColumn('education');
            $table->dropColumn('facebook');
            $table->dropColumn('twitter');
            $table->dropColumn('instagram');
        });
    }   

}

