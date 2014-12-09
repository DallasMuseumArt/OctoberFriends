<?php namespace DMA\Friends\Updates;

use DB;
use October\Rain\Database\Updates\Migration;

class CreateActivityView extends Migration {

    /** 
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   
        DB::statement('CREATE VIEW dma_friends_activity_stream AS

            SELECT 
                activity_id as object_id, 
                created_at, 
                user_id, 
                "DMA\\Friends\\Models\\Activity" as object_type
            FROM dma_friends_activity_user
            UNION
            SELECT 
                badge_id as object_id, 
                created_at, 
                user_id, 
                "DMA\\Friends\\Models\\Badge" as object_type
            FROM dma_friends_badge_user
            UNION
            SELECT 
                reward_id as id, 
                created_at, 
                user_id, 
                "DMA\\Friends\\Models\\Reward" as object_type
            FROM dma_friends_reward_user
            UNION
            SELECT 
                step_id as object_id, 
                created_at, 
                user_id, 
                "DMA\\Friends\\Models\\Step" as object_type
            FROM dma_friends_step_user

            ORDER BY created_at DESC
        ');
    }   


    /** 
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {   
        DB::raw('DROP VIEW dma_friends_activity_stream');
    }   

}

