<?php namespace DMA\Friends\Components;

use Auth;
use Request;
use Redirect; 
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;

use System\Classes\ApplicationException;
use DMA\Friends\Models\Settings;
use DMA\Friends\Models\UserGroup;
use Rainlab\User\Models\User;
use RainLab\User\Models\Settings as UserSettings;
use October\Rain\Support\Facades\Flash;


class GroupManager extends ComponentBase
{
    
    /**
     * @var string RainLab.User pluging username field
     */
    private $loginAttr;
    
    /**
     * @var RainLab\User\Models\User
     */
    private $user = null;
    
    public function componentDetails()
    {
        return [
            'name'        => 'Group management',
            'description' => ''
        ];
    }
    
    public function defineProperties()
    {
        return [
            'newGroupRedirectTo' => [
                'title'     => 'Redirect to page after create group',
                'type'      => 'dropdown',
             ]
        ];
    }
    
    /**
     * @return RainLab\User\Models\User
     */
    protected function getUser()
    {
        if(is_null($this->user)){
            $this->user = Auth::getUser();
        }
        return $this->user;
    }
    
    /**
     * @return DMA\Friends\Models\UserGroup
     */
    protected function getGroups()
    {
        if($user = $this->getUser()){
            $groups = UserGroup::where('owner_id', $user->getKey())
                                ->isActive()->get();
            return $groups;   
        }
        return [];
    }
    
  
    protected function prepareVars($vars = [])
    {
        
        // Refresh group list
        $this->page['groups'] = $this->getGroups();

        
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
     * Ajax handler for adding new groups
     */
    public function onSubmit(){
        if( $user = $this->getUser() ){
            if( !empty( $name = post('name'))){
                try{
                    $group = UserGroup::createGroup($user, $name);
                }catch(\Exception $e){
                    Flash::error($e->getMessage());
                }
            }
        }

        // Updated list of grous and other vars
        $this->prepareVars();

    }
    
    /**
     * Ajax handler for adding members
     */
    public function onCancel(){
    	if (($groupId = post('groupId')) != ''){
    		if($group = UserGroup::find($groupId)){
    			// mark as inactive  group
    			$group->is_active = false;
    			$group->save();
    
                // Updated list of groups and other vars
                $this->prepareVars($group);
    
    		}else{
    			throw new \Exception('Group not found.');
    		}
    	}
    }
       
    
    /**
     * Create a new group
     */
    public function CreateGroups()
    {
        if ($user = $this->getUser()){
                      
            $group = new UserGroup();
            $group->owner = $user;
            
            $group->save();
            
            $goTo = $this->property('newGroupRedirectTo');
            $goTo = (trim($goTo) === '' || !isset($goTo)) ? $_SERVER['PHP_SELF'] : $goTo;
            return Redirect::to($goTo);
            
        }
    }
    
    ###
    # OPTIONS
    ##
    
    private function getListPages()
    {
        $pages = Page::sortBy('baseFileName')->lists('baseFileName', 'url');
        return [''=>'- none -'] + $pages;
    }   

}