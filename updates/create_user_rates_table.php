<?php namespace DMA\Friends\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateUserRatesTable extends Migration
{

    public function up()
    {
        Schema::create('dma_friends_user_rates', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            
            $table->integer('rating_id')->unsigned();
            $table->foreign('rating_id')
                ->references('id')
                ->on('dma_friends_ratings')
                ->onDelete('cascade');
            
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            
                $table->float('rate');
            $table->string('comment');
            $table->timestamps();
            
            $table->unique( array('rating_id','user_id') );

            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('dma_friends_user_rates');
    }

}
