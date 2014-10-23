<?php namespace DMA\Friends\Components;

use Request;
use Cms\Classes\ComponentBase;
use DMA\Friends\Models\Settings;
use DMA\Friends\Models\UserGroup;
use DMA\Friends\Models\User;
use System\Classes\ApplicationException;

class GroupFormCreation extends ComponentBase
{
    
    public function componentDetails()
    {
        return [
            'name'        => 'Group creation form',
            'description' => 'Form use for users to create groups'
        ];
    }
    
    public function defineProperties()
    {
        return [
            'maxUserSuggestions' => [
                'title'     => 'Limit list user autocomplete',
                'default'   => 10,
            ]
        ];
    }
    
    /**
     * @return DMA\Friends\Models\UserGroup
     */
    protected function getGroup(){
        // use for testing
        return UserGroup::find(1);
    }
    
    //protected function refreshGroupUsers()

    public function onRun()
    {
        // Inject CSS and JS
        $this->addCss('components/groupformcreation/assets/css/group.creation.css');
        $this->addJs('components/groupformcreation/assets/js/group.creation.js');
        
        // Populate users
    	$group = $this->getGroup();
    	if ($group){
    		$this->page['users'] = $group->getUsers()->toArray();
    	}
    
    }    
    
    /**
     * Ajax handler for adding members
     */
    public function onAdd(){
        $users = post('users', []);
        $maxUsers = Settings::get('maximum_users_group');
        
        if (count($users) >= $maxUsers)
        	throw new \Exception(sprintf('Sorry only %s members per group are allowed.', $maxUsers));
        
        
        // Add to group
        $group = $this->getGroup();
        
        if (($newUser = post('newUser')) != ''){
            $user = User::where('email', '=', $newUser)->first();
            if ($user){
                $group->addUser($user);
            }else{
                throw new \Exception('User not found.');
            }
            
        }
        // Updated list of users
        $this->page['users'] = $group->getUsers()->toArray();
    }
    
    /**
     * Ajax handler for adding members
     */
    public function onDelete(){
    	if (($removeUser = post('removeUser')) != ''){
    		$user = User::find($removeUser);
    		if ($user){
    			// remove from group
    			$group = $this->getGroup();
    			$group->removeUser($user);
    
    			// Return updated list of users
    			$this->page['users'] = $group->getUsers()->toArray();
    
    		}else{
    			throw new \Exception('User not found.');
    		}
    	}
    }
    
    /**
     * Ajax handler for searching users
     */
    public function onSearchUser(){
        // Suggest usernames
        if (($search = post('newUser')) != ''){
            $users = User::where('email', 'LIKE', "$search%")
                    ->orWhere('name', 'LIKE', "%$search%")
                    ->take($this->property('maxUserSuggestions'))
                    ->get();
            
            $this->page['users'] = $users->toArray();
        }
    }    
    
    
}