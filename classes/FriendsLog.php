<?php

namespace DMA\Friends\Classes;

use DMA\Friends\Models\ActivityLog;

class FriendsLog
{
    /**
     * Write a log entry to the activity log
     *
     * @param $action
     *  The type of action to be logged
     * 
     * @param $params
     * An array of parameters about the log entry
     * - 'user_id': The user ID to associate the log entry
     * - 'message': A log message
     * - (optional) 'object': A database model to associate the log with
     * - (optional) 'points_earned': The number of points earned by the action
     * - (optional) 'artwork_id': The assession number of an artwork to associate the log entry
     */
    public function write($action, $params)
    {
        $log            = new ActivityLog;
        $log->user_id   = $params['user_id'];
        $log->site_id   = ''; //TODO implement settings and/or gethostname();
        $log->action    = $action;
        $log->message   = $params['message'];
        $log->points_earned = $params['points_earned'];
        
        // Log date/time
        $datetime = new DateTime;
        $tzstring = date_default_timezone_get();
        $timezone = new DateTimeZone($tzstring);
        $datetime->setTimezone($timezone);

        $log->timestamp = $datetime->format('c');
        $log->timezone  = $datetime->getTimezone()->getName(); 

        // Object type and id
        // pass an object in $params['object'] and do some logic for that
        if (isset($params['object']) && !empty($params['object'])) {
            $log->object_type   = typeof($params['object']);
            $log->object_id     = $params['object']->id;
        }

        if (isset($params['artwork_id']) {
            $log->artwork_id = $params['artwork_id'];
        }

        $log->save();

    }

    /**************************************************
     * Alias functions to quickly create log types
     *************************************************/

    /**
     * Log an activity
     *
     * @array $params
     * An array of parameters to log
     * See write() for details
     *
     * @return void
     */
    public function activity($params)
    {
        self::write('activity', $params);
    }

    /** 
     * Log an artwork activity
     * See write() for details
     *
     * @array $params
     * An array of parameters to log
     *
     * @return void
     */
    public function artwork($params)
    {
        self::write('artwork', $params);
    }

    /** 
     * Log a checkin
     * See write() for details
     *
     * @array $params
     * An array of parameters to log
     *
     * @return void
     */
    public function checkin($params)
    {
        self::write('checkin', $params);
    }

    /** 
     * Log when a user has been awarded points
     * See write() for details
     *
     * @array $params
     * An array of parameters to log
     *
     * @return void
     */
    public function points($params)
    {
        self::write('points', $params);
    }

    /** 
     * Log when a user redeems a reward
     * See write() for details
     *
     * @array $params
     * An array of parameters to log
     *
     * @return void
     */
    public function reward($params)
    {
        self::write('reward', $params);
    }

    /** 
     * Log when a user unlocks an achievement
     * See write() for details
     *
     * @array $params
     * An array of parameters to log
     *
     * @return void
     */
    public function unlocked($params)
    {
        self::write('unlocked', $params);
    }

}
