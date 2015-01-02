<?php namespace DMA\Friends\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddUserBarcodeId extends Migration {

    /** 
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   
        Schema::table('users', function($table)
        {   
            $table->string('barcode_id')->nullable();
        });
    }   


    /** 
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {   
        Schema::table('users', function($table)
        {
            $table->dropColumn('barcode_id');
        });
    }   

}

