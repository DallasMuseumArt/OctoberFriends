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

            CREATE 
                ALGORITHM = UNDEFINED 
                DEFINER = `root`@`localhost` 
                SQL SECURITY DEFINER
            VIEW `dma_friends_activity_stream` AS
                select 
                    `a`.`title` AS `title`,
                    `pivot`.`activity_id` AS `object_id`,
                    `pivot`.`created_at` AS `created_at`,
                    `pivot`.`user_id` AS `user_id`,
                    'Activity' COLLATE utf8_unicode_ci AS `object_type` 
                from
                    (`dma_friends_activity_user` `pivot`
                    left join `dma_friends_activities` `a` ON ((`pivot`.`activity_id` = `a`.`id`))) 
                union select 
                    `a`.`title` AS `title`,
                    `pivot`.`badge_id` AS `object_id`,
                    `pivot`.`created_at` AS `created_at`,
                    `pivot`.`user_id` AS `user_id`,
                    'Badge' COLLATE utf8_unicode_ci AS `object_type`
                from
                    (`dma_friends_badge_user` `pivot`
                    left join `dma_friends_badges` `a` ON ((`pivot`.`badge_id` = `a`.`id`))) 
                union select 
                    `a`.`title` AS `title`,
                    `pivot`.`reward_id` AS `id`,
                    `pivot`.`created_at` AS `created_at`,
                    `pivot`.`user_id` AS `user_id`,
                    'Reward' COLLATE utf8_unicode_ci AS `object_type`
                from
                    (`dma_friends_reward_user` `pivot`
                    left join `dma_friends_rewards` `a` ON ((`pivot`.`reward_id` = `a`.`id`))) 
                union select 
                    `a`.`title` AS `title`,
                    `pivot`.`step_id` AS `object_id`,
                    `pivot`.`created_at` AS `created_at`,
                    `pivot`.`user_id` AS `user_id`,
                    'Step' COLLATE utf8_unicode_ci AS `object_type`
                from
                    (`dma_friends_step_user` `pivot`
                    left join `dma_friends_steps` `a` ON ((`pivot`.`step_id` = `a`.`id`)))
                order by `created_at` desc
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

