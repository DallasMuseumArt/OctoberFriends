<?php namespace DMA\Friends\Updates;

use October\Rain\Database\Updates\Migration;
use DMA\Friends\Models\Activity;

class FixTimeRestrictions extends Migration {

    /** 
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   
        Activity::chunk(200, function($activities) {
            foreach ($activities as $activity) {
                if ($activity->date_begin && $activity->date_end) {
                    $activity->time_restriction = Activity::TIME_RESTRICT_DAYS;
                } elseif (!empty($activity->time_restriction_data)) {
                    $activity->time_restriction = Activity::TIME_RESTRICT_HOURS;
                } else {
                    $activity->time_restriction = Activity::TIME_RESTRICT_NONE;
                }
                
                $activity->save();

            }
        });
    }

    public function down() {}

}

