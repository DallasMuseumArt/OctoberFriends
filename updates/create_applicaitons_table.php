<?php namespace DMA\Friends\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use DMA\Friends\Models\Application;

class CreateApplicationsTable extends Migration
{

    public function up()
    {
        Schema::create('dma_friends_api_applications', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('app_key')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('token_ttl')->default(20160); // 2 weeks
            $table->enum('access_level', [
                    Application::ACCESS_ALL_DATA,
                    Application::ACCESS_USER_DATA
            ]);
            $table->string('notes')->nullable();
            $table->timestamps();
            
        });
    }

    public function down()
    {
        Schema::dropIfExists('dma_friends_api_applications');
    }

}
