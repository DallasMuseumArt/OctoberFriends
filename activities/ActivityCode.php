<?php
namespace DMA\Friends\Activities;

use RainLab\User\Models\User;
use DMA\Friends\Models\Activity;
use DMA\Friends\Classes\ActivityTypeBase;

class ActivityCode extends ActivityTypeBase
{

    /**
     * Register details about your activity.
     * 
     * @return array 
     * An array of options
     * - name: The name of the activity type.  
     *   This will be used in the form drop down when users configure an activity
     * - description: (optional) An optional description of the activity type
     */
    public function details()
    {
        return [
            'name'          => 'Activity Code',
            'description'   => 'Complete activities by entering in a code',
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getFormDefaultValues($model)
    {
        return [
            'activity_code' => (isset($model->activity_code)) ? $model->activity_code : null,
        ];
    }

    /**
     * @see \DMA\Friends\Classes\ActivityTypeBase
     *
     * Process and determine if an award can be issued
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
    public static function process(User $user, $params)
    {
        if (!isset($params['code']) || empty($params['code'])) return false;

        if ($activity = Activity::findCode($params['code'])->first()) {
            return parent::process($user, ['activity' => $activity]);
        }

        return false;

    }
}
