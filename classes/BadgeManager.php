<?php namespace DMA\Friends\Classes;

use DMA\Friends\Models\Activity;
use DMA\Friends\Models\Step;
use DMA\Friends\Models\Badge;
use RainLab\User\Models\User;
use DB;
use Flash;
use Lang;
use Exception;
use Event;

/**
 * This class handles badging logic
 *
 * @package DMA\Friends\Classes
 * @author Kristen Arnold, Carlos Arroyo
 */
class BadgeManager
{

    /**
     * Take an activity and apply it to any badges and steps that apply
     *
     * @param User $user
     * A user model
     * @param Activity $activity 
     * An activity model
     */
    public static function applyActivityToBadges(User $user, Activity $activity)
    {

        $steps = $activity->steps()->get();

        foreach ($steps as $step) {

            // see if step is associated with user
            if (!$step->users->contains($user->id)) {
                // user has not completed the step
                $isStepCompletable = self::checkUserActivities($user, $activity, $step);

                if ($isStepCompletable) {
                    // Find badge associated with steps
                    self::completeBadge($step, $user);
                }
            }
        }

    }

    /**
     * Check the amount of times a user has completed an activity
     * against the amount of times an activity needs to be done to complete a step
     *
     * @param User $user
     * A user object
     * @param Activity $activity
     * An activity object
     * @param Step $step
     * A step object
     *
     * @return boolean
     * Returns true if number of times a user has completed an activity match what is required to complete a step
     */
    private static function checkUserActivities(User $user, Activity $activity, Step $step)
    {
        static $cache;
        $key = $user->id . '_' . $activity->id;

        if (!isset($cache[$key])) {
            $count = DB::table('dma_friends_activity_user')
                ->select(DB::raw('count(*) as count'))
                ->where('user_id', $user->id)
                ->where('activity_id', $activity->id)
                ->first();

            $cache[$key] = $count;
        } else {
            $count = $cache[$key];
        }

        return ($count->count == $step->count);
    }

    /**
     * Complete the step and proceed to complete a badge if it can be completed
     *
     * @param Step $step
     * A step model
     * @param User $user
     * A user model
     */
    private static function completeBadge(Step $step, User $user)
    {
        $badge = $step->badge;

        // Complete step
        try {
            $user->steps()->save($step);
            Event::fire('dma.friends.step.completed', [ $step, $user ]);
        } catch(Exception $e) {
            throw new Exception(Lang::get('dma.friends::lang.exceptions.stepFailed'));
        }

        // See if the user has completed all steps for a badge
        foreach($badge->steps as $step) {
            if (!$user->steps->contains($step->id)) {
                //user did not complete a step in badge so we cannot complete the badge
                return;
            }
        }

        try {
            // If loop completes with out returning false the user has completed all steps
            $user->badges()->save($badge);
            Event::fire('dma.friends.badge.completed', [ $badge, $user ]);
            Flash::info(Lang::get('dma.friends::lang.badges.completed', ['title' => $badge->title]));
        } catch(Exception $e) {
            throw new Exception(Lang::get('dma.friends::lang.exceptions.badgeFailed'));
        }
    }
}