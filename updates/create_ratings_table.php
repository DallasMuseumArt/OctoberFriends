<?php namespace DMA\Friends\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateRatingsTable extends Migration
{

    public function up()
    {
        Schema::create('dma_friends_ratings', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('object_type')->nullable();
            $table->integer('object_id')->nullable();
            $table->float('average')->default(0)->nullable();
            $table->integer('total')->default(0)->nullable();
            $table->timestamps();

            $table->unique( array('object_type','object_id') );

            $table->index('object_id');
            $table->index('object_type');
        });
    }

    public function down()
    {
        Schema::dropIfExists('dma_friends_ratings');
    }

}
