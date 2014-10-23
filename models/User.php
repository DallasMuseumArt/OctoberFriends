<?php namespace DMA\Friends\Models;

use Event;
use DMA\Friends\Classes\FriendsLog;
use RainLab\User\Models\User as UserBase;

/**
 * Friends User model
 * @package DMA\Friends\Models
 * @see RainLab\User\Models\User
 * @author Carlos Arroyo
 */
class User extends UserBase
{
    /**
     * @var array Relations
     */
    public $belongsToMany = [
        'groups'    => ['DMA\Friends\Models\UserGroup', 'table' => 'users_groups', 'user_id', 'group_id'],
        'steps'     => ['DMA\Friends\Models\Step', 'table' => 'dma_friends_step_user', 'user_id', 'step_id'],
        'badges'    => ['DMA\Friends\Models\Badge',  'table' => 'dma_friends_badge_user',    'user_id', 'badge_id'],
        'rewards'   => ['DMA\Friends\Models\Reward', 'table' => 'dma_friends_reward_user',   'user_id', 'reward_id'],
    ];
    public $hasOne = [
        'metadata' => ['DMA\Friends\Models\Usermeta'],
    ];
    public $hasMany = [
        'activityLogs' => ['DMA\Friends\Models\ActivityLog'],
    ];

    /**
     * Add points to a users account
     *
     * @param integer $points
     * The amount of points to add to a user
     */
    public function addPoints($points)
    {
        if (!is_int($points))
            throw new Exception('Points must be an integer');

        $this->points += $points;
        $this->points_this_week += $points;

        if ($this->forceSave()) {

            Event::fire('friends.user.pointsEarned', [$this, $points]);

            $params = [
                'user'          => $this,
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
        if (!is_int($points))
            throw new Exception('Points must be an integer');

        $this->points -= $points;
        $this->points_this_week -= $points;
        if ($this->forceSave()) {
            Event::fire('friends.user.pointsRemoved', [$this, $points]);
        }
    }
    
    public function getMembershipStatusOptions(){
        return [
            UserGroup::MEMBERSHIP_PENDING   =>  UserGroup::MEMBERSHIP_PENDING,
            UserGroup::MEMBERSHIP_ACCEPTED  =>  UserGroup::MEMBERSHIP_ACCEPTED,
            UserGroup::MEMBERSHIP_REJECTED  =>  UserGroup::MEMBERSHIP_REJECTED,
            UserGroup::MEMBERSHIP_CANCELLED =>  UserGroup::MEMBERSHIP_CANCELLED
        ];
    }
}
