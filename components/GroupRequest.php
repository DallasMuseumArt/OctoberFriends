<?php namespace DMA\Friends\Components;

use Request;
use Auth;
use Redirect; 
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;

use System\Classes\ApplicationException;
use DMA\Friends\Models\Settings;
use DMA\Friends\Models\UserGroup;
use RainLab\User\Models\Settings as UserSettings;

class GroupRequest extends ComponentBase
{
    
    private $user;
    
    
    
    public function componentDetails()
    {
        return [
            'name'        => 'User group request',
            'description' => 'List all active group request'
        ];
    }
    
    public function defineProperties()
    {
        return [            
            'noUserRedirectTo' => [
                    'title'     => 'Redirect anonymous users to',
                    'type'      => 'dropdown'
            ],
        ];
    }
    
    protected function getuser()
    {
        $this->user = Auth::getUser();
        return $this->user;
    }
    
    protected function getGroupRequest()
    {
        $user = $this->getuser();
        if(!is_null($user)){
            
            // 27/010/2014  : I know this is kind of ugly $user::find instead of User::find but 
            // at this stage I don't have idea what user model we are going to use RainLab\User or DMA\Friends
            // TODO : this logic should go to in to User model
            $groups = $user->groups()
                           ->where('is_active', true)
                           ->where(function($query) use ($user){
                                $status = [ UserGroup::MEMBERSHIP_PENDING, 
                                            UserGroup::MEMBERSHIP_ACCEPTED,
                                            UserGroup::MEMBERSHIP_CANCELLED
                                ];
                                $query->whereIn('membership_status', $status);
                           })->get();
            return $groups;
        }        
        return [];
    }
    
    protected function prepareVars($vars = [])
    {
        // Refresh group list
        $this->page['groups'] = $this->getGroupRequest();
        $this->page['hasActiveMemberships'] = UserGroup::hasActiveMemberships($this->getuser());
        
        // UI group options
        $this->page['options'] = [ 
            UserGroup::MEMBERSHIP_ACCEPTED => 'join',
            UserGroup::MEMBERSHIP_REJECTED => 'ignore',
            UserGroup::MEMBERSHIP_CANCELLED => 'leave'
        ];
        

        foreach($vars as $key => $value){
            // Append or refresh extra variables
            $this->page[$key] = $value;
        }

                   
    }

    public function onRun()
    {
        // Inject CSS and JS
        $this->addCss('components/grouprequest/assets/css/group.request.css');
        $this->addJs('components/grouprequest/assets/js/group.request.js');
        
    	if($user = $this->getUser()){
    	
    		// Populate users and other variables
    		$this->prepareVars();
    	}else{
    		if($goTo = $this->property('noUserRedirectTo')){
    			return Redirect::to($goTo);
    		}
    	}    	
    }    
   
    
    /**
     * Ajax handler for accept request
     */
    public function onChangeStatus(){
                
        if (($groupId = post('groupId')) != ''){
            
            $group = UserGroup::findOrFail($groupId);
            
            if (($status = post('status')) != ''){
                $user = $this->getuser();
                
                switch ($status){
                    case UserGroup::MEMBERSHIP_ACCEPTED:
                        $group->acceptMembership($user);
                        break;
                        
                    case UserGroup::MEMBERSHIP_REJECTED:
                    	$group->rejectMembership($user);
                    	break;
                    	
                	case UserGroup::MEMBERSHIP_CANCELLED:
                		$group->cancelMembership($user);
                		break;                        	                        
                       
                } 
            }
        }
        
        
        // Updated list of request and other vars
        $this->prepareVars();
    }
  
    ###
    # OPTIONS
    ##
    
    private function getListPages()
    {
    	$pages = Page::sortBy('baseFileName')->lists('baseFileName', 'url');
    	return [''=>'- none -'] + $pages;
    }
    
    
    public function getNoUserRedirectToOptions()
    {
    	return $this->getListPages();
    }    
  
}