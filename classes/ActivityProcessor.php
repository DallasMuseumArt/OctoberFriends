<?php
namespace DMA\Friends\Classes;

use DMA\Friends\Models\Activity;
use RainLab\User\Models\User;
use Event;
use Lang;
use FriendsLog;
use DateTime;

interface ActivityProcessorInterface {
    public static function process(User $user, $params);
    public static function canComplete(Activity $activity);
}

class ActivityProcessor implements ActivityProcessorInterface
{ 

    /** 
     * Process and determine if an award can be issued
     * based on a provided activity code
     *
     * @param object $user
     * A user model for which the activity should act upon
     * 
     * @param array $params
     * An array of parameters for validating activities 
     *
     * @return boolean
     * returns true if the process was successful
     */
    public static function process(User $user, $params)
    {
        $activity = $params['activity'];

        if (self::canComplete($activity)) {

            Event::fire('friends.activityCompleted', [ $user, $activity ]); 

            // log an entry to the activity log
            FriendsLog::activity([
                'user'          => $user,
                'message'       => Lang::get('dma.friends::lang.log.activity', ['name' => $user->name, 'title' => $activity->title]), 
                'object'        => $activity,
                'points_earned' => $activity->points,
            ]); 

            return true;
        }

        return false;
    }

   /** 
     * Determine if an activity is capable of being completed
     *
     * @return boolean
     * returns true if an activity can be completed by the user
     */
    public static function canComplete(Activity $activity)
    {   
        if (!$activity->isActive()) return false;

        switch ($activity->time_restriction) {
            case Activity::TIME_RESTRICT_NONE:
                return true;
            case Activity::TIME_RESTRICT_HOURS:
                if ($activity->time_restriction_data) {
                    $now        = time();
                    $start_time = strtotime($activity->time_restriction_data['start_time'], $now);
                    $end_time   = strtotime($activity->time_restriction_data['end_time'], $now);
                    $day        = date('w');

                    if ($activity->time_restriction_date['days'][$day] !== false
                        && $now >= $start_time && $now <= $end_time) return true;
                }

                break;
            case Activity::TIME_RESTRICT_DAYS: 
                $now = new DateTime('now');
                if ($now >= $activity->date_begin 
                    && $now <= $activity->date_end) return true;

                break;
        } 

        return false;
    } 

}
