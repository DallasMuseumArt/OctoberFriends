<?php namespace DMA\Friends\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateCategoriesTable extends Migration
{

    public function up()
    {
        Schema::create('dma_friends_categories', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->timestamps();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('slug');
            $table->boolean('touch');
        }); 

        Schema::create('dma_friends_object_categories', function($table)
        {   
            $table->engine = 'InnoDB';
            $table->integer('category_id');
            $table->string('object_type');
            $table->integer('object_id');
        }); 
    }

    public function down()
    {
        Schema::dropIfExists('dma_friends_categories');
        Schema::dropIfExists('dma_friends_object_categories');
    }

}
