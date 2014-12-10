<?php namespace DMA\Friends\Components;

use Request;
use Auth;
use Redirect; 
use Cms\Classes\ComponentBase;

use System\Classes\ApplicationException;
use DMA\Friends\Models\Settings;
use DMA\Friends\Models\UserGroup;
use Rainlab\User\Models\User;
use RainLab\User\Models\Settings as UserSettings;

class GroupFormCreation extends ComponentBase
{
    
    /**
     * @var string RainLab.User pluging username field
     */
    private $loginAttr;
    
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
            ],
            'noUserRedirectTo' => [
                'title'     => 'Redirect anonymous users to',
                'type'      => 'dropdown',
                'default'   => '/'
            ]            
        ];
    }
    
    /**
     * @return DMA\Friends\Models\UserGroup
     */
    protected function getGroup()
    {
        $user = Auth::getUser();
        
        if (!is_null($user)){
            //if (!UserGroup::hasActiveMemberships($user)){
                //1. If user don't have an active membership create a group for it
                $group = UserGroup::where('owner_id', $user->getKey())
                            ->where('is_active', true)
                            ->first();
                return $group;
            //}
        }
    }
    
    protected function getLoginAttr()
    {
        if (is_null($this->loginAttr)){
            $this->loginAttr = UserSettings::get('login_attribute', UserSettings::LOGIN_EMAIL);
        }
        return $this->loginAttr;
    }
    
    protected function prepareVars($group = null, $vars = [])
    {
        // Refresh group list
        $this->page['users'] = (!is_null($group)) ? $group->getUsers()->toArray() : [];
        
        $this->page['owner'] = (!is_null($group)) ? $group->owner : Null;
        
        // Allow to add more users
        $this->page['allowAdd'] =  count($this->page['users']) < Settings::get('maximum_users_group');

        // Get login attribute configured in RainLab.User plugin
        $this->page['loginAttr'] = $this->getLoginAttr();
        
        foreach($vars as $key => $value){
            // Append or refresh extra variables
            $this->page[$key] = $value;
        }

                   
    }

    public function onRun()
    {
        // Inject CSS and JS
        $this->addCss('components/groupformcreation/assets/css/group.creation.css');
        $this->addJs('components/groupformcreation/assets/js/group.creation.js');
        
        // Populate users and other variables
    	$group = $this->getGroup();
    	$this->prepareVars($group);
    
    }    
    
    /**
     * Ajax handler for adding members
     */
    public function onAdd(){
        $users = post('users', []);
        $maxUsers = Settings::get('maximum_users_group');
        
        if (count($users) >= $maxUsers)
            //trans('dma.friends::lang.group.invalid_activation_code')
        	throw new \Exception(sprintf('Sorry only %s members per group are allowed.', $maxUsers));
        
        
        // Add to group
        $group = $this->getGroup();

        
        if (($newUser = post('newUser')) != ''){
            $user = User::where($this->getLoginAttr(), '=', $newUser)->first();
            if ($user){
                $group->addUser($user);
            }else{
                throw new \Exception('User not found.');
            }
            
        }
        
        // Updated list of users and other vars
        $this->prepareVars($group);
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
    
                // Updated list of users and other vars
                $this->prepareVars($group);
    
    		}else{
    			throw new \Exception('User not found.');
    		}
    	}
    }
    
    /**
     * Ajax handler for searching users
     */
    public function onSearchUser(){        
        // Suggest exsiting members
        if (($search = post('newUser')) != ''){
   
            $users = User::where($this->getLoginAttr(), 'LIKE', "$search%")
                    ->orWhere('name', 'LIKE', "$search%")
                    ->take($this->property('maxUserSuggestions'))
                    ->get();
             
            /*
            $users = User::groups()
                    ->wherePivot('membership_status', '<>', UserGroup::MEMBERSHIP_ACCEPTED )
                    ->with('groups')
                    ->whereHas('groups', function($query){
                        $query->where('is_active', true);
                    })
                   
                    ->get();
            */
            // Updated list of users and other vars
            $this->prepareVars(null, ['search' => $search, 'users'=>$users]);            
        }
    }    
    
    
    ###
    # OPTIONS
    ##
    
    public function getNoUserRedirectToOptions()
    {
    	return [''=>'- none -'] + Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }    
}