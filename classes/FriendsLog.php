<?php

namespace DMA\Friends\Classes;

use DMA\Friends\Models\ActivityLog;
use DateTime;
use DateTimeZone;

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
     * - 'user': The user object to associate the log entry
     * - 'message': A log message
     * - (optional) 'object': A database model to associate the log with
     * - (optional) 'points_earned': The number of points earned by the action
     * - (optional) 'artwork_id': The assession number of an artwork to associate the log entry
     */
    public static function write($action, array $params)
    {
        $log                = new ActivityLog;
        $log->user          = $params['user'];
        $log->site_id       = gethostname();
        $log->action        = $action;
        $log->message       = $params['message'];
        $log->points_earned = isset($params['points_earned']) ? $params['points_earned'] : 0;
        $log->timestamp     = new DateTime('now');

        if (isset($params['artwork_id'])) {
            $log->artwork_id = $params['artwork_id'];
        }

        $log->save();

        // Associate an object if present
        if (isset($params['object']) && !empty($params['object'])) {
            $params['object']->activityLogs()->save($log);
        }

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
    public static function activity($params)
    {
        $params['message'] = Lang::get('dma.friends::lang.log.activity', [
            'name'  => $params['user']->name, 
            'title' => $params['object']->title
        ]);

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
    public static function artwork($params)
    {
        $params['message'] = Lang::get('dma.friends::lang.log.artwork', [
            'name'          => $params['user']->name, 
            'artwork_id'    => $params['artwork_id'],
        ]); 

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
    public static function checkin($params)
    {
        $params['message'] = Lang::get('dma.friends::lang.log.checkin', [
            'name'  => $params['user']->name, 
            'title' => $params['object']->title
        ]); 

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
    public static function points($params)
    {
        $params['message'] = Lang::get('dma.friends::lang.log.points', [
            'name'          => $params['user']->name, 
            'points'        => $params['points_earned'],
            'total_points'  => $params['user']->metadata->points,
        ]); 

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
    public static function reward($params)
    {
        $params['message'] = Lang::get('dma.friends::lang.log.reward', [
            'name'  => $params['user']->name, 
            'title' => $params['object']->title
        ]); 

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
    public static function unlocked($params)
    {
        self::write('unlocked', $params);
    }

}
