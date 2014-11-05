<?php
namespace DMA\Friends\Activities;

use RainLab\User\Models\User;
use DMA\Friends\Models\Activity;
use DMA\Friends\Classes\ActivityTypeBase;
use Session;
use Lang;

class ActivityCode extends ActivityTypeBase
{

    /**
     * {@inheritDoc}
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
    public static function process(User $user, $params)
    {
        if (!isset($params['code']) || empty($params['code'])) return false;

        if ($activity = Activity::findCode($params['code'])->first()) {
            return parent::process($user, ['activity' => $activity]);
        }

        Session::put('activityError', Lang::get('dma.friends::lang.activities.codeError', ['code' => $params['code']]));
        
        return false;

    }
}
