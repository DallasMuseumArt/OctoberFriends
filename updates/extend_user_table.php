<?php namespace DMA\Friends\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use DMA\Friends\Models\Usermeta;

class ExtendUsersTable extends Migration
{

    public function up()
    {   
        Schema::table('users', function($table)
        {   
            $table->integer('points')->default(0);
            $table->integer('points_this_week')->default(0);
            $table->integer('points_today')->default(0);
        }); 

        Schema::table('dma_friends_usermetas', function($table)
        {
            $table->dropColumn('points');
        });
    }   

    public function down()
    {   
        Schema::table('users', function($table)
        {
            $table->dropColumn('points');
            $table->dropColumn('points_this_week');
            $table->dropColumn('points_today');
        });
    }   

}
