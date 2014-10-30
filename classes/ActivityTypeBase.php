<?php
namespace DMA\Friends\Classes;

use DMA\Friends\Models\Activity;
use DMA\Friends\Classes\UserExtend;
use RainLab\User\Models\User;
use Event;
use Lang;
use Str;
use Config;
use FriendsLog;
use DateTime;

interface ActivityTypeBaseInterface {
    public function getConfig();
    public function getFormDefaultValues($model);
    public function saveData($model, $values);
    public static function process(User $user, $params);
    public static function canComplete(Activity $activity);
}

class ActivityTypeBase implements ActivityTypeBaseInterface
{ 
    /**
     * @var string Name of the form configuration
     */
    public $fieldConfig = 'fields.yaml';

    /** 
     * @var string Specifies the component directory name.
     */
    protected $dirName;

    public function __construct()
    {
        $className = Str::normalizeClassName(get_called_class());
        $this->dirName = strtolower(str_replace('\\', '/', $className));
    }

    /**
     * Return the path to the yaml configuration for additional form fields
     *
     * @return string 
     * YAML config path
     */
    public function getConfig()
    {
        return $this->dirName . '/' . $this->fieldConfig;
    }

    /**
     * If additional fields are configured then implement to
     * tell the form how to get the default values
     *
     * @param object $model
     * The activity model
     *
     * @return array
     * An array with matching key/value pairs for a
     * set of custom fields
     */
    public function getFormDefaultValues($model) {}

    /**
     * Save additional data for an activity type. By default
     * attempt to save any additional field directly to model
     * attributes
     *
     * @param object $model
     * An activity model
     *
     * @param array $values
     * an array of values
     */
    public function saveData($model, $values) {
        foreach ($values as $key => $val) {
            $model->{$key} = $val;
        }
    }

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
            $userExtend = new UserExtend($user);
            $userExtend->addPoints($activity->points);

            Event::fire('friends.activityCompleted', [ $user, $activity ]); 

            // log an entry to the activity log
            FriendsLog::activity([
                'user'          => $user,
                'object'        => $activity,
            ]); 

            return $activity;
        }

        return false;
    }

   /** 
     * Determine if an activity is capable of being completed
     *
     * @param Activity
     * An activity model
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
