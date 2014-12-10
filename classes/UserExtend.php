<?php namespace DMA\Friends\Classes;

use Event;
use DMA\Friends\Classes\FriendsLog;
use RainLab\User\Models\User;
use System\Classes\SystemException;

/**
 * Custom class to add additional functionality based on the Rainlab User model
 * 
 * @package DMA\Friends\Classes
 * @author Carlos Arroyo
 */
class UserExtend
{

    /**
     * @var The user object
     */
    public $user = null;

    /**
     * Extended functionality against user objects 
     * This is a really ugly way to extend the functionality
     * of the user object
     * 
     * @param \RainLab\User\Model\User (optional) If no user object
     * is provided then a new user object will be instantiated
     */
    public function __construct(User $user = null)
    {
        if (!$user)
            $user = new User;

        $this->user = $user;
    }

    /**
     * Add points to a users account
     *
     * @param integer $points
     * The amount of points to add to a user
     */
    public function addPoints($points)
    {
        if (!is_numeric($points))
            throw new SystemException('Points must be an integer');

        $this->user->points += $points;
        $this->user->points_this_week += $points;

        if ($this->user->forceSave()) {

            Event::fire('dma.friends.user.pointsEarned', [$this->user, $points]);

            $params = [
                'user'          => $this->user,
                'points_earned' => $points,
            ];
            FriendsLog::points($params);
        }
    }

    /**
     * Remove points from a user account
     *
     * @param integer $points
     * The amount of points to remove from a user account
     */
    public function removePoints($points)
    {
        if (!is_numeric($points))
            throw new SystemException('Points must be an integer');

        if ($this->user->points < $points) {
            return false;
        }

        $this->user->points -= $points;
        $this->user->points_this_week -= $points;
        if ($this->user->forceSave()) {
            Event::fire('dma.friends.user.pointsRemoved', [$this->user, $points]);
        }

        return true;
    }
    
    public function getMembershipStatusOptions()
    {
        return [
            UserGroup::MEMBERSHIP_PENDING   =>  UserGroup::MEMBERSHIP_PENDING,
            UserGroup::MEMBERSHIP_ACCEPTED  =>  UserGroup::MEMBERSHIP_ACCEPTED,
            UserGroup::MEMBERSHIP_REJECTED  =>  UserGroup::MEMBERSHIP_REJECTED,
            UserGroup::MEMBERSHIP_CANCELLED =>  UserGroup::MEMBERSHIP_CANCELLED
        ];
    }
}
