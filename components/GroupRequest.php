<?php namespace DMA\Friends\Components;

use Request;
use Auth;
use Redirect; 
use Cms\Classes\ComponentBase;

use System\Classes\ApplicationException;
use DMA\Friends\Models\Settings;
use DMA\Friends\Models\UserGroup;
use DMA\Friends\Models\User;
use RainLab\User\Models\Settings as UserSettings;

class GroupRequest extends ComponentBase
{
    
    private $user;
    
    public function componentDetails()
    {
        return [
            'name'        => 'User group request',
            'description' => 'List all request to join a group'
        ];
    }
    
    public function defineProperties()
    {
        return [];
    }
    
    protected function getuser()
    {
        $this->user = Auth::getUser();
        return $this->user;
    }
    
    protected function getGroups()
    {
        $user = $this->getuser();
        if(!is_null($user)){
            return $user->groups();
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
        $this->addCss('components/grouprequest/assets/css/group.request.css');
        $this->addJs('components/grouprequest/assets/js/group.request.js');
        
        // Populate page user and other variables
    	$this->prepareVars();
    
    }    
    
    /**
     * Ajax handler for accept request
     */
    public function onAccept(){

        // Updated list of request and other vars
        $this->prepareVars($group);
    }
  
}