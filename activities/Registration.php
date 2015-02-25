<?php
namespace DMA\Friends\Activities;

use RainLab\User\Models\User;
use DMA\Friends\Models\Activity;
use DMA\Friends\Classes\ActivityTypeBase;

class Registration extends ActivityTypeBase
{

    /**
     * {@inheritDoc}
     */
    public function details()
    {
        return [
            'name'          => 'Award activity on registration',
            'description'   => 'Activity is processed when a user registers for an account',
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getFormDefaultValues($model)
    {
        return [
            'event' => (isset($model->event)) ? $model->event : null,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public static function process(User $user, $params = [])
    {

        $activities = Activity::where('activity_type', '=', 'Registration')->get();

        if (!$activities) return;

        foreach($activities as $activity) {
            parent::process($user, ['activity' => $activity]);
        }

        return true;

    }
}
