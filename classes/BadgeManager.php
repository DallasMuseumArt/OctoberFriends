<?php namespace DMA\Friends\Classes;

use DMA\Friends\Models\Activity;
use DMA\Friends\Models\Step;
use DMA\Friends\Models\Badge;
use RainLab\User\Models\User;


/**
 * This class handles badging logic
 *
 * @package DMA\Friends\Classes
 */
class BadgeManager
{

    /**
     * Take an activity and apply it to any badges and steps that apply
     *
     * @param Activity an activity model
     */
    public static function applyActivityToBadges(User $user, Activity $activity)
    {
        //TODO look up all steps that use this activity

        $steps = Step->activities()->find($activity->id);
        \Debugbar::info($steps);


        // if we have steps complete the step for a user

    }
}