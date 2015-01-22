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

class GroupJoinCodeForm extends ComponentBase
{
    
    private $user;
    
    
    
    public function componentDetails()
    {
        return [
            'name'        => 'User group join code form',
            'description' => 'Join current logged user to a group using its code'
        ];
    }
    
    public function defineProperties()
    {
        return [            
            'afterJoinRedirectTo' => [
                    'title'     => 'Redirect to after join group',
                    'type'      => 'dropdown'
            ],
        ];
    }
    
    protected function getuser()
    {
        $this->user = Auth::getUser();
        return $this->user;
    }

    
    protected function prepareVars($vars = [])
    {

        foreach($vars as $key => $value){
            // Append or refresh extra variables
            $this->page[$key] = $value;
        }

                   
    }

    public function onRun()
    {
        // Inject CSS and JS
        //$this->addCss('components/grouprequest/assets/css/group.request.css');
        //$this->addJs('components/grouprequest/assets/js/group.request.js');

    	// Populate users and other variables
    	$this->prepareVars();
    }    
   
    
    /**
     * Ajax handler for accept request
     */
    public function onSubmit(){

        if (($code = post('code')) != ''){
            $user = $this->getuser();
            UserGroup::joinByCode($code, $user);
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
    
    
    public function getAfterJoinRedirectToOptions()
    {
    	return $this->getListPages();
    }    
  
}