<?php namespace DMA\Friends\Models;

use October\Rain\Auth\Models\Group as GroupBase;
use DMA\Friends\Models\Settings;

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
		'pivot' => ['membership_status']
		]
	];	
		
	/**
	 * @var array Relations
	 */
	public $belongsTo = [
		'owner' => ['RainLab\User\Models\User', 'foreignKey' => 'owner_id']	
	];
	
	/**
	 * @var array Users of the group.
	 */
	protected $groupUsers;	
	
	// CONSTANTS
	const MEMBERSHIP_PENDING   = 'PENDING';
	const MEMBERSHIP_ACCEPTED  = 'ACCEPTED';
	const MEMBERSHIP_REJECTED  = 'REJECTED';
	const MEMBERSHIP_CANCELLED = 'CANCELLED';
		

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
	 * Only returns Pending and Active users
	 * @return array
	 */
	public function getUsers()
	{
		if (!$this->groupUsers)
			//$this->groupUsers = $this->users()->get();
			$this->groupUsers = self::find($this->getKey())->users->filter(function($user){
				$status = $user->pivot->membership_status;
				return $status == self::MEMBERSHIP_PENDING || $status == self::MEMBERSHIP_ACCEPTED;  
			});
			
		return $this->groupUsers;
	}
	
	/**
	 * Adds the user to the group.
	 * @param RainLab\User\Models\User $user
	 * @return bool
	 */
	public function addUser(&$user)
	{

		if (count($this->getUsers()) < Settings::get('maximum_users_group')
			&& $this->is_active ){
			if (!$this->inGroup($user)) {
				$this->users()->attach($user);
				$this->groupUsers = null;
				
				// FIXME : Sometimes pivot is empty but I didn't find why it happens.
				// I am suspecting that is an internal problem in the Eloquent model and how it gets updated
				// after a new relationship is created.
				// For now the solution is if the pivot is empty reload the model.
				if (is_null($user->pivot)){
					$user = $this->users()->where('user_id', $user->getKey())->get()[0];
				}				
				
				$this->sendNotification($user, 'invite');
				return true;
			}
		}else{
			// TODO : Raise exceptions or fire events 
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
		if ($this->inGroup($user, $includeAll=true)) {
			$this->users()->detach($user);
			$this->groupUsers = null;
			return true;
		}
	
		return false;
	}
	
	/**
	 * See if the user is in the group.
	 * @param RainLab\User\Models\User $user
	 * @params boolean if True check all users attach to the group regarless of the status.
	 * 				   if True check only filtered users returned by getUsers method.
	 * @return bool
	 */
	public function inGroup($user, $includeAll=false)
	{
		$users = $this->getUsers();
		if ($includeAll) $users = $this->users()->get();
		
		foreach ($users as $u) {
			if ($u->getKey() == $user->getKey())
				return true;
		}
	
		return false;
	}	
	
	// TODO : Group acceptance maybe should be move to an extend 
	// version of RainLab\User\Models\User version

	/**
	 * User accept invite
	 * @param RainLab\User\Models\User $user
	 * @return bool
	 */
	public function acceptMembership(&$user)
	{
		return $this->setMembershipStatus($user, self::MEMBERSHIP_ACCEPTED);
	}

	/**
	 * User accept invite
	 * @param RainLab\User\Models\User $user
	 * @return bool
	 */
	public function rejectMembership(&$user)
	{
		return $this->setMembershipStatus($user, self::MEMBERSHIP_REJECTED);
	}	

	/**
	 * User accept invite
	 * @param RainLab\User\Models\User $user
	 * @return bool
	 */
	public function cancelMembership(&$user)
	{	        
		return $this->setMembershipStatus($user, self::MEMBERSHIP_CANCELLED);
	}
	
	/**
	 * See if the user is in the group.
	 * @param RainLab\User\Models\User $user
	 * @param string PENDING, ACCEPTED, REJECTED, CANCELLED
	 * @return bool
	 */
	private function setMembershipStatus(&$user, $status)
	{
		if ($this->inGroup($user)){
						
			if ($user->pivot->membership_status != $status){
				$user->pivot->membership_status = $status;
				$user->pivot->save();
				
				// Clean cache of groups
				$this->groupUsers = null;
				
				// Send notification to group owners if the status
				// is different to PENDING. That notification is send by addUser.
				if ($status != self::MEMBERSHIP_PENDING){
					$notificationName = strtolower($status);
					$this->sendNotification($this->owner, $notificationName);
				}
			}else{
				// Return current user status
				return $user->pivot->membership_status; 
			}
			return $status;
		}
	}
	/**
	 * 
	 * @param RainLab\User\Models\User $user $user
	 * @param string $notificationName
	 * @return boolean|Ambigous <boolean, void>
	 */
	public function sendNotification(&$user, $notificationName)
	{
		// Check if the user is part of the group or the owner.
		if (!$this->inGroup($user)) 
			if($this->owner->getKey() != $user->getKey()) 
				return false;
		
		// TODO : implement other channels
		$channel = 'mail';
		if($channel == 'mail'){
			if (!$mailTemplate = Settings::get('mail_group_'. strtolower($notificationName) . '_template'))				
				return $this->sendEmailNotification($user, $mailTemplate);		
		}elseif ($channel == 'text'){
			//if (!$textTemplate = Settings::get('text_group_'. strtolower($notificationName) . '_template'))
			//	return $this->sendTXTNotification($user, $textTemplate);			
		}elseif ($channel == 'kiosk'){
			$kiosk = null; // Get kioks from settings
			//if (!$template = Settings::get('kiosk_group_'. strtolower($notificationName) . '_template'))
			//	return $this->sendKioskNotification($user, $template, $kiosk);
				
		}
		
		return false;
	}
	
	
	/**
	 * Send email notification to the given user.
	 * @param RainLab\User\Models\User $user
	 * @param string $mailTemplate view name of the email.
	 * @return bool
	 */
	protected function sendEmailNotification(&$user, $mailTemplate){
		if (!$this->inGroup($user)) return false;
			
		$data = [
			'user'   => $user->name,
			'owner'  => $this->owner->name
		];
	
		if (!$mailTemplate)
			return;
	
		if(\Mail::send($mailTemplate, $data, function($message) use ($user)
		{
			$message->to($user->email, $user->name);
		}) == 1){
			return true;
		}
		return false;
	}	


	/**
	 * Send text notification to the given user.
	 * @param RainLab\User\Models\User $user
	 * @param string $textTemplate view name of the email.
	 * @return bool
	 */
	protected function sendTXTNotification(&$user, $textTemplate){
		// TODO : Implmemented
		return false;
	}
	
	/**
	 * Send notification to Kiosk to the given user.
	 * @param RainLab\User\Models\User $user
	 * @param string $template message template 
	 * @param DMA\Friends\Models\Kiosk $kiosk 
	 * @return bool
	 */
	protected function sendKioskNotification(&$user, $template, $kiosk){
		// TODO : Implmemented
		return false;
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