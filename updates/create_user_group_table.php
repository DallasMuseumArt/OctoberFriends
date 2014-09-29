<?php namespace DMA\Friends\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateUserGroupTable extends Migration
{

    public function up()
    {
        Schema::create('dma_friends_user_groups', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name')->nullable();
            $table->integer('owner_id');
            $table->boolean('is_active')->default(true);
            $table->text('permissions')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('dma_friends_user_groups');
    }

}
