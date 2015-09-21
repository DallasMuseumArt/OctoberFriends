<?php
namespace DMA\Friends\Activities;

use RainLab\User\Models\User;
use DMA\Friends\Models\Activity;
use DMA\Friends\Classes\ActivityTypeBase;

class Points extends ActivityTypeBase
{

    /**
     * {@inheritDoc}
     */
    public function details()
    {
        return [
            'name'          => 'Points',
            'description'   => 'Complete activities by earning points',
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getFormDefaultValues($model)
    {
        return [
            'points_to_complete' => (isset($model->points_to_complete)) ? $model->points_to_complete : null,
        ];
    }

    /**
     * @see \DMA\Friends\Classes\ActivityTypeBase
     *
     * Process and determine if an award can be isued
     * based on a provided activity code
     *
     * @param User $user
     * A user model for which the activity should act upon
     * 
     * @param array $params
     * An array of parameters for validating activities 
     *
     * @return boolean
     * returns true if the process was successful
     */
    public static function process(User $user, $params = [])
    {
        static $is_running = false;

        if (!$is_running) {
            $is_running = true;

            $activities = Activity::findActivityType('Points')->get();

            foreach ($activities as $activity) {
                if ($user->activities->contains($activity->id)) continue;

                if ($user->points >= $activity->points) {
                    parent::process($user, ['activity' => $activity]);
                }
            }

            $is_running = false;
        }
        
        return true;
    }
}
