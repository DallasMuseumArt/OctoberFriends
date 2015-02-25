<?php namespace DMA\Friends\Updates;

use Log;
use DB;
use Schema;
use October\Rain\Database\Updates\Migration;
use DMA\Friends\Models\Usermeta;

class ChangeMemberNumberType extends Migration
{

    public function up()
    {
        Schema::table('dma_friends_usermetas', function($table)
        {   
            $table->dropColumn('current_member_number');
        });

        Schema::table('dma_friends_usermetas', function($table)
        {   
            $table->string('current_member_number')->nullable();
            $table->index('current_member_number');
        });

    }

    public function down() {}

}
