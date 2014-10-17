<?php namespace DMA\Friends\Models;

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
	 * @var string The database table used by the model.
	 */
	#protected $table = 'dma_friends_users';

	/**
	 * @var array Relations
	 */
	public $belongsToMany = [
		'groups' => ['DMA\Friends\Models\UserGroup', 'table' => 'users_groups', 'user_id', 'owner_id']
	];
	
	
	public function getMembershipStatusOptions(){
		return [
            UserGroup::MEMBERSHIP_PENDING   =>  UserGroup::MEMBERSHIP_PENDING,
            UserGroup::MEMBERSHIP_ACCEPTED  =>  UserGroup::MEMBERSHIP_ACCEPTED,
            UserGroup::MEMBERSHIP_REJECTED  =>  UserGroup::MEMBERSHIP_REJECTED,
            UserGroup::MEMBERSHIP_CANCELLED =>  UserGroup::MEMBERSHIP_CANCELLED
        ];
	}
}
