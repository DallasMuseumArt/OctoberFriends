<?php namespace DMA\Friends\Models;

use October\Rain\Auth\Models\Group as GroupBase;

/**
 * Friends User group model
 * @package DMA\Friends\Models
 * @author Carlos Arroyo
 *
 */
class UserGroup extends GroupBase{
	/**
	 * @var string The database table used by the model.
	 */
	protected $table = 'dma_friends_user_groups';
	
	/**
	 * @var array Validation rules
	 */
	public $rules = [
		'is_active' => 'boolean',
	];
	
	/**
	 * @var array Relations
	 */
	public $belongsToMany = [
		'users' => ['RainLab\User\Models\User', 
		'table' => 'dma_friends_users_groups',
		'primaryKey' => 'group_id',
		'foreignKey' => 'user_id',
		'timestamps' => true,
		'pivot' => ['is_confirmed', 'sent_invite']
		]
	];	
	
	/**
	 * @var array Relations
	 */
	public $belongsTo = [
		'owner' => ['RainLab\User\Models\User', 'foreignKey' => 'owner_id']	
	];
	
	/**
	 * 
	 */
	public static function boot()
	{
		parent::boot();
	
		// Setup event bindings...
		//UserGroup::observe(new UserGroupObserver);
	}
	
	/**
	 * Returns an array of users which the given group belongs to.
	 * @return array
	 */
	public function getUsers()
	{
		if (!$this->groupUsers)
			$this->groupUsers = $this->users()->get();
	
		return $this->groupUsers;
	}
	
	/**
	 * Adds the user to the group.
	 * @param RainLab\User\Models\User $user
	 * @return bool
	 */
	public function addUser($user)
	{
		if (!$this->inGroup($user)) {
			$this->users()->attach($user);
			$this->groupUsers = null;
			$this->sendInvite($user);
			return true;
		}
	
		return false;
	}
	
	/**
	 * Removes the user from the group.
	 * @param RainLab\User\Models\User $user
	 * @return bool
	 */
	public function removeUser($user)
	{
		if ($this->inGroup($user)) {
			$this->users()->detach($user);
			$this->groupUsers = null;
			return true;
		}
	
		return false;
	}
	
	/**
	 * See if the user is in the group.
	 * @param RainLab\User\Models\User $user
	 * @return bool
	 */
	public function inGroup($user)
	{
		foreach ($this->getUsers() as $u) {
			if ($u->getKey() == $user->getKey())
				return true;
		}
	
		return false;
	}	
	

	/**
	 * See if the user is in the group.
	 * @param RainLab\User\Models\User $user
	 * @return bool
	 */
	public function acceptInvite($user)
	{
		UserGroup::with(array('users' => function($query)
		{
		    $query->where('primary', 1); 
		}))->find(1);
	
		return false;
	}
	
	
	/**
	 * Send invite to the given user to join the group. 
	 * @return bool
	 */
	public function sendInvite($user){
		// TODO : Implement different channels (SMS, EMAIL, etc..)
		if ($user->pivot->sent_invite) return false;
		
		$full_name = $user->first_name . ' ' . $user->last_name;
			
		$data = [
		'user' => $user,
		'name' => $full_name,
		'link' => \Backend::url('dma/friends/groups'),
		];
		
        $mailTemplate = 'backend::mail.invite';
		
		if(\Mail::send($mailTemplate, $data, function($message)
		{
			$message->to($user->email, $full_name);
		}) == 1){
			// Email was sent
			$user->pivot->sent_invite = true;
			$user->save();
			return true;
		}	
		return false;
	}
	
	/**
	 * Bulk send invites to users of the group.
	 * sendUserInvitations will send only invites
	 * to user where sent_invite is false 
	 */
	public function sendUserInvitations()
	{	
		foreach ($this->users as $user){
			$this->sendInvite($user);
		}
	}	
	

}


class UserGroupObserver {

	public function saving($model)
	{
		if(!$model->is_active) return false;
	}

	public function updating($model)
	{
		if(!$model->is_active) return false;
	}

}