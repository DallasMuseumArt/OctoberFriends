<?php namespace DMA\Friends\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateUsermetasTable extends Migration
{

    public function up()
    {
        Schema::create('dma_friends_usermetas', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->integer('points');
            $table->string('first_name');
            $table->string('last_name');
            $table->boolean('email_optin');
            $table->smallInteger('current_member');
            $table->integer('current_member_number');
        });
    }

    public function down()
    {
        Schema::dropIfExists('dma_friends_usermetas');
    }

}
