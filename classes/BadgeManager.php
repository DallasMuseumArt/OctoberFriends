<?php namespace DMA\Friends\Classes;

use DMA\Friends\Models\Activity;
use DMA\Friends\Models\Step;
use DMA\Friends\Models\Badge;
use RainLab\User\Models\User;
use DMA\Friends\Classes\UserExtend;
use DMA\Friends\Classes\Notifications\NotificationMessage;
use DB;
use Flash;
use Lang;
use Exception;
use Event;
use Postman;
use View;
use Auth;

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

            // user has not completed the step
            $isStepCompletable = self::checkUserActivities($user, $activity, $step);

            if ($isStepCompletable) {
                // Find badge associated with steps
                self::completeBadge($step, $user);
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
        if (!isset($step->badge)) return;

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

        if ($step->badge->maximum_earnings) {
            $timesEarned = floor($count->count / $step->count);

            if ($timesEarned > $step->badge->maximum_earnings) 
                return false;
        }

        // If count is evenly divisable by the required step count then return true
        return !($count->count % $step->count);
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
            $userExtend = new UserExtend($user);
            $userExtend->addPoints($badge->points);

            Event::fire('dma.friends.badge.completed', [ $badge, $user ]);

            // log an entry to the activity log
            FriendsLog::unlocked([
                'user'      => $user,
                'object'    => $badge,
            ]); 

            if ($badge->congratulations_text) {
                $notificationText = $badge->congratulations_text;
            } else { 
                $notificationText = Lang::get('dma.friends::lang.badges.completed', ['title' => $badge->title]);
            }
            
            //Flash::info($notificationText);
            
            Postman::send('simple', function(NotificationMessage $notification) use ($user, $badge, $notificationText){
           
                // Set user
                $notification->to($user, $user->name);
                 
                // Send code and activity just in case we want to use in the template
                $notification->addData(['badge' => $badge]);
            
                $notification->message($notificationText);
                 
            }, ['flash', 'kiosk']);
            
        } catch(Exception $e) {
            throw new Exception(Lang::get('dma.friends::lang.exceptions.badgeFailed'));
        }
    }

    public static function render($controller, $badge)
    {

        $user = Auth::getUser();
        
        return $controller->renderPartial('@modalDisplay', [
            'title'     => $badge->title,
            'content'   => View::make('dma.friends::badge', ['model' => $badge, 'user' => $user])->render(),
        ]);
    }
}
