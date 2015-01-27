<?php namespace DMA\Friends\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddRewardEmailOptionsTable extends Migration
{

    public function up()
    {
        Schema::table('dma_friends_rewards', function($table)
        {   

            $table->boolean('enable_admin_email')->default(false);
            $table->string('email_template')->nullable();
            $table->string('admin_email_template')->nullable();
        });

    }

    public function down()
    {
        Schema::table('dma_friends_rewards', function($table)
        {   
            $table->dropColumn('enable_admin_email');
            $table->dropColumn('email_template');
            $table->dropColumn('admin_email_template');
        });
    }

}
